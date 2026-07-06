<?php
$labels = ['page' => 'Pages', 'project' => 'Projects', 'archive' => 'Archive entries'];
$adminTitle = $labels[$kind] ?? 'Documents';
include __DIR__ . '/_top.php';
?>
<h1><?= e($adminTitle) ?></h1>
<table class="doc-table">
    <thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($docs as $d): ?>
        <tr>
            <td><a href="/admin/?r=edit&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($d['slug']) ?>"><?= e($d['title'] ?? $d['name'] ?? $d['slug']) ?></a></td>
            <td class="mono"><?= e($d['slug']) ?></td>
            <td><span class="chip chip--<?= ($d['status'] ?? 'draft') === 'published' ? 'live' : 'draft' ?>"><?= e($d['status'] ?? 'draft') ?></span></td>
            <td class="mono"><?= e(substr($d['updated_at'] ?? '', 0, 10)) ?></td>
            <td class="doc-table__actions">
                <?php if ($kind !== 'archive'): ?>
                    <a class="btn btn--ghost" href="/admin/?r=preview&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($d['slug']) ?>" target="_blank">Preview</a>
                <?php endif; ?>
                <a class="btn btn--ghost" href="/admin/?r=versions&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($d['slug']) ?>">Versions</a>
                <form method="post" action="/admin/?r=delete&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($d['slug']) ?>" onsubmit="return confirm('Delete <?= e($d['slug']) ?>? A version snapshot is kept.')">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <button type="submit" class="btn btn--danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<form class="create-form" method="post" action="/admin/?r=create&amp;kind=<?= e($kind) ?>">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="text" name="title" placeholder="Title" required>
    <input type="text" name="slug" placeholder="slug-like-this" pattern="[a-z0-9-]{1,64}" required>
    <button type="submit" class="btn btn--primary">Create <?= e(rtrim($labels[$kind] ?? '', 's')) ?></button>
</form>
<?php include __DIR__ . '/_bottom.php'; ?>
