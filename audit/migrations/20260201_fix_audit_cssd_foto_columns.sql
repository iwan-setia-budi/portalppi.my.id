-- Perbaiki skema audit_cssd_foto agar cocok dengan aplikasi (INSERT 4 kolom).
-- Jalankan di database yang sama (mis. porx9725_myppi) SETELAH tabel dibuat.
--
-- Gejala: data tidak pernah tersimpan jika ada upload foto — transaksi gagal di INSERT foto.
-- Kolom detail_id NOT NULL tanpa nilai dari PHP menyebabkan error.

-- Jika ada foreign key dari detail_id ke detail_audit_cssd, hapus dulu (ganti nama constraint dari phpMyAdmin > Structure > Relation view, atau: SHOW CREATE TABLE audit_cssd_foto;)
-- Contoh (sesuaikan nama constraint):
-- ALTER TABLE `audit_cssd_foto` DROP FOREIGN KEY `nama_constraint_di_sini`;

ALTER TABLE `audit_cssd_foto`
  MODIFY `detail_id` int unsigned NULL DEFAULT NULL,
  MODIFY `kode_item` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  MODIFY `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
