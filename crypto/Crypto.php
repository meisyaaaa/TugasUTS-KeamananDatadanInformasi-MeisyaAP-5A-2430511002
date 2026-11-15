<?php
declare(strict_types=1);

/**
 * Hybrid cryptography:
 * AES-256-GCM encrypts data/file bytes.
 * RSA-OAEP wraps each random AES key with the RSA public key.
 *
 * Note: PHP OpenSSL's openssl_public_encrypt() OAEP padding uses SHA-1.
 * For OAEP-SHA-256, use a crypto library that exposes the OAEP digest option.
 */
final class Crypto
{
    private OpenSSLAsymmetricKey $publicKey;
    private OpenSSLAsymmetricKey $privateKey;

    public function __construct()
    {
        $pubPath = dirname(__DIR__) . '/secure/public.pem';
        $privPath = dirname(__DIR__) . '/secure/private.pem';
        $pub = @file_get_contents($pubPath);
        $priv = @file_get_contents($privPath);

        if ($pub === false || $priv === false) {
            throw new RuntimeException('RSA key pair belum tersedia. Baca README.md bagian instalasi.');
        }

        $this->publicKey = openssl_pkey_get_public($pub);
        $this->privateKey = openssl_pkey_get_private($priv);
        if (!$this->publicKey || !$this->privateKey) {
            throw new RuntimeException('RSA key tidak valid atau private key tidak dapat dibuka.');
        }
    }

    /** @return array{cipher:string,key:string,iv:string,tag:string} */
    public function encrypt(string $plain): array
    {
        $key = random_bytes(32);
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($cipher === false) {
            throw new RuntimeException('Enkripsi AES-256-GCM gagal.');
        }

        $wrapped = '';
        if (!openssl_public_encrypt($key, $wrapped, $this->publicKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new RuntimeException('Enkripsi kunci AES dengan RSA-OAEP gagal.');
        }

        return [
            'cipher' => base64_encode($cipher),
            'key' => base64_encode($wrapped),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }

    public function decrypt(string $cipherB64, string $wrappedKeyB64, string $ivB64, string $tagB64): string
    {
        $cipher = base64_decode($cipherB64, true);
        $wrapped = base64_decode($wrappedKeyB64, true);
        $iv = base64_decode($ivB64, true);
        $tag = base64_decode($tagB64, true);

        if ($cipher === false || $wrapped === false || $iv === false || $tag === false) {
            throw new RuntimeException('Format data terenkripsi tidak valid.');
        }

        $key = '';
        if (!openssl_private_decrypt($wrapped, $key, $this->privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new RuntimeException('Dekripsi kunci AES gagal. Periksa private key.');
        }

        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new RuntimeException('Dekripsi gagal: autentikasi GCM tidak cocok atau data berubah.');
        }
        return $plain;
    }
}
