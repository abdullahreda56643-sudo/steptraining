<?php

declare(strict_types=1);

class QuestionAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->guard();
        $pdo = Database::getConnection();
        $filters = [];
        $where = [];
        foreach (['practice_id', 'skill_id'] as $field) {
            if (isset($_GET[$field]) && $_GET[$field] !== '') {
                $filters[$field] = $this->positiveId($_GET[$field], 'مرشح البحث');
                $where[] = 'q.' . $field . ' = :' . $field;
            }
        }
        if (isset($_GET['section_id']) && $_GET['section_id'] !== '') {
            $filters['section_id'] = $this->positiveId($_GET['section_id'], 'القسم');
            $where[] = 's.id = :section_id';
        }
        $search = trim((string) ($_GET['q'] ?? ''));
        if ($search !== '') {
            $where[] = 'q.text LIKE :search';
            $filters['search'] = '%' . $search . '%';
        }
        $condition = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $queryParams = $filters;

        $result = $this->paginate(
            'SELECT COUNT(*) FROM questions q
             INNER JOIN practices p ON p.id=q.practice_id
             INNER JOIN sections s ON s.id=p.section_id
             INNER JOIN skills sk ON sk.id=q.skill_id' . $condition,
            'SELECT q.*, p.title_ar AS practice_title, s.name_ar AS section_name_ar,
                    sk.name_ar AS skill_name_ar
             FROM questions q
             INNER JOIN practices p ON p.id=q.practice_id
             INNER JOIN sections s ON s.id=p.section_id
             INNER JOIN skills sk ON sk.id=q.skill_id' . $condition . '
             ORDER BY s.sort_order, p.sort_order, q.sort_order, q.id',
            $queryParams
        );
        $this->page('questions', 'إدارة الأسئلة', $result + [
            'practices' => $pdo->query('SELECT id,title_ar FROM practices ORDER BY section_id,sort_order')->fetchAll(),
            'skills' => $pdo->query('SELECT id,name_ar FROM skills ORDER BY name_ar')->fetchAll(),
            'sections' => $pdo->query('SELECT id,name_ar FROM sections ORDER BY sort_order')->fetchAll(),
            'passages' => $pdo->query('SELECT id,title FROM passages ORDER BY title')->fetchAll(),
            'filters' => $filters,
            'search' => $search,
        ]);
    }

    public function save(): void
    {
        $this->handlePost(function (): void {
            $pdo = Database::getConnection();
            $action = $_POST['action'] ?? 'save';
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? $this->positiveId($_POST['id']) : null;
            if ($action === 'delete') {
                if (!$id) {
                    throw new InvalidArgumentException('السؤال المطلوب غير صالح.');
                }
                $this->assertExists('questions', $id, 'السؤال');
                $pdo->prepare('DELETE FROM questions WHERE id = :id')->execute(['id' => $id]);
                return;
            }

            $correct = strtoupper(trim((string) ($_POST['correct'] ?? '')));
            if (!in_array($correct, ['A', 'B', 'C', 'D'], true)) {
                throw new InvalidArgumentException('اختر الإجابة الصحيحة من A إلى D.');
            }
            $sortOrder = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
            if ($sortOrder === false) {
                throw new InvalidArgumentException('ترتيب السؤال غير صالح.');
            }
            $passageId = trim((string) ($_POST['passage_id'] ?? ''));
            $values = [
                'practice_id' => $this->positiveId($_POST['practice_id'] ?? null, 'التدريب'),
                'passage_id' => $passageId === '' ? null : $this->positiveId($passageId, 'المقطع'),
                'skill_id' => $this->positiveId($_POST['skill_id'] ?? null, 'المهارة'),
                'text' => $this->requiredText('text', 'نص السؤال'),
                'option_a' => $this->requiredText('option_a', 'الخيار A'),
                'option_b' => $this->requiredText('option_b', 'الخيار B'),
                'option_c' => $this->requiredText('option_c', 'الخيار C'),
                'option_d' => $this->requiredText('option_d', 'الخيار D'),
                'correct' => $correct,
                'explanation_ar' => $this->requiredText('explanation_ar', 'الشرح العربي'),
                'wrong_a_ar' => $this->optionalText('wrong_a_ar'),
                'wrong_b_ar' => $this->optionalText('wrong_b_ar'),
                'wrong_c_ar' => $this->optionalText('wrong_c_ar'),
                'wrong_d_ar' => $this->optionalText('wrong_d_ar'),
                'in_placement' => isset($_POST['in_placement']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => $sortOrder,
            ];

            if ($values['passage_id'] !== null) {
                $passageCheck = $pdo->prepare(
                    'SELECT id FROM passages WHERE id=:passage_id AND practice_id=:practice_id LIMIT 1'
                );
                $passageCheck->execute([
                    'passage_id' => $values['passage_id'],
                    'practice_id' => $values['practice_id'],
                ]);
                if ($passageCheck->fetchColumn() === false) {
                    throw new InvalidArgumentException('اختر مقطعاً تابعاً للتدريب المحدد.');
                }
            }

            $skillCheck = $pdo->prepare(
                'SELECT sk.id
                 FROM skills sk
                 INNER JOIN practices pr ON pr.id=:practice_id
                 WHERE sk.id=:skill_id
                   AND (sk.section_id IS NULL OR sk.section_id=pr.section_id)
                 LIMIT 1'
            );
            $skillCheck->execute([
                'practice_id' => $values['practice_id'],
                'skill_id' => $values['skill_id'],
            ]);
            if ($skillCheck->fetchColumn() === false) {
                throw new InvalidArgumentException('اختر مهارة عامة أو مهارة تابعة لقسم التدريب.');
            }

            if ($values['is_active']) {
                foreach (['wrong_a_ar', 'wrong_b_ar', 'wrong_c_ar', 'wrong_d_ar'] as $key) {
                    if ($key !== 'wrong_' . strtolower($correct) . '_ar' && $values[$key] === null) {
                        throw new InvalidArgumentException('أدخل سبب خطأ لكل خيار غير صحيح قبل تفعيل السؤال.');
                    }
                }
            }

            if ($id) {
                $this->assertExists('questions', $id, 'السؤال');
                $values['id'] = $id;
                $stmt = $pdo->prepare(
                    'UPDATE questions SET practice_id=:practice_id, passage_id=:passage_id, skill_id=:skill_id,
                     text=:text, option_a=:option_a, option_b=:option_b, option_c=:option_c, option_d=:option_d,
                     correct=:correct, explanation_ar=:explanation_ar, wrong_a_ar=:wrong_a_ar,
                     wrong_b_ar=:wrong_b_ar, wrong_c_ar=:wrong_c_ar, wrong_d_ar=:wrong_d_ar,
                     in_placement=:in_placement, is_active=:is_active, sort_order=:sort_order WHERE id=:id'
                );
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO questions (practice_id,passage_id,skill_id,text,option_a,option_b,option_c,option_d,
                     correct,explanation_ar,wrong_a_ar,wrong_b_ar,wrong_c_ar,wrong_d_ar,in_placement,is_active,sort_order)
                     VALUES (:practice_id,:passage_id,:skill_id,:text,:option_a,:option_b,:option_c,:option_d,
                     :correct,:explanation_ar,:wrong_a_ar,:wrong_b_ar,:wrong_c_ar,:wrong_d_ar,:in_placement,:is_active,:sort_order)'
                );
            }
            $stmt->execute($values);
        }, '/admin/questions');
    }
}