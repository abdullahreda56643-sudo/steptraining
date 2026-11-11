<?php

declare(strict_types=1);

class Attempt
{
    public static function findOrCreateForPractice(int $userId, int $practiceId, array $questionIds): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id FROM attempts
             WHERE user_id = :user_id AND practice_id = :practice_id
               AND type = 'practice' AND finished_at IS NULL
             ORDER BY started_at DESC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId, 'practice_id' => $practiceId]);
        $existing = $stmt->fetchColumn();

        if ($existing !== false) {
            $attemptId = (int) $existing;
            self::ensureQuestionSet($attemptId, $questionIds);
            return $attemptId;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO attempts (user_id, practice_id, type, total)
             VALUES (:user_id, :practice_id, 'practice', :total)"
        );
        $stmt->execute([
            'user_id' => $userId,
            'practice_id' => $practiceId,
            'total' => count($questionIds),
        ]);

        $attemptId = (int) $pdo->lastInsertId();
        self::ensureQuestionSet($attemptId, $questionIds);

        return $attemptId;
    }

    public static function createQuestionAttempt(int $userId, string $type, array $questionIds): int
    {
        if (!in_array($type, ['custom', 'mistakes', 'placement'], true) || !$questionIds) {
            throw new InvalidArgumentException('Invalid question attempt configuration.');
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO attempts (user_id, practice_id, type, total)
                 VALUES (:user_id, NULL, :type, :total)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'type' => $type,
                'total' => count($questionIds),
            ]);
            $attemptId = (int) $pdo->lastInsertId();
            self::ensureQuestionSet($attemptId, $questionIds);
            $pdo->commit();

            return $attemptId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function findOpenByType(int $userId, string $type): ?int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM attempts
             WHERE user_id=:user_id AND type=:type AND finished_at IS NULL
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId, 'type' => $type]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    public static function ensureQuestionSet(int $attemptId, array $questionIds): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM attempt_questions WHERE attempt_id = :attempt_id');
        $stmt->execute(['attempt_id' => $attemptId]);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $insert = $pdo->prepare(
            'INSERT INTO attempt_questions (attempt_id, question_id, position)
             VALUES (:attempt_id, :question_id, :position)'
        );
        foreach (array_values($questionIds) as $position => $questionId) {
            $insert->execute([
                'attempt_id' => $attemptId,
                'question_id' => (int) $questionId,
                'position' => $position + 1,
            ]);
        }
    }

    public static function questionCount(int $attemptId): int
    {
        $stmt = Database::getConnection()->prepare('SELECT COUNT(*) FROM attempt_questions WHERE attempt_id = :attempt_id');
        $stmt->execute(['attempt_id' => $attemptId]);

        return (int) $stmt->fetchColumn();
    }

    public static function latestMistakeQuestionIds(int $userId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT q.id
             FROM questions q
             INNER JOIN attempt_answers aa ON aa.question_id = q.id
             INNER JOIN attempts a ON a.id = aa.attempt_id
             WHERE a.user_id = :user_id
               AND aa.is_correct = 0
               AND NOT EXISTS (
                   SELECT 1
                   FROM attempt_answers newer
                   INNER JOIN attempts newer_attempt ON newer_attempt.id = newer.attempt_id
                   WHERE newer.question_id = aa.question_id
                     AND newer_attempt.user_id = :latest_user_id
                     AND (newer.answered_at > aa.answered_at
                          OR (newer.answered_at = aa.answered_at AND newer.id > aa.id))
               )
               AND q.is_active = 1
             ORDER BY q.id'
        );
        $stmt->execute(['user_id' => $userId, 'latest_user_id' => $userId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function answers(int $attemptId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT aa.question_id, aa.chosen, aa.is_correct, q.correct,
                    q.explanation_ar, q.wrong_a_ar, q.wrong_b_ar, q.wrong_c_ar, q.wrong_d_ar
             FROM attempt_answers aa
             INNER JOIN questions q ON q.id = aa.question_id
             WHERE aa.attempt_id = :attempt_id'
        );
        $stmt->execute(['attempt_id' => $attemptId]);

        $answers = [];
        foreach ($stmt->fetchAll() as $answer) {
            $answers[(int) $answer['question_id']] = $answer;
        }

        return $answers;
    }

    public static function findForUser(int $attemptId, int $userId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT a.*, p.title_ar, p.title_en, s.slug AS section_slug, s.name_ar AS section_name_ar
             FROM attempts a
             LEFT JOIN practices p ON p.id = a.practice_id
             LEFT JOIN sections s ON s.id = p.section_id
             WHERE a.id = :attempt_id AND a.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['attempt_id' => $attemptId, 'user_id' => $userId]);

        return $stmt->fetch() ?: null;
    }

    public static function submitAnswer(int $attemptId, int $userId, int $questionId, string $choice): array
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'SELECT id, practice_id, finished_at FROM attempts
                 WHERE id = :id AND user_id = :user_id FOR UPDATE'
            );
            $stmt->execute(['id' => $attemptId, 'user_id' => $userId]);
            $attempt = $stmt->fetch();

            if (!$attempt || $attempt['finished_at'] !== null) {
                throw new DomainException('المحاولة غير متاحة أو تم إنهاؤها.');
            }

            $stmt = $pdo->prepare(
                'SELECT q.correct, q.explanation_ar, q.wrong_a_ar, q.wrong_b_ar,
                        q.wrong_c_ar, q.wrong_d_ar
                 FROM questions q
                 INNER JOIN attempt_questions aq ON aq.question_id = q.id
                 WHERE q.id = :question_id AND aq.attempt_id = :attempt_id
                 LIMIT 1'
            );
            $stmt->execute([
                'question_id' => $questionId,
                'attempt_id' => $attemptId,
            ]);
            $question = $stmt->fetch();

            if (!$question) {
                throw new DomainException('السؤال غير تابع لهذا التدريب.');
            }

            $stmt = $pdo->prepare(
                'SELECT id FROM attempt_answers WHERE attempt_id = :attempt_id AND question_id = :question_id LIMIT 1'
            );
            $stmt->execute(['attempt_id' => $attemptId, 'question_id' => $questionId]);
            if ($stmt->fetchColumn() !== false) {
                throw new DomainException('تمت الإجابة عن هذا السؤال مسبقاً.');
            }

            $isCorrect = hash_equals((string) $question['correct'], $choice);
            $stmt = $pdo->prepare(
                'INSERT INTO attempt_answers (attempt_id, question_id, chosen, is_correct)
                 VALUES (:attempt_id, :question_id, :chosen, :is_correct)'
            );
            $stmt->execute([
                'attempt_id' => $attemptId,
                'question_id' => $questionId,
                'chosen' => $choice,
                'is_correct' => $isCorrect ? 1 : 0,
            ]);

            $pdo->commit();

            return [
                'is_correct' => $isCorrect,
                'correct' => $question['correct'],
                'explanation' => $question['explanation_ar'],
                'wrong_reasons' => [
                    'A' => $question['wrong_a_ar'],
                    'B' => $question['wrong_b_ar'],
                    'C' => $question['wrong_c_ar'],
                    'D' => $question['wrong_d_ar'],
                ],
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    public static function finish(int $attemptId, int $userId, int $total): array
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'SELECT id, type, correct_count, total, finished_at
                 FROM attempts WHERE id = :id AND user_id = :user_id FOR UPDATE'
            );
            $stmt->execute(['id' => $attemptId, 'user_id' => $userId]);
            $attempt = $stmt->fetch();

            if (!$attempt) {
                throw new DomainException('المحاولة غير موجودة.');
            }

            if ($attempt['finished_at'] !== null) {
                $pdo->commit();
                return [
                    'correct_count' => (int) $attempt['correct_count'],
                    'total' => (int) $attempt['total'],
                ];
            }

            $stmt = $pdo->prepare(
                'SELECT COUNT(*) AS answered, COALESCE(SUM(is_correct), 0) AS correct_count
                 FROM attempt_answers WHERE attempt_id = :attempt_id'
            );
            $stmt->execute(['attempt_id' => $attemptId]);
            $result = $stmt->fetch();
            $correctCount = (int) $result['correct_count'];
            $total = max(0, $total);
            if (in_array($attempt['type'], ['daily', 'placement'], true)
                && (int) $result['answered'] < $total) {
                $message = $attempt['type'] === 'daily'
                    ? 'أجب عن جميع أسئلة التحدي اليومي قبل إنهائه.'
                    : 'أجب عن جميع أسئلة تحديد المستوى قبل إنهائه.';
                throw new DomainException($message);
            }

            $stmt = $pdo->prepare(
                'UPDATE attempts SET correct_count = :correct_count, total = :total,
                    finished_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND finished_at IS NULL'
            );
            $stmt->execute([
                'correct_count' => $correctCount,
                'total' => $total,
                'id' => $attemptId,
            ]);

            $pdo->commit();

            return ['correct_count' => $correctCount, 'total' => $total];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }
}