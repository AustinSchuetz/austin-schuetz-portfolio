<?php

declare(strict_types=1);

/*
 * File-backed sliding-window limiter. Keys are pre-hashed by callers so no
 * raw IPs land on disk.
 */
final class RateLimiter
{
    public static function hit(string $bucket, string $key, int $max, int $windowSeconds): bool
    {
        $safe = preg_replace('/[^a-z0-9_-]/i', '', $bucket . '_' . $key) ?? '';
        $file = STORAGE_DIR . '/ratelimit/' . substr($safe, 0, 100) . '.json';
        $now = time();

        $hits = [];
        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded)) {
                $hits = array_values(array_filter($decoded, fn($t) => is_int($t) && $t > $now - $windowSeconds));
            }
        }
        if (count($hits) >= $max) {
            return false;
        }
        $hits[] = $now;
        file_put_contents($file, json_encode($hits), LOCK_EX);
        return true;
    }
}
