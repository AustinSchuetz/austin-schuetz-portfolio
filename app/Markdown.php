<?php

declare(strict_types=1);

/*
 * Deliberately small Markdown subset, safe by construction: every text node
 * is HTML-escaped before any tags are added, and the renderer emits only its
 * own whitelisted tags. Raw HTML in the source is displayed, never executed.
 *
 * Supported: paragraphs (single newline = <br>), ##/###/#### headings,
 * **bold**, *italic*, `code`, [links](url) with scheme whitelist, - and 1.
 * lists, > blockquotes, ``` fenced code, --- horizontal rule.
 */
final class Markdown
{
    public static function render(?string $md): string
    {
        if ($md === null || trim($md) === '') {
            return '';
        }
        $md = preg_replace('/\R/', "\n", $md) ?? '';

        // Pull fenced code blocks out first so nothing inside them is parsed.
        $segments = preg_split('/^```([a-zA-Z0-9+-]*)\n(.*?)^```$/ms', $md, -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';
        for ($i = 0; $i < count($segments); $i += 3) {
            $html .= self::renderBlocks($segments[$i]);
            if (isset($segments[$i + 2])) {
                $lang = $segments[$i + 1] !== '' ? ' class="language-' . e($segments[$i + 1]) . '"' : '';
                $html .= '<pre><code' . $lang . '>' . e(rtrim($segments[$i + 2], "\n")) . '</code></pre>' . "\n";
            }
        }
        return $html;
    }

    private static function renderBlocks(string $text): string
    {
        $html = '';
        foreach (preg_split('/\n{2,}/', trim($text)) ?: [] as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            $lines = explode("\n", $chunk);

            if (preg_match('/^(#{1,4})\s+(.+)$/', $chunk, $m) && count($lines) === 1) {
                $level = min(4, max(2, strlen($m[1]) + 1)); // # maps to h2: pages own their h1
                $html .= "<h{$level}>" . self::inline($m[2]) . "</h{$level}>\n";
                continue;
            }
            if (preg_match('/^-{3,}$/', $chunk)) {
                $html .= "<hr>\n";
                continue;
            }
            if (self::every($lines, '/^>\s?/')) {
                $inner = array_map(fn($l) => self::inline(preg_replace('/^>\s?/', '', $l) ?? ''), $lines);
                $html .= '<blockquote><p>' . implode('<br>', $inner) . "</p></blockquote>\n";
                continue;
            }
            if (self::every($lines, '/^[-*]\s+/')) {
                $items = array_map(fn($l) => '<li>' . self::inline(preg_replace('/^[-*]\s+/', '', $l) ?? '') . '</li>', $lines);
                $html .= '<ul>' . implode('', $items) . "</ul>\n";
                continue;
            }
            if (self::every($lines, '/^\d+\.\s+/')) {
                $items = array_map(fn($l) => '<li>' . self::inline(preg_replace('/^\d+\.\s+/', '', $l) ?? '') . '</li>', $lines);
                $html .= '<ol>' . implode('', $items) . "</ol>\n";
                continue;
            }
            $html .= '<p>' . implode('<br>', array_map([self::class, 'inline'], $lines)) . "</p>\n";
        }
        return $html;
    }

    private static function inline(string $raw): string
    {
        $s = e($raw);

        // Code spans first, protected from later replacements by placeholders.
        $spans = [];
        $s = preg_replace_callback('/`([^`]+)`/', function ($m) use (&$spans) {
            $spans[] = '<code>' . $m[1] . '</code>';
            return "\x1A" . (count($spans) - 1) . "\x1A";
        }, $s) ?? $s;

        $s = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
            $href = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
            if (!preg_match('#^(https?://|mailto:|/|\#)#', $href)) {
                return $m[1];
            }
            return '<a href="' . e($href) . '">' . $m[1] . '</a>';
        }, $s) ?? $s;

        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s) ?? $s;
        $s = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $s) ?? $s;

        return preg_replace_callback('/\x1A(\d+)\x1A/', fn($m) => $spans[(int) $m[1]], $s) ?? $s;
    }

    private static function every(array $lines, string $pattern): bool
    {
        foreach ($lines as $l) {
            if (!preg_match($pattern, $l)) {
                return false;
            }
        }
        return count($lines) > 0;
    }
}
