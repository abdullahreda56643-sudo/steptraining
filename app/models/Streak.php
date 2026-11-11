<?php

declare(strict_types=1);

class Streak
{
    public static function forUser(int $userId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT current_streak,longest_streak,last_active_date FROM streaks WHERE user_id=:user_id LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['current_streak' => 0, 'longest_streak' => 0, 'last_active_date' => null];
        }

        $today = new DateTimeImmutable('now', new DateTimeZone('Asia/Riyadh'));
        $yesterday = $today->modify('-1 day')->format('Y-m-d');
        if ($row['last_active_date'] !== $today->format('Y-m-d') && $row['last_active_date'] !== $yesterday) {
            $row['current_streak'] = 0;
        }

        return $row;
    }
}