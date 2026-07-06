<?php

declare(strict_types=1);

/*
 * Single-admin session auth. Credentials live in storage/auth/admin.json,
 * created only via `php scripts/make-admin.php` — there is no web installer.
 */
final class Auth
{
    private const SESSION_NAME = 'as_admin';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name(self::SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/admin',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function login(string $username, string $password): bool
    {
        $ipKey = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!RateLimiter::hit('login', $ipKey, 5, 900)) {
            return false;
        }
        $creds = self::credentials();
        if ($creds === null) {
            return false;
        }
        if (!hash_equals($creds['username'], $username) || !password_verify($password, $creds['password_hash'])) {
            return false;
        }
        self::start();
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['login_at'] = time();
        return true;
    }

    public static function check(): bool
    {
        self::start();
        return !empty($_SESSION['admin']);
    }

    public static function require(): void
    {
        if (!self::check()) {
            header('Location: /admin/?r=login');
            exit;
        }
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    private static function credentials(): ?array
    {
        $file = STORAGE_DIR . '/auth/admin.json';
        if (!is_file($file)) {
            return null;
        }
        $creds = json_decode((string) file_get_contents($file), true);
        return (is_array($creds) && isset($creds['username'], $creds['password_hash'])) ? $creds : null;
    }
}
