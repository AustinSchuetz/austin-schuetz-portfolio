<?php $b = $block; ?>
<section class="code-block">
    <div class="container container--prose">
        <figure>
            <pre><code<?= ($b['language'] ?? '') !== '' ? ' class="language-' . e($b['language']) . '"' : '' ?>><?= e($b['snippet'] ?? '') ?></code></pre>
            <?php if (($b['caption'] ?? '') !== ''): ?>
                <figcaption><?= e($b['caption']) ?></figcaption>
            <?php endif; ?>
        </figure>
    </div>
</section>
