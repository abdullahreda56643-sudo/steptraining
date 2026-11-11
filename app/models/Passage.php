<?php

declare(strict_types=1);

class Passage
{
    public static function findForPractice(int $practiceId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id,title,body,audio_path FROM passages WHERE practice_id=:practice_id ORDER BY id LIMIT 1'
        );
        $stmt->execute(['practice_id' => $practiceId]);

        return $stmt->fetch() ?: null;
    }
}