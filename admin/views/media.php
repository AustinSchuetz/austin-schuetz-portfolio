<?php $adminTitle = 'Media'; include __DIR__ . '/_top.php'; ?>
<h1>Media</h1>
<form class="upload-form" method="post" action="/admin/?r=upload" enctype="multipart/form-data" id="upload-form">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="file" name="file" accept=".jpg,.jpeg,.png,.gif,.webp" required>
    <button type="submit" class="btn btn--primary">Upload</button>
    <span class="upload-form__note">jpg / png / gif / webp, max 10MB — re-encoded on save</span>
</form>
<div class="media-grid">
    <?php foreach ($images as $img): ?>
        <figure class="media-item">
            <img src="<?= e($img['thumb']) ?>" alt="" loading="lazy">
            <figcaption class="mono" title="Click to copy" data-copy="<?= e($img['path']) ?>"><?= e(basename($img['path'])) ?></figcaption>
            <form method="post" action="/admin/?r=delete-media" onsubmit="return confirm('Delete this image?')">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="path" value="<?= e($img['path']) ?>">
                <button type="submit" class="btn btn--danger">Delete</button>
            </form>
        </figure>
    <?php endforeach; ?>
</div>
<script>
document.querySelectorAll('[data-copy]').forEach(el => {
    el.addEventListener('click', () => {
        navigator.clipboard.writeText(el.dataset.copy).then(() => { el.textContent = 'copied!'; setTimeout(() => { el.textContent = el.dataset.copy.split('/').pop(); }, 900); });
    });
});
</script>
<?php include __DIR__ . '/_bottom.php'; ?>
