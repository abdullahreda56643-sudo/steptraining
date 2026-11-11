<?php

declare(strict_types=1);

class PracticeController
{
    public function index(string $practiceId): void
    {
        Auth::requireRole('student');
        $practiceId = filter_var($practiceId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($practiceId === false) {
            http_response_code(404);
            view('errors/404', ['message' => 'التدريب المطلوب غير موجود.']);
            return;
        }

        $practice = Practice::findActiveById($practiceId);
        if (!$practice) {
            http_response_code(404);
            view('errors/404', ['message' => 'التدريب المطلوب غير موجود.']);
            return;
        }

        $availableQuestions = Practice::questions($practiceId);
        if (!$availableQuestions) {
            view('student/practice', [
                'title' => $practice['title_ar'],
                'practice' => $practice,
                'questions' => [],
                'attemptId' => null,
                'answers' => [],
                'showExplanation' => true,
                'canToggleExplanation' => $practice['section_slug'] !== 'vocabulary',
                'requiresAllAnswers' => false,
                'csrfToken' => Csrf::token(),
            ]);
            return;
        }

        $user = Auth::user();
        $attemptId = Attempt::findOrCreateForPractice(
            (int) $user['id'],
            $practiceId,
            array_column($availableQuestions, 'id')
        );
        $questions = Practice::questionsForAttempt($attemptId);
        $settings = Database::getConnection()->prepare(
            'SELECT show_explanation FROM user_settings WHERE user_id = :user_id LIMIT 1'
        );
        $settings->execute(['user_id' => $user['id']]);
        $explanationSetting = $settings->fetchColumn();
        if ($explanationSetting === false) {
            $defaultSetting = Database::getConnection()->query(
                "SELECT value FROM settings WHERE `key`='default_explanation' LIMIT 1"
            )->fetchColumn();
            $explanationSetting = $defaultSetting === false ? 1 : (int) $defaultSetting;
        }
        $showExplanation = $practice['section_slug'] === 'vocabulary'
            || (int) $explanationSetting === 1;

        view('student/practice', [
            'title' => $practice['title_ar'],
            'practice' => $practice,
            'questions' => $questions,
            'attemptId' => $attemptId,
            'answers' => Attempt::answers($attemptId),
            'showExplanation' => $showExplanation,
            'canToggleExplanation' => $practice['section_slug'] !== 'vocabulary',
            'requiresAllAnswers' => false,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function startCustom(string $skillId): void
    {
        Auth::requireRole('student');
        $skillId = filter_var($skillId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $user = Auth::user();
        $skill = $skillId === false ? null : Practice::findSkill($skillId);
        $questionIds = $skill ? Practice::customQuestionIds((int) $user['id'], (int) $skillId) : [];

        if (!$skill || count($questionIds) < 10) {
            flash('error', 'لا توجد ١٠ أسئلة جديدة كافية لهذه المهارة حالياً. جرّب تدريباً آخر.');
            redirect('/analysis');
        }

        $attemptId = Attempt::createQuestionAttempt((int) $user['id'], 'custom', $questionIds);
        redirect('/practice/attempt/' . $attemptId);
    }

    public function startMistakes(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $questionIds = Attempt::latestMistakeQuestionIds((int) $user['id']);
        if (!$questionIds) {
            view('student/mistakes', [
                'title' => 'تدريب الأخطاء',
                'mistakesCount' => 0,
            ]);
            return;
        }

        $attemptId = Attempt::findOpenByType((int) $user['id'], 'mistakes')
            ?? Attempt::createQuestionAttempt((int) $user['id'], 'mistakes', $questionIds);
        redirect('/practice/attempt/' . $attemptId);
    }

    public function showQuestionAttempt(string $attemptId): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $attemptId = filter_var($attemptId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $attempt = $attemptId === false
            ? null
            : Attempt::findForUser((int) $attemptId, (int) $user['id']);

        if (!$attempt || !in_array($attempt['type'], ['custom', 'mistakes', 'placement', 'daily'], true)) {
            http_response_code(404);
            view('errors/404', ['message' => 'المحاولة المطلوبة غير موجودة.']);
            return;
        }
        if ($attempt['finished_at'] !== null) {
            redirect($attempt['type'] === 'placement' ? '/placement' : ($attempt['type'] === 'daily' ? '/daily' : '/analysis'));
        }

        $questions = Practice::questionsForAttempt((int) $attemptId);
        if (!$questions) {
            http_response_code(404);
            view('errors/404', ['message' => 'لا توجد أسئلة في هذه المحاولة.']);
            return;
        }

        $section = $questions[0];
        $isMistakes = $attempt['type'] === 'mistakes';
        $isPlacement = $attempt['type'] === 'placement';
        $isDaily = $attempt['type'] === 'daily';
        $practice = [
            'title_ar' => $isMistakes ? 'تدريب الأخطاء'
                : ($isPlacement ? 'اختبار تحديد المستوى'
                    : ($isDaily ? 'التحدي اليومي' : 'تدريب مخصص: ' . $section['skill_name_ar'])),
            'title_en' => $isMistakes ? 'Mistakes practice'
                : ($isPlacement ? 'Placement test'
                    : ($isDaily ? 'Daily challenge' : 'Custom skill practice')),
            'section_slug' => $isMistakes || $isPlacement || $isDaily
                ? 'home'
                : ($section['section_slug'] ?? 'home'),
            'section_name_ar' => $isMistakes || $isPlacement || $isDaily
                ? 'الرئيسية'
                : ($section['section_name_ar'] ?? 'الرئيسية'),
        ];

        $settings = Database::getConnection()->prepare(
            'SELECT show_explanation FROM user_settings WHERE user_id = :user_id LIMIT 1'
        );
        $settings->execute(['user_id' => $user['id']]);
        $setting = $settings->fetchColumn();
        if ($setting === false) {
            $defaultSetting = Database::getConnection()->query(
                "SELECT value FROM settings WHERE `key`='default_explanation' LIMIT 1"
            )->fetchColumn();
            $setting = $defaultSetting === false ? 1 : (int) $defaultSetting;
        }
        $showExplanation = $isMistakes
            || ($section['section_slug'] ?? '') === 'vocabulary'
            || (int) $setting === 1;

        view('student/practice', [
            'title' => $practice['title_ar'],
            'practice' => $practice,
            'questions' => $questions,
            'attemptId' => (int) $attemptId,
            'answers' => Attempt::answers((int) $attemptId),
            'showExplanation' => $showExplanation,
            'canToggleExplanation' => !$isMistakes && ($section['section_slug'] ?? '') !== 'vocabulary',
            'requiresAllAnswers' => $isPlacement || $isDaily,
            'csrfToken' => Csrf::token(),
            'backUrl' => $isPlacement ? '/placement' : ($isDaily || $isMistakes ? '/' : '/analysis'),
        ]);
    }

    public function submitAnswer(): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'student') {
            $this->json(['ok' => false, 'error' => 'يرجى تسجيل الدخول بحساب طالب.'], 401);
            return;
        }
        if (!Csrf::validate()) {
            $this->json(['ok' => false, 'error' => 'رمز الحماية غير صالح. أعد تحميل الصفحة.'], 419);
            return;
        }

        $body = Csrf::jsonBody();
        $attemptId = filter_var($body['attempt_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $questionId = filter_var($body['question_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $choice = strtoupper((string) ($body['choice'] ?? ''));
        if ($attemptId === false || $questionId === false || !in_array($choice, ['A', 'B', 'C', 'D'], true)) {
            $this->json(['ok' => false, 'error' => 'بيانات الإجابة غير مكتملة أو غير صحيحة.'], 422);
            return;
        }

        try {
            $result = Attempt::submitAnswer($attemptId, (int) $user['id'], $questionId, $choice);
            $visibility = Database::getConnection()->prepare(
                'SELECT a.type,s.slug
                 FROM attempts a
                 INNER JOIN attempt_questions aq ON aq.attempt_id=a.id AND aq.question_id=:question_id
                 INNER JOIN questions q ON q.id=aq.question_id
                 INNER JOIN skills sk ON sk.id=q.skill_id
                 LEFT JOIN sections s ON s.id=sk.section_id
                 WHERE a.id=:attempt_id AND a.user_id=:user_id LIMIT 1'
            );
            $visibility->execute([
                'question_id' => $questionId,
                'attempt_id' => $attemptId,
                'user_id' => $user['id'],
            ]);
            $context = $visibility->fetch();
            $showExplanation = $context
                && ($context['type'] === 'mistakes'
                    || $context['slug'] === 'vocabulary'
                    || self::explanationPreference((int) $user['id']));
            if (!$showExplanation) {
                $result['explanation'] = null;
                $result['wrong_reasons'] = [];
            }
            $this->json(['ok' => true, 'data' => $result]);
        } catch (DomainException $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 409);
        } catch (Throwable $e) {
            error_log('Answer submission failed: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'تعذر حفظ الإجابة الآن. حاول مرة أخرى.'], 500);
        }
    }

    public function finish(): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'student') {
            $this->json(['ok' => false, 'error' => 'يرجى تسجيل الدخول بحساب طالب.'], 401);
            return;
        }
        if (!Csrf::validate()) {
            $this->json(['ok' => false, 'error' => 'رمز الحماية غير صالح. أعد تحميل الصفحة.'], 419);
            return;
        }

        $body = Csrf::jsonBody();
        $attemptId = filter_var($body['attempt_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($attemptId === false) {
            $this->json(['ok' => false, 'error' => 'رقم المحاولة غير صالح.'], 422);
            return;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT id FROM attempts WHERE id = :id AND user_id = :user_id LIMIT 1');
            $stmt->execute(['id' => $attemptId, 'user_id' => $user['id']]);
            if ($stmt->fetchColumn() === false) {
                $this->json(['ok' => false, 'error' => 'المحاولة غير موجودة.'], 404);
                return;
            }

            $attempt = Attempt::findForUser((int) $attemptId, (int) $user['id']);
            if (!$attempt) {
                $this->json(['ok' => false, 'error' => 'المحاولة غير موجودة.'], 404);
                return;
            }
            $result = Attempt::finish(
                $attemptId,
                (int) $user['id'],
                Attempt::questionCount($attemptId)
            );
            $accuracy = $result['total'] > 0
                ? $result['correct_count'] / $result['total']
                : 0.0;
            $data = $result + ['accuracy' => (int) round($accuracy * 100)];
            if ($attempt['type'] === 'placement') {
                $data['placement'] = PlacementService::level(
                    (int) $result['correct_count'],
                    (int) $result['total'],
                    (int) $user['id']
                );
            } else {
                $prediction = ScoreService::predict((int) $user['id'], $accuracy, $attemptId);
                $stmt = $pdo->prepare(
                    'UPDATE attempts SET predicted_score = :score WHERE id = :id AND user_id = :user_id'
                );
                $stmt->execute([
                    'score' => $prediction['score'],
                    'id' => $attemptId,
                    'user_id' => $user['id'],
                ]);
                $data['prediction'] = $prediction;
            }

            if (in_array($attempt['type'], ['practice', 'daily'], true)) {
                $data['streak'] = StreakService::recordCompletion((int) $user['id'], (int) $attemptId);
            }
            $data['analysis_url'] = url('/analysis');
            $data['attempt_type'] = $attempt['type'];
            $this->json(['ok' => true, 'data' => $data]);
        } catch (DomainException $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 409);
        } catch (Throwable $e) {
            error_log('Practice finish failed: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'تعذر إنهاء التدريب الآن. حاول مرة أخرى.'], 500);
        }
    }

    public function toggleExplanation(): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'student') {
            $this->json(['ok' => false, 'error' => 'يرجى تسجيل الدخول بحساب طالب.'], 401);
            return;
        }
        if (!Csrf::validate()) {
            $this->json(['ok' => false, 'error' => 'رمز الحماية غير صالح. أعد تحميل الصفحة.'], 419);
            return;
        }

        $body = Csrf::jsonBody();
        if (!array_key_exists('enabled', $body) || !in_array($body['enabled'], [true, false, 0, 1, '0', '1'], true)) {
            $this->json(['ok' => false, 'error' => 'قيمة إعداد الشرح غير صالحة.'], 422);
            return;
        }

        try {
            $stmt = Database::getConnection()->prepare(
                'INSERT INTO user_settings (user_id,show_explanation) VALUES (:user_id,:enabled)
                 ON DUPLICATE KEY UPDATE show_explanation=VALUES(show_explanation)'
            );
            $stmt->execute([
                'user_id' => $user['id'],
                'enabled' => filter_var($body['enabled'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            ]);
            $this->json(['ok' => true, 'data' => ['enabled' => (bool) filter_var($body['enabled'], FILTER_VALIDATE_BOOLEAN)]]);
        } catch (Throwable $e) {
            error_log('Explanation preference update failed: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'تعذر حفظ إعداد الشرح.'], 500);
        }
    }

    private static function explanationPreference(int $userId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT show_explanation FROM user_settings WHERE user_id=:user_id LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $value = $stmt->fetchColumn();
        if ($value !== false) {
            return (int) $value === 1;
        }

        $default = Database::getConnection()->query(
            "SELECT value FROM settings WHERE `key`='default_explanation' LIMIT 1"
        )->fetchColumn();

        return $default === false || (int) $default === 1;
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}