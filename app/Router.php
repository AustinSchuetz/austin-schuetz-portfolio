<?php

declare(strict_types=1);

class Router
{
    public function dispatch(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $trimmed = rtrim($path, '/');
        if ($trimmed !== '' && $trimmed !== $path) {
            http_response_code(301);
            header('Location: ' . $trimmed);
            return;
        }

        // Phase 0 stub — replaced by the full content-driven router in Phase 1.
        if ($path === '/') {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><meta charset="utf-8"><title>austinschuetz.com</title><h1>Skeleton up — CMS core lands next.</h1>';
            return;
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta charset="utf-8"><title>404</title><h1>Off the trail.</h1>';
    }
}
