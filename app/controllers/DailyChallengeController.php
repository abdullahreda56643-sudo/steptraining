<?php

declare(strict_types=1);

class DailyChallengeController
{
    public function index(): void
    {
        Auth::requireRole('student');
        $userId = (int) Auth::user()['id'];
        $questionIds = DailyChallengeService::questionIds();
        $attempt = DailyChallengeService::attemptForUserToday($userId);
        $attemptId = $attempt ? (int) $attempt['id'] : null;

        if (!$attempt && $questionIds) {
            $attemptId = DailyChallengeService::findOrStartAttempt($userId, $questionIds);
            $attempt = $attemptId ? Attempt::findForUser($attemptId, $userId) : null;
        }

        $completed = $attempt && $attempt['finished_at'] !== null;
        view('student/daily', [
            'title' => 'التحدي اليومي',
            'date' => DailyChallengeService::challengeDate(),
            'questionCount' => count($questionIds),
            'attemptId' => $attemptId,
            'attempt' => $attempt,
            'hasAnswers' => $attemptId !== null && (bool) Attempt::answers($attemptId),
            'completed' => $completed,
            'streak' => Streak::forUser($userId),
        ]);
    }
}