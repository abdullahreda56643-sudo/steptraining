<?php

declare(strict_types=1);

class Question
{
    public static function forPractice(int $practiceId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT q.id,q.passage_id,q.text,q.option_a,q.option_b,q.option_c,q.option_d,
                    s.name_ar AS skill_name_ar,s.name_en AS skill_name_en,
                    p.title AS passage_title,p.body AS passage_body,p.audio_path
             FROM questions q
             INNER JOIN skills s ON s.id=q.skill_id
             LEFT JOIN passages p ON p.id=q.passage_id
             WHERE q.practice_id=:practice_id AND q.is_active=1
             ORDER BY q.sort_order,q.id'
        );
        $stmt->execute(['practice_id' => $practiceId]);

        return $stmt->fetchAll();
    }

    public static function forAttempt(int $attemptId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT q.id,q.passage_id,q.text,q.option_a,q.option_b,q.option_c,q.option_d,
                    s.name_ar AS skill_name_ar,s.name_en AS skill_name_en,
                    sec.slug AS section_slug,sec.name_ar AS section_name_ar,
                    p.title AS passage_title,p.body AS passage_body,p.audio_path
             FROM attempt_questions aq
             INNER JOIN questions q ON q.id=aq.question_id
             INNER JOIN skills s ON s.id=q.skill_id
             LEFT JOIN sections sec ON sec.id=s.section_id
             LEFT JOIN passages p ON p.id=q.passage_id
             WHERE aq.attempt_id=:attempt_id
             ORDER BY aq.position'
        );
        $stmt->execute(['attempt_id' => $attemptId]);

        return $stmt->fetchAll();
    }
}