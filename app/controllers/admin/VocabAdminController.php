<?php

declare(strict_types=1);

class VocabAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->guard();
        $this->fillMissingKnownVocabulary(Database::getConnection());
        $result = $this->paginate(
            'SELECT COUNT(*) FROM vocabulary',
            'SELECT * FROM vocabulary ORDER BY word'
        );
        $this->page('vocab', 'إدارة المفردات', $result);
    }

    private function fillMissingKnownVocabulary(PDO $pdo): void
    {
        $rows = $pdo->query(
            'SELECT id,word,meaning_ar,synonym,antonym,example FROM vocabulary'
        )->fetchAll();
        if (!$rows) {
            return;
        }
        $update = $pdo->prepare(
            'UPDATE vocabulary SET meaning_ar=:meaning_ar,synonym=:synonym,
             antonym=:antonym,example=:example WHERE id=:id'
        );
        foreach ($rows as $row) {
            $hasArabicMeaning = preg_match('/[\x{0600}-\x{06FF}]/u', (string) ($row['meaning_ar'] ?? '')) === 1;
            $hasCompleteRelations = trim((string) ($row['synonym'] ?? '')) !== ''
                && trim((string) ($row['antonym'] ?? '')) !== ''
                && trim((string) ($row['example'] ?? '')) !== '';
            if ($hasArabicMeaning && $hasCompleteRelations) {
                continue;
            }
            $item = VocabularyEnhancer::enrich($row);
            $update->execute([
                'id' => $row['id'],
                'meaning_ar' => $item['meaning_ar'],
                'synonym' => $item['synonym'] !== '' ? $item['synonym'] : null,
                'antonym' => $item['antonym'] !== '' ? $item['antonym'] : null,
                'example' => $item['example'],
            ]);
        }
    }

    public function save(): void
    {
        $this->handlePost(function (): void {
            $pdo = Database::getConnection();
            $action = $_POST['action'] ?? 'save';
            if (in_array($action, ['bulk_delete', 'bulk_activate', 'bulk_deactivate'], true)) {
                $ids = $this->selectedIds($_POST['ids'] ?? []);
                if (!$ids) {
                    throw new InvalidArgumentException('اختر مفردة واحدة على الأقل.');
                }
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                if ($action === 'bulk_delete') {
                    $pdo->beginTransaction();
                    try {
                        $pdo->prepare("DELETE FROM vocab_progress WHERE vocab_id IN ($placeholders)")->execute($ids);
                        $pdo->prepare("DELETE FROM vocabulary WHERE id IN ($placeholders)")->execute($ids);
                        $pdo->commit();
                    } catch (Throwable $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        throw $e;
                    }
                } else {
                    $isActive = $action === 'bulk_activate' ? 1 : 0;
                    $pdo->prepare("UPDATE vocabulary SET is_active=? WHERE id IN ($placeholders)")->execute(array_merge([$isActive], $ids));
                }
                return;
            }
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? $this->positiveId($_POST['id']) : null;
            if ($action === 'delete') {
                if (!$id) {
                    throw new InvalidArgumentException('المفردة المطلوبة غير صالحة.');
                }

                $pdo->prepare('DELETE FROM vocab_progress WHERE vocab_id = :id')->execute(['id' => $id]);
                $pdo->prepare('DELETE FROM vocabulary WHERE id = :id')->execute(['id' => $id]);
                return;
            }

            $values = VocabularyEnhancer::enrich([
                'word' => $this->requiredText('word', 'الكلمة', 255),
                'meaning_ar' => $this->optionalText('meaning_ar', 2000),
                'synonym' => $this->optionalText('synonym', 255),
                'antonym' => $this->optionalText('antonym', 255),
                'example' => $this->optionalText('example'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ]);
            if ($id) {
                $this->assertExists('vocabulary', $id, 'المفردة');
                $values['id'] = $id;
                $stmt = $pdo->prepare(
                    'UPDATE vocabulary SET word=:word,meaning_ar=:meaning_ar,synonym=:synonym,
                     antonym=:antonym,example=:example,is_active=:is_active WHERE id=:id'
                );
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO vocabulary (word,meaning_ar,synonym,antonym,example,is_active)
                     VALUES (:word,:meaning_ar,:synonym,:antonym,:example,:is_active)'
                );
            }
            $stmt->execute($values);
        }, '/admin/vocab');
    }

    public function importCsvFromCenter(): void
    {
        $this->handlePost(function (): void {
            $this->importCsv(Database::getConnection());
        }, '/admin/imports');
    }

    private function selectedIds($values): array
    {
        if (!is_array($values)) {
            return [];
        }
        $ids = [];
        foreach ($values as $value) {
            $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $ids[(int) $id] = (int) $id;
            }
        }
        return array_values($ids);
    }

    private function importCsv(PDO $pdo): void
    {
        $file = $_FILES['csv'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('اختر ملف CSV صالحاً.');
        }
        if ((int) $file['size'] > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('الحد الأقصى لملف CSV هو 2 ميجابايت.');
        }
        $filename = basename((string) $file['name']);
        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'csv') {
            throw new InvalidArgumentException('يجب أن يكون الملف بصيغة CSV.');
        }
        $handle = fopen($file['tmp_name'], 'rb');
        if ($handle === false) {
            throw new InvalidArgumentException('تعذر قراءة ملف CSV.');
        }

        $pdo->beginTransaction();
        try {
            $insert = $pdo->prepare(
                'INSERT INTO vocabulary (word,meaning_ar,synonym,antonym,example,is_active)
                 VALUES (:word,:meaning_ar,:synonym,:antonym,:example,1)'
            );
            $existingWords = $pdo->query('SELECT word FROM vocabulary')->fetchAll(PDO::FETCH_COLUMN);
            $knownWords = [];
            foreach ($existingWords as $existingWord) {
                $knownWords[mb_strtolower(trim((string) $existingWord), 'UTF-8')] = true;
            }
            $rowNumber = 0;
            $itemsFound = 0;
            $itemsInserted = 0;
            $duplicatesSkipped = 0;
            while (($row = fgetcsv($handle, 10000, ',', '"', '\\')) !== false) {
                $rowNumber++;
                if ($rowNumber === 1) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($row[0] ?? ''));
                    if (strtolower(trim((string) $row[0])) === 'word') {
                        continue;
                    }
                }
                $itemsFound++;
                if ($itemsFound > 1000) {
                    throw new InvalidArgumentException('الحد الأقصى للاستيراد 1000 صف.');
                }
                if (count($row) < 2 || count($row) > 5) {
                    throw new InvalidArgumentException('عدد أعمدة CSV غير صحيح في الصف ' . $rowNumber . '.');
                }
                $word = trim((string) $row[0]);
                $meaning = trim((string) $row[1]);
                if ($word === '' || mb_strlen($word, 'UTF-8') > 255) {
                    throw new InvalidArgumentException('الكلمة مطلوبة في الصف ' . $rowNumber . '.');
                }
                $wordKey = mb_strtolower($word, 'UTF-8');
                if (isset($knownWords[$wordKey])) {
                    $duplicatesSkipped++;
                    continue;
                }
                $item = VocabularyEnhancer::enrich([
                    'word' => $word,
                    'meaning_ar' => $meaning,
                    'synonym' => trim((string) ($row[2] ?? '')),
                    'antonym' => trim((string) ($row[3] ?? '')),
                    'example' => trim((string) ($row[4] ?? '')),
                ]);
                $insert->execute([
                    'word' => $item['word'],
                    'meaning_ar' => $item['meaning_ar'],
                    'synonym' => $item['synonym'] !== '' ? $item['synonym'] : null,
                    'antonym' => $item['antonym'] !== '' ? $item['antonym'] : null,
                    'example' => $item['example'] ?: null,
                ]);
                $knownWords[$wordKey] = true;
                $itemsInserted++;
            }
            if ($itemsFound === 0) {
                throw new InvalidArgumentException('ملف CSV لا يحتوي على مفردات للاستيراد.');
            }
            $log = $pdo->prepare(
                'INSERT INTO pdf_imports(filename,type,status,items_found,items_inserted,duplicates_skipped,failed)
                 VALUES(:filename,\'vocabulary-csv\',\'completed\',:found,:inserted,:duplicates,0)'
            );
            $log->execute([
                'filename' => $filename,
                'found' => $itemsFound,
                'inserted' => $itemsInserted,
                'duplicates' => $duplicatesSkipped,
            ]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        } finally {
            fclose($handle);
        }
    }
}