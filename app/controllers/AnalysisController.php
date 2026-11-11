<?php

declare(strict_types=1);

class AnalysisController
{
    public function index(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT id, correct_count, total FROM attempts
             WHERE user_id = :user_id AND finished_at IS NOT NULL AND total > 0
             ORDER BY finished_at DESC, id DESC LIMIT 1'
        );
        $stmt->execute(['user_id' => $user['id']]);
        $latestAttempt = $stmt->fetch() ?: null;
        $prediction = $latestAttempt
            ? ScoreService::predict(
                (int) $user['id'],
                (int) $latestAttempt['correct_count'] / (int) $latestAttempt['total'],
                (int) $latestAttempt['id']
            )
            : null;

        view('student/analysis', [
            'title' => 'تحليل الأداء',
            'skills' => WeaknessService::forUser((int) $user['id']),
            'weakSkillThreshold' => WeaknessService::threshold(),
            'prediction' => $prediction,
            'latestAttempt' => $latestAttempt,
        ]);
    }
}