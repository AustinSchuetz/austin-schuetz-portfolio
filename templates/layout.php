<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= Seo::head($doc, $site, $path) ?>
<link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<?= View::partial('motifs') ?>
<?= View::partial('header', ['site' => $site, 'path' => $path]) ?>
<main id="main">
<?= $content ?>
</main>
<?= View::partial('footer', ['site' => $site]) ?>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
