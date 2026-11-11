<?php

declare(strict_types=1);

class Practice
{
    public static function findActiveById(int $practiceId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT p.*, s.slug AS section_slug, s.name_ar AS section_name_ar,
                    s.name_en AS section_name_en, s.tip_text
             FROM practices p
             INNER JOIN sections s ON s.id = p.section_id
             WHERE p.id = :id AND p.is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $practiceId]);

        return $stmt->fetch() ?: null;
    }

    public static function questions(int $practiceId): array
    {
        return Question::forPractice($practiceId);
    }

    public static function questionsForAttempt(int $attemptId): array
    {
        return Question::forAttempt($attemptId);
    }

    public static function customQuestionIds(int $userId, int $skillId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT q.id
             FROM questions q
             LEFT JOIN (
                SELECT answer.question_id, answer.is_correct
                FROM attempt_answers answer
                INNER JOIN (
                    SELECT aa.question_id, MAX(aa.id) AS latest_answer_id
                    FROM attempt_answers aa
                    INNER JOIN attempts a ON a.id = aa.attempt_id
                    WHERE a.user_id = :user_id
                    GROUP BY aa.question_id
                ) latest ON latest.latest_answer_id = answer.id
             ) latest_answer ON latest_answer.question_id = q.id
             WHERE q.skill_id = :skill_id AND q.is_active = 1
               AND (latest_answer.is_correct IS NULL OR latest_answer.is_correct = 0)
             ORDER BY RAND()
             LIMIT 10'
        );
        $stmt->execute(['user_id' => $userId, 'skill_id' => $skillId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function findSkill(int $skillId): ?array
    {
        return Skill::findById($skillId);
    }
}