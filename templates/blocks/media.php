<?php $b = $block; ?>
<section class="media-block media-block--<?= e($b['layout'] ?? 'single') ?>"<?= !empty($b['lightbox']) ? ' data-lightbox' : '' ?>>
    <div class="container">
        <div class="media-block__grid">
            <?php foreach ($b['items'] ?? [] as $item): ?>
                <?php if (($item['src'] ?? '') === '') { continue; } ?>
                <figure class="frame frame--<?= e($item['frame'] ?? 'none') ?>">
                    <img src="<?= e($item['src']) ?>" alt="<?= e($item['alt'] ?? '') ?>"
                         class="<?= ($item['treatment'] ?? 'color') === 'duotone' ? 'img-duotone' : '' ?>" loading="lazy" decoding="async">
                    <?php if (($item['caption'] ?? '') !== ''): ?>
                        <figcaption><?= e($item['caption']) ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
