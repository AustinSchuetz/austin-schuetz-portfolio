<?php
$b = $block;
$sent = isset($_GET['sent']);
$errors = $GLOBALS['contact_errors'] ?? [];
$old = $GLOBALS['contact_old'] ?? [];
$token = class_exists('ContactHandler') ? ContactHandler::formToken() : '';
?>
<section class="contact-form-block">
    <div class="container container--prose">
        <?php if (($b['heading'] ?? '') !== ''): ?>
            <h2 class="section-heading"><?= e($b['heading']) ?></h2>
        <?php endif; ?>
        <?php if ($sent): ?>
            <p class="form-success" role="status"><?= e(($b['success_message'] ?? '') !== '' ? $b['success_message'] : 'Thanks — your message is on its way.') ?></p>
        <?php else: ?>
            <?php if ($errors !== []): ?>
                <ul class="form-errors" role="alert">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <form class="contact-form" method="post" action="/contact">
                <input type="hidden" name="ft" value="<?= e($token) ?>">
                <p class="visually-hidden" aria-hidden="true">
                    <label>Leave this field empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </p>
                <p>
                    <label for="cf-name">Name</label>
                    <input id="cf-name" type="text" name="name" required maxlength="100" value="<?= e($old['name'] ?? '') ?>">
                </p>
                <p>
                    <label for="cf-email">Email</label>
                    <input id="cf-email" type="email" name="email" required maxlength="200" value="<?= e($old['email'] ?? '') ?>">
                </p>
                <p>
                    <label for="cf-message">Message</label>
                    <textarea id="cf-message" name="message" required minlength="10" maxlength="5000" rows="7"><?= e($old['message'] ?? '') ?></textarea>
                </p>
                <p><button class="button button--primary" type="submit">Send message</button></p>
            </form>
        <?php endif; ?>
    </div>
</section>
