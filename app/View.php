<?php

declare(strict_types=1);

final class View
{
    /** Render a top-level template (page, project, 404) with variables. */
    public static function render(string $template, array $vars = []): string
    {
        return self::capture(TEMPLATE_DIR . '/' . $template . '.php', $vars);
    }

    public static function partial(string $name, array $vars = []): string
    {
        return self::capture(TEMPLATE_DIR . '/partials/' . $name . '.php', $vars);
    }

    /**
     * Render an ordered list of typed blocks. Types not present in the
     * BlockSchema registry (or missing a template) are silently skipped.
     */
    public static function renderBlocks(array $blocks, array $context = []): string
    {
        $registry = BlockSchema::blocks();
        $html = '';
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }
            $type = $block['type'] ?? '';
            if (!isset($registry[$type])) {
                continue;
            }
            $file = TEMPLATE_DIR . '/blocks/' . $type . '.php';
            if (!is_file($file)) {
                continue;
            }
            $html .= self::capture($file, ['block' => $block] + $context);
        }
        return $html;
    }

    private static function capture(string $file, array $vars): string
    {
        extract($vars, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}
