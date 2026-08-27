<?php
declare(strict_types=1);

define('DB_PATH', __DIR__ . '/../db/kronstorf.sqlite');
define('SITE_NAME', 'SC Kronstorf Songcontest');
define('DEVICE_TOKEN_COOKIE', 'sck_device_token');
define('DEVICE_TOKEN_LIFETIME', 60 * 60 * 24 * 400);
define('ADMIN_SESSION_TIMEOUT', 60 * 60);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
