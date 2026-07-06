<header class="site-header">
    <div class="site-header__inner container">
        <a class="wordmark" href="/">
            <svg class="wordmark__mark" viewBox="0 0 48 32" aria-hidden="true" focusable="false">
                <polygon points="2,28 14,10 24,28" fill="#8FBEA8"/>
                <polygon points="12,28 25,5 38,28" fill="#3B8266"/>
                <polygon points="24,28 34,14 46,28" fill="#1B3D2F"/>
            </svg>
            <?= e($site['site_name'] ?? 'Austin Schuetz') ?>
        </a>
        <nav class="site-nav" aria-label="Main">
            <?php foreach ($site['nav'] ?? [] as $item): ?>
                <?php $current = ($item['url'] ?? '') === $path || (($item['url'] ?? '') !== '/' && str_starts_with($path, ($item['url'] ?? '') . '/')); ?>
                <a href="<?= e($item['url'] ?? '#') ?>"<?= $current ? ' aria-current="page"' : '' ?>><?= e($item['label'] ?? '') ?></a>
            <?php endforeach; ?>
            <?php if (($site['nav_cta_label'] ?? '') !== ''): ?>
                <a class="site-nav__cta" href="<?= e($site['nav_cta_url'] ?? '/contact') ?>"><?= e($site['nav_cta_label']) ?> &rarr;</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
