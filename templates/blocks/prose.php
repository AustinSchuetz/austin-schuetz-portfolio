<?php $b = $block; ?>
<section class="prose-block prose-block--<?= e($b['width'] ?? 'prose') ?>">
    <div class="container container--<?= e($b['width'] ?? 'prose') ?>">
        <?= Markdown::render($b['body_md'] ?? '') ?>
    </div>
</section>
