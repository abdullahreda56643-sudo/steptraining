<?php

declare(strict_types=1);

class Skill
{
    public static function findById(int $skillId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT s.id,s.name_ar,s.name_en,s.section_id,
                    sec.slug AS section_slug,sec.name_ar AS section_name_ar
             FROM skills s
             LEFT JOIN sections sec ON sec.id=s.section_id
             WHERE s.id=:id LIMIT 1'
        );
        $stmt->execute(['id' => $skillId]);

        return $stmt->fetch() ?: null;
    }

    public static function forSection(int $sectionId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id,name_ar,name_en,section_id
             FROM skills WHERE section_id=:section_id
             ORDER BY name_ar,id'
        );
        $stmt->execute(['section_id' => $sectionId]);

        return $stmt->fetchAll();
    }
}