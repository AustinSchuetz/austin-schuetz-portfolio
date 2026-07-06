<?php

declare(strict_types=1);

final class Seo
{
    public static function head(array $doc, array $site, string $path): string
    {
        $base = rtrim($site['base_url'] ?? '', '/');
        $suffix = $site['seo_title_suffix'] ?? '';
        $title = ($doc['seo_title'] ?? '') !== ''
            ? $doc['seo_title']
            : (($doc['title'] ?? $site['site_name'] ?? '') . ($path === '/' ? '' : $suffix));
        $description = ($doc['seo_description'] ?? '') !== ''
            ? $doc['seo_description']
            : ($site['seo_default_description'] ?? '');
        $ogImage = ($doc['og_image'] ?? '') !== '' ? $doc['og_image'] : ($site['og_default_image'] ?? '');
        $canonical = $base . ($path === '/' ? '/' : $path);

        $head = '<title>' . e($title) . '</title>' . "\n";
        if ($description !== '') {
            $head .= '<meta name="description" content="' . e($description) . '">' . "\n";
        }
        if ($base !== '') {
            $head .= '<link rel="canonical" href="' . e($canonical) . '">' . "\n";
        }
        $head .= '<meta property="og:type" content="website">' . "\n";
        $head .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
        if ($description !== '') {
            $head .= '<meta property="og:description" content="' . e($description) . '">' . "\n";
        }
        if ($base !== '') {
            $head .= '<meta property="og:url" content="' . e($canonical) . '">' . "\n";
        }
        if ($ogImage !== '') {
            $head .= '<meta property="og:image" content="' . e($base . $ogImage) . '">' . "\n";
            $head .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        }
        if ($path === '/') {
            $head .= self::personJsonLd($site);
        }
        return $head;
    }

    private static function personJsonLd(array $site): string
    {
        $links = array_values(array_filter(array_map(
            fn($s) => $s['url'] ?? '',
            $site['socials'] ?? []
        )));
        $person = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $site['site_name'] ?? 'Austin Schuetz',
            'jobTitle' => 'Freelance Front-End Web Developer & AI Technical Partner',
            'url' => $site['base_url'] ?? '',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Denver', 'addressRegion' => 'CO'],
            'areaServed' => 'Colorado',
            'knowsAbout' => [
                'Front-end web development',
                'WordPress theme development',
                'React and React Native',
                'AI integration',
                'Claude API',
                'Full-stack product engineering',
            ],
            'sameAs' => $links,
        ];
        return '<script type="application/ld+json">'
            . json_encode($person, JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }

    public static function sitemap(): void
    {
        $site = ContentStore::site();
        $base = rtrim($site['base_url'] ?? '', '/');
        $urls = [];
        foreach (ContentStore::listKind('page') as $page) {
            $loc = $page['slug'] === 'home' ? '/' : '/' . $page['slug'];
            $urls[] = ['loc' => $base . $loc, 'lastmod' => $page['updated_at'] ?? null];
        }
        foreach (ContentStore::listProjects() as $project) {
            $urls[] = ['loc' => $base . '/work/' . $project['slug'], 'lastmod' => $project['updated_at'] ?? null];
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo '  <url><loc>' . e($u['loc']) . '</loc>';
            if (!empty($u['lastmod'])) {
                echo '<lastmod>' . e(substr($u['lastmod'], 0, 10)) . '</lastmod>';
            }
            echo "</url>\n";
        }
        echo '</urlset>' . "\n";
    }
}
