<?php $b = $block; ?>
<aside class="project-meta card">
    <dl>
        <?php if (($b['role'] ?? '') !== ''): ?>
            <dt>Role</dt><dd><?= e($b['role']) ?></dd>
        <?php endif; ?>
        <?php if (($b['timeline'] ?? '') !== ''): ?>
            <dt>Timeline</dt><dd><?= e($b['timeline']) ?></dd>
        <?php endif; ?>
        <?php if (!empty($b['stack'])): ?>
            <dt>Stack</dt>
            <dd><p class="tags mono"><?php foreach ($b['stack'] as $item): ?><span class="tag"><?= e($item) ?></span><?php endforeach; ?></p></dd>
        <?php endif; ?>
        <?php if (!empty($b['items'])): ?>
            <dt>Links</dt>
            <dd>
                <?php foreach ($b['items'] as $link): ?>
                    <?php if (($link['url'] ?? '') === '') { continue; } ?>
                    <a class="link-accent project-meta__link" href="<?= e($link['url']) ?>" rel="noopener" data-kind="<?= e($link['kind'] ?? 'other') ?>"><?= e($link['label'] ?? $link['url']) ?></a>
                <?php endforeach; ?>
            </dd>
        <?php endif; ?>
    </dl>
</aside>
