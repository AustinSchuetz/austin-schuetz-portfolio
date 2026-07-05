<?php $b = $block; ?>
<section class="cta-band cta-band--<?= e($b['style'] ?? 'panel-dark') ?><?= ($b['style'] ?? 'panel-dark') === 'panel-dark' ? ' panel-dark' : '' ?><?= ($b['motif'] ?? 'none') === 'topo-corner' ? ' has-topo-corner' : '' ?>">
    <div class="container cta-band__inner">
        <h2 class="cta-band__heading"><?= e($b['heading'] ?? '') ?></h2>
        <?php if (($b['body_md'] ?? '') !== ''): ?>
            <div class="cta-band__body"><?= Markdown::render($b['body_md']) ?></div>
        <?php endif; ?>
        <p class="cta-band__actions">
            <?php if (($b['button_label'] ?? '') !== ''): ?>
                <a class="button button--primary" href="<?= e($b['button_url'] ?? '#') ?>"><?= e($b['button_label']) ?></a>
            <?php endif; ?>
            <?php if (($b['email_display'] ?? '') !== ''): ?>
                <a class="link-accent mono" href="mailto:<?= e($b['email_display']) ?>"><?= e($b['email_display']) ?></a>
            <?php endif; ?>
        </p>
    </div>
</section>
