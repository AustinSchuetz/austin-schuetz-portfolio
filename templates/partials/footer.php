<footer class="site-footer panel-dark">
    <div class="container site-footer__meta mono">
        <span>&copy; <?= date('Y') ?> <?= e(strtoupper($site['site_name'] ?? 'Austin Schuetz')) ?></span>
        <span class="site-footer__coords">39.74&deg; N, 104.99&deg; W &mdash; DENVER, COLORADO</span>
        <span class="site-footer__links">
            <?php foreach ($site['socials'] ?? [] as $s): ?>
                <a href="<?= e($s['url'] ?? '#') ?>" rel="me noopener"><?= e(strtoupper($s['label'] ?? '')) ?></a>
            <?php endforeach; ?>
            <a href="/work/this-site">THIS SITE</a>
        </span>
    </div>
</footer>
