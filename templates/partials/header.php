<header class="site-header">
    <div class="site-header__inner container">
        <a class="wordmark" href="/">
            <?= e($site['site_name'] ?? 'Austin Schuetz') ?>
            <span class="wordmark__loc mono">Denver, CO</span>
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
