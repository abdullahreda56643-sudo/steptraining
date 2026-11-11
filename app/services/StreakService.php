<?php

declare(strict_types=1);

class StreakService
{
    public static function recordCompletion(int $userId, int $attemptId): array
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $attemptStmt = $pdo->prepare(
                'SELECT user_id,type,finished_at FROM attempts WHERE id=:id FOR UPDATE'
            );
            $attemptStmt->execute(['id' => $attemptId]);
            $attempt = $attemptStmt->fetch();
            if (!$attempt || (int) $attempt['user_id'] !== $userId || $attempt['finished_at'] === null) {
                throw new DomainException('لا يمكن تسجيل سلسلة النشاط لهذه المحاولة.');
            }
            if (!in_array($attempt['type'], ['practice', 'daily'], true)) {
                $pdo->commit();
                return Streak::forUser($userId);
            }

            $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Riyadh')))->format('Y-m-d');
            $stmt = $pdo->prepare(
                'SELECT current_streak,longest_streak,last_active_date
                 FROM streaks WHERE user_id=:user_id FOR UPDATE'
            );
            $stmt->execute(['user_id' => $userId]);
            $streak = $stmt->fetch();

            if (!$streak) {
                $current = 1;
                $longest = 1;
                $stmt = $pdo->prepare(
                    'INSERT INTO streaks (user_id,current_streak,longest_streak,last_active_date)
                     VALUES (:user_id,:current,:longest,:active_date)'
                );
                $stmt->execute([
                    'user_id' => $userId,
                    'current' => $current,
                    'longest' => $longest,
                    'active_date' => $today,
                ]);
            } elseif ($streak['last_active_date'] === $today) {
                $current = (int) $streak['current_streak'];
                $longest = (int) $streak['longest_streak'];
            } else {
                $yesterday = (new DateTimeImmutable($today, new DateTimeZone('Asia/Riyadh')))
                    ->modify('-1 day')
                    ->format('Y-m-d');
                $current = $streak['last_active_date'] === $yesterday
                    ? (int) $streak['current_streak'] + 1
                    : 1;
                $longest = max((int) $streak['longest_streak'], $current);
                $stmt = $pdo->prepare(
                    'UPDATE streaks SET current_streak=:current,longest_streak=:longest,
                     last_active_date=:active_date WHERE user_id=:user_id'
                );
                $stmt->execute([
                    'current' => $current,
                    'longest' => $longest,
                    'active_date' => $today,
                    'user_id' => $userId,
                ]);
            }

            $pdo->commit();
            return [
                'current_streak' => $current,
                'longest_streak' => $longest,
                'last_active_date' => $today,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}