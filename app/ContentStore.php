<?php

declare(strict_types=1);

/*
 * All content JSON reads go through here. This is the path-safety chokepoint:
 * no user input ever becomes a filesystem path without passing validSlug().
 */
final class ContentStore
{
    private static array $memo = [];

    public static function validSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9-]{1,64}$/', $slug);
    }

    public static function kindDir(string $kind): string
    {
        return match ($kind) {
            'page' => CONTENT_DIR . '/pages',
            'project' => CONTENT_DIR . '/projects',
            'archive' => CONTENT_DIR . '/archive',
            default => throw new InvalidArgumentException('Unknown kind: ' . $kind),
        };
    }

    /** Validated absolute path for a document, or null for a bad slug. */
    public static function path(string $kind, string $slug): ?string
    {
        if ($kind === 'site') {
            return CONTENT_DIR . '/site.json';
        }
        if (!self::validSlug($slug)) {
            return null;
        }
        return self::kindDir($kind) . '/' . $slug . '.json';
    }

    public static function site(): array
    {
        return self::read(CONTENT_DIR . '/site.json') ?? [];
    }

    public static function page(string $slug): ?array
    {
        return self::doc('page', $slug);
    }

    public static function project(string $slug): ?array
    {
        return self::doc('project', $slug);
    }

    public static function doc(string $kind, string $slug): ?array
    {
        $path = self::path($kind, $slug);
        if ($path === null) {
            return null;
        }
        $doc = self::read($path);
        if ($doc !== null) {
            $doc['slug'] = $slug;
        }
        return $doc;
    }

    /** @return array[] published projects sorted by sort_order */
    public static function listProjects(bool $publishedOnly = true): array
    {
        return self::listKind('project', $publishedOnly);
    }

    /** @return array[] published archive entries sorted by sort_order */
    public static function listArchive(bool $publishedOnly = true): array
    {
        return self::listKind('archive', $publishedOnly);
    }

    public static function listKind(string $kind, bool $publishedOnly = true): array
    {
        $dir = self::kindDir($kind);
        $docs = [];
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $slug = basename($file, '.json');
            if (!self::validSlug($slug)) {
                continue;
            }
            $doc = self::read($file);
            if ($doc === null) {
                continue;
            }
            if ($publishedOnly && ($doc['status'] ?? 'draft') !== 'published') {
                continue;
            }
            $doc['slug'] = $slug;
            $docs[] = $doc;
        }
        usort($docs, fn($a, $b) => ($a['sort_order'] ?? 999) <=> ($b['sort_order'] ?? 999));
        return $docs;
    }

    private static function read(string $file): ?array
    {
        if (array_key_exists($file, self::$memo)) {
            return self::$memo[$file];
        }
        $doc = null;
        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            $doc = is_array($decoded) ? $decoded : null;
        }
        return self::$memo[$file] = $doc;
    }
}
