<?php

declare(strict_types=1);

class Vocabulary
{
    public static function forUser(int $userId, int $limit = 48, int $offset = 0): array
    {
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $stmt = Database::getConnection()->prepare(
            'SELECT v.id,v.word,v.meaning_ar,v.synonym,v.antonym,v.example,
                    vp.status AS progress_status
             FROM vocabulary v
             LEFT JOIN vocab_progress vp ON vp.vocab_id=v.id AND vp.user_id=:user_id
             WHERE v.is_active=1
             ORDER BY CASE vp.status WHEN \'review\' THEN 0 WHEN \'known\' THEN 2 ELSE 1 END, v.word
             LIMIT ' . $limit . ' OFFSET ' . $offset
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function progressSummaryForUser(int $userId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(v.id) AS total_count,
                    COALESCE(SUM(CASE WHEN vp.status IS NULL THEN 1 ELSE 0 END), 0) AS new_count,
                    COALESCE(SUM(CASE WHEN vp.status = \'review\' THEN 1 ELSE 0 END), 0) AS review_count,
                    COALESCE(SUM(CASE WHEN vp.status = \'known\' THEN 1 ELSE 0 END), 0) AS known_count
             FROM vocabulary v
             LEFT JOIN vocab_progress vp ON vp.vocab_id = v.id AND vp.user_id = :user_id
             WHERE v.is_active = 1'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch() ?: [
            'total_count' => 0,
            'new_count' => 0,
            'review_count' => 0,
            'known_count' => 0,
        ];
    }

    public static function saveProgress(int $userId, int $vocabId, string $status): void
    {
        if (!in_array($status, ['known', 'review'], true)) {
            throw new InvalidArgumentException('حالة المفردة غير صالحة.');
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM vocabulary WHERE id=:id AND is_active=1 LIMIT 1'
        );
        $stmt->execute(['id' => $vocabId]);
        if ($stmt->fetchColumn() === false) {
            throw new DomainException('المفردة غير موجودة.');
        }

        $stmt = Database::getConnection()->prepare(
            'INSERT INTO vocab_progress (user_id,vocab_id,status)
             VALUES (:user_id,:vocab_id,:status)
             ON DUPLICATE KEY UPDATE status=VALUES(status),updated_at=CURRENT_TIMESTAMP'
        );
        $stmt->execute(['user_id' => $userId, 'vocab_id' => $vocabId, 'status' => $status]);
    }
}