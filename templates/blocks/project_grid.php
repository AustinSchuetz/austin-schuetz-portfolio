<?php
$b = $block;
$source = $b['source'] ?? 'featured';
if ($source === 'manual') {
    $projects = [];
    foreach ($b['project_slugs'] ?? [] as $slug) {
        $p = ContentStore::project($slug);
        if ($p !== null && ($p['status'] ?? 'draft') === 'published') {
            $projects[] = $p;
        }
    }
} else {
    $projects = ContentStore::listProjects();
    if ($source === 'featured') {
        $projects = array_values(array_filter($projects, fn($p) => !empty($p['featured'])));
    }
}
?>
<section class="project-grid">
    <div class="container">
        <?php if (($b['heading'] ?? '') !== ''): ?>
            <h2 class="section-heading"><?= e($b['heading']) ?></h2>
        <?php endif; ?>
        <div class="grid grid--cols-<?= e($b['columns'] ?? '2') ?>">
            <?php foreach ($projects as $p): ?>
                <article class="card project-card">
                    <?php if (($p['thumb'] ?? '') !== ''): ?>
                        <a href="/work/<?= e($p['slug']) ?>"><img src="<?= e($p['thumb']) ?>" alt="<?= e(($p['title'] ?? '') . ' thumbnail') ?>" loading="lazy" decoding="async"></a>
                    <?php endif; ?>
                    <h3 class="card__title"><a href="/work/<?= e($p['slug']) ?>"><?= e($p['title'] ?? $p['slug']) ?></a></h3>
                    <p class="card__summary"><?= e($p['summary'] ?? '') ?></p>
                    <?php if (!empty($p['tags'])): ?>
                        <p class="tags mono"><?php foreach ($p['tags'] as $tag): ?><span class="tag"><?= e($tag) ?></span><?php endforeach; ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
