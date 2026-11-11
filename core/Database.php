<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $connection = null;

    public static function initialize(): void
    {
        if (self::$connection instanceof PDO) {
            return;
        }

        $config = require ROOT_PATH . '/config/database.php';
        if (($config['driver'] ?? 'mysql') !== 'mysql') {
            throw new RuntimeException('Only the MySQL database driver is supported.');
        }

        $sql = file_get_contents(ROOT_PATH . '/database/schema.sql');
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('The database schema file is missing or empty.');
        }

        $database = (string) ($config['database'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new InvalidArgumentException('Invalid database name configuration.');
        }
        $sql = str_replace('`steptraining`', '`' . $database . '`', $sql);

        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            $config['host'],
            (int) ($config['port'] ?? 3306),
            $config['charset']
        );
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql);
        if ($statements === false) {
            throw new RuntimeException('Unable to parse the database schema.');
        }

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }

        self::$connection = $pdo;
    }

    public static function getConnection(): PDO
    {
        if (!self::$connection instanceof PDO) {
            self::initialize();
        }

        return self::$connection;
    }
}
