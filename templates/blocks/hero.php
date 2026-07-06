<?php
$b = $block;
// *word* in the heading renders as an italic emphasis span (Fraunces italic)
$heading = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', e($b['heading'] ?? '')) ?? e($b['heading'] ?? '');
?>
<section class="hero hero--<?= e($b['variant'] ?? 'page') ?><?= ($b['bg_motif'] ?? 'none') === 'topo' ? ' has-topo' : '' ?>">
    <div class="container hero__inner">
        <?php if (($b['eyebrow'] ?? '') !== ''): ?>
            <p class="eyebrow mono"><?= e($b['eyebrow']) ?></p>
        <?php endif; ?>
        <h1 class="hero__heading"><?= $heading ?></h1>
        <?php if (($b['lede_md'] ?? '') !== ''): ?>
            <div class="hero__lede"><?= Markdown::render($b['lede_md']) ?></div>
        <?php endif; ?>
        <?php if (($b['cta_primary_label'] ?? '') !== '' || ($b['cta_secondary_label'] ?? '') !== ''): ?>
            <p class="hero__ctas">
                <?php if (($b['cta_primary_label'] ?? '') !== ''): ?>
                    <a class="button button--primary" href="<?= e($b['cta_primary_url'] ?? '#') ?>"><?= e($b['cta_primary_label']) ?></a>
                <?php endif; ?>
                <?php if (($b['cta_secondary_label'] ?? '') !== ''): ?>
                    <a class="link-accent link-accent--underline" href="<?= e($b['cta_secondary_url'] ?? '#') ?>"><?= e($b['cta_secondary_label']) ?></a>
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <?php if (($b['availability'] ?? '') !== ''): ?>
            <p class="hero__availability mono"><span class="hero__availability-dot" aria-hidden="true"></span><?= e($b['availability']) ?></p>
        <?php endif; ?>
    </div>
    <?php if (($b['ridge_bottom'] ?? 'none') !== 'none'): ?>
        <?= View::partial('ridge-' . $b['ridge_bottom'], ['tint' => 'green', 'flip' => false]) ?>
    <?php endif; ?>
</section>
