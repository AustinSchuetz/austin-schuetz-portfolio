<footer class="site-footer">
    <div class="container site-footer__cols">
        <div class="site-footer__col">
            <p class="wordmark"><?= e($site['site_name'] ?? 'Austin Schuetz') ?></p>
            <div class="site-footer__text"><?= Markdown::render($site['footer_md'] ?? '') ?></div>
        </div>
        <nav class="site-footer__col" aria-label="Footer">
            <?php foreach ($site['nav'] ?? [] as $item): ?>
                <a href="<?= e($item['url'] ?? '#') ?>"><?= e($item['label'] ?? '') ?></a>
            <?php endforeach; ?>
            <a href="/style-guide">Style Guide</a>
            <a href="/work/this-site">This site is a project &rarr;</a>
        </nav>
        <div class="site-footer__col">
            <?php foreach ($site['socials'] ?? [] as $s): ?>
                <a href="<?= e($s['url'] ?? '#') ?>" rel="me noopener"><?= e($s['label'] ?? '') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <p class="site-footer__colophon mono container">39.7392&deg; N, 104.9903&deg; W &mdash; Built in Denver on a hand-rolled CMS</p>
</footer>
