<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

function apiDispatch(callable $action): void
{
    try {
        Database::initialize();
        $action();
    } catch (Throwable $e) {
        error_log('API request failed: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(
            ['ok' => false, 'error' => 'حدث خطأ غير متوقع.'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
