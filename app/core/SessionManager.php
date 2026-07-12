<?php

function session_manager_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function session_get(string $key, mixed $default = null): mixed
{
    return $_SESSION[$key] ?? $default;
}

function session_set(string $key, mixed $value): void
{
    $_SESSION[$key] = $value;
}

function session_has(string $key): bool
{
    return isset($_SESSION[$key]);
}



function session_destroy_all(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function session_collection_push(string $key, array $item): array
{
    $collection = session_get($key, []);
    $ids = array_column($collection, 'id');
    $item['id'] = $ids ? max($ids) + 1 : 1;
    $collection[] = $item;
    session_set($key, $collection);
    return $item;
}

function session_collection_find(string $key, int $id): ?array
{
    foreach (session_get($key, []) as $item) {
        if ((int) $item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

function flash_set(string $type, string $message): void
{
    session_set('_flash', ['type' => $type, 'message' => $message]);
}

function flash_get(): ?array
{
    $flash = session_get('_flash');
    session_set('_flash', null);
    return $flash;
}


function csrf_token(): string
{
    if (!session_has('_csrf_token')) {
        session_set('_csrf_token', bin2hex(random_bytes(32)));
    }
    return session_get('_csrf_token');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    return $token !== null && session_has('_csrf_token') && hash_equals(session_get('_csrf_token'), $token);
}