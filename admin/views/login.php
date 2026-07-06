<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Log in · CMS</title>
<link rel="stylesheet" href="<?= e(asset('/admin/assets/admin.css')) ?>">
</head>
<body class="login-body">
<form class="login-card" method="post" action="/admin/?r=login">
    <h1 class="mono">AS·CMS</h1>
    <?php if (!empty($error)): ?>
        <p class="form-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <p>
        <label for="lg-user">Username</label>
        <input id="lg-user" type="text" name="username" required autocomplete="username">
    </p>
    <p>
        <label for="lg-pass">Passphrase</label>
        <input id="lg-pass" type="password" name="password" required autocomplete="current-password">
    </p>
    <p><button type="submit" class="btn btn--primary">Log in</button></p>
</form>
</body>
</html>
