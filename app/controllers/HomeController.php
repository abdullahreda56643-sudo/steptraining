<?php

declare(strict_types=1);

class HomeController
{
    public static function index(): void
    {
        if (Auth::check() && (Auth::user()['role'] ?? '') === 'admin') {
            redirect('/admin');
        }

        Auth::requireRole('student');
        $user = Auth::user();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT s.slug, s.name_ar,
                    COUNT(DISTINCT CASE WHEN p.is_active = 1 THEN p.id END) AS total_practices,
                    COUNT(DISTINCT CASE WHEN p.is_active = 1 AND a.finished_at IS NOT NULL THEN p.id END) AS completed_practices
             FROM sections s
             LEFT JOIN practices p ON p.section_id = s.id
             LEFT JOIN attempts a ON a.practice_id = p.id AND a.user_id = :user_id
             GROUP BY s.id, s.slug, s.name_ar, s.sort_order
             ORDER BY s.sort_order, s.id'
        );
        $stmt->execute(['user_id' => $user['id']]);
        $sections = [];
        $colors = ['bg-emerald-500', 'bg-emerald-600', 'bg-teal-500', 'bg-green-600'];
        foreach ($stmt->fetchAll() as $index => $section) {
            $total = (int) $section['total_practices'];
            $completed = (int) $section['completed_practices'];
            $sections[] = [
                'slug' => $section['slug'],
                'name' => $section['name_ar'],
                'progress' => $total > 0 ? (int) round($completed / $total * 100) : 0,
                'color' => $colors[$index % count($colors)],
            ];
        }

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS completed_attempts,
                    COALESCE(SUM(correct_count), 0) AS correct_answers,
                    COALESCE(SUM(total), 0) AS answered_questions
             FROM attempts
             WHERE user_id = :user_id AND finished_at IS NOT NULL'
        );
        $stmt->execute(['user_id' => $user['id']]);
        $performance = $stmt->fetch();
        $completedAttempts = (int) $performance['completed_attempts'];
        $answeredQuestions = (int) $performance['answered_questions'];
        $accuracy = $answeredQuestions > 0
            ? (int) round((int) $performance['correct_answers'] / $answeredQuestions * 100)
            : 0;

        $today = new DateTimeImmutable('today');
        $weekStart = $today->modify('-6 days');
        $stmt = $pdo->prepare(
            'SELECT DATE(finished_at) AS activity_date, COUNT(*) AS completed_count
             FROM attempts
             WHERE user_id = :user_id
               AND finished_at IS NOT NULL
               AND finished_at >= :week_start
               AND finished_at < :tomorrow
             GROUP BY DATE(finished_at)'
        );
        $stmt->execute([
            'user_id' => $user['id'],
            'week_start' => $weekStart->format('Y-m-d H:i:s'),
            'tomorrow' => $today->modify('+1 day')->format('Y-m-d H:i:s'),
        ]);
        $activityByDate = [];
        foreach ($stmt->fetchAll() as $day) {
            $activityByDate[$day['activity_date']] = (int) $day['completed_count'];
        }

        $weekdayNames = [
            1 => 'الإثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
            7 => 'الأحد',
        ];
        $weeklyActivity = [];
        for ($offset = 0; $offset < 7; $offset++) {
            $date = $weekStart->modify('+' . $offset . ' days');
            $dateKey = $date->format('Y-m-d');
            $weeklyActivity[] = [
                'label' => $weekdayNames[(int) $date->format('N')],
                'count' => $activityByDate[$dateKey] ?? 0,
            ];
        }

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM attempt_answers aa
             INNER JOIN attempts a ON a.id = aa.attempt_id
             INNER JOIN questions q ON q.id = aa.question_id AND q.is_active = 1
             WHERE a.user_id = :user_id AND aa.is_correct = 0
               AND NOT EXISTS (
                   SELECT 1
                   FROM attempt_answers newer
                   INNER JOIN attempts newer_attempt ON newer_attempt.id = newer.attempt_id
                   WHERE newer.question_id = aa.question_id
                     AND newer_attempt.user_id = :latest_user_id
                     AND (newer.answered_at > aa.answered_at
                          OR (newer.answered_at = aa.answered_at AND newer.id > aa.id))
               )'
        );
        $stmt->execute(['user_id' => $user['id'], 'latest_user_id' => $user['id']]);
        $mistakesCount = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT id, correct_count, total FROM attempts
             WHERE user_id = :user_id AND finished_at IS NOT NULL AND total > 0
             ORDER BY finished_at DESC, id DESC LIMIT 1'
        );
        $stmt->execute(['user_id' => $user['id']]);
        $latestAttempt = $stmt->fetch() ?: null;
        $prediction = $latestAttempt
            ? ScoreService::predict(
                (int) $user['id'],
                (int) $latestAttempt['correct_count'] / (int) $latestAttempt['total'],
                (int) $latestAttempt['id']
            )['score']
            : null;

        $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'daily_challenge_size' LIMIT 1");
        $stmt->execute();
        $dailyChallengeSize = (int) ($stmt->fetchColumn() ?: 5);

        $streak = (int) Streak::forUser((int) $user['id'])['current_streak'];

        view('student/home', [
            'title' => 'الرئيسية',
            'sections' => $sections,
            'dailyChallenge' => $dailyChallengeSize,
            'mistakesCount' => $mistakesCount,
            'predictedScore' => $prediction,
            'streak' => $streak,
            'completedAttempts' => $completedAttempts,
            'answeredQuestions' => $answeredQuestions,
            'accuracy' => $accuracy,
            'weeklyActivity' => $weeklyActivity,
            'user' => $user,
        ], 'main');
    }
}
