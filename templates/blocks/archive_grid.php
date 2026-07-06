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
?>
<?php if (!empty($b['teaser'])): ?>
<section class="archive-strip">
    <div class="container">
        <div class="archive-strip__head">
            <?php if (($b['heading'] ?? '') !== ''): ?>
                <p class="eyebrow mono"><?= e($b['heading']) ?></p>
            <?php endif; ?>
            <a class="link-accent" href="/archive">Full archive &rarr;</a>
        </div>
        <div class="archive-strip__grid">
            <?php foreach ($entries as $i => $a): ?>
                <?php $href = ($a['url'] ?? '') !== '' ? $a['url'] : ($a['wayback_url'] ?? '/archive'); ?>
                <a class="archive-strip__item" href="<?= e($href) ?>" rel="noopener">
                    <?php if (($a['thumb'] ?? '') !== ''): ?>
                        <span class="duotone archive-strip__thumb"><img src="<?= e($a['thumb']) ?>" alt="Homepage of <?= e($a['name'] ?? '') ?>" loading="lazy" decoding="async"></span>
                    <?php endif; ?>
                    <span class="archive-strip__caption mono"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?> &mdash; <?= e(strtoupper($a['name'] ?? '')) ?><br><?= e(strtoupper($a['services'][0] ?? '')) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<section class="archive-grid">
    <div class="container">
        <?php
        $groups = [];
        if (!empty($b['group_by_era'])) {
            foreach ($entries as $entry) {
                $groups[$entry['era'] ?? 'freelance'][] = $entry;
            }
        } else {
            $groups[''] = $entries;
        }
        ?>
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
                            <div class="archive-card__media duotone">
                                <img src="<?= e($a['thumb']) ?>" alt="Homepage of <?= e($a['name'] ?? '') ?><?= !$live ? ', archived' : '' ?>" loading="lazy" decoding="async">
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
<?php endif; ?>
