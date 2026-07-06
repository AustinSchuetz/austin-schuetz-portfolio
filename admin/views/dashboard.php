<?php $adminTitle = 'Dashboard'; include __DIR__ . '/_top.php'; ?>
<h1>Dashboard</h1>
<div class="dash-cards">
    <a class="dash-card" href="/admin/?r=list&amp;kind=page"><strong><?= (int) $counts['pages'] ?></strong> Pages</a>
    <a class="dash-card" href="/admin/?r=list&amp;kind=project"><strong><?= (int) $counts['projects'] ?></strong> Projects</a>
    <a class="dash-card" href="/admin/?r=list&amp;kind=archive"><strong><?= (int) $counts['archive'] ?></strong> Archive entries</a>
    <a class="dash-card" href="/admin/?r=media"><strong><?= (int) $counts['media'] ?></strong> Media files</a>
</div>
<p><a href="/" class="btn btn--ghost">View site &rarr;</a></p>
<?php include __DIR__ . '/_bottom.php'; ?>
