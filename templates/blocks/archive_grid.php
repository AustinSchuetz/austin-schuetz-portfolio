<?php
$b = $block;
$entries = ContentStore::listArchive();
if (($b['era_filter'] ?? 'all') !== 'all') {
    $entries = array_values(array_filter($entries, fn($a) => ($a['era'] ?? '') === $b['era_filter']));
}
if (!empty($b['limit'])) {
    $entries = array_slice($entries, 0, (int) $b['limit']);
}
$eraLabels = [
    'freelance' => 'Freelance — Denver',
    'studio' => 'Studio & in-house WordPress',
    'agency' => 'Digital Dudes Marketing',
];
$groups = [];
if (!empty($b['group_by_era'])) {
    foreach ($entries as $entry) {
        $groups[$entry['era'] ?? 'freelance'][] = $entry;
    }
} else {
    $groups[''] = $entries;
}
?>
<section class="archive-grid<?= !empty($b['teaser']) ? ' archive-grid--teaser' : '' ?>">
    <div class="container">
        <?php foreach ($groups as $era => $items): ?>
            <?php if ($era !== ''): ?>
                <h2 class="archive-grid__era mono"><?= e($eraLabels[$era] ?? $era) ?></h2>
            <?php endif; ?>
            <div class="grid grid--cols-3">
                <?php foreach ($items as $a): ?>
                    <?php
                    $live = ($a['url'] ?? '') !== '';
                    $href = $live ? $a['url'] : ($a['wayback_url'] ?? '');
                    ?>
                    <article class="card archive-card">
                        <?php if (($a['thumb'] ?? '') !== ''): ?>
                            <div class="archive-card__media">
                                <img src="<?= e($a['thumb']) ?>" alt="Homepage of <?= e($a['name'] ?? '') ?><?= !$live ? ', archived' : '' ?>" class="img-duotone" loading="lazy" decoding="async">
                            </div>
                        <?php endif; ?>
                        <h3 class="card__title">
                            <?php if ($href !== ''): ?>
                                <a href="<?= e($href) ?>" rel="noopener"><?= e($a['name'] ?? '') ?></a>
                            <?php else: ?>
                                <?= e($a['name'] ?? '') ?>
                            <?php endif; ?>
                        </h3>
                        <?php if (!empty($a['services'])): ?>
                            <p class="card__summary"><?= e(implode(' · ', $a['services'])) ?></p>
                        <?php endif; ?>
                        <p class="archive-card__status">
                            <span class="chip chip--<?= $live ? 'live' : 'retired' ?> mono"><?= $live ? 'LIVE' : 'RETIRED — ARCHIVED' ?></span>
                            <?php if (($a['years'] ?? '') !== ''): ?><span class="mono archive-card__years"><?= e($a['years']) ?></span><?php endif; ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
