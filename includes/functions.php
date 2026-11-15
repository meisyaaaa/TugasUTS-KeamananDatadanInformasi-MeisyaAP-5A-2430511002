<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/config.php';

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $path = ''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
function require_login(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . url('login.php'));
        exit;
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(419);
        exit('Token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show">'
            . e($f['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
function log_activity(PDO $pdo, string $message): void {
    $q = $pdo->prepare('INSERT INTO aktivitas (admin_id, aktivitas) VALUES (?, ?)');
    $q->execute([(int)$_SESSION['admin_id'], $message]);
}
function get_archive(PDO $pdo, int $id): array {
    $q = $pdo->prepare('SELECT * FROM arsip WHERE id = ?');
    $q->execute([$id]);
    $row = $q->fetch();
    if (!$row) {
        http_response_code(404);
        exit('Data arsip tidak ditemukan.');
    }
    return $row;
}
function decrypt_metadata(array $row): array {
    require_once dirname(__DIR__) . '/crypto/Crypto.php';
    $json = (new Crypto())->decrypt($row['data_cipher'], $row['data_key'], $row['data_iv'], $row['data_tag']);
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException('Metadata arsip tidak valid.');
    }
    return $data;
}
