<?php

declare(strict_types=1);

/*
 * CLI-only admin credential setup:
 *   php scripts/make-admin.php <username> <passphrase>
 * There is deliberately no web installer.
 */
if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/app/bootstrap.php';

$username = $argv[1] ?? '';
$password = $argv[2] ?? '';

if ($username === '' || $password === '') {
    fwrite(STDERR, "Usage: php scripts/make-admin.php <username> <passphrase>\n");
    exit(1);
}
if (strlen($password) < 16) {
    fwrite(STDERR, "Passphrase must be at least 16 characters.\n");
    exit(1);
}

$file = STORAGE_DIR . '/auth/admin.json';
$payload = json_encode([
    'username' => $username,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
], JSON_PRETTY_PRINT);

if (file_put_contents($file, $payload . "\n", LOCK_EX) === false) {
    fwrite(STDERR, "Could not write {$file}\n");
    exit(1);
}
@chmod($file, 0600);
echo "Admin credentials written to {$file}\n";
