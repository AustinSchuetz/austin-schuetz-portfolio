<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= Seo::head($doc, $site, $path) ?>
<link rel="preload" href="/assets/fonts/fraunces-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/public-sans-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/tokens.css">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="icon" href="/assets/img/icon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<?= View::partial('header', ['site' => $site, 'path' => $path]) ?>
<main id="main">
<?= $content ?>
</main>
<?= View::partial('footer', ['site' => $site]) ?>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
