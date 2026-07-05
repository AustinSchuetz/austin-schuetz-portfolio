<?php

declare(strict_types=1);

require __DIR__ . '/paths.php';

spl_autoload_register(static function (string $class): void {
    $file = APP_DIR . '/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$GLOBALS['config'] = require APP_DIR . '/config.php';

error_reporting(E_ALL);
ini_set('display_errors', config('env') === 'dev' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_DIR . '/logs/app.log');
ini_set('session.use_strict_mode', '1');

function config(string $key, mixed $default = null): mixed
{
    return $GLOBALS['config'][$key] ?? $default;
}

/** Escape for HTML output. Every echo in every template goes through this. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
