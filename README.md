# Sistem Informasi Pengarsipan Dokumen Digital

Aplikasi PHP Native + MySQL dengan hybrid cryptography:
- AES-256-GCM untuk mengenkripsi metadata arsip dan byte file dokumen.
- RSA-OAEP untuk membungkus kunci AES acak.
- Password admin disimpan dengan `password_hash()` Argon2id (bukan enkripsi reversible).

## Kebutuhan
- PHP 8.1+ dengan ekstensi `pdo_mysql`, `openssl`, `fileinfo`
- MySQL/MariaDB
- Apache (XAMPP/Laragon)
- OpenSSL CLI untuk membuat RSA key pair

## Instalasi lokal
1. Ekstrak folder `sistem-arsip-kriptografi` ke `C:\xampp\htdocs\` atau folder `www` Laragon.
2. Jika nama folder berbeda, ubah `BASE_URL` di `config/config.php`.
3. Jalankan Apache dan MySQL.
4. Buka phpMyAdmin, import `database.sql`.
5. Pastikan PHP extension `openssl`, `pdo_mysql`, dan `fileinfo` aktif.
6. Dari terminal, masuk ke folder proyek, lalu jalankan:

   ```bash
   mkdir secure
   openssl genpkey -algorithm RSA -out secure/private.pem -pkeyopt rsa_keygen_bits:3072
   openssl pkey -in secure/private.pem -pubout -out secure/public.pem
   ```

7. Buat folder `storage/encrypted` jika belum ada. Beri izin tulis kepada proses Apache/PHP.
8. Buka `http://localhost/sistem-arsip-kriptografi/setup_admin.php`, buat admin dengan password minimal 12 karakter.
9. **Hapus `setup_admin.php` setelah admin dibuat.**
10. Login di `http://localhost/sistem-arsip-kriptografi/login.php`.

## Penggunaan
- Dashboard menampilkan total arsip, surat masuk, surat keluar, dan dokumen lainnya.
- Tambah Arsip: isi metadata dan unggah PDF/DOC/DOCX/XLS/XLSX/JPG/PNG (maksimum 10 MB).
- Saat disimpan, metadata dan isi file dienkripsi menggunakan kunci AES yang berbeda.
- Kunci AES dienkripsi dengan RSA public key.
- Detail arsip mendekripsi metadata setelah admin login.
- Tombol unduh mendekripsi file saat diminta, lalu mengirimkannya sebagai unduhan.
- Hapus arsip menghapus record database dan file ciphertext.

## Catatan kriptografi dan keamanan
- `openssl_public_encrypt()` PHP dengan `OPENSSL_PKCS1_OAEP_PADDING` menggunakan OAEP SHA-1 pada API ini. Jika spesifikasi tugas mensyaratkan OAEP SHA-256, gunakan library yang mendukung pengaturan digest OAEP secara eksplisit (misalnya phpseclib), bukan mengklaim implementasi ini OAEP-SHA-256.
- Private key `secure/private.pem` adalah aset kritis. Pada server produksi pindahkan ke luar document root, batasi permission, backup terenkripsi, dan jangan commit ke Git.
- Folder `secure` dan `storage` dilindungi `.htaccess` untuk Apache. Pastikan konfigurasi server benar-benar menerapkan aturan ini.
- Gunakan HTTPS, rate limiting login, kebijakan backup dan pemulihan kunci, serta audit keamanan sebelum deployment.
- Penghapusan saat ini permanen. Backup file terenkripsi dan private key harus dikelola terpisah.
- Starter project pembelajaran; belum diaudit untuk produksi.
