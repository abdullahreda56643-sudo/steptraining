<?php

declare(strict_types=1);

class Section
{
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id,slug,name_en,name_ar,tip_text,sort_order
             FROM sections WHERE slug=:slug LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);

        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::getConnection()->query(
            'SELECT id,slug,name_en,name_ar,tip_text,sort_order
             FROM sections ORDER BY sort_order,id'
        )->fetchAll();
    }

    public static function practicesForUser(int $sectionId, int $userId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT p.*,
                    CASE
                        WHEN latest.id IS NULL THEN \'not_started\'
                        WHEN latest.finished_at IS NULL THEN \'in_progress\'
                        ELSE \'completed\'
                    END AS student_status,
                    COALESCE(summary.best_score,0) AS best_score,
                    CASE
                        WHEN latest.id IS NULL OR latest.total=0 THEN 0
                        WHEN latest.finished_at IS NOT NULL THEN 100
                        ELSE ROUND(COALESCE(answered.total_answered,0)*100/latest.total)
                    END AS progress_percent
             FROM practices p
             LEFT JOIN (
                SELECT practice_id,MAX(id) AS latest_attempt_id,MAX(predicted_score) AS best_score
                FROM attempts
                WHERE user_id=:user_id AND practice_id IS NOT NULL
                GROUP BY practice_id
             ) summary ON summary.practice_id=p.id
             LEFT JOIN attempts latest ON latest.id=summary.latest_attempt_id
             LEFT JOIN (
                SELECT attempt_id,COUNT(*) AS total_answered
                FROM attempt_answers GROUP BY attempt_id
             ) answered ON answered.attempt_id=latest.id
             WHERE p.section_id=:section_id AND p.is_active=1
             ORDER BY p.sort_order,p.id'
        );
        $stmt->execute(['user_id' => $userId, 'section_id' => $sectionId]);

        return $stmt->fetchAll();
    }
}