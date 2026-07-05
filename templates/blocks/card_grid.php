<?php $b = $block; ?>
<section class="card-grid">
    <div class="container">
        <div class="grid grid--cols-<?= e($b['columns'] ?? '2') ?>">
            <?php foreach ($b['items'] ?? [] as $card): ?>
                <article class="card">
                    <?php if (($card['image'] ?? '') !== ''): ?>
                        <img src="<?= e($card['image']) ?>" alt="" loading="lazy" decoding="async">
                    <?php endif; ?>
                    <?php if (($card['badge'] ?? '') !== ''): ?>
                        <span class="badge mono"><?= e($card['badge']) ?></span>
                    <?php endif; ?>
                    <h3 class="card__title">
                        <?php if (($card['link'] ?? '') !== ''): ?>
                            <a href="<?= e($card['link']) ?>"><?= e($card['title'] ?? '') ?></a>
                        <?php else: ?>
                            <?= e($card['title'] ?? '') ?>
                        <?php endif; ?>
                    </h3>
                    <div class="card__body"><?= Markdown::render($card['body_md'] ?? '') ?></div>
                    <?php if (!empty($card['tags'])): ?>
                        <p class="tags mono"><?php foreach ($card['tags'] as $tag): ?><span class="tag"><?= e($tag) ?></span><?php endforeach; ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
