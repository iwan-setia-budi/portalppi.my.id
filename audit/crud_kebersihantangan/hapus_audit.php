<?php
require_once __DIR__ . '/../../config/assets.php';
session_start();
include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';
$conn = $koneksi;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
  header("Location: ../kebersihantangan.php?tab=tab-data&status=invalid");
  exit;
}

require_once __DIR__ . '/../../include/audit_delete_auth.php';
ppi_require_admin_delete_redirect('../kebersihantangan.php?tab=tab-data');

mysqli_begin_transaction($conn);

try {
  $stmtDetail = mysqli_prepare($conn, "DELETE FROM audit_hand_hygiene_detail WHERE audit_id = ?");
  mysqli_stmt_bind_param($stmtDetail, "i", $id);
  mysqli_stmt_execute($stmtDetail);

  $stmtMain = mysqli_prepare($conn, "DELETE FROM audit_hand_hygiene WHERE id = ?");
  mysqli_stmt_bind_param($stmtMain, "i", $id);
  mysqli_stmt_execute($stmtMain);

  mysqli_commit($conn);

  header("Location: ../kebersihantangan.php?tab=tab-data&status=deleted");
  exit;
} catch (Throwable $e) {
  mysqli_rollback($conn);
  header("Location: ../kebersihantangan.php?tab=tab-data&status=error");
  exit;
}