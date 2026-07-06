<?php
$b = $block;
$project = ($b['project_slug'] ?? '') !== '' ? ContentStore::project($b['project_slug']) : null;
$title = $project['title'] ?? '';
$summary = $project['summary'] ?? '';
$href = $project !== null ? '/work/' . $project['slug'] : '';
?>
<section class="project-feature<?= !empty($b['reverse']) ? ' project-feature--reverse' : '' ?>"<?= ($b['section_eyebrow'] ?? '') !== '' ? ' id="work"' : '' ?>>
    <div class="container">
        <?php if (($b['section_eyebrow'] ?? '') !== ''): ?>
            <p class="eyebrow mono project-feature__eyebrow"><?= e($b['section_eyebrow']) ?></p>
        <?php endif; ?>
    </div>
    <div class="container project-feature__inner">
        <div class="project-feature__media">
            <?php if (($b['media_src'] ?? '') !== ''): ?>
                <div class="frame frame--<?= e($b['media_frame'] ?? 'none') ?><?= ($b['media_treatment'] ?? 'color') === 'duotone' ? ' duotone' : '' ?>">
                    <img src="<?= e($b['media_src']) ?>" alt="<?= e($title !== '' ? $title . ' screenshot' : 'Project screenshot') ?>" loading="lazy" decoding="async">
                </div>
            <?php endif; ?>
        </div>
        <div class="project-feature__body">
            <?php if (!empty($b['items'])): ?>
                <p class="stat-chips mono">
                    <?php foreach ($b['items'] as $chip): ?>
                        <span class="chip"><strong><?= e($chip['value'] ?? '') ?></strong> <?= e($chip['label'] ?? '') ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
            <h2 class="project-feature__title"><?= e($title) ?></h2>
            <p class="project-feature__summary"><?= e($summary) ?></p>
            <?php if (!empty($b['tags'])): ?>
                <p class="tags mono"><?php foreach ($b['tags'] as $tag): ?><span class="tag"><?= e($tag) ?></span><?php endforeach; ?></p>
            <?php endif; ?>
            <?php if ($href !== ''): ?>
                <p><a class="link-accent" href="<?= e($href) ?>">Read case study &rarr;</a></p>
            <?php endif; ?>
        </div>
    </div>
</section>
