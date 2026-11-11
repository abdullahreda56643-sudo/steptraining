<?php

declare(strict_types=1);

class DailyChallengeService
{
    public static function challengeDate(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone('Asia/Riyadh')))->format('Y-m-d');
    }

    public static function questionIds(?string $date = null): array
    {
        $date = $date ?? self::challengeDate();
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT id,question_ids FROM daily_challenges WHERE challenge_date=:challenge_date FOR UPDATE'
            );
            $stmt->execute(['challenge_date' => $date]);
            $challenge = $stmt->fetch();
            if (!$challenge) {
                $size = self::setting('daily_challenge_size', 5, 1, 20);
                $questionIds = self::balancedQuestionIds($size);
                if (!$questionIds) {
                    $pdo->commit();
                    return [];
                }
                $insert = $pdo->prepare(
                    'INSERT INTO daily_challenges (challenge_date,question_ids)
                     VALUES (:challenge_date,:question_ids)'
                );
                $insert->execute([
                    'challenge_date' => $date,
                    'question_ids' => json_encode($questionIds, JSON_THROW_ON_ERROR),
                ]);
            } else {
                $questionIds = json_decode((string) $challenge['question_ids'], true, 512, JSON_THROW_ON_ERROR);
                $questionIds = is_array($questionIds) ? array_values(array_map('intval', $questionIds)) : [];
            }
            $pdo->commit();

            return self::activeQuestionIds($questionIds);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $stmt = $pdo->prepare(
                'SELECT question_ids FROM daily_challenges WHERE challenge_date=:challenge_date LIMIT 1'
            );
            $stmt->execute(['challenge_date' => $date]);
            $storedIds = $stmt->fetchColumn();
            if ($storedIds === false) {
                throw $e;
            }
            $questionIds = json_decode((string) $storedIds, true, 512, JSON_THROW_ON_ERROR);

            return self::activeQuestionIds(array_map('intval', is_array($questionIds) ? $questionIds : []));
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function findOrStartAttempt(int $userId, array $questionIds): ?int
    {
        if (!$questionIds) {
            return null;
        }
        $date = self::challengeDate();
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT attempt_id FROM daily_challenge_attempts
                 WHERE user_id=:user_id AND challenge_date=:challenge_date FOR UPDATE'
            );
            $stmt->execute(['user_id' => $userId, 'challenge_date' => $date]);
            $attemptId = $stmt->fetchColumn();
            if ($attemptId !== false) {
                $pdo->commit();
                return (int) $attemptId;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO attempts (user_id,practice_id,type,total)
                 VALUES (:user_id,NULL,'daily',:total)"
            );
            $stmt->execute(['user_id' => $userId, 'total' => count($questionIds)]);
            $attemptId = (int) $pdo->lastInsertId();
            Attempt::ensureQuestionSet($attemptId, $questionIds);

            $stmt = $pdo->prepare(
                'INSERT INTO daily_challenge_attempts (user_id,challenge_date,attempt_id)
                 VALUES (:user_id,:challenge_date,:attempt_id)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'challenge_date' => $date,
                'attempt_id' => $attemptId,
            ]);
            $pdo->commit();

            return $attemptId;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $stmt = $pdo->prepare(
                'SELECT attempt_id FROM daily_challenge_attempts
                 WHERE user_id=:user_id AND challenge_date=:challenge_date LIMIT 1'
            );
            $stmt->execute(['user_id' => $userId, 'challenge_date' => $date]);
            $existingId = $stmt->fetchColumn();
            if ($existingId === false) {
                throw $e;
            }

            return (int) $existingId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function attemptForUserToday(int $userId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT a.id,a.finished_at,a.correct_count,a.total
             FROM daily_challenge_attempts dca
             INNER JOIN attempts a ON a.id=dca.attempt_id
             WHERE dca.user_id=:user_id AND dca.challenge_date=:challenge_date LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId, 'challenge_date' => self::challengeDate()]);

        return $stmt->fetch() ?: null;
    }

    private static function balancedQuestionIds(int $size): array
    {
        $rows = Database::getConnection()->query(
            'SELECT q.id,s.id AS section_id
             FROM questions q
             INNER JOIN practices p ON p.id=q.practice_id AND p.is_active=1
             INNER JOIN sections s ON s.id=p.section_id
             WHERE q.is_active=1
             ORDER BY RAND()'
        )->fetchAll();

        return self::balance($rows, $size);
    }

    public static function balance(array $rows, int $size): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $sectionId = (int) ($row['section_id'] ?? 0);
            $groups[$sectionId][] = (int) $row['id'];
        }
        $selected = [];
        while (count($selected) < $size && $groups) {
            foreach (array_keys($groups) as $sectionId) {
                if (!$groups[$sectionId]) {
                    unset($groups[$sectionId]);
                    continue;
                }
                $selected[] = array_shift($groups[$sectionId]);
                if (count($selected) >= $size) {
                    break;
                }
                if (!$groups[$sectionId]) {
                    unset($groups[$sectionId]);
                }
            }
        }

        return $selected;
    }

    private static function activeQuestionIds(array $questionIds): array
    {
        if (!$questionIds) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM questions WHERE is_active=1 AND id IN (' . $placeholders . ')'
        );
        $stmt->execute($questionIds);
        $active = array_fill_keys(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);

        return array_values(array_filter($questionIds, static function (int $id) use ($active): bool {
            return isset($active[$id]);
        }));
    }

    private static function setting(string $key, int $default, int $min, int $max): int
    {
        $stmt = Database::getConnection()->prepare('SELECT value FROM settings WHERE `key`=:key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = filter_var($stmt->fetchColumn(), FILTER_VALIDATE_INT);

        return $value !== false && $value >= $min && $value <= $max ? $value : $default;
    }
}