<?php

declare(strict_types=1);

/*
 * Single source of truth for all content structure. Three consumers:
 *   - View::renderBlocks() renders only types registered here
 *   - the admin editor generates its field forms from these specs
 *   - AdminController::save() sanitizes incoming documents against them
 *
 * Field spec keys: type (text|textarea|markdown|image|url|slug|select|check|
 * number|csv|repeater), label, options (select), fields (repeater), max.
 * csv fields are stored as arrays of strings; repeaters as arrays of rows.
 */
final class BlockSchema
{
    public const MAX_BLOCKS = 100;
    public const MAX_ROWS = 50;

    public static function blocks(): array
    {
        return [
            'hero' => ['label' => 'Hero', 'fields' => [
                'variant' => ['type' => 'select', 'label' => 'Variant', 'options' => ['page', 'home', 'case']],
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow (mono)'],
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'lede_md' => ['type' => 'markdown', 'label' => 'Lede'],
                'cta_primary_label' => ['type' => 'text', 'label' => 'Primary CTA label'],
                'cta_primary_url' => ['type' => 'url', 'label' => 'Primary CTA URL'],
                'cta_secondary_label' => ['type' => 'text', 'label' => 'Secondary CTA label'],
                'cta_secondary_url' => ['type' => 'url', 'label' => 'Secondary CTA URL'],
                'bg_motif' => ['type' => 'select', 'label' => 'Background motif', 'options' => ['none', 'topo']],
                'ridge_bottom' => ['type' => 'select', 'label' => 'Ridgeline below', 'options' => ['none', 'a', 'b', 'c']],
                'availability' => ['type' => 'text', 'label' => 'Availability line (mono, amber dot)'],
            ]],
            'prose' => ['label' => 'Prose', 'fields' => [
                'body_md' => ['type' => 'markdown', 'label' => 'Body'],
                'width' => ['type' => 'select', 'label' => 'Width', 'options' => ['prose', 'wide']],
            ]],
            'project_feature' => ['label' => 'Project feature', 'fields' => [
                'section_eyebrow' => ['type' => 'text', 'label' => 'Section eyebrow (first row only)'],
                'project_slug' => ['type' => 'slug', 'label' => 'Project (slug)'],
                'media_src' => ['type' => 'image', 'label' => 'Feature image'],
                'media_frame' => ['type' => 'select', 'label' => 'Frame', 'options' => ['browser', 'phone', 'none']],
                'media_treatment' => ['type' => 'select', 'label' => 'Treatment', 'options' => ['color', 'duotone']],
                'items' => ['type' => 'repeater', 'label' => 'Stat chips', 'fields' => [
                    'value' => ['type' => 'text', 'label' => 'Value'],
                    'label' => ['type' => 'text', 'label' => 'Label'],
                ]],
                'tags' => ['type' => 'csv', 'label' => 'Tags (comma-separated)'],
                'reverse' => ['type' => 'check', 'label' => 'Reverse (media right)'],
            ]],
            'project_grid' => ['label' => 'Project grid', 'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'source' => ['type' => 'select', 'label' => 'Source', 'options' => ['featured', 'all', 'manual']],
                'project_slugs' => ['type' => 'csv', 'label' => 'Slugs (when manual)'],
                'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => ['2', '3']],
            ]],
            'card_grid' => ['label' => 'Card grid', 'fields' => [
                'variant' => ['type' => 'select', 'label' => 'Variant', 'options' => ['cards', 'numbered']],
                'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => ['2', '3', '4']],
                'items' => ['type' => 'repeater', 'label' => 'Cards', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'body_md' => ['type' => 'markdown', 'label' => 'Body'],
                    'tags' => ['type' => 'csv', 'label' => 'Tags'],
                    'image' => ['type' => 'image', 'label' => 'Image'],
                    'link' => ['type' => 'url', 'label' => 'Link'],
                    'badge' => ['type' => 'text', 'label' => 'Badge'],
                ]],
            ]],
            'stat_row' => ['label' => 'Stat row', 'fields' => [
                'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['inline', 'boxed']],
                'items' => ['type' => 'repeater', 'label' => 'Stats', 'fields' => [
                    'value' => ['type' => 'text', 'label' => 'Value'],
                    'label' => ['type' => 'text', 'label' => 'Label'],
                    'footnote' => ['type' => 'text', 'label' => 'Footnote'],
                ]],
            ]],
            'media' => ['label' => 'Media / gallery', 'fields' => [
                'layout' => ['type' => 'select', 'label' => 'Layout', 'options' => ['single', 'two-up', 'grid']],
                'lightbox' => ['type' => 'check', 'label' => 'Lightbox'],
                'items' => ['type' => 'repeater', 'label' => 'Images', 'fields' => [
                    'src' => ['type' => 'image', 'label' => 'Image'],
                    'alt' => ['type' => 'text', 'label' => 'Alt text'],
                    'caption' => ['type' => 'text', 'label' => 'Caption'],
                    'treatment' => ['type' => 'select', 'label' => 'Treatment', 'options' => ['color', 'duotone']],
                    'frame' => ['type' => 'select', 'label' => 'Frame', 'options' => ['none', 'browser', 'phone']],
                ]],
            ]],
            'archive_grid' => ['label' => 'Client archive grid', 'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Eyebrow heading (teaser)'],
                'group_by_era' => ['type' => 'check', 'label' => 'Group by era'],
                'era_filter' => ['type' => 'select', 'label' => 'Era filter', 'options' => ['all', 'freelance', 'studio', 'agency']],
                'limit' => ['type' => 'number', 'label' => 'Limit (0 = all)'],
                'teaser' => ['type' => 'check', 'label' => 'Teaser mode'],
            ]],
            'timeline' => ['label' => 'Timeline', 'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'items' => ['type' => 'repeater', 'label' => 'Entries', 'fields' => [
                    'year' => ['type' => 'text', 'label' => 'Year'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'org' => ['type' => 'text', 'label' => 'Org'],
                    'body_md' => ['type' => 'markdown', 'label' => 'Body'],
                ]],
            ]],
            'divider' => ['label' => 'Divider', 'fields' => [
                'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['ridge-a', 'ridge-b', 'ridge-c', 'rule', 'space']],
                'flip' => ['type' => 'check', 'label' => 'Flip'],
                'tint' => ['type' => 'select', 'label' => 'Tint', 'options' => ['green', 'stone', 'paper', 'dark']],
            ]],
            'cta_band' => ['label' => 'CTA band', 'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'body_md' => ['type' => 'markdown', 'label' => 'Body'],
                'button_label' => ['type' => 'text', 'label' => 'Button label'],
                'button_url' => ['type' => 'url', 'label' => 'Button URL'],
                'email_display' => ['type' => 'text', 'label' => 'Email to display'],
                'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['panel-dark', 'paper']],
                'motif' => ['type' => 'select', 'label' => 'Motif', 'options' => ['none', 'topo-corner']],
            ]],
            'code' => ['label' => 'Code', 'fields' => [
                'language' => ['type' => 'text', 'label' => 'Language'],
                'snippet' => ['type' => 'textarea', 'label' => 'Snippet'],
                'caption' => ['type' => 'text', 'label' => 'Caption'],
            ]],
            'embed' => ['label' => 'Embed', 'fields' => [
                'provider' => ['type' => 'select', 'label' => 'Provider', 'options' => ['youtube', 'vimeo']],
                'embed_id' => ['type' => 'text', 'label' => 'Video ID (not URL)'],
                'aspect' => ['type' => 'select', 'label' => 'Aspect', 'options' => ['16:9', '4:3']],
                'caption' => ['type' => 'text', 'label' => 'Caption'],
            ]],
            'project_meta' => ['label' => 'Project facts card', 'fields' => [
                'role' => ['type' => 'text', 'label' => 'Role'],
                'timeline' => ['type' => 'text', 'label' => 'Timeline'],
                'stack' => ['type' => 'csv', 'label' => 'Stack (comma-separated)'],
                'items' => ['type' => 'repeater', 'label' => 'Links', 'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label'],
                    'url' => ['type' => 'url', 'label' => 'URL'],
                    'kind' => ['type' => 'select', 'label' => 'Kind', 'options' => ['live', 'repo', 'docs', 'other']],
                ]],
            ]],
            'form_contact' => ['label' => 'Contact form', 'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'success_message' => ['type' => 'text', 'label' => 'Success message'],
            ]],
            'style_tokens' => ['label' => 'Style guide section', 'fields' => [
                'kind' => ['type' => 'select', 'label' => 'Section', 'options' => ['colors', 'type', 'spacing', 'shadows', 'motifs', 'blocks-gallery']],
            ]],
        ];
    }

    public static function types(): array
    {
        return array_keys(self::blocks());
    }

    /** Meta (non-block) field schemas per document kind. */
    public static function meta(string $kind): array
    {
        $common = [
            'title' => ['type' => 'text', 'label' => 'Title'],
            'seo_title' => ['type' => 'text', 'label' => 'SEO title'],
            'seo_description' => ['type' => 'textarea', 'label' => 'SEO description', 'max' => 300],
            'og_image' => ['type' => 'image', 'label' => 'OG image'],
            'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['draft', 'published']],
        ];
        return match ($kind) {
            'page' => $common,
            'project' => $common + [
                'summary' => ['type' => 'textarea', 'label' => 'Summary', 'max' => 500],
                'tags' => ['type' => 'csv', 'label' => 'Tags'],
                'role' => ['type' => 'text', 'label' => 'Role'],
                'year' => ['type' => 'text', 'label' => 'Year'],
                'featured' => ['type' => 'check', 'label' => 'Featured'],
                'sort_order' => ['type' => 'number', 'label' => 'Sort order'],
                'thumb' => ['type' => 'image', 'label' => 'Thumbnail'],
                'hero_image' => ['type' => 'image', 'label' => 'Hero image'],
                'link_url' => ['type' => 'url', 'label' => 'Live link'],
            ],
            'archive' => [
                'name' => ['type' => 'text', 'label' => 'Client / site name'],
                'thumb' => ['type' => 'image', 'label' => 'Thumbnail'],
                'url' => ['type' => 'url', 'label' => 'Live URL (if still up)'],
                'wayback_url' => ['type' => 'url', 'label' => 'Wayback snapshot URL'],
                'years' => ['type' => 'text', 'label' => 'Years'],
                'services' => ['type' => 'csv', 'label' => 'Services'],
                'era' => ['type' => 'select', 'label' => 'Era', 'options' => ['freelance', 'studio', 'agency']],
                'blurb_md' => ['type' => 'markdown', 'label' => 'Blurb'],
                'sort_order' => ['type' => 'number', 'label' => 'Sort order'],
                'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['draft', 'published']],
            ],
            'site' => [
                'site_name' => ['type' => 'text', 'label' => 'Site name'],
                'base_url' => ['type' => 'url', 'label' => 'Base URL'],
                'seo_title_suffix' => ['type' => 'text', 'label' => 'SEO title suffix'],
                'seo_default_description' => ['type' => 'textarea', 'label' => 'Default SEO description', 'max' => 300],
                'og_default_image' => ['type' => 'image', 'label' => 'Default OG image'],
                'footer_md' => ['type' => 'markdown', 'label' => 'Footer text'],
                'contact_email' => ['type' => 'text', 'label' => 'Contact email (form recipient)'],
                'nav_cta_label' => ['type' => 'text', 'label' => 'Nav CTA label'],
                'nav_cta_url' => ['type' => 'url', 'label' => 'Nav CTA URL'],
                'nav' => ['type' => 'repeater', 'label' => 'Navigation', 'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label'],
                    'url' => ['type' => 'url', 'label' => 'URL'],
                ]],
                'socials' => ['type' => 'repeater', 'label' => 'Social links', 'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label'],
                    'url' => ['type' => 'url', 'label' => 'URL'],
                ]],
            ],
            default => [],
        };
    }

    public static function hasBlocks(string $kind): bool
    {
        return in_array($kind, ['page', 'project'], true);
    }

    /** Sanitize a whole incoming document (meta + blocks) for a kind. */
    public static function sanitizeDoc(string $kind, array $doc): array
    {
        $clean = [];
        foreach (self::meta($kind) as $name => $spec) {
            $clean[$name] = self::sanitizeField($spec, $doc[$name] ?? null);
        }
        if (self::hasBlocks($kind)) {
            $blocks = [];
            foreach (array_slice(is_array($doc['blocks'] ?? null) ? $doc['blocks'] : [], 0, self::MAX_BLOCKS) as $block) {
                if (is_array($block) && ($b = self::sanitizeBlock($block)) !== null) {
                    $blocks[] = $b;
                }
            }
            $clean['blocks'] = $blocks;
        }
        return $clean;
    }

    private static function sanitizeBlock(array $block): ?array
    {
        $type = is_string($block['type'] ?? null) ? $block['type'] : '';
        $registry = self::blocks();
        if (!isset($registry[$type])) {
            return null; // unknown types are dropped, never stored
        }
        $id = is_string($block['id'] ?? null) && preg_match('/^b_[a-f0-9]{6,12}$/', $block['id'])
            ? $block['id']
            : 'b_' . bin2hex(random_bytes(3));
        $clean = ['id' => $id, 'type' => $type];
        foreach ($registry[$type]['fields'] as $name => $spec) {
            $clean[$name] = self::sanitizeField($spec, $block[$name] ?? null);
        }
        return $clean;
    }

    private static function sanitizeField(array $spec, mixed $value): mixed
    {
        switch ($spec['type']) {
            case 'text':
                $s = self::str($value, $spec['max'] ?? 500);
                return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s) ?? '');
            case 'textarea':
            case 'markdown':
                $s = self::str($value, $spec['max'] ?? 20000);
                $s = preg_replace('/\R/', "\n", $s) ?? '';
                return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s) ?? '');
            case 'image':
                $s = self::str($value, 300);
                return preg_match('#^/(media/uploads|assets)/[a-zA-Z0-9/_.\-]+$#', $s) ? $s : '';
            case 'url':
                $s = trim(self::str($value, 500));
                return preg_match('#^(https?://|mailto:|/|\#)#', $s) ? $s : '';
            case 'slug':
                $s = self::str($value, 64);
                return preg_match('/^[a-z0-9-]{1,64}$/', $s) ? $s : '';
            case 'select':
                $opts = $spec['options'];
                return in_array($value, $opts, true) ? $value : $opts[0];
            case 'check':
                return (bool) $value;
            case 'number':
                return max(0, min(100000, (int) (is_scalar($value) ? $value : 0)));
            case 'csv':
                if (is_string($value)) {
                    $value = explode(',', $value);
                }
                if (!is_array($value)) {
                    return [];
                }
                $out = [];
                foreach (array_slice($value, 0, self::MAX_ROWS) as $v) {
                    $v = trim(self::str($v, 100));
                    if ($v !== '') {
                        $out[] = $v;
                    }
                }
                return $out;
            case 'repeater':
                if (!is_array($value)) {
                    return [];
                }
                $rows = [];
                foreach (array_slice($value, 0, self::MAX_ROWS) as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $cleanRow = [];
                    foreach ($spec['fields'] as $name => $sub) {
                        $cleanRow[$name] = self::sanitizeField($sub, $row[$name] ?? null);
                    }
                    $rows[] = $cleanRow;
                }
                return $rows;
            default:
                return '';
        }
    }

    private static function str(mixed $value, int $max): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        return mb_substr((string) $value, 0, $max);
    }
}
