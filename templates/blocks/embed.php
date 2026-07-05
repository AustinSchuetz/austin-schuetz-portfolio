<?php
$b = $block;
$id = preg_replace('/[^A-Za-z0-9_-]/', '', $b['embed_id'] ?? '') ?? '';
if ($id === '') {
    return;
}
$src = ($b['provider'] ?? 'youtube') === 'vimeo'
    ? 'https://player.vimeo.com/video/' . $id
    : 'https://www.youtube-nocookie.com/embed/' . $id;
?>
<section class="embed-block">
    <div class="container">
        <figure>
            <div class="embed embed--<?= ($b['aspect'] ?? '16:9') === '4:3' ? '4x3' : '16x9' ?>">
                <iframe src="<?= e($src) ?>" title="<?= e($b['caption'] ?? 'Embedded video') ?>"
                        loading="lazy" allowfullscreen
                        allow="accelerometer; encrypted-media; picture-in-picture"
                        referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
            <?php if (($b['caption'] ?? '') !== ''): ?>
                <figcaption><?= e($b['caption']) ?></figcaption>
            <?php endif; ?>
        </figure>
    </div>
</section>
