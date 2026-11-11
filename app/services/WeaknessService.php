<?php

declare(strict_types=1);

class WeaknessService
{
    public const WEAK_SKILL_THRESHOLD = 60;

    public static function threshold(): int
    {
        $stmt = Database::getConnection()->prepare("SELECT value FROM settings WHERE `key`='weak_skill_threshold' LIMIT 1");
        $stmt->execute();
        $threshold = filter_var($stmt->fetchColumn(), FILTER_VALIDATE_INT);

        return $threshold !== false && $threshold >= 1 && $threshold <= 99
            ? $threshold
            : self::WEAK_SKILL_THRESHOLD;
    }

    public static function forUser(int $userId): array
    {
        $threshold = self::threshold();
        $stmt = Database::getConnection()->prepare(
            'SELECT skill_id, name_ar, name_en, section_slug, COUNT(*) AS answer_count,
                    SUM(is_correct) AS correct_count
             FROM (
                SELECT q.skill_id, s.name_ar, s.name_en, sec.slug AS section_slug, aa.is_correct,
                       ROW_NUMBER() OVER (
                           PARTITION BY q.skill_id
                           ORDER BY aa.answered_at DESC, aa.id DESC
                       ) AS answer_rank
                FROM attempt_answers aa
                INNER JOIN attempts a ON a.id = aa.attempt_id
                INNER JOIN questions q ON q.id = aa.question_id
                INNER JOIN skills s ON s.id = q.skill_id
                LEFT JOIN sections sec ON sec.id = s.section_id
                WHERE a.user_id = :user_id
             ) recent_answers
             WHERE answer_rank <= 50
             GROUP BY skill_id, name_ar, name_en, section_slug
             ORDER BY (SUM(is_correct) / COUNT(*)) ASC, name_ar'
        );
        $stmt->execute(['user_id' => $userId]);

        $skills = [];
        foreach ($stmt->fetchAll() as $row) {
            $accuracy = (int) round(((int) $row['correct_count'] / (int) $row['answer_count']) * 100);
            $skills[] = [
                'id' => (int) $row['skill_id'],
                'name_ar' => $row['name_ar'],
                'name_en' => $row['name_en'],
                'section_slug' => $row['section_slug'],
                'answer_count' => (int) $row['answer_count'],
                'correct_count' => (int) $row['correct_count'],
                'accuracy' => $accuracy,
                'is_weak' => $accuracy < $threshold,
            ];
        }

        return $skills;
    }
}