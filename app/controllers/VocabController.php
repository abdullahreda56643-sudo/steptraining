<?php

declare(strict_types=1);

class VocabController
{
    public function index(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $pageSize = 48;
        $summary = Vocabulary::progressSummaryForUser((int) $user['id']);
        $pageCount = max(1, (int) ceil((int) $summary['total_count'] / $pageSize));
        $requestedPage = filter_var(
            $_GET['page'] ?? 1,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $page = min($pageCount, $requestedPage === false ? 1 : (int) $requestedPage);
        $items = Vocabulary::forUser((int) $user['id'], $pageSize, ($page - 1) * $pageSize);
        view('student/vocab', [
            'title' => 'بنك المفردات',
            'items' => $items,
            'summary' => $summary,
            'page' => $page,
            'pageCount' => $pageCount,
            'pageSize' => $pageSize,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function saveProgress(): void
    {
        $this->requireStudentJson();
        $body = Csrf::jsonBody();
        $vocabId = filter_var($body['vocab_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = (string) ($body['status'] ?? '');
        if ($vocabId === false || !in_array($status, ['known', 'review'], true)) {
            $this->json(['ok' => false, 'error' => 'بيانات المفردة غير صحيحة.'], 422);
            return;
        }

        try {
            Vocabulary::saveProgress((int) Auth::user()['id'], (int) $vocabId, $status);
            $this->json(['ok' => true, 'data' => ['status' => $status]]);
        } catch (DomainException $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            error_log('Vocabulary progress update failed: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'تعذر حفظ تقدم المفردات.'], 500);
        }
    }

    private function requireStudentJson(): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'student') {
            $this->json(['ok' => false, 'error' => 'يرجى تسجيل الدخول بحساب طالب.'], 401);
            exit;
        }
        if (!Csrf::validate()) {
            $this->json(['ok' => false, 'error' => 'رمز الحماية غير صالح.'], 419);
            exit;
        }
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}