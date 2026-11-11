<?php

declare(strict_types=1);

class SkillAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->guard();
        $pdo = Database::getConnection();
        $sections = $pdo->query('SELECT id,slug,name_ar,name_en,tip_text FROM sections ORDER BY sort_order')->fetchAll();
        $result = $this->paginate(
            'SELECT COUNT(*) FROM skills',
            'SELECT sk.*, s.name_ar AS section_name_ar
             FROM skills sk LEFT JOIN sections s ON s.id=sk.section_id
             ORDER BY s.sort_order,sk.name_ar'
        );
        $this->page('skills', 'إدارة المهارات والنصائح', $result + ['sections' => $sections]);
    }

    public function save(): void
    {
        $this->handlePost(function (): void {
            $pdo = Database::getConnection();
            $action = $_POST['action'] ?? '';
            if ($action === 'tip') {
                $sectionId = $this->positiveId($_POST['section_id'] ?? null, 'القسم');
                $tip = $this->requiredText('tip_text', 'نصيحة القسم');
                $stmt = $pdo->prepare('UPDATE sections SET tip_text=:tip WHERE id=:id');
                $stmt->execute(['tip' => $tip, 'id' => $sectionId]);
                if ($stmt->rowCount() === 0) {
                    $check = $pdo->prepare('SELECT id FROM sections WHERE id=:id');
                    $check->execute(['id' => $sectionId]);
                    if ($check->fetchColumn() === false) {
                        throw new InvalidArgumentException('القسم غير موجود.');
                    }
                }
                return;
            }

            $id = isset($_POST['id']) && $_POST['id'] !== '' ? $this->positiveId($_POST['id']) : null;
            if ($action === 'delete') {
                if (!$id) {
                    throw new InvalidArgumentException('المهارة المطلوبة غير صالحة.');
                }
                $this->assertExists('skills', $id, 'المهارة');
                $pdo->prepare('DELETE FROM skills WHERE id=:id')->execute(['id' => $id]);
                return;
            }

            $sectionId = trim((string) ($_POST['section_id'] ?? ''));
            $values = [
                'name_en' => $this->requiredText('name_en', 'اسم المهارة بالإنجليزية', 255),
                'name_ar' => $this->requiredText('name_ar', 'اسم المهارة بالعربية', 255),
                'section_id' => $sectionId === '' ? null : $this->positiveId($sectionId, 'القسم'),
            ];
            if ($id) {
                $this->assertExists('skills', $id, 'المهارة');
                $values['id'] = $id;
                $stmt = $pdo->prepare('UPDATE skills SET name_en=:name_en,name_ar=:name_ar,section_id=:section_id WHERE id=:id');
            } else {
                $stmt = $pdo->prepare('INSERT INTO skills (name_en,name_ar,section_id) VALUES (:name_en,:name_ar,:section_id)');
            }
            $stmt->execute($values);
        }, '/admin/skills');
    }
}
