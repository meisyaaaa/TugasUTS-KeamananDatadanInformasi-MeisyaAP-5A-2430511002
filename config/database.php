<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$host = '127.0.0.1';
$dbname = 'db_arsip_kriptografi';
$dbuser = 'root';
$dbpass = ''; // Sesuaikan dengan konfigurasi MySQL milik Anda.

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa config/database.php dan pastikan MySQL aktif.');
}
