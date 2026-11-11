<?php

declare(strict_types=1);

class Csrf
{
    private static ?array $decodedBody = null;

    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validate(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $submitted = $_POST['_csrf'] ?? '';
        if ($submitted === '') {
            $submitted = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        if ($submitted === '') {
            $body = self::jsonBody();
            $submitted = (string) ($body['_csrf'] ?? '');
        }
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        return $sessionToken !== '' && $submitted !== '' && hash_equals($sessionToken, $submitted);
    }

    public static function jsonBody(): array
    {
        if (self::$decodedBody === null) {
            $body = json_decode((string) file_get_contents('php://input'), true);
            self::$decodedBody = is_array($body) ? $body : [];
        }

        return self::$decodedBody;
    }
}
