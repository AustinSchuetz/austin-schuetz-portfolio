<?php

declare(strict_types=1);

/*
 * The PHP built-in server is only ever used for local dev, so it doubles as
 * the environment switch. An optional config.local.php (gitignored) can
 * override any key on the live host if ever needed.
 */
$isDev = PHP_SAPI === 'cli-server';

$config = [
    'env' => $isDev ? 'dev' : 'prod',
    'mail_transport' => $isDev ? 'log' : 'mail',
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_merge($config, require $local);
}

return $config;
