<?php

declare(strict_types=1);

class PracticeAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->guard();
        $result = $this->paginate(
            'SELECT COUNT(*) FROM practices p INNER JOIN sections s ON s.id = p.section_id',
            'SELECT p.*, s.name_ar AS section_name_ar,
                    (SELECT COUNT(*) FROM questions q WHERE q.practice_id = p.id) AS question_count
             FROM practices p INNER JOIN sections s ON s.id = p.section_id
             ORDER BY s.sort_order, p.sort_order, p.id'
        );
        $sections = Database::getConnection()->query('SELECT id, name_ar FROM sections ORDER BY sort_order')->fetchAll();

        $this->page('practices', 'إدارة التدريبات', $result + ['sections' => $sections]);
    }

    public function save(): void
    {
        $this->handlePost(function (): void {
            $pdo = Database::getConnection();
            $action = $_POST['action'] ?? 'save';
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? $this->positiveId($_POST['id']) : null;

            if ($action === 'delete') {
                if (!$id) {
                    throw new InvalidArgumentException('التدريب المطلوب غير صالح.');
                }
                $this->assertExists('practices', $id, 'التدريب');
                $stmt = $pdo->prepare('DELETE FROM practices WHERE id = :id');
                $stmt->execute(['id' => $id]);
                return;
            }

            $sectionId = $this->positiveId($_POST['section_id'] ?? null, 'القسم');
            $titleAr = $this->requiredText('title_ar', 'عنوان التدريب بالعربية', 255);
            $titleEn = $this->requiredText('title_en', 'عنوان التدريب بالإنجليزية', 255);
            $sortOrder = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
            $timeLimit = trim((string) ($_POST['time_limit_min'] ?? ''));
            if ($sortOrder === false || ($timeLimit !== '' && (!ctype_digit($timeLimit) || (int) $timeLimit < 1 || (int) $timeLimit > 600))) {
                throw new InvalidArgumentException('ترتيب التدريب أو مدته غير صالح.');
            }

            $values = [
                'section_id' => $sectionId,
                'title_en' => $titleEn,
                'title_ar' => $titleAr,
                'sort_order' => $sortOrder,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'time_limit_min' => $timeLimit === '' ? null : (int) $timeLimit,
            ];
            if ($id) {
                $this->assertExists('practices', $id, 'التدريب');
                $values['id'] = $id;
                $stmt = $pdo->prepare(
                    'UPDATE practices SET section_id=:section_id, title_en=:title_en, title_ar=:title_ar,
                     sort_order=:sort_order, is_active=:is_active, time_limit_min=:time_limit_min WHERE id=:id'
                );
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO practices (section_id,title_en,title_ar,sort_order,is_active,time_limit_min)
                     VALUES (:section_id,:title_en,:title_ar,:sort_order,:is_active,:time_limit_min)'
                );
            }
            $stmt->execute($values);
        }, '/admin/practices');
    }
}