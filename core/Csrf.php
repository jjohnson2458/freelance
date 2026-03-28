<?php

namespace Core;

class Csrf
{
    public static function init(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function token(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token()) . '">';
    }

    public static function verify(): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals(self::token(), $token);
    }

    public static function verifyOrFail(): void
    {
        if (!self::verify()) {
            http_response_code(403);
            echo 'Invalid CSRF token';
            exit;
        }
    }
}
