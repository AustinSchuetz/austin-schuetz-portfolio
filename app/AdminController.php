<?php

declare(strict_types=1);

final class AdminController
{
    private const KINDS = ['page', 'project', 'archive', 'site'];

    public function route(string $r): void
    {
        Auth::start();
        $post = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

        if ($r === 'login') {
            $post ? $this->loginPost() : $this->view('login', ['error' => null]);
            return;
        }

        Auth::require();
        if ($post) {
            Csrf::validate();
        }

        match ($r) {
            'logout' => $this->logout($post),
            'dashboard' => $this->dashboard(),
            'list' => $this->listDocs(),
            'edit' => $this->edit(),
            'save' => $this->save($post),
            'create' => $this->create($post),
            'delete' => $this->delete($post),
            'preview' => $this->preview(),
            'media' => $this->view('media', ['images' => Uploads::list()]),
            'media-list' => $this->json(['ok' => true, 'images' => Uploads::list()]),
            'upload' => $this->upload($post),
            'delete-media' => $this->deleteMedia($post),
            'versions' => $this->versions(),
            'restore' => $this->restore($post),
            default => $this->dashboard(),
        };
    }

    private function loginPost(): void
    {
        Csrf::validate();
        $ok = Auth::login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''));
        if ($ok) {
            header('Location: /admin/');
            return;
        }
        http_response_code(401);
        $this->view('login', ['error' => 'Login failed (bad credentials or rate limit — wait 15 minutes).']);
    }

    private function logout(bool $post): void
    {
        if ($post) {
            Auth::logout();
        }
        header('Location: /admin/?r=login');
    }

    private function dashboard(): void
    {
        $this->view('dashboard', [
            'counts' => [
                'pages' => count(ContentStore::listKind('page', false)),
                'projects' => count(ContentStore::listKind('project', false)),
                'archive' => count(ContentStore::listKind('archive', false)),
                'media' => count(Uploads::list()),
            ],
        ]);
    }

    private function listDocs(): void
    {
        $kind = $this->kind();
        if ($kind === 'site') {
            header('Location: /admin/?r=edit&kind=site&slug=site');
            return;
        }
        $this->view('list', ['kind' => $kind, 'docs' => ContentStore::listKind($kind, false)]);
    }

    private function edit(): void
    {
        $kind = $this->kind();
        $slug = $this->slug();
        $doc = $kind === 'site' ? ContentStore::site() : ContentStore::doc($kind, $slug);
        if ($doc === null) {
            http_response_code(404);
            echo 'Document not found.';
            return;
        }
        $this->view('editor', [
            'kind' => $kind,
            'slug' => $slug,
            'doc' => $doc,
            'schema' => [
                'meta' => BlockSchema::meta($kind),
                'blocks' => BlockSchema::blocks(),
                'hasBlocks' => BlockSchema::hasBlocks($kind),
            ],
        ]);
    }

    private function save(bool $post): void
    {
        if (!$post) {
            $this->json(['ok' => false, 'error' => 'POST required'], 405);
            return;
        }
        $kind = $this->kind();
        $slug = $this->slug();
        $raw = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($raw)) {
            $this->json(['ok' => false, 'error' => 'Invalid JSON body'], 400);
            return;
        }
        $path = ContentStore::path($kind, $slug);
        if ($path === null || !is_file($path)) {
            $this->json(['ok' => false, 'error' => 'Unknown document'], 404);
            return;
        }
        $clean = BlockSchema::sanitizeDoc($kind, $raw);
        $clean['updated_at'] = gmdate('Y-m-d\TH:i:s\Z');

        Versions::snapshot($kind, $kind === 'site' ? 'site' : $slug);
        $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false || !Versions::atomicWrite($path, $json . "\n")) {
            $this->json(['ok' => false, 'error' => 'Write failed'], 500);
            return;
        }
        $this->json(['ok' => true, 'saved_at' => $clean['updated_at']]);
    }

    private function create(bool $post): void
    {
        if (!$post) {
            header('Location: /admin/');
            return;
        }
        $kind = $this->kind();
        if ($kind === 'site') {
            header('Location: /admin/');
            return;
        }
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $title = trim((string) ($_POST['title'] ?? ''));
        $path = ContentStore::path($kind, $slug);
        if ($path === null || is_file($path)) {
            http_response_code(400);
            echo 'Bad or duplicate slug.';
            return;
        }
        $doc = BlockSchema::sanitizeDoc($kind, [
            $kind === 'archive' ? 'name' : 'title' => $title !== '' ? $title : $slug,
            'status' => 'draft',
        ]);
        $doc['updated_at'] = gmdate('Y-m-d\TH:i:s\Z');
        Versions::atomicWrite($path, json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        header('Location: /admin/?r=edit&kind=' . $kind . '&slug=' . $slug);
    }

    private function delete(bool $post): void
    {
        if (!$post) {
            header('Location: /admin/');
            return;
        }
        $kind = $this->kind();
        $slug = $this->slug();
        if ($kind === 'site') {
            http_response_code(400);
            return;
        }
        $path = ContentStore::path($kind, $slug);
        if ($path !== null && is_file($path)) {
            Versions::snapshot($kind, $slug); // recoverable from versions
            unlink($path);
        }
        header('Location: /admin/?r=list&kind=' . $kind);
    }

    private function preview(): void
    {
        $kind = $this->kind();
        $slug = $this->slug();
        if (!in_array($kind, ['page', 'project'], true)) {
            http_response_code(400);
            return;
        }
        $doc = ContentStore::doc($kind, $slug);
        if ($doc === null) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo View::render($kind === 'project' ? 'project' : 'page', [
            'doc' => $doc,
            'site' => ContentStore::site(),
            'path' => $kind === 'project' ? '/work/' . $slug : ($slug === 'home' ? '/' : '/' . $slug),
        ]);
    }

    private function upload(bool $post): void
    {
        if (!$post || !isset($_FILES['file'])) {
            $this->json(['ok' => false, 'error' => 'No file'], 400);
            return;
        }
        $result = Uploads::handle($_FILES['file']);
        $this->json($result, $result['ok'] ? 200 : 422);
    }

    private function deleteMedia(bool $post): void
    {
        $ok = $post && Uploads::delete((string) ($_POST['path'] ?? ''));
        if (($_SERVER['HTTP_X_CSRF'] ?? '') !== '') {
            $this->json(['ok' => $ok]);
            return;
        }
        header('Location: /admin/?r=media');
    }

    private function versions(): void
    {
        $kind = $this->kind();
        $slug = $this->slug();
        $this->view('versions', [
            'kind' => $kind,
            'slug' => $slug,
            'versions' => Versions::list($kind, $kind === 'site' ? 'site' : $slug),
        ]);
    }

    private function restore(bool $post): void
    {
        $kind = $this->kind();
        $slug = $this->slug();
        if ($post) {
            Versions::restore($kind, $kind === 'site' ? 'site' : $slug, (string) ($_POST['version'] ?? ''));
        }
        header('Location: /admin/?r=edit&kind=' . $kind . '&slug=' . $slug);
    }

    private function kind(): string
    {
        $kind = (string) ($_GET['kind'] ?? 'page');
        if (!in_array($kind, self::KINDS, true)) {
            http_response_code(400);
            echo 'Bad kind';
            exit;
        }
        return $kind;
    }

    private function slug(): string
    {
        $slug = (string) ($_GET['slug'] ?? '');
        if ($slug === 'site' || ContentStore::validSlug($slug)) {
            return $slug;
        }
        http_response_code(400);
        echo 'Bad slug';
        exit;
    }

    private function view(string $name, array $vars): void
    {
        header('Content-Type: text/html; charset=utf-8');
        extract($vars, EXTR_SKIP);
        $csrf = Csrf::token();
        include dirname(APP_DIR) . '/admin/views/' . $name . '.php';
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
    }
}
