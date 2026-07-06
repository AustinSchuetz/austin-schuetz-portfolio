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

    // Cloudflare Turnstile — spam protection on the contact form. Both blank =
    // disabled (form still works). To enable, set BOTH in config.local.php on
    // the live host (the secret must never be committed):
    //   'turnstile_site_key' => '0x4AAAA...',   // public, rendered in the page
    //   'turnstile_secret'   => '0x4AAAA...',   // private, used server-side only
    'turnstile_site_key' => '',
    'turnstile_secret' => '',
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_merge($config, require $local);
}

return $config;
