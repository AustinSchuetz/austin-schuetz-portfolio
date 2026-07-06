<?php

declare(strict_types=1);

/*
 * POST /contact — spam defense is honeypot + HMAC min-time token + per-IP
 * rate limit; user input never reaches mail headers unsanitized.
 */
final class ContactHandler
{
    private const MIN_SECONDS = 4;
    private const MAX_SECONDS = 7200;

    public static function formToken(): string
    {
        $t = time();
        return $t . '.' . hash_hmac('sha256', (string) $t, self::secret());
    }

    public function handle(): void
    {
        // Honeypot: bots fill it, humans never see it. Pretend success.
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            error_log('contact: honeypot tripped');
            $this->redirectSent();
            return;
        }

        if (!$this->tokenValid((string) ($_POST['ft'] ?? ''))) {
            $this->fail(['The form expired — please try again.']);
            return;
        }

        $ipKey = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!RateLimiter::hit('contact', $ipKey, 5, 3600)) {
            $this->fail(['Too many messages from this connection — please try again later.']);
            return;
        }

        $name = $this->headerSafe((string) ($_POST['name'] ?? ''), 100);
        $email = $this->headerSafe((string) ($_POST['email'] ?? ''), 200);
        $message = trim((string) ($_POST['message'] ?? ''));

        $errors = [];
        if ($name === '') {
            $errors[] = 'Please add your name.';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please use a valid email address.';
        }
        if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
            $errors[] = 'Message must be between 10 and 5000 characters.';
        }
        if ($errors !== []) {
            $this->fail($errors, ['name' => $name, 'email' => $email, 'message' => $message]);
            return;
        }

        $site = ContentStore::site();
        $to = $site['contact_email'] ?? 'contact@austinschuetz.com';
        $host = parse_url($site['base_url'] ?? 'https://austinschuetz.com', PHP_URL_HOST) ?: 'austinschuetz.com';
        $subject = 'Portfolio contact from ' . $name;
        $body = "Name: {$name}\nEmail: {$email}\nSent: " . gmdate('c') . "\n\n" . $message . "\n";
        $headers = 'From: noreply@' . $host . "\r\n"
            . 'Reply-To: ' . $email . "\r\n"
            . 'X-Mailer: PHP/' . PHP_VERSION;

        if (config('mail_transport') === 'log') {
            file_put_contents(
                STORAGE_DIR . '/logs/mail.log',
                "---\nTo: {$to}\nSubject: {$subject}\n{$headers}\n\n{$body}\n",
                FILE_APPEND | LOCK_EX
            );
            $sent = true;
        } else {
            $sent = mail($to, $subject, $body, $headers);
        }

        if (!$sent) {
            error_log('contact: mail() returned false');
            $this->fail(['Sending failed — please email directly instead.'], ['name' => $name, 'email' => $email, 'message' => $message]);
            return;
        }
        $this->redirectSent();
    }

    private function tokenValid(string $token): bool
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || !ctype_digit($parts[0])) {
            return false;
        }
        $t = (int) $parts[0];
        $age = time() - $t;
        return hash_equals(hash_hmac('sha256', $parts[0], self::secret()), $parts[1])
            && $age >= self::MIN_SECONDS
            && $age <= self::MAX_SECONDS;
    }

    /** Strip anything that could smuggle a mail header, then trim + cap. */
    private function headerSafe(string $value, int $max): string
    {
        return trim(mb_substr(str_replace(["\r", "\n", '%0a', '%0d'], '', $value), 0, $max));
    }

    private function redirectSent(): void
    {
        http_response_code(303);
        header('Location: /contact?sent=1');
    }

    private function fail(array $errors, array $old = []): void
    {
        $GLOBALS['contact_errors'] = $errors;
        $GLOBALS['contact_old'] = $old;
        http_response_code(422);
        $doc = ContentStore::page('contact');
        if ($doc === null || ($doc['status'] ?? '') !== 'published') {
            header('Content-Type: text/plain; charset=utf-8');
            echo implode("\n", $errors);
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo View::render('page', ['doc' => $doc, 'site' => ContentStore::site(), 'path' => '/contact']);
    }

    private static function secret(): string
    {
        $file = STORAGE_DIR . '/auth/form-secret';
        if (is_file($file)) {
            return (string) file_get_contents($file);
        }
        $secret = bin2hex(random_bytes(32));
        file_put_contents($file, $secret, LOCK_EX);
        @chmod($file, 0600);
        return $secret;
    }
}
