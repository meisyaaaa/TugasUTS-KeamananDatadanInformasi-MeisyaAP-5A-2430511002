Nama   : Meisya Amelia Putri
NIM    : 2430511002
Kelas  : 5A

# Sistem Manajemen Arsip Digital

**Sistem Manajemen Arsip Digital Berbasis Web Menggunakan Hybrid Cryptography**

Aplikasi berbasis web untuk mengelola dokumen arsip secara digital, mulai dari penyimpanan, pencarian, pengelolaan, hingga pengunduhan dokumen. Sistem menerapkan metode **Hybrid Cryptography** menggunakan AES-256-GCM dan RSA-OAEP untuk menjaga kerahasiaan dokumen. Akses ke isi arsip dikendalikan dengan verifikasi private key RSA saat login: private key yang cocok menampilkan data asli, sedangkan private key yang salah membuat data dan antarmuka tampil dalam bentuk terenkripsi.

## Identitas Pengembang

- **Nama:** Meisya Amelia Putri
- **NIM:** 2430511002
- **Kelas:** 5A
- **Program Studi:** Teknik Informatika
- **Mata Kuliah:** Keamanan Data dan Informasi
- **Dosen Pengampu:** Asril Adi Sunarto, M.Kom

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
- Verifikasi private key terhadap public key sistem (challenge-response RSA-OAEP)
- Mode tampilan sesuai kunci: private key cocok menampilkan data terdekripsi, private key salah menampilkan data terenkripsi
- Antarmuka ikut terenkripsi pada mode kunci salah: label menu, judul halaman, nama admin, judul tabel, dan statistik tampil sebagai teks acak atau ikon gembok
- Dashboard statistik arsip
- Tambah arsip digital (nomor, kategori, perihal, pengirim, penerima, tanggal dokumen, keterangan, file)
- Validasi file unggahan: ekstensi (pdf, doc, docx, xls, xlsx, jpg, jpeg, png), tipe MIME, dan ukuran maksimal 10 MB
- Melihat detail arsip
- Pencarian arsip berdasarkan nomor arsip
- Filter arsip berdasarkan kategori
- Edit data arsip
- Mempertahankan file lama apabila tidak dilakukan penggantian
- Enkripsi dokumen menggunakan AES-256-GCM
- Pengamanan kunci AES menggunakan RSA-OAEP
- Dekripsi dan pengunduhan dokumen
- Penghapusan arsip
- Riwayat aktivitas administrator (login, tambah, detail, unduh, edit, hapus)
- Perlindungan CSRF pada form

## Mekanisme Hybrid Cryptography

Sistem menerapkan Hybrid Cryptography dengan menggabungkan algoritma kriptografi simetris dan asimetris.

### 1. AES-256-GCM

AES-256-GCM digunakan untuk mengenkripsi informasi dan isi dokumen arsip. AES menggunakan kunci yang sama untuk proses enkripsi dan dekripsi, sedangkan mode GCM menyediakan pemeriksaan autentikasi terhadap data terenkripsi.

### 2. RSA-OAEP

RSA-OAEP digunakan untuk melindungi kunci AES. Public key RSA digunakan untuk mengamankan kunci AES, sedangkan private key RSA digunakan untuk membuka kembali kunci AES tersebut.

### Alur Enkripsi dan Dekripsi

1. Dokumen diunggah oleh administrator.
2. Sistem menghasilkan kunci AES.
3. Metadata arsip dan dokumen dienkripsi menggunakan AES-256-GCM.
4. Kunci AES diamankan menggunakan public key RSA.
5. Dokumen terenkripsi disimpan di penyimpanan sistem (`storage/encrypted/*.enc`), metadata terenkripsi disimpan di database.
6. Ketika dokumen akan dibuka atau diunduh, sesi harus sudah terverifikasi private key-nya.
7. Kunci AES dibuka kembali dengan private key RSA sistem, lalu dokumen didekripsi menggunakan AES dan dikembalikan ke bentuk aslinya.

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

Jika nama folder berbeda, sesuaikan `BASE_URL` pada `config/config.php`.

### 3. Menjalankan Laragon

Buka Laragon, kemudian klik **Start All** untuk menjalankan Apache dan MySQL.

### 4. Membuat Database

1. Buka phpMyAdmin melalui browser.
2. Buat database sesuai konfigurasi aplikasi (`db_arsip_kriptografi`).
3. Pilih database yang telah dibuat.
4. Import file `database.sql`.

### 5. Membuat RSA Key

> **Penting:** file `private.pem` tidak disertakan di repository untuk alasan keamanan. Buat sendiri pasangan kuncinya dengan langkah berikut.

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

> Jika folder `secure` sudah tersedia, tidak perlu menjalankan `mkdir secure` lagi. Pastikan file kunci yang digunakan sesuai dengan konfigurasi aplikasi dan jangan menimpa kunci yang sudah digunakan untuk arsip sebelumnya, karena arsip lama tidak akan bisa didekripsi.

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

Private key hanya dipakai untuk pemeriksaan saat login. Yang disimpan pada session hanya status hasil verifikasi (valid atau tidak), bukan kuncinya.

### Perilaku Login Berdasarkan Private Key

| Kondisi | Hasil |
|---|---|
| Private key cocok dengan `secure/public.pem` | Dashboard dan data arsip tampil dalam bentuk asli (terdekripsi). Semua fitur terbuka. |
| Private key salah | Tetap masuk ke dashboard, tetapi data tampil dalam bentuk terenkripsi (ciphertext), dan label antarmuka (menu, judul, nama admin, judul tabel) tampil sebagai teks acak. Detail, tambah, edit, hapus, unduh, pencarian, filter, dan riwayat aktivitas dikunci. |

Kecocokan kunci diperiksa dengan menguji enkripsi RSA-OAEP memakai public key sistem, kemudian membukanya dengan private key yang dimasukkan.

Tombol **Keluar** dan pesan peringatan sengaja tetap terbaca pada mode terenkripsi agar pengguna dapat keluar dan mencoba masuk kembali dengan kunci yang benar.

## Struktur Folder Penting

```text
sistem-arsip-kriptografi/
│
├── index.php            (landing page)
├── login.php            (login username + private key)
├── logout.php
├── admin/
│   ├── dashboard.php
│   ├── arsip.php
│   ├── tambah.php
│   ├── detail.php
│   ├── edit.php
│   ├── hapus.php
│   ├── download.php
│   └── aktivitas.php
│
├── config/
│   ├── config.php
│   └── database.php
├── crypto/
│   └── Crypto.php       (enkripsi AES-256-GCM + RSA-OAEP)
├── includes/
│   ├── functions.php    (verifikasi kunci, fungsi ct() untuk teks terenkripsi)
│   └── header.php       (sidebar dan header)
├── secure/
│   ├── private.pem      (dibuat sendiri, tidak di-upload ke GitHub)
│   └── public.pem
│
├── storage/
│   └── encrypted/       (berkas dokumen terenkripsi .enc)
│
├── database.sql
├── setup_admin.php      (hapus setelah akun admin dibuat)
└── README.md
```

## Catatan Keamanan

- Jangan membagikan file `private.pem` kepada pihak lain.
- Jangan mengunggah private key ke repository publik GitHub. Tambahkan ke `.gitignore`:

  ```text
  secure/private.pem
  storage/encrypted/*.enc
  ```

  Jika file tersebut sudah pernah ter-commit, lepaskan dari pelacakan Git dengan `git rm --cached secure/private.pem`, dan buat pasangan kunci baru bila repository bersifat publik.
- Jangan menghapus atau mengganti private key yang sedang digunakan untuk mengakses arsip terenkripsi tanpa prosedur migrasi kunci.
- Pada implementasi saat ini, proses dekripsi memakai `secure/private.pem` yang tersimpan di server, sedangkan private key yang dimasukkan saat login berfungsi sebagai verifikasi akses. Pengembangan lanjutan dapat melindungi kunci server dengan passphrase atau menyimpannya terpisah dari aplikasi.
- Pastikan direktori `secure` tidak dapat diakses langsung oleh pengguna melalui browser.
- Lindungi direktori penyimpanan dokumen terenkripsi dari akses yang tidak berwenang.
- Hapus `setup_admin.php` setelah proses pembuatan administrator selesai.
- Gunakan HTTPS apabila aplikasi diterapkan pada server online.
- Lakukan backup database dan file kunci secara aman.

# Screenshot Aplikasi

1. Tampilan login

<img width="629" height="355" alt="TampilanLogin" src="https://github.com/user-attachments/assets/0fdba15f-fa65-4897-a96e-4a24dc6c1c16" />


2. Dashboard (private key valid)

<img width="637" height="356" alt="Dashboard (private key valid)" src="https://github.com/user-attachments/assets/2818d79c-ed73-41ac-908f-2ec3fc677941" />


3. Dashboard mode terenkripsi (private key salah)

<img width="632" height="335" alt="Dashboard (private key salah)" src="https://github.com/user-attachments/assets/636a4daa-eeb5-434d-a926-c2f8b6e1e1a4" />


4. Penambahan Arsip

<img width="628" height="353" alt="tambaharsip" src="https://github.com/user-attachments/assets/cbe29c57-f25b-4966-a62a-e3e691eed78c" />


5. Data Arsip

<img width="635" height="355" alt="Data Arsip" src="https://github.com/user-attachments/assets/e204ed55-ff51-4c6b-abd9-e59fae5902d9" />


6. Edit Arsip

<img width="631" height="334" alt="Edit Arsip" src="https://github.com/user-attachments/assets/c56e1d16-b0bd-489c-854d-fc696661c9d8" />


7. Riwayat Aktivitas

<img width="626" height="337" alt="Riwayat Aktivitas" src="https://github.com/user-attachments/assets/fe656dc9-2248-4c19-9cd4-330baad1c6f7" />

