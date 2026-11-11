<?php

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (!defined('APP_URL')) {
    $appUrl = getenv('APP_URL') ?: '';
    if ($appUrl === '') {
        $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
        $projectRoot = realpath(ROOT_PATH) ?: ROOT_PATH;
        $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
        $projectRoot = str_replace('\\', '/', $projectRoot);

        if ($documentRoot !== '' && strpos($projectRoot, $documentRoot . '/') === 0) {
            $appUrl = substr($projectRoot, strlen($documentRoot));
        } else {
            $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
            $appUrl = dirname($scriptName);
        }

        $appUrl = '/' . trim($appUrl, '/') . '/';
    } else {
        $appUrl = '/' . trim(str_replace('\\', '/', $appUrl), '/') . '/';
    }

    define('APP_URL', $appUrl);
}

spl_autoload_register(static function (string $class): void {
    $paths = [
        ROOT_PATH . '/core/' . $class . '.php',
        ROOT_PATH . '/app/controllers/' . $class . '.php',
        ROOT_PATH . '/app/controllers/admin/' . $class . '.php',
        ROOT_PATH . '/app/models/' . $class . '.php',
        ROOT_PATH . '/app/services/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
