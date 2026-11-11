<?php

declare(strict_types=1);

class PdfImportAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->guard();
        $pdo = Database::getConnection();
        $this->page('imports', 'مركز الاستيراد', [
            'practices' => $pdo->query(
                'SELECT p.id,p.title_ar,s.name_ar AS section_name FROM practices p
                 JOIN sections s ON s.id=p.section_id ORDER BY s.sort_order,p.sort_order'
            )->fetchAll(),
            'sections' => $pdo->query('SELECT id,name_ar FROM sections ORDER BY sort_order')->fetchAll(),
            'skills' => $pdo->query('SELECT id,name_ar FROM skills ORDER BY name_ar')->fetchAll(),
            'imports' => $pdo->query(
                'SELECT * FROM pdf_imports ORDER BY created_at DESC LIMIT 20'
            )->fetchAll(),
        ]);
    }

    public function preview(): void
    {
        $this->guard();
        $this->requirePost('/admin/imports');
        @set_time_limit(600);
        try {
            $file = $_FILES['pdf'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                throw new InvalidArgumentException('اختر ملف PDF صالحاً.');
            }
            if ((int) $file['size'] > 15 * 1024 * 1024) {
                throw new InvalidArgumentException('الحد الأقصى لحجم PDF هو 15 ميجابايت.');
            }
            $name = basename((string) $file['name']);
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'pdf') {
                throw new InvalidArgumentException('يجب أن يكون الملف بصيغة PDF.');
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (!in_array($finfo->file($file['tmp_name']), ['application/pdf', 'application/x-pdf'], true)) {
                throw new InvalidArgumentException('نوع الملف المرفوع ليس PDF صالحاً.');
            }

            $directory = ROOT_PATH . '/storage/imports';
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('تعذر تجهيز مجلد الاستيراد.');
            }
            $stored = $directory . '/' . bin2hex(random_bytes(16)) . '.pdf';
            if (!move_uploaded_file($file['tmp_name'], $stored)) {
                throw new RuntimeException('تعذر حفظ ملف PDF مؤقتاً.');
            }

            $audioFiles = [];
            if (!empty($_FILES['audio_files']['name'])) {
                $audioDirectory = ROOT_PATH . '/public/uploads/audio';
                if (!is_dir($audioDirectory) && !mkdir($audioDirectory, 0755, true) && !is_dir($audioDirectory)) {
                    throw new RuntimeException('تعذر تجهيز مجلد الملفات الصوتية.');
                }
                foreach ((array) $_FILES['audio_files']['name'] as $audioIndex => $audioName) {
                    $audioError = (int) ($_FILES['audio_files']['error'][$audioIndex] ?? UPLOAD_ERR_NO_FILE);
                    if ($audioError === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    if ($audioError !== UPLOAD_ERR_OK) {
                        throw new InvalidArgumentException('تعذر رفع أحد الملفات الصوتية.');
                    }
                    $audioTmp = (string) ($_FILES['audio_files']['tmp_name'][$audioIndex]);
                    if (!is_uploaded_file($audioTmp)) {
                        throw new InvalidArgumentException('تعذر التحقق من أحد الملفات الصوتية.');
                    }
                    if ((int) ($_FILES['audio_files']['size'][$audioIndex] ?? 0) > 30 * 1024 * 1024) {
                        throw new InvalidArgumentException('الحد الأقصى لحجم كل ملف صوتي 30 ميجابايت.');
                    }
                    $audioExtension = strtolower(pathinfo((string) $audioName, PATHINFO_EXTENSION));
                    $allowedAudio = ['mp3', 'wav', 'm4a', 'ogg'];
                    if (!in_array($audioExtension, $allowedAudio, true)) {
                        throw new InvalidArgumentException('الملفات الصوتية المدعومة: MP3 وWAV وM4A وOGG.');
                    }
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $audioMime = (string) $finfo->file($audioTmp);
                    if (!preg_match('/^audio\\/(mpeg|mp3|wav|x-wav|mp4|x-m4a|ogg)|^application\\/ogg/', $audioMime)) {
                        throw new InvalidArgumentException('نوع أحد الملفات الصوتية غير صالح.');
                    }
                    $storedAudioName = bin2hex(random_bytes(16)) . '.' . $audioExtension;
                    if (!move_uploaded_file($audioTmp, $audioDirectory . '/' . $storedAudioName)) {
                        throw new RuntimeException('تعذر حفظ أحد الملفات الصوتية.');
                    }
                    $audioFiles[] = [
                        'name' => basename((string) $audioName),
                        'path' => 'uploads/audio/' . $storedAudioName,
                    ];
                }
            }

            $python = getenv('PYTHON_BIN') ?: 'python';
            $script = ROOT_PATH . '/tools/pdf_extract.py';
            $command = escapeshellarg($python) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($stored);
            $embeddedDirectory = $directory . '/' . bin2hex(random_bytes(12)) . '-audio';
            if (!mkdir($embeddedDirectory, 0755, true) && !is_dir($embeddedDirectory)) {
                throw new RuntimeException('تعذر تجهيز مجلد الصوتيات المضمنة.');
            }
            $command .= ' ' . escapeshellarg($embeddedDirectory);
            $json = shell_exec($command) ?: '';
            $data = json_decode(trim($json), true);
            if (!is_array($data) || !($data['ok'] ?? false)) {
                throw new RuntimeException(($data['error'] ?? null) ?: 'تعذر استخراج نص PDF.');
            }
            foreach ((array) ($data['embedded_audio'] ?? []) as $embeddedName) {
                $source = $embeddedDirectory . '/' . $embeddedName;
                if (!is_file($source)) {
                    continue;
                }
                $extension = strtolower(pathinfo($embeddedName, PATHINFO_EXTENSION));
                $publicDirectory = ROOT_PATH . '/public/uploads/audio';
                if (!is_dir($publicDirectory) && !mkdir($publicDirectory, 0755, true) && !is_dir($publicDirectory)) {
                    throw new RuntimeException('تعذر تجهيز مجلد الصوتيات.');
                }
                $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
                if (!copy($source, $publicDirectory . '/' . $storedName)) {
                    throw new RuntimeException('تعذر نسخ أحد التسجيلات المضمنة.');
                }
                $audioFiles[] = [
                    'name' => $embeddedName,
                    'path' => 'uploads/audio/' . $storedName,
                ];
            }

            foreach ((array) ($data['media_links'] ?? []) as $mediaLink) {
                if (!preg_match('#drive\.google\.com/(?:drive/(?:u/\d+/)?folders|embeddedfolderview)/([A-Za-z0-9_-]+)#', $mediaLink, $matches)) {
                    continue;
                }
                $driveDirectory = $directory . '/' . bin2hex(random_bytes(12)) . '-drive';
                $driveScript = ROOT_PATH . '/tools/drive_audio_fetch.py';
                $driveResultFile = $driveDirectory . '.json';
                $driveCommand = escapeshellarg($python) . ' -u ' . escapeshellarg($driveScript) . ' ' . escapeshellarg($matches[1]) . ' ' . escapeshellarg($driveDirectory)
                    . ' ' . escapeshellarg($driveResultFile) . ' >NUL 2>&1';
                shell_exec($driveCommand);
                $driveOutput = is_file($driveResultFile) ? (string) file_get_contents($driveResultFile) : '';
                $driveJson = json_decode($driveOutput, true);
                if (!is_array($driveJson)) {
                    $firstBrace = strpos($driveOutput, '{');
                    $lastBrace = strrpos($driveOutput, '}');
                    if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                        $driveJson = json_decode(substr($driveOutput, $firstBrace, $lastBrace - $firstBrace + 1), true);
                    }
                }
                if (!is_array($driveJson) || !($driveJson['ok'] ?? false)) {
                    $driveError = is_array($driveJson) ? ($driveJson['error'] ?? '') : '';
                    throw new RuntimeException('تعذر جلب الملفات الصوتية من Google Drive.' . ($driveError !== '' ? ' السبب: ' . $driveError : ' خرج البرنامج: ' . mb_substr(trim($driveOutput), 0, 500)));
                }
                foreach ((array) ($driveJson['files'] ?? []) as $driveFile) {
                    $source = $driveDirectory . '/' . $driveFile['stored_name'];
                    if (!is_file($source)) {
                        continue;
                    }
                    $extension = strtolower(pathinfo((string) $driveFile['stored_name'], PATHINFO_EXTENSION));
                    $publicDirectory = ROOT_PATH . '/public/uploads/audio';
                    if (!is_dir($publicDirectory) && !mkdir($publicDirectory, 0755, true) && !is_dir($publicDirectory)) {
                        throw new RuntimeException('تعذر تجهيز مجلد الملفات الصوتية في المنصة.');
                    }
                    $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
                    if (!copy($source, $publicDirectory . '/' . $storedName)) {
                        throw new RuntimeException('تعذر حفظ أحد التسجيلات التي تم جلبها من Drive.');
                    }
                    $audioFiles[] = [
                        'name' => (string) $driveFile['name'],
                        'path' => 'uploads/audio/' . $storedName,
                    ];
                }
            }

            $items = $this->markDuplicates((string) $data['type'], $data['items'] ?? []);
            if ((string) $data['type'] === 'vocab') {
                foreach ($items as &$previewItem) {
                    $previewItem = VocabularyEnhancer::enrich($previewItem);
                }
                unset($previewItem);
            }
            $previewName = bin2hex(random_bytes(12)) . '.json';
            foreach (array_values($items) as $itemIndex => &$previewItem) {
                $previewItem['index'] = $itemIndex;
            }
            unset($previewItem);
            file_put_contents($directory . '/' . $previewName, json_encode([
                'name' => $name,
                'type' => (string) $data['type'],
                'items' => $items,
                'audio_files' => $audioFiles,
                'media_links' => array_values((array) ($data['media_links'] ?? [])),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $_SESSION['pdf_import_preview'] = $directory . '/' . $previewName;
            redirect('/admin/imports');
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/imports');
        } catch (Throwable $e) {
            error_log('PDF preview failed: ' . $e->getMessage());
            flash('error', $e->getMessage());
            redirect('/admin/imports');
        }
    }

    public function publish(): void
    {
        $this->guard();
        $this->requirePost('/admin/imports');
        try {
            $preview = (string) ($_SESSION['pdf_import_preview'] ?? '');
            if ($preview === '' || !str_starts_with(realpath($preview) ?: '', realpath(ROOT_PATH . '/storage/imports') ?: ROOT_PATH) || !is_file($preview)) {
                throw new InvalidArgumentException('انتهت المعاينة أو تعذر العثور عليها. أعد رفع الملف.');
            }
            $payload = json_decode((string) file_get_contents($preview), true);
            if (!is_array($payload)) {
                throw new InvalidArgumentException('بيانات المعاينة غير صالحة.');
            }
            $type = (string) ($payload['type'] ?? 'passages');
            $practiceId = $type === 'vocab' ? null : $this->positiveId($_POST['practice_id'] ?? 0, 'التدريب');
            $skillId = $type === 'questions' ? $this->positiveId($_POST['skill_id'] ?? 0, 'المهارة') : null;
            $vocabSectionId = $type === 'vocab' && !empty($_POST['section_id'])
                ? $this->positiveId($_POST['section_id'], 'القسم')
                : null;
            $selected = $_POST['publish'] ?? [];
            if (!is_array($selected)) {
                $selected = [];
            }
            $items = $payload['items'] ?? [];

            $pdo = Database::getConnection();
            $pdo->beginTransaction();
            $inserted = $duplicates = $failed = 0;
            $dbDuplicates = $this->markDuplicates($type, array_values(array_filter($items, static fn ($item) => !($item['duplicate_db'] ?? false))));
            $passageIds = [];
            if ($type === 'questions' && $practiceId) {
                foreach ($items as $item) {
                    $group = trim((string) ($item['group'] ?? ''));
                    if ($group === '' || isset($passageIds[$group])) {
                        continue;
                    }
                    $audioPath = null;
                    foreach (($payload['audio_files'] ?? []) as $audioIndex => $audioFile) {
                        if ((string) ($_POST['audio_passage_' . $audioIndex] ?? '') === $group) {
                            $audioPath = (string) $audioFile['path'];
                            break;
                        }
                    }
                    $stmt = $pdo->prepare(
                        'INSERT INTO passages(practice_id,title,body,audio_path) VALUES(:practice,:title,NULL,:audio)'
                    );
                    $stmt->execute([
                        'practice' => $practiceId,
                        'title' => $group,
                        'audio' => $audioPath,
                    ]);
                    $passageIds[$group] = (int) $pdo->lastInsertId();
                }
            }
            foreach (array_values($items) as $item) {
                if (!in_array((string) ($item['index'] ?? 0), array_map('strval', $selected), true)
                    || ($item['duplicate_db'] ?? false) || ($item['duplicate_file'] ?? false)) {
                    if (($item['duplicate_db'] ?? false) || ($item['duplicate_file'] ?? false)) {
                        $duplicates++;
                    }
                    continue;
                }
                try {
                    $passageId = $type === 'questions' ? ($passageIds[(string) ($item['group'] ?? '')] ?? null) : null;
                    $this->insertItem($pdo, $type, $item, $practiceId, $skillId, $vocabSectionId, $passageId);
                    $inserted++;
                } catch (Throwable) {
                    $failed++;
                }
            }
            $stmt = $pdo->prepare(
                'INSERT INTO pdf_imports(filename,type,status,items_found,items_inserted,duplicates_skipped,failed)
                 VALUES(:filename,:type,\'completed\',:found,:inserted,:duplicates,:failed)'
            );
            $stmt->execute([
                'filename' => (string) $payload['name'],
                'type' => $type,
                'found' => count($items),
                'inserted' => $inserted,
                'duplicates' => $duplicates,
                'failed' => $failed,
            ]);
            $pdo->commit();
            unset($_SESSION['pdf_import_preview']);
            @unlink($preview);
            flash('success', "اكتمل الاستيراد: $inserted عنصر جديد، $duplicates مكررًا، $failed فشلًا.");
            redirect('/admin/imports');
        } catch (InvalidArgumentException $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('error', $e->getMessage());
            redirect('/admin/imports');
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('PDF publish failed: ' . $e->getMessage());
            flash('error', 'تعذر حفظ عناصر PDF بسبب خطأ في البيانات أو العلاقات.');
            redirect('/admin/imports');
        }
    }

    private function markDuplicates(string $type, array $items): array
    {
        $pdo = Database::getConnection();
        $seen = [];
        $column = $type === 'vocab' ? 'word' : 'text';
        $table = $type === 'vocab' ? 'vocabulary' : 'questions';
        $existing = $pdo->query("SELECT $column FROM $table")->fetchAll(PDO::FETCH_COLUMN);
        foreach (array_values($items) as $index => &$item) {
            $item['index'] = $index;
            $keySource = $type === 'vocab' ? ((string) ($item['word'] ?? '') . '|' . (string) ($item['meaning_ar'] ?? ''))
                : (string) ($item['text'] ?? '');
            $key = mb_strtolower($this->normalize($keySource));
            $item['duplicate_file'] = isset($seen[$key]);
            if (!$item['duplicate_file']) {
                $seen[$key] = true;
            }
            $currentKey = mb_strtolower($this->normalize($type === 'vocab'
                ? (string) ($item['word'] ?? '')
                : (string) ($item['text'] ?? '')));
            $item['duplicate_db'] = false;
            foreach ($existing as $existingValue) {
                if (mb_strtolower($this->normalize((string) $existingValue)) === $currentKey) {
                    $item['duplicate_db'] = true;
                    break;
                }
            }
        }
        unset($item);
        return $items;
    }

    private function normalize(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text) ?: '');
    }

    private function insertItem(PDO $pdo, string $type, array $item, ?int $practiceId, ?int $skillId, ?int $vocabSectionId = null, ?int $passageId = null): void
    {
        if ($type === 'vocab') {
            $item = VocabularyEnhancer::enrich($item);
            $stmt = $pdo->prepare(
                'INSERT INTO vocabulary(word,meaning_ar,synonym,antonym,example,section_id,practice_id,is_active)
                 VALUES(:word,:meaning,:synonym,:antonym,:example,:section_id,NULL,1)'
            );
            $stmt->execute([
                'word' => mb_substr((string) $item['word'], 0, 255),
                'meaning' => (string) $item['meaning_ar'],
                'synonym' => $item['synonym'] ?: null,
                'antonym' => $item['antonym'] ?: null,
                'example' => $item['example'] ?: null,
                'section_id' => $vocabSectionId,
            ]);
            return;
        }
        if ($type !== 'questions' || !$practiceId || !$skillId) {
            throw new InvalidArgumentException('جهة نشر غير مكتملة.');
        }
        $stmt = $pdo->prepare(
            'INSERT INTO questions(practice_id,passage_id,skill_id,text,option_a,option_b,option_c,option_d,correct,explanation_ar,wrong_a_ar,wrong_b_ar,wrong_c_ar,wrong_d_ar,in_placement,is_active,sort_order)
             VALUES(:practice,:passage,:skill,:text,:a,:b,:c,:d,:correct,:explanation,NULL,NULL,NULL,NULL,0,1,0)'
        );
        $stmt->execute([
            'practice' => $practiceId,
            'passage' => $passageId,
            'skill' => $skillId,
            'text' => $item['text'],
            'a' => $item['option_a'] ?: '-',
            'b' => $item['option_b'] ?: '-',
            'c' => $item['option_c'] ?: '-',
            'd' => $item['option_d'] ?: '-',
            'correct' => in_array((string) ($item['correct'] ?? 'A'), ['A','B','C','D'], true) ? $item['correct'] : 'A',
            'explanation' => $item['explanation_ar'] ?? '',
        ]);
    }
}
