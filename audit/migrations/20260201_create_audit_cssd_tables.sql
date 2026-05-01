-- Audit CSSD: header, checklist detail, dokumentasi foto
-- Jalankan sekali di database aplikasi (MySQL/MariaDB).

CREATE TABLE IF NOT EXISTS `audit_cssd` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tanggal_audit` date NOT NULL,
  `catatan_audit` text,
  `nama_petugas_unit` varchar(255) NOT NULL,
  `tanda_tangan_petugas` varchar(512) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_cssd_tanggal` (`tanggal_audit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `detail_audit_cssd` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `audit_id` int unsigned NOT NULL,
  `kode_bagian` varchar(10) NOT NULL,
  `urutan_item` int unsigned NOT NULL,
  `item_text` text NOT NULL,
  `jawaban` varchar(16) NOT NULL DEFAULT 'na',
  PRIMARY KEY (`id`),
  KEY `idx_detail_audit_cssd_audit` (`audit_id`),
  KEY `idx_detail_audit_cssd_kode` (`kode_bagian`),
  CONSTRAINT `fk_detail_audit_cssd_audit`
    FOREIGN KEY (`audit_id`) REFERENCES `audit_cssd` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Foto per audit (tanpa wajib detail_id): aplikasi hanya mengisi audit_id, nama_file, path_file, ukuran_file.
CREATE TABLE IF NOT EXISTS `audit_cssd_foto` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `audit_id` int unsigned NOT NULL,
  `nama_file` varchar(512) DEFAULT NULL,
  `path_file` varchar(512) NOT NULL,
  `ukuran_file` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_audit_cssd_foto_audit` (`audit_id`),
  CONSTRAINT `fk_audit_cssd_foto_audit`
    FOREIGN KEY (`audit_id`) REFERENCES `audit_cssd` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jika tabel Anda sudah dibuat manual dengan detail_id/kode_item/mime_type NOT NULL, jalankan juga:
-- audit/migrations/20260201_fix_audit_cssd_foto_columns.sql
