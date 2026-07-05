<?php $b = $block; ?>
<section class="stat-row stat-row--<?= e($b['style'] ?? 'inline') ?>">
    <div class="container">
        <dl class="stat-row__list">
            <?php foreach ($b['items'] ?? [] as $stat): ?>
                <div class="stat">
                    <dd class="stat__value mono"><?= e($stat['value'] ?? '') ?></dd>
                    <dt class="stat__label"><?= e($stat['label'] ?? '') ?><?php if (($stat['footnote'] ?? '') !== ''): ?> <small><?= e($stat['footnote']) ?></small><?php endif; ?></dt>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>
