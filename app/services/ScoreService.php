<?php

declare(strict_types=1);

class ScoreService
{
    public static function predict(int $userId, float $currentAccuracy, int $currentAttemptId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT correct_count, total FROM attempts
             WHERE user_id = :user_id AND id <> :current_attempt_id
               AND finished_at IS NOT NULL AND total > 0
             ORDER BY finished_at DESC, id DESC LIMIT 2"
        );
        $stmt->execute([
            'user_id' => $userId,
            'current_attempt_id' => $currentAttemptId,
        ]);

        $accuracies = [$currentAccuracy];
        foreach ($stmt->fetchAll() as $attempt) {
            $accuracies[] = (int) $attempt['correct_count'] / (int) $attempt['total'];
        }

        $weights = [0.5, 0.3, 0.2];
        $weightedAccuracy = 0.0;
        $weightTotal = 0.0;
        foreach ($accuracies as $index => $accuracy) {
            $weightedAccuracy += max(0.0, min(1.0, $accuracy)) * $weights[$index];
            $weightTotal += $weights[$index];
        }

        $score = (int) round(20 + 80 * ($weightedAccuracy / $weightTotal));
        if ($score < 50) {
            $band = 'يحتاج تحسيناً كبيراً';
        } elseif ($score < 70) {
            $band = 'جيد';
        } elseif ($score < 85) {
            $band = 'جيد جداً';
        } else {
            $band = 'ممتاز';
        }

        $nextBand = min(100, (intdiv($score, 10) + 1) * 10);

        return [
            'score' => $score,
            'band' => $band,
            'gap' => max(0, $nextBand - $score),
            'weighted_accuracy' => $weightedAccuracy / $weightTotal,
        ];
    }
}