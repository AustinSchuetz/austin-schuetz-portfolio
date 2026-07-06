<?php
$adminTitle = 'Edit ' . ($doc['title'] ?? $doc['name'] ?? $slug);
include __DIR__ . '/_top.php';
?>
<div class="editor-toolbar">
    <h1 class="editor-toolbar__title">Edit <span class="mono"><?= e($kind) ?>/<?= e($slug) ?></span></h1>
    <div class="editor-toolbar__actions">
        <?php if (in_array($kind, ['page', 'project'], true)): ?>
            <a class="btn btn--ghost" href="/admin/?r=preview&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($slug) ?>" target="_blank">Preview</a>
        <?php endif; ?>
        <a class="btn btn--ghost" href="/admin/?r=versions&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($slug) ?>">Versions</a>
        <button id="save-btn" class="btn btn--primary">Save</button>
        <span id="save-status" class="mono" role="status"></span>
    </div>
</div>

<section class="editor-meta">
    <h2>Document</h2>
    <div id="meta-form" class="field-grid"></div>
</section>

<div class="editor-panes" id="editor-panes" hidden>
    <section class="editor-blocks">
        <h2>Blocks <span class="mono" id="block-count"></span></h2>
        <ol id="block-list" class="block-list"></ol>
        <div class="block-palette">
            <select id="palette-select"></select>
            <button id="palette-add" class="btn btn--ghost">+ Add block</button>
        </div>
    </section>
    <section class="editor-fields">
        <h2 id="fields-title">Select a block</h2>
        <div id="field-form"></div>
    </section>
</div>

<dialog id="media-dialog">
    <div class="media-dialog__bar">
        <strong>Pick an image</strong>
        <form id="media-upload-form">
            <input type="file" id="media-upload-input" accept=".jpg,.jpeg,.png,.gif,.webp">
        </form>
        <button id="media-close" class="btn btn--ghost">Close</button>
    </div>
    <div id="media-dialog-grid" class="media-grid media-grid--dialog"></div>
</dialog>

<script>
window.CMS = <?= json_encode([
    'kind' => $kind,
    'slug' => $slug,
    'doc' => $doc,
    'schema' => $schema,
    'csrf' => $csrf,
    'saveUrl' => '/admin/?r=save&kind=' . $kind . '&slug=' . $slug,
    'mediaListUrl' => '/admin/?r=media-list',
    'uploadUrl' => '/admin/?r=upload',
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= e(asset('/admin/assets/sortable.min.js')) ?>"></script>
<script src="<?= e(asset('/admin/assets/editor.js')) ?>"></script>
<?php include __DIR__ . '/_bottom.php'; ?>
