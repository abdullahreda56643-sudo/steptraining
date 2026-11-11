<?php

declare(strict_types=1);

class User
{
    public static function create(string $name, string $email, string $password): array
    {
        $pdo = Database::getConnection();
        $query = 'INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (:name, :email, :password_hash, :role, 1, :created_at)';
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'student',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return self::findByEmail(strtolower(trim($email)));
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }
}
