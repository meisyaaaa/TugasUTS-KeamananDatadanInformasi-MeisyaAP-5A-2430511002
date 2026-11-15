Nama   : Meisya Amelia Putri
NIM    : 2430511002
Kelas  : 5A

# Sistem Manajemen Arsip Digital

Aplikasi berbasis web untuk mengelola dokumen arsip secara digital dengan menerapkan metode Hybrid Cryptography untuk menjaga keamanan dan kerahasiaan dokumen.

# Teknologi yang Digunakan

- PHP Native
- MySQL
- Bootstrap
- JavaScript
- OpenSSL
- AES-256-GCM
- RSA-OAEP

# Fitur Utama

- Login administrator
- Dashboard statistik arsip
- Tambah, lihat, cari, dan hapus arsip
- Enkripsi dokumen menggunakan AES-256-GCM
- Pengamanan kunci AES menggunakan RSA-OAEP
- Dekripsi dan pengunduhan dokumen
- Log aktivitas administrator

# Instalasi

1. Download atau salin folder proyek ke direktori `laragon/www`.
2. Jalankan Laragon dengan mengaktifkan Apache dan MySQL.
3. Buka phpMyAdmin melalui browser.
4. Buat database dan impor file `database.sql`.
5. Buka proyek menggunakan Visual Studio Code.
6. Jalankan perintah berikut melalui terminal untuk membuat kunci RSA:

```bash
mkdir secure
openssl genpkey -algorithm RSA -out secure/private.pem -pkeyopt rsa_keygen_bits:3072
openssl pkey -in secure/private.pem -pubout -out secure/public.pem
```

7. Buka halaman berikut untuk membuat akun administrator:

```text
http://localhost/sistem-arsip-kriptografi/setup_admin.php
```

8. Setelah akun berhasil dibuat, hapus file `setup_admin.php`.
9. Akses aplikasi melalui browser:

```text
http://localhost/sistem-arsip-kriptografi/
```

10. Login menggunakan akun administrator yang telah dibuat.

# Mekanisme Keamanan

Sistem menggunakan metode Hybrid Cryptography dengan mekanisme:

1. Dokumen dienkripsi menggunakan AES-256-GCM.
2. Kunci AES diamankan menggunakan public key RSA.
3. Private key RSA digunakan untuk membuka kembali kunci AES.
4. Dokumen didekripsi ketika akan diakses atau diunduh.

# Struktur Folder Penting

```text
sistem-arsip-kriptografi/
├── secure/
│   ├── private.pem
│   └── public.pem
├── storage/
│   └── encrypted/
├── database.sql
└── README.md
```

# Catatan Keamanan

- Jangan membagikan file `private.pem` kepada pihak lain.
- Jangan mengunggah private key ke repository publik GitHub.
- Pastikan folder `secure` dan `storage` terlindungi dari akses langsung.
- Gunakan HTTPS apabila aplikasi diterapkan pada server online.

# Screenshoot Aplikasi
1. Tampilan login
<img width="588" height="335" alt="Tampilan Login" src="https://github.com/user-attachments/assets/1a00e255-7703-4174-aa54-73029a41e33e" />

3. Dashboard
  <img width="638" height="354" alt="Dashboard" src="https://github.com/user-attachments/assets/21e5b6fa-b865-4fe8-b338-6cfc9bde69d2" />

5. Penambahan Arsip
<img width="634" height="338" alt="Tambah Arsip" src="https://github.com/user-attachments/assets/bf4af259-78f5-47cb-a311-c291934bf4e2" />

4. Data Arsip
<img width="640" height="358" alt="Data Arsip" src="https://github.com/user-attachments/assets/82014a70-d65b-4fd2-a33e-475e0e45ee95" />

5. Riwayat Aktivitas
<img width="637" height="331" alt="Riwayat Aktivitas" src="https://github.com/user-attachments/assets/bdcccd55-dadd-4ce6-9f64-a20804058d1f" />

7. Enkripsi
<img width="590" height="41" alt="Enkripsi" src="https://github.com/user-attachments/assets/5228ed0e-1f1e-4f0a-9d9a-06fc345e0253" />

9. Deskripsi 
<img width="509" height="314" alt="Deskripsi" src="https://github.com/user-attachments/assets/70d5da41-2e33-46a9-9f96-1f5b09689836" />

