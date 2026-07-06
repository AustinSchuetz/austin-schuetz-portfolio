<?php

declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        Auth::start();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    /** Validate the token on any mutating request; dies with 403 on failure. */
    public static function validate(): void
    {
        Auth::start();
        $sent = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? '';
        $known = $_SESSION['csrf'] ?? '';
        if ($known === '' || !is_string($sent) || !hash_equals($known, $sent)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'CSRF token mismatch']);
            exit;
        }
    }
}
