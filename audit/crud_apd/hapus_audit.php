<?php
require_once __DIR__ . '/../../config/assets.php';
session_start();
include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';
$conn = $koneksi;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
  header("Location: ../apd.php?tab=tab-data&status=invalid");
  exit;
}

$foto = '';
$stmtFoto = mysqli_prepare($conn, "SELECT foto FROM audit_apd WHERE id = ?");
if ($stmtFoto) {
  mysqli_stmt_bind_param($stmtFoto, "i", $id);
  mysqli_stmt_execute($stmtFoto);
  $resultFoto = mysqli_stmt_get_result($stmtFoto);
  $rowFoto = mysqli_fetch_assoc($resultFoto);
  $foto = $rowFoto['foto'] ?? '';
  mysqli_stmt_close($stmtFoto);
}

mysqli_begin_transaction($conn);

try {
  $stmtDetail = mysqli_prepare($conn, "DELETE FROM audit_apd_detail WHERE audit_id = ?");
  mysqli_stmt_bind_param($stmtDetail, "i", $id);
  mysqli_stmt_execute($stmtDetail);

  $stmtMain = mysqli_prepare($conn, "DELETE FROM audit_apd WHERE id = ?");
  mysqli_stmt_bind_param($stmtMain, "i", $id);
  mysqli_stmt_execute($stmtMain);

  mysqli_commit($conn);

  if ($foto !== '') {
    $fotoPath = __DIR__ . '/../uploads_apd/' . $foto;
    if (is_file($fotoPath)) {
      @unlink($fotoPath);
    }
  }

  header("Location: ../apd.php?tab=tab-data&status=deleted");
  exit;
} catch (Throwable $e) {
  mysqli_rollback($conn);
  header("Location: ../apd.php?tab=tab-data&status=error");
  exit;
}
