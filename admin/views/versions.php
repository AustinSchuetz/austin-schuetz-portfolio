<?php $adminTitle = 'Versions'; include __DIR__ . '/_top.php'; ?>
<h1>Versions — <span class="mono"><?= e($kind) ?>/<?= e($slug) ?></span></h1>
<p><a class="btn btn--ghost" href="/admin/?r=edit&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($slug) ?>">&larr; Back to editor</a></p>
<?php if ($versions === []): ?>
    <p>No snapshots yet — one is taken automatically before every save.</p>
<?php else: ?>
    <table class="doc-table">
        <thead><tr><th>Snapshot (UTC)</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($versions as $v): ?>
            <tr>
                <td class="mono"><?= e($v) ?></td>
                <td>
                    <form method="post" action="/admin/?r=restore&amp;kind=<?= e($kind) ?>&amp;slug=<?= e($slug) ?>" onsubmit="return confirm('Restore this snapshot? Current state is snapshotted first.')">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="version" value="<?= e($v) ?>">
                        <button type="submit" class="btn btn--primary">Restore</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/_bottom.php'; ?>
