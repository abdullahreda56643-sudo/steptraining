<?php

declare(strict_types=1);

class DashboardController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::getConnection();

        $stats = $pdo->query(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'student') AS students,
                (SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1) AS active_students,
                (SELECT COUNT(*) FROM practices) AS practices,
                (SELECT COUNT(*) FROM practices WHERE is_active = 1) AS active_practices,
                (SELECT COUNT(*) FROM questions) AS questions,
                (SELECT COUNT(*) FROM questions WHERE is_active = 1) AS active_questions,
                (SELECT COUNT(*) FROM passages) AS passages,
                (SELECT COUNT(*) FROM skills) AS skills,
                (SELECT COUNT(*) FROM vocabulary) AS vocabulary,
                (SELECT COUNT(*) FROM vocabulary WHERE is_active = 1) AS active_vocabulary,
                (SELECT COUNT(*) FROM attempts a
                   INNER JOIN users u ON u.id = a.user_id
                   WHERE u.role = 'student' AND a.finished_at IS NOT NULL) AS completed_attempts,
                (SELECT COUNT(*) FROM attempts a
                   INNER JOIN users u ON u.id = a.user_id
                   WHERE u.role = 'student' AND a.finished_at IS NOT NULL AND DATE(a.finished_at) = CURRENT_DATE()) AS completed_today,
                (SELECT COUNT(DISTINCT a.user_id)
                   FROM attempts a INNER JOIN users u ON u.id = a.user_id
                   WHERE u.role = 'student' AND a.started_at >= CURRENT_DATE()) AS active_today,
                (SELECT ROUND(AVG(a.predicted_score))
                   FROM attempts a INNER JOIN users u ON u.id = a.user_id
                   WHERE u.role = 'student' AND a.finished_at IS NOT NULL AND a.predicted_score IS NOT NULL) AS average_score"
        )->fetch();

        $recentAttempts = $pdo->query(
            'SELECT a.type, a.started_at, a.finished_at, a.correct_count, a.total, a.predicted_score,
                    u.name AS student_name, p.title_ar AS practice_title
             FROM attempts a
             INNER JOIN users u ON u.id = a.user_id
             LEFT JOIN practices p ON p.id = a.practice_id
             WHERE u.role = \'student\'
             ORDER BY a.started_at DESC, a.id DESC
             LIMIT 8'
        )->fetchAll();

        $recentStudents = $pdo->query(
            "SELECT u.id, u.name, u.email, u.is_active, u.created_at,
                    COUNT(a.id) AS completed_attempts
             FROM users u
             LEFT JOIN attempts a ON a.user_id = u.id AND a.finished_at IS NOT NULL
             WHERE u.role = 'student'
             GROUP BY u.id, u.name, u.email, u.is_active, u.created_at
             ORDER BY u.created_at DESC, u.id DESC
             LIMIT 5"
        )->fetchAll();

        $activityCounts = $pdo->query(
            "SELECT DATE(a.finished_at) AS activity_date, COUNT(*) AS completed_count
             FROM attempts a
             INNER JOIN users u ON u.id = a.user_id
             WHERE u.role = 'student'
               AND a.finished_at IS NOT NULL
               AND a.finished_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY)
               AND a.finished_at < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY)
             GROUP BY DATE(a.finished_at)"
        )->fetchAll();
        $activityByDate = [];
        foreach ($activityCounts as $row) {
            $activityByDate[$row['activity_date']] = (int) $row['completed_count'];
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
        for ($offset = 6; $offset >= 0; $offset--) {
            $date = date('Y-m-d', strtotime('-' . $offset . ' days'));
            $weeklyActivity[] = [
                'date' => $date,
                'label' => $weekdayNames[(int) date('N', strtotime($date))],
                'count' => $activityByDate[$date] ?? 0,
            ];
        }

        $admin = Auth::user();
        view('admin/dashboard', [
            'title' => 'لوحة الإدارة',
            'adminName' => $admin['name'] ?? 'مدير المنصة',
            'stats' => $stats,
            'recentAttempts' => $recentAttempts,
            'recentStudents' => $recentStudents,
            'weeklyActivity' => $weeklyActivity,
        ], 'admin');
    }
}
