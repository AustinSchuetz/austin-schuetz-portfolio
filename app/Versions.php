<?php

declare(strict_types=1);

/*
 * Timestamped copies of a document taken before every save/restore/delete.
 * The newest KEEP snapshots are retained per document.
 */
final class Versions
{
    private const KEEP = 20;

    public static function snapshot(string $kind, string $slug): void
    {
        $source = ContentStore::path($kind, $slug);
        if ($source === null || !is_file($source)) {
            return;
        }
        $dir = self::dir($kind, $slug);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        copy($source, $dir . '/' . gmdate('Ymd\THis\Z') . '.json');
        self::prune($dir);
    }

    /** @return string[] snapshot filenames, newest first */
    public static function list(string $kind, string $slug): array
    {
        $files = glob(self::dir($kind, $slug) . '/*.json') ?: [];
        $names = array_map(fn($f) => basename($f), $files);
        rsort($names);
        return $names;
    }

    public static function restore(string $kind, string $slug, string $name): bool
    {
        if (!preg_match('/^\d{8}T\d{6}Z\.json$/', $name)) {
            return false;
        }
        $file = self::dir($kind, $slug) . '/' . $name;
        $target = ContentStore::path($kind, $slug);
        if ($target === null || !is_file($file)) {
            return false;
        }
        self::snapshot($kind, $slug); // current state becomes a snapshot first
        return self::atomicWrite($target, (string) file_get_contents($file));
    }

    public static function atomicWrite(string $path, string $contents): bool
    {
        $tmp = $path . '.tmp';
        if (file_put_contents($tmp, $contents, LOCK_EX) === false) {
            return false;
        }
        return rename($tmp, $path);
    }

    private static function dir(string $kind, string $slug): string
    {
        if (!ContentStore::validSlug($slug) && $kind !== 'site') {
            throw new InvalidArgumentException('Bad slug');
        }
        return STORAGE_DIR . '/versions/' . $kind . '/' . ($kind === 'site' ? 'site' : $slug);
    }

    private static function prune(string $dir): void
    {
        $files = glob($dir . '/*.json') ?: [];
        rsort($files);
        foreach (array_slice($files, self::KEEP) as $old) {
            @unlink($old);
        }
    }
}
