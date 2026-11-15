CREATE DATABASE IF NOT EXISTS db_arsip_kriptografi
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_arsip_kriptografi;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE arsip (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_arsip VARCHAR(80) NOT NULL UNIQUE,
  kategori ENUM('Surat Masuk','Surat Keluar','Dokumen Lainnya') NOT NULL,
  data_cipher LONGTEXT NOT NULL,
  data_key TEXT NOT NULL,
  data_iv VARCHAR(100) NOT NULL,
  data_tag VARCHAR(100) NOT NULL,
  file_token CHAR(32) NOT NULL,
  file_key TEXT NOT NULL,
  file_iv VARCHAR(100) NOT NULL,
  file_tag VARCHAR(100) NOT NULL,
  file_extension VARCHAR(10) NOT NULL,
  file_size BIGINT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_arsip_admin FOREIGN KEY (created_by) REFERENCES admins(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_arsip_kategori (kategori),
  INDEX idx_arsip_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE aktivitas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  aktivitas VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_aktivitas_admin FOREIGN KEY (admin_id) REFERENCES admins(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_aktivitas_created (created_at)
) ENGINE=InnoDB;
