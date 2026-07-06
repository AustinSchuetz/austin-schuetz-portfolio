<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

(new AdminController())->route((string) ($_GET['r'] ?? 'dashboard'));
