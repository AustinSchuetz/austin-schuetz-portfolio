<?php

declare(strict_types=1);

class Router
{
    public function dispatch(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            http_response_code(301);
            header('Location: ' . rtrim($path, '/'));
            return;
        }

        if ($path === '/sitemap.xml') {
            Seo::sitemap();
            return;
        }

        if ($path === '/contact' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (class_exists('ContactHandler')) {
                (new ContactHandler())->handle();
                return;
            }
            http_response_code(404);
            return;
        }

        if ($path === '/') {
            $this->renderDoc('page', 'home', '/');
            return;
        }

        if (preg_match('#^/work/([a-z0-9-]{1,64})$#', $path, $m)) {
            $this->renderDoc('project', $m[1], $path);
            return;
        }

        if (preg_match('#^/([a-z0-9-]{1,64})$#', $path, $m)) {
            $this->renderDoc('page', $m[1], $path);
            return;
        }

        $this->notFound();
    }

    private function renderDoc(string $kind, string $slug, string $path): void
    {
        $doc = ContentStore::doc($kind, $slug);
        if ($doc === null || ($doc['status'] ?? 'draft') !== 'published') {
            $this->notFound();
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo View::render($kind === 'project' ? 'project' : 'page', [
            'doc' => $doc,
            'site' => ContentStore::site(),
            'path' => $path,
        ]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo View::render('404', [
            'doc' => ['title' => 'Not found', 'status' => 'published'],
            'site' => ContentStore::site(),
            'path' => '/404',
        ]);
    }
}
