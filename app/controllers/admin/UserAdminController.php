<?php

declare(strict_types=1);

class UserAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->guard();
        $term = trim((string) ($_GET['q'] ?? ''));
        $params = [];
        $condition = " WHERE role = 'student'";
        if ($term !== '') {
            $condition .= ' AND (name LIKE :name OR email LIKE :email)';
            $params = ['name' => '%' . $term . '%', 'email' => '%' . $term . '%'];
        }
        $result = $this->paginate(
            'SELECT COUNT(*) FROM users' . $condition,
            'SELECT u.id,u.name,u.email,u.is_active,u.created_at,
                    COALESCE(progress.completed_attempts,0) AS completed_attempts,
                    progress.average_score
             FROM users u
             LEFT JOIN (
                SELECT user_id,COUNT(*) AS completed_attempts,ROUND(AVG(predicted_score)) AS average_score
                FROM attempts
                WHERE finished_at IS NOT NULL
                GROUP BY user_id
             ) progress ON progress.user_id=u.id' .
             str_replace(' WHERE ', ' WHERE u.', $condition) .
             ' ORDER BY u.created_at DESC,u.id DESC',
            $params
        );
        $this->page('users', 'إدارة الطلاب', $result + ['search' => $term]);
    }

    public function save(): void
    {
        $this->handlePost(function (): void {
            $pdo = Database::getConnection();
            $id = $this->positiveId($_POST['id'] ?? null, 'الطالب');
            $action = $_POST['action'] ?? '';
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id=:id AND role='student' LIMIT 1");
            $stmt->execute(['id' => $id]);
            if ($stmt->fetchColumn() === false) {
                throw new InvalidArgumentException('حساب الطالب غير موجود.');
            }

            if ($action === 'toggle') {
                $active = (int) ($_POST['is_active'] ?? 0) === 1 ? 0 : 1;
                $pdo->prepare('UPDATE users SET is_active=:active WHERE id=:id')->execute([
                    'active' => $active,
                    'id' => $id,
                ]);
                return;
            }

            if ($action === 'reset_password') {
                $password = (string) ($_POST['password'] ?? '');
                if (mb_strlen($password, 'UTF-8') < 8 || mb_strlen($password, 'UTF-8') > 200) {
                    throw new InvalidArgumentException('كلمة المرور يجب أن تكون بين 8 و200 حرف.');
                }
                $pdo->prepare('UPDATE users SET password_hash=:password_hash WHERE id=:id')->execute([
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'id' => $id,
                ]);
                return;
            }

            throw new InvalidArgumentException('الإجراء المطلوب غير معروف.');
        }, '/admin/users');
    }
}