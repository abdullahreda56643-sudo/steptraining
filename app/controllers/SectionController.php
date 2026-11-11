<?php

declare(strict_types=1);

class SectionController
{
    public function index(string $slug): void
    {
        Auth::requireRole('student');
        $section = Section::findBySlug($slug);
        if (!$section) {
            http_response_code(404);
            view('errors/404', ['message' => 'القسم المطلوب غير موجود.']);
            return;
        }

        $practices = Section::practicesForUser((int) $section['id'], (int) Auth::user()['id']);

        view('student/section', [
            'title' => $section['name_ar'],
            'section' => $section,
            'practices' => $practices,
        ], 'main');
    }
}
