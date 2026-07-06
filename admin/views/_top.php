<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($adminTitle ?? 'Admin') ?> · CMS</title>
<link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body>
<header class="admin-header">
    <span class="admin-header__brand mono">AS·CMS</span>
    <nav>
        <a href="/admin/">Dashboard</a>
        <a href="/admin/?r=list&amp;kind=page">Pages</a>
        <a href="/admin/?r=list&amp;kind=project">Projects</a>
        <a href="/admin/?r=list&amp;kind=archive">Archive</a>
        <a href="/admin/?r=edit&amp;kind=site&amp;slug=site">Site</a>
        <a href="/admin/?r=media">Media</a>
    </nav>
    <form method="post" action="/admin/?r=logout" class="admin-header__logout">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <button type="submit" class="btn btn--ghost">Log out</button>
    </form>
</header>
<main class="admin-main">
