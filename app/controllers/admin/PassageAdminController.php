<?php

declare(strict_types=1);

class PassageAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->guard();
        $result = $this->paginate(
            'SELECT COUNT(*) FROM passages pa INNER JOIN practices p ON p.id=pa.practice_id',
            'SELECT pa.*, p.title_ar AS practice_title
             FROM passages pa INNER JOIN practices p ON p.id=pa.practice_id
             ORDER BY pa.id DESC'
        );
        $practices = Database::getConnection()->query(
            'SELECT id,title_ar FROM practices ORDER BY section_id,sort_order'
        )->fetchAll();
        $this->page('passages', 'إدارة المقاطع والصوت', $result + ['practices' => $practices]);
    }

    public function save(): void
    {
        $this->handlePost(function (): void {
            $pdo = Database::getConnection();
            $action = $_POST['action'] ?? 'save';
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? $this->positiveId($_POST['id']) : null;
            if ($action === 'delete') {
                if (!$id) {
                    throw new InvalidArgumentException('المقطع المطلوب غير صالح.');
                }
                $this->assertExists('passages', $id, 'المقطع');
                $stmt = $pdo->prepare('SELECT audio_path FROM passages WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $oldAudio = $stmt->fetchColumn();
                $pdo->prepare('DELETE FROM passages WHERE id = :id')->execute(['id' => $id]);
                $this->deleteAudioIfUnused($oldAudio);
                return;
            }

            $values = [
                'practice_id' => $this->positiveId($_POST['practice_id'] ?? null, 'التدريب'),
                'title' => $this->requiredText('title', 'عنوان المقطع', 255),
                'body' => $this->optionalText('body'),
                'audio_path' => null,
            ];
            $practiceCheck = $pdo->prepare('SELECT id FROM practices WHERE id=:id');
            $practiceCheck->execute(['id' => $values['practice_id']]);
            if ($practiceCheck->fetchColumn() === false) {
                throw new InvalidArgumentException('التدريب المحدد غير موجود.');
            }
            if ($id) {
                $this->assertExists('passages', $id, 'المقطع');
                $currentStmt = $pdo->prepare('SELECT audio_path FROM passages WHERE id = :id');
                $currentStmt->execute(['id' => $id]);
                $oldAudio = $currentStmt->fetchColumn();
                if ($oldAudio === false) {
                    throw new InvalidArgumentException('المقطع المطلوب غير موجود.');
                }
                $audio = $this->uploadAudio();
                $values['id'] = $id;
                if ($audio === null) {
                    $values['audio_path'] = $oldAudio;
                } else {
                    $values['audio_path'] = $audio;
                }
                $stmt = $pdo->prepare(
                    'UPDATE passages SET practice_id=:practice_id,title=:title,body=:body,audio_path=:audio_path WHERE id=:id'
                );
                $stmt->execute($values);
                if ($audio !== null) {
                    $this->deleteAudioIfUnused($oldAudio);
                }
            } else {
                $audio = $this->uploadAudio();
                $values['audio_path'] = $audio;
                $stmt = $pdo->prepare(
                    'INSERT INTO passages (practice_id,title,body,audio_path) VALUES (:practice_id,:title,:body,:audio_path)'
                );
                $stmt->execute($values);
            }
        }, '/admin/passages');
    }

    private function deleteAudioIfUnused($path): void
    {
        if (!is_string($path) || $path === '') {
            return;
        }
        $stmt = Database::getConnection()->prepare('SELECT COUNT(*) FROM passages WHERE audio_path = :path');
        $stmt->execute(['path' => $path]);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $relative = str_replace('\\', '/', $path);
        if (strpos($relative, 'uploads/audio/') !== 0 || strpos($relative, '..') !== false) {
            return;
        }
        $file = ROOT_PATH . '/public/' . $relative;
        if (is_file($file)) {
            unlink($file);
        }
    }
}