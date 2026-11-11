<?php

declare(strict_types=1);

class PlacementService
{
    public static function questionIds(): array
    {
        $size = self::setting('placement_size', 15, 5, 100);
        $rows = Database::getConnection()->query(
            'SELECT q.id,s.id AS section_id
             FROM questions q
             INNER JOIN practices p ON p.id=q.practice_id AND p.is_active=1
             INNER JOIN sections s ON s.id=p.section_id
             WHERE q.is_active=1 AND q.in_placement=1
             ORDER BY RAND()'
        )->fetchAll();

        return DailyChallengeService::balance($rows, $size);
    }

    public static function availableQuestionCount(): int
    {
        $count = (int) Database::getConnection()->query(
            'SELECT COUNT(*)
             FROM questions q
             INNER JOIN practices p ON p.id=q.practice_id AND p.is_active=1
             WHERE q.is_active=1 AND q.in_placement=1'
        )->fetchColumn();

        return min($count, self::setting('placement_size', 15, 5, 100));
    }

    public static function level(int $correct, int $total, int $userId = 0): array
    {
        $accuracy = $total > 0 ? ($correct / $total) * 100 : 0;
        if ($accuracy < 40) {
            $level = 'مبتدئ';
            $plan = ['ابدأ بتدريبات القواعد والمفردات الأساسية، ثم انتقل إلى الاستماع والقراءة.'];
        } elseif ($accuracy < 70) {
            $level = 'متوسط';
            $plan = ['ابدأ بتدريبات المهارات الأضعف في التحليل، ثم أكمل نماذج الأقسام بالتدريج.'];
        } else {
            $level = 'متقدم';
            $plan = ['تدرّب على النماذج الكاملة، وراجع المهارات التي تقل دقتها عن ٦٠٪.'];
        }
        if ($userId > 0) {
            $weakSkills = array_values(array_filter(
                WeaknessService::forUser($userId),
                static function (array $skill): bool {
                    return $skill['is_weak'];
                }
            ));
            foreach (array_slice($weakSkills, 0, 3) as $skill) {
                $plan[] = 'ابدأ بمهارة ' . $skill['name_ar'] . ' (' . $skill['accuracy'] . '٪).';
            }
        }

        return [
            'level' => $level,
            'accuracy' => (int) round($accuracy),
            'plan' => $plan,
        ];
    }

    public static function history(int $userId, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));
        $stmt = Database::getConnection()->prepare(
            "SELECT id,correct_count,total,started_at,finished_at
             FROM attempts WHERE user_id=:user_id AND type='placement' AND finished_at IS NOT NULL
             ORDER BY finished_at DESC,id DESC LIMIT " . $limit
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function latest(int $userId): ?array
    {
        $history = self::history($userId, 1);
        if (!$history) {
            return null;
        }

        return $history[0] + self::level(
            (int) $history[0]['correct_count'],
            (int) $history[0]['total'],
            $userId
        );
    }

    private static function setting(string $key, int $default, int $min, int $max): int
    {
        $stmt = Database::getConnection()->prepare('SELECT value FROM settings WHERE `key`=:key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = filter_var($stmt->fetchColumn(), FILTER_VALIDATE_INT);

        return $value !== false && $value >= $min && $value <= $max ? $value : $default;
    }
}