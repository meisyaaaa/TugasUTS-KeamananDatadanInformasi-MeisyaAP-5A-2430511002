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
function crypto(): Crypto {
    static $instance = null;
    if ($instance === null) {
        require_once dirname(__DIR__) . '/crypto/Crypto.php';
        $instance = new Crypto();
    }
    return $instance;
}
/** True bila admin masuk dengan private key yang cocok dengan public key sistem. */
function is_unlocked(): bool {
    return !empty($_SESSION['key_verified']);
}
/** Halaman yang membutuhkan dekripsi hanya bisa dibuka setelah private key terverifikasi. */
function require_unlocked(): void {
    require_login();
    if (!is_unlocked()) {
        flash('warning', 'Fitur ini terkunci karena private key tidak cocok. Keluar, lalu masuk kembali dengan private key yang benar.');
        header('Location: ' . url('admin/dashboard.php'));
        exit;
    }
}
/**
 * Memeriksa apakah private key yang dimasukkan benar-benar pasangan dari secure/public.pem.
 * Caranya: acak 32 byte dienkripsi dengan public key sistem (RSA-OAEP), lalu harus
 * bisa dibuka kembali dengan private key yang dimasukkan admin.
 */
function verify_private_key(string $pem): bool {
    $pem = trim(str_replace("\r", '', $pem));
    if ($pem === '' || strlen($pem) > 16384) return false;
    $privateKey = @openssl_pkey_get_private($pem);
    $publicPem = @file_get_contents(dirname(__DIR__) . '/secure/public.pem');
    $publicKey = $publicPem === false ? false : @openssl_pkey_get_public($publicPem);
    if (!$privateKey || !$publicKey) { while (openssl_error_string() !== false); return false; }
    $challenge = random_bytes(32);
    $wrapped = '';
    $opened = '';
    $ok = @openssl_public_encrypt($challenge, $wrapped, $publicKey, OPENSSL_PKCS1_OAEP_PADDING)
        && @openssl_private_decrypt($wrapped, $opened, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)
        && hash_equals($challenge, $opened);
    while (openssl_error_string() !== false);
    return $ok;
}
/** Potongan ciphertext asli (base64) untuk ditampilkan saat mode terenkripsi. */
function cipher_preview(array $row, int $offset = 0, int $length = 16): string {
    $cipher = (string)($row['data_cipher'] ?? '');
    $part = substr($cipher, $offset, $length);
    return $part === '' ? '…' : $part . '…';
}

/**
 * [BARU] Teks "terenkripsi" untuk label antarmuka. Panjangnya mengikuti teks asli dan hasilnya
 * selalu sama untuk teks yang sama (deterministik), jadi tampilan tidak berubah-ubah saat refresh.
 */
function cipher_text(string $text): string {
    $len = max(4, mb_strlen($text));
    $raw = '';
    for ($i = 0; strlen($raw) < $len * 2; $i++) {
        $raw .= hash_hmac('sha256', $text . '|' . $i, 'arsip-ui-label', true);
    }
    $b64 = str_replace(['=', '+', '/'], ['', 'x', 'Q'], base64_encode($raw));
    return substr($b64, 0, $len);
}
/**
 * [BARU] Cetak teks apa adanya bila kunci valid, atau versi acak (terenkripsi) bila kunci salah.
 * Sudah di-escape, jadi aman dipakai langsung: <?= ct('Dashboard') ?>
 */
function ct(?string $text): string {
    $text = (string)$text;
    if (is_unlocked()) return e($text);
    return '<span class="cipher cipher-ui">' . e(cipher_text($text)) . '</span>';
}

function decrypt_metadata(array $row): array {
    if (!is_unlocked()) {
        throw new RuntimeException('Private key belum diverifikasi.');
    }
    $json = crypto()->decrypt($row['data_cipher'], $row['data_key'], $row['data_iv'], $row['data_tag']);
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException('Metadata arsip tidak valid.');
    }
    return $data;
}