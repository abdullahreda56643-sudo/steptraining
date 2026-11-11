<?php

declare(strict_types=1);

abstract class AdminBaseController
{
    protected function guard(): void
    {
        Auth::requireRole('admin');
    }

    protected function page(string $template, string $title, array $data = []): void
    {
        view('admin/' . $template, $data + [
            'title' => $title,
            'csrfToken' => Csrf::token(),
        ], 'admin');
    }

    protected function requirePost(string $fallback): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Method Not Allowed');
        }

        if (!Csrf::validate()) {
            flash('error', 'انتهت صلاحية الجلسة. أعد المحاولة.');
            $this->redirectBack($fallback);
        }
    }

    protected function positiveId($value, string $label = 'المعرّف'): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException($label . ' غير صالح.');
        }

        return (int) $id;
    }

    protected function assertExists(string $table, int $id, string $label): void
    {
        $allowedTables = ['practices', 'questions', 'passages', 'vocabulary', 'skills'];
        if (!in_array($table, $allowedTables, true)) {
            throw new LogicException('Unsupported admin table.');
        }
        $stmt = Database::getConnection()->prepare('SELECT id FROM ' . $table . ' WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() === false) {
            throw new InvalidArgumentException($label . ' غير موجود.');
        }
    }

    protected function requiredText(string $key, string $label, int $maxLength = 10000): string
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        if ($value === '') {
            throw new InvalidArgumentException('حقل ' . $label . ' مطلوب.');
        }
        if (mb_strlen($value, 'UTF-8') > $maxLength) {
            throw new InvalidArgumentException('حقل ' . $label . ' أطول من المسموح.');
        }

        return $value;
    }

    protected function optionalText(string $key, int $maxLength = 10000): ?string
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        if ($value === '') {
            return null;
        }
        if (mb_strlen($value, 'UTF-8') > $maxLength) {
            throw new InvalidArgumentException('أحد الحقول أطول من المسموح.');
        }

        return $value;
    }

    protected function pageNumber(): int
    {
        return max(1, (int) ($_GET['page'] ?? 1));
    }

    protected function paginate(string $countSql, string $listSql, array $params = [], int $perPage = 20): array
    {
        $pdo = Database::getConnection();
        $count = $pdo->prepare($countSql);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($this->pageNumber(), $pages);
        $offset = ($page - 1) * $perPage;

        $list = $pdo->prepare($listSql . ' LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $list->execute($params);

        return [
            'items' => $list->fetchAll(),
            'pagination' => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ];
    }

    protected function redirectBack(string $fallback): void
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $appPath = parse_url(APP_URL, PHP_URL_PATH) ?: '/';
        $refererPath = parse_url($referer, PHP_URL_PATH) ?: '';
        $appPrefix = rtrim($appPath, '/');

        if ($refererPath !== ''
            && ($appPrefix === '' || $refererPath === $appPrefix || strpos($refererPath, $appPrefix . '/') === 0)) {
            $query = parse_url($referer, PHP_URL_QUERY);
            $target = $refererPath . ($query ? '?' . $query : '');
            redirect($target);
        }

        redirect($fallback);
    }

    protected function handlePost(callable $action, string $fallback): void
    {
        $this->guard();
        $this->requirePost($fallback);

        try {
            $action();
            flash('success', 'تم حفظ التغييرات بنجاح.');
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
        } catch (PDOException $e) {
            error_log('Admin database operation failed: ' . $e->getMessage());
            flash('error', 'تعذر حفظ البيانات. تحقق من القيم المرتبطة وحاول مجدداً.');
        } catch (Throwable $e) {
            error_log('Admin operation failed: ' . $e->getMessage());
            flash('error', 'حدث خطأ أثناء تنفيذ العملية.');
        }

        $this->redirectBack($fallback);
    }

    protected function uploadAudio(): ?string
    {
        if (!isset($_FILES['audio']) || $_FILES['audio']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['audio'];
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('تعذر رفع الملف الصوتي.');
        }
        if ((int) $file['size'] > 10 * 1024 * 1024) {
            throw new InvalidArgumentException('الحد الأقصى لحجم الملف الصوتي 10 ميجابايت.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'mp3' => ['audio/mpeg', 'audio/mp3', 'audio/x-mpeg'],
            'wav' => ['audio/wav', 'audio/x-wav', 'audio/vnd.wave'],
            'm4a' => ['audio/mp4', 'audio/x-m4a'],
            'ogg' => ['audio/ogg', 'application/ogg'],
        ];

        if (!isset($allowed[$extension]) || !in_array($mime, $allowed[$extension], true)) {
            throw new InvalidArgumentException('نوع الملف غير صالح. الصيغ المدعومة: MP3 وWAV وM4A وOGG.');
        }

        $directory = ROOT_PATH . '/public/uploads/audio';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('تعذر تجهيز مجلد رفع الملفات الصوتية.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            throw new RuntimeException('تعذر حفظ الملف الصوتي.');
        }

        return 'uploads/audio/' . $filename;
    }
}
