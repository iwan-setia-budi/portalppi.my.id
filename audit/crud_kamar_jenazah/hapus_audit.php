<?php
require_once __DIR__ . '/../../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';
$conn = $koneksi;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
  header("Location: ../audit_kamar_jenazah.php?tab=tab-data");
  exit;
}

require_once __DIR__ . '/../../include/audit_delete_auth.php';
ppi_require_admin_delete_redirect('../audit_kamar_jenazah.php?tab=tab-data');

mysqli_begin_transaction($conn);
try {
  mysqli_query($conn, "DELETE FROM audit_kamar_jenazah_foto WHERE audit_id = $id");
  mysqli_query($conn, "DELETE FROM detail_audit_kamar_jenazah WHERE audit_id = $id");
  mysqli_query($conn, "DELETE FROM audit_kamar_jenazah WHERE id = $id");
  mysqli_commit($conn);
} catch (Throwable $e) {
  mysqli_rollback($conn);
}

header("Location: ../audit_kamar_jenazah.php?tab=tab-data");
exit;
