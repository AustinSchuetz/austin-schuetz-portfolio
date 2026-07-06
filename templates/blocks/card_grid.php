<?php $b = $block; ?>
<?php if (($b['variant'] ?? 'cards') === 'numbered'): ?>
<section class="numbered-grid">
    <div class="container">
        <div class="numbered-grid__cols numbered-grid__cols--<?= e($b['columns'] ?? '3') ?>">
            <?php foreach ($b['items'] ?? [] as $card): ?>
                <div class="numbered-grid__col">
                    <span class="numbered-grid__index mono" aria-hidden="true"></span>
                    <h3 class="numbered-grid__title"><?= e($card['title'] ?? '') ?></h3>
                    <div class="numbered-grid__body"><?= Markdown::render($card['body_md'] ?? '') ?></div>
                    <?php if (!empty($card['tags'])): ?>
                        <p class="chips mono"><?php foreach ($card['tags'] as $tag): ?><span class="chip-flat"><?= e(strtoupper($tag)) ?></span><?php endforeach; ?></p>
                    <?php endif; ?>
                    <?php if (($card['link'] ?? '') !== ''): ?>
                        <p><a class="link-accent" href="<?= e($card['link']) ?>">More &rarr;</a></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
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
<?php endif; ?>
