<?php

declare(strict_types=1);

class PlacementController
{
    public function index(): void
    {
        Auth::requireRole('student');
        $userId = (int) Auth::user()['id'];
        $attempt = PlacementService::latest($userId);
        $history = PlacementService::history($userId);
        $openAttemptId = Attempt::findOpenByType($userId, 'placement');
        $questionCount = PlacementService::availableQuestionCount();
        view('student/placement', [
            'title' => 'اختبار تحديد المستوى',
            'attempt' => $attempt,
            'history' => $history,
            'questionCount' => $questionCount,
            'openAttemptId' => $openAttemptId,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function start(): void
    {
        Auth::requireRole('student');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !Csrf::validate()) {
            flash('error', 'انتهت صلاحية الجلسة. أعد المحاولة.');
            redirect('/placement');
        }

        $userId = (int) Auth::user()['id'];
        $openAttemptId = Attempt::findOpenByType($userId, 'placement');
        if ($openAttemptId !== null) {
            redirect('/practice/attempt/' . $openAttemptId);
        }

        $questionIds = PlacementService::questionIds();
        if (!$questionIds) {
            flash('error', 'لا توجد أسئلة مفعّلة لاختبار المستوى. حاول لاحقاً أو تواصل مع الإدارة.');
            redirect('/placement');
        }

        try {
            $attemptId = Attempt::createQuestionAttempt(
                $userId,
                'placement',
                $questionIds
            );
            redirect('/practice/attempt/' . $attemptId);
        } catch (Throwable $e) {
            error_log('Placement test start failed: ' . $e->getMessage());
            flash('error', 'تعذر بدء اختبار المستوى حالياً.');
            redirect('/placement');
        }
    }

}