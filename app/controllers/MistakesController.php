<?php

declare(strict_types=1);

class MistakesController
{
    public function index(): void
    {
        Auth::requireRole('student');
        $userId = (int) Auth::user()['id'];
        $questionIds = Attempt::latestMistakeQuestionIds($userId);

        if (!$questionIds) {
            view('student/mistakes', [
                'title' => 'تدريب الأخطاء',
                'mistakesCount' => 0,
            ]);
            return;
        }

        $attemptId = Attempt::findOpenByType($userId, 'mistakes');
        if ($attemptId === null) {
            $attemptId = Attempt::createQuestionAttempt($userId, 'mistakes', $questionIds);
        }
        redirect('/practice/attempt/' . $attemptId);
    }
}