<?php $b = $block; ?>
<section class="timeline">
    <div class="container container--prose">
        <?php if (($b['heading'] ?? '') !== ''): ?>
            <h2 class="section-heading"><?= e($b['heading']) ?></h2>
        <?php endif; ?>
        <ol class="timeline__list">
            <?php foreach ($b['items'] ?? [] as $entry): ?>
                <li class="timeline__entry">
                    <span class="timeline__year mono"><?= e($entry['year'] ?? '') ?></span>
                    <div class="timeline__body">
                        <h3><?= e($entry['title'] ?? '') ?><?php if (($entry['org'] ?? '') !== ''): ?> <span class="timeline__org">&mdash; <?= e($entry['org']) ?></span><?php endif; ?></h3>
                        <?= Markdown::render($entry['body_md'] ?? '') ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
