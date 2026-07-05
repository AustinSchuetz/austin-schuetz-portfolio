<?php
$projects = ContentStore::listProjects();
$slugs = array_column($projects, 'slug');
$i = array_search($doc['slug'] ?? '', $slugs, true);
if ($i === false) {
    return;
}
$prev = $projects[$i - 1] ?? null;
$next = $projects[$i + 1] ?? null;
if (!$prev && !$next) {
    return;
}
?>
<nav class="project-pager container" aria-label="More projects">
    <?php if ($prev): ?>
        <a class="project-pager__prev" href="/work/<?= e($prev['slug']) ?>" rel="prev">&larr; <?= e($prev['title'] ?? $prev['slug']) ?></a>
    <?php endif; ?>
    <?php if ($next): ?>
        <a class="project-pager__next" href="/work/<?= e($next['slug']) ?>" rel="next"><?= e($next['title'] ?? $next['slug']) ?> &rarr;</a>
    <?php endif; ?>
</nav>
