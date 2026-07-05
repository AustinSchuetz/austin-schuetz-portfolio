<?php

declare(strict_types=1);

/*
 * Every filesystem location the app touches is defined here, so content and
 * storage can be relocated above the docroot later without touching code.
 */
define('BASE_DIR', dirname(__DIR__));
define('APP_DIR', BASE_DIR . '/app');
define('TEMPLATE_DIR', BASE_DIR . '/templates');
define('CONTENT_DIR', BASE_DIR . '/content');
define('STORAGE_DIR', BASE_DIR . '/storage');
define('MEDIA_DIR', BASE_DIR . '/media');
define('UPLOADS_DIR', MEDIA_DIR . '/uploads');
