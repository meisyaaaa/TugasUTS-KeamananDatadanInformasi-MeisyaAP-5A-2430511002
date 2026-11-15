<?php
declare(strict_types=1);

define('APP_NAME', 'Sistem Informasi Arsip Digital');
define('BASE_URL', '/sistem-arsip-kriptografi'); // Ubah sesuai nama folder proyek di htdocs/www.
define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024); // 10 MB
define('STORAGE_PATH', dirname(__DIR__) . '/storage/encrypted');

date_default_timezone_set('Asia/Jakarta');

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}
