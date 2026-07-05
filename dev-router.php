<?php

/*
 * Router script for the PHP built-in server (local dev only):
 *   php -S localhost:8080 dev-router.php
 *
 * Mirrors the production .htaccess rules so local behavior matches the live
 * host: protected directories 403, real files are served as-is, /admin goes
 * to the admin front controller, everything else goes to index.php.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

if (
    preg_match('#^/(app|content|storage|templates|scripts|tests|design)(/|$)#', $path)
    || preg_match('#(^|/)\.(git|env|htaccess)#', $path)
    || preg_match('#\.(json|md|yml|lock)$#', $path)
) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Forbidden';
    return true;
}

if ($path !== '/' && is_file(__DIR__ . $path)) {
    if (preg_match('#^/media/uploads/.+\.(php|phtml|phar)#i', $path)) {
        http_response_code(403);
        return true; // mirror the uploads no-PHP rule
    }
    return false; // let the built-in server stream the file
}

if (preg_match('#^/admin($|/)#', $path)) {
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    require __DIR__ . '/admin/index.php';
    return true;
}

require __DIR__ . '/index.php';
return true;
