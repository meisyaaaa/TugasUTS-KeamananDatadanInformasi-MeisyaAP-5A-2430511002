Nama   : Meisya Amelia Putri
NIM    : 2430511002
Kelas  : 5A

# Sistem Manajemen Arsip Digital

**Sistem Manajemen Arsip Digital Berbasis Web Menggunakan Hybrid Cryptography**

Aplikasi berbasis web untuk mengelola dokumen arsip secara digital, mulai dari penyimpanan, pencarian, pengelolaan, hingga pengunduhan dokumen. Sistem menerapkan metode **Hybrid Cryptography** menggunakan AES-256-GCM dan RSA-OAEP untuk menjaga kerahasiaan dokumen.

## Identitas Pengembang

- **Nama:** Meisya Amelia Putri
- **NIM:** 2430511002
- **Kelas:** 5A
- **Program Studi:** Teknik Informatika

## Teknologi yang Digunakan

| Teknologi | Keterangan |
|---|---|
| PHP Native | Backend aplikasi |
| MySQL | Database penyimpanan data |
| Bootstrap | Framework antarmuka |
| JavaScript | Interaksi pada halaman web |
| OpenSSL | Implementasi fungsi kriptografi |
| AES-256-GCM | Enkripsi dan dekripsi dokumen |
| RSA-OAEP | Pengamanan kunci AES |

## Fitur Utama

- Landing page sebelum masuk ke halaman login
- Login administrator menggunakan username dan private key RSA
- Mode tampilan sesuai kunci: private key cocok menampilkan data terdekripsi, private key salah menampilkan data terenkripsi
- Dashboard statistik arsip
- Tambah arsip digital
- Melihat detail arsip
- Pencarian arsip berdasarkan nomor arsip
- Filter arsip berdasarkan kategori
- Edit data arsip
- Mempertahankan file lama apabila tidak dilakukan penggantian
- Enkripsi dokumen menggunakan AES-256-GCM
- Pengamanan kunci AES menggunakan RSA-OAEP
- Dekripsi dan pengunduhan dokumen
- Penghapusan arsip
- Riwayat aktivitas administrator


## Mekanisme Hybrid Cryptography

Sistem menerapkan Hybrid Cryptography dengan menggabungkan algoritma kriptografi simetris dan asimetris.

### 1. AES-256-GCM

AES-256-GCM digunakan untuk mengenkripsi informasi dan isi dokumen arsip. AES menggunakan kunci yang sama untuk proses enkripsi dan dekripsi, sedangkan mode GCM menyediakan pemeriksaan autentikasi terhadap data terenkripsi.

### 2. RSA-OAEP

RSA-OAEP digunakan untuk melindungi kunci AES. Public key RSA digunakan untuk mengamankan kunci AES, sedangkan private key RSA digunakan untuk membuka kembali kunci AES tersebut.

### Alur Enkripsi dan Dekripsi

1. Dokumen diunggah oleh administrator.
2. Sistem menghasilkan kunci AES.
3. Dokumen dienkripsi menggunakan AES-256-GCM.
4. Kunci AES diamankan menggunakan public key RSA.
5. Dokumen terenkripsi disimpan di penyimpanan sistem.
6. Ketika dokumen akan diunduh, private key RSA digunakan untuk membuka kunci AES.
7. Dokumen didekripsi menggunakan AES dan dikembalikan ke bentuk aslinya.

## Instalasi dan Konfigurasi

### 1. Persiapan Lingkungan

Pastikan perangkat telah memiliki:

- Laragon
- PHP
- MySQL
- OpenSSL
- Visual Studio Code
- Web Browser

### 2. Menempatkan Project

Download atau clone repository ini, kemudian letakkan folder proyek di direktori:

```text
laragon/www/
```

### 3. Menjalankan Laragon

Buka Laragon, kemudian klik **Start All** untuk menjalankan Apache dan MySQL.

### 4. Membuat Database

1. Buka phpMyAdmin melalui browser.
2. Buat database sesuai konfigurasi aplikasi.
3. Pilih database yang telah dibuat.
4. Import file `database.sql`.

### 5. Membuat RSA Key

Buka terminal pada direktori project.

Jika folder `secure` belum tersedia, buat folder terlebih dahulu:

```bash
mkdir secure
```

Kemudian generate private key RSA:

```bash
openssl genpkey -algorithm RSA -out secure/private.pem -pkeyopt rsa_keygen_bits:3072
```

Generate public key dari private key:

```bash
openssl pkey -in secure/private.pem -pubout -out secure/public.pem
```

File kunci yang dihasilkan:

```text
secure/
├── private.pem
└── public.pem
```

> Jika folder `secure` sudah tersedia, tidak perlu menjalankan `mkdir secure` lagi. Pastikan file kunci yang digunakan sesuai dengan konfigurasi aplikasi dan jangan menimpa kunci yang sudah digunakan untuk arsip sebelumnya.

### 6. Membuat Akun Administrator

Buka alamat berikut melalui browser:

```text
http://localhost/sistem-arsip-kriptografi/setup_admin.php
```

Gunakan halaman tersebut untuk membuat akun administrator.

Setelah akun berhasil dibuat, **hapus file `setup_admin.php`** untuk mencegah akses pembuatan akun yang tidak diperlukan.

### 7. Menjalankan Aplikasi

Buka aplikasi melalui browser:

```text
http://localhost/sistem-arsip-kriptografi/
```

Halaman awal adalah landing page. Klik **Masuk sebagai admin** untuk menuju halaman login, lalu isi:

- **Username** administrator yang telah dibuat.
- **Private key** RSA (tempel isi `secure/private.pem` atau pilih file `.pem`).

Private key hanya dipakai untuk pemeriksaan saat login dan tidak disimpan di server maupun session.

### Perilaku Login Berdasarkan Private Key

| Kondisi | Hasil |
|---|---|
| Private key cocok dengan `secure/public.pem` | Dashboard dan data arsip tampil dalam bentuk asli (terdekripsi). Semua fitur terbuka. |
| Private key salah | Tetap masuk ke dashboard, tetapi data tampil dalam bentuk terenkripsi (ciphertext). Detail, tambah, edit, hapus, unduh, pencarian, filter, dan riwayat aktivitas dikunci. |

Kecocokan kunci diperiksa dengan menguji enkripsi RSA-OAEP memakai public key sistem, kemudian membukanya dengan private key yang dimasukkan.

## Struktur Folder Penting

```text
sistem-arsip-kriptografi/
│
├── index.php          (landing page)
├── login.php          (login username + private key)
├── admin/
│   ├── arsip.php
│   ├── detail.php
│   ├── edit.php
│   └── ...
││
├── config/
├── includes/
│├── secure/
│   ├── private.pem
│   └── public.pem
││
├── storage/
│   └── encrypted/
││
├── database.sql
├── setup_admin.php
└── README.md
```

## Catatan Keamanan

- Jangan membagikan file `private.pem` kepada pihak lain.
- Jangan mengunggah private key ke repository publik GitHub.
- Jangan menghapus atau mengganti private key yang sedang digunakan untuk mengakses arsip terenkripsi tanpa prosedur migrasi kunci.
- Pastikan direktori `secure` tidak dapat diakses langsung oleh pengguna melalui browser.
- Lindungi direktori penyimpanan dokumen terenkripsi dari akses yang tidak berwenang.
- Hapus `setup_admin.php` setelah proses pembuatan administrator selesai.
- Gunakan HTTPS apabila aplikasi diterapkan pada server online.
- Lakukan backup database dan file kunci secara aman.

# Screenshoot Aplikasi
1. Tampilan login
<img width="588" height="335" alt="Tampilan Login" src="https://github.com/user-attachments/assets/1a00e255-7703-4174-aa54-73029a41e33e" />

3. Dashboard
  <img width="638" height="354" alt="Dashboard" src="https://github.com/user-attachments/assets/21e5b6fa-b865-4fe8-b338-6cfc9bde69d2" />

5. Penambahan Arsip
<img width="634" height="338" alt="Tambah Arsip" src="https://github.com/user-attachments/assets/bf4af259-78f5-47cb-a311-c291934bf4e2" />

4. Data Arsip
<img width="640" height="356" alt="DataArsip" src="https://github.com/user-attachments/assets/2eb9d68c-0d27-42cc-90bc-b8d435b76f2a" />


5. Edit Arsip
<img width="639" height="353" alt="EditArsip" src="https://github.com/user-attachments/assets/a4cc1a67-075d-43fa-b6d5-f5e6e41e2db5" />


5. Riwayat Aktivitas
<img width="637" height="331" alt="Riwayat Aktivitas" src="https://github.com/user-attachments/assets/bdcccd55-dadd-4ce6-9f64-a20804058d1f" />

7. Enkripsi
<img width="590" height="41" alt="Enkripsi" src="https://github.com/user-attachments/assets/5228ed0e-1f1e-4f0a-9d9a-06fc345e0253" />

9. Deskripsi 
<img width="509" height="314" alt="Deskripsi" src="https://github.com/user-attachments/assets/70d5da41-2e33-46a9-9f96-1f5b09689836" />

