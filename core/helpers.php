<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . url($path), true, 302);
    exit;
}

function asset(string $path): string
{
    return url('public/' . ltrim($path, '/'));
}

function url(string $path): string
{
    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $path)) {
        return $path;
    }

    $base = '/' . trim((string) parse_url(APP_URL, PHP_URL_PATH), '/');
    $path = ltrim($path, '/');
    $baseSegments = array_values(array_filter(explode('/', $base), static fn (string $segment) => $segment !== ''));
    if (count($baseSegments) >= 2) {
        $last = end($baseSegments);
        $first = reset($baseSegments);
        if ($last !== false && $first !== false && $last === $first) {
            $base = '/' . implode('/', array_unique($baseSegments));
        }
    }

    $basePrefix = rtrim($base, '/') . '/';
    if ($base !== '/' && ($path === trim($base, '/') || str_starts_with($path, trim($base, '/') . '/'))) {
        return '/' . ltrim($path, '/');
    }

    $base = rtrim($base, '/');
    return ($base === '' ? '' : $base) . '/' . $path;
}

function csrfField(): string
{
    $token = Csrf::token();

    return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
}

function flash(string $key, ?string $value = null): ?string
{
    if (!isset($_SESSION)) {
        session_start();
    }

    if ($value === null) {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);

        return $message;
    }

    $_SESSION['flash'][$key] = $value;

    return $value;
}

function old(string $key, $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function view(string $template, array $data = [], ?string $layout = 'main'): void
{
    $viewPath = ROOT_PATH . '/app/views/' . $template . '.php';

    if (!is_file($viewPath)) {
        throw new RuntimeException('Template not found: ' . $template);
    }

    extract($data, EXTR_SKIP);

    if ($layout) {
        $content = null;
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        include ROOT_PATH . '/app/views/layouts/' . $layout . '.php';
        return;
    }

    include $viewPath;
}
