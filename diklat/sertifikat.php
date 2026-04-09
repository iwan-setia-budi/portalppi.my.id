<?php
require_once __DIR__ . '/../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;

if (!isset($_SESSION['username'])) {
  header("Location: " . base_url('login.php'));
  exit();
}

$pageTitle = "SERTIFIKAT PELATIHAN";
$activeTab = (isset($_GET['tab']) && $_GET['tab'] === 'rekap') ? 'rekap' : 'input';
$flashMessage = $_SESSION['message'] ?? null;
$flashType = $_SESSION['msg_type'] ?? 'success';
unset($_SESSION['message'], $_SESSION['msg_type']);

function redirectWithFlash($message, $type = 'success', $tab = 'rekap') {
  $_SESSION['message'] = $message;
  $_SESSION['msg_type'] = $type;
  header("Location: ./sertifikat.php?tab={$tab}");
  exit;
}

function isAllowedFile($filename) {
  $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
  $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  return in_array($extension, $allowedExtensions, true);
}

function isAllowedFileSize($sizeInBytes) {
  $maxSizeBytes = 5 * 1024 * 1024;
  return $sizeInBytes > 0 && $sizeInBytes <= $maxSizeBytes;
}

function buildUploadFilename($filename) {
  $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $baseName = pathinfo($filename, PATHINFO_FILENAME);
  $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
  $safeBaseName = trim($safeBaseName, '_');
  if ($safeBaseName === '') {
    $safeBaseName = 'sertifikat';
  }
  return time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeBaseName . '.' . $extension;
}

function deleteSertifikatFiles($fileList) {
  if (!$fileList) {
    return;
  }

  $uploadDir = '../uploads/sertifikat/';
  $files = array_filter(array_map('trim', explode(', ', $fileList)));

  foreach ($files as $file) {
    $path = $uploadDir . $file;
    if (is_file($path)) {
      @unlink($path);
    }
  }
}

$editData = null;
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($editId > 0) {
  $editStmt = mysqli_prepare($conn, "SELECT * FROM tb_sertifikat WHERE id = ? LIMIT 1");
  if ($editStmt) {
    mysqli_stmt_bind_param($editStmt, 'i', $editId);
    mysqli_stmt_execute($editStmt);
    $editResult = mysqli_stmt_get_result($editStmt);
    $editData = mysqli_fetch_assoc($editResult) ?: null;
    mysqli_stmt_close($editStmt);
    if (!$editData) {
      redirectWithFlash('❌ Data yang akan diedit tidak ditemukan.', 'error', 'rekap');
    }
  }
}

if (isset($_POST['submit'])) {
  $editId = (int) ($_POST['edit_id'] ?? 0);
  $tanggal = $_POST['tanggal'] ?? '';
  $pelatihan = trim($_POST['pelatihan'] ?? '');
  $peserta = trim($_POST['peserta'] ?? '');
  $unit = trim($_POST['unit'] ?? '');
  $existingFiles = '';

  if ($tanggal === '' || $pelatihan === '' || $peserta === '' || $unit === '') {
    redirectWithFlash('❌ Data wajib belum lengkap. Periksa kembali form input.', 'error', 'input');
  }

  if ($editId > 0) {
    $existingStmt = mysqli_prepare($conn, "SELECT sertifikat FROM tb_sertifikat WHERE id = ? LIMIT 1");
    if (!$existingStmt) {
      redirectWithFlash('❌ Gagal mengambil data yang akan diperbarui.', 'error', 'input');
    }
    mysqli_stmt_bind_param($existingStmt, 'i', $editId);
    mysqli_stmt_execute($existingStmt);
    $existingResult = mysqli_stmt_get_result($existingStmt);
    $existingRow = mysqli_fetch_assoc($existingResult);
    mysqli_stmt_close($existingStmt);
    if (!$existingRow) {
      redirectWithFlash('❌ Data edit tidak ditemukan.', 'error', 'rekap');
    }
    $existingFiles = $existingRow['sertifikat'] ?? '';
  }

  $tanggalBaru = date('Y-m-d', strtotime($tanggal));
  $sertifikat = $existingFiles;
  $uploadedFiles = [];

  if (!empty($_FILES['sertifikat']['name'][0])) {
    $uploadDir = '../uploads/sertifikat/';
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $newFiles = [];
    foreach ($_FILES['sertifikat']['name'] as $idx => $filename) {
      if ($filename === '') {
        continue;
      }
      if (!isAllowedFile($filename)) {
        redirectWithFlash('❌ Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau PDF.', 'error', 'input');
      }

      $fileSize = (int) ($_FILES['sertifikat']['size'][$idx] ?? 0);
      if (!isAllowedFileSize($fileSize)) {
        redirectWithFlash('❌ Ukuran file maksimal 5 MB per file.', 'error', 'input');
      }

      $tmp = $_FILES['sertifikat']['tmp_name'][$idx];
      $storedFilename = buildUploadFilename($filename);
      $path = $uploadDir . $storedFilename;
      if (move_uploaded_file($tmp, $path)) {
        $newFiles[] = $storedFilename;
      }
    }

    $uploadedFiles = $newFiles;
    if (!empty($uploadedFiles)) {
      $sertifikat = implode(', ', $uploadedFiles);
    }
  }

  if ($editId > 0) {
    $stmt = mysqli_prepare($conn, "UPDATE tb_sertifikat SET tanggal = ?, pelatihan = ?, peserta = ?, unit = ?, sertifikat = ? WHERE id = ?");
    if (!$stmt) {
      redirectWithFlash('❌ Gagal menyiapkan pembaruan data: ' . mysqli_error($conn), 'error', 'input');
    }

    mysqli_stmt_bind_param($stmt, 'sssssi', $tanggalBaru, $pelatihan, $peserta, $unit, $sertifikat, $editId);
    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_close($stmt);
      if (!empty($uploadedFiles) && $existingFiles && $existingFiles !== $sertifikat) {
        deleteSertifikatFiles($existingFiles);
      }
      redirectWithFlash('✏️ Data sertifikat berhasil diperbarui!', 'success', 'rekap');
    }

    $errorMessage = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    if (!empty($uploadedFiles)) {
      deleteSertifikatFiles(implode(', ', $uploadedFiles));
    }
    redirectWithFlash('❌ Query Error: ' . $errorMessage, 'error', 'input');
  }

  if ($sertifikat === '') {
    redirectWithFlash('❌ Sertifikat wajib diunggah.', 'error', 'input');
  }

  $stmt = mysqli_prepare($conn, "INSERT INTO tb_sertifikat (tanggal, pelatihan, peserta, unit, sertifikat) VALUES (?, ?, ?, ?, ?)");
  if (!$stmt) {
    if (!empty($uploadedFiles)) {
      deleteSertifikatFiles(implode(', ', $uploadedFiles));
    }
    redirectWithFlash('❌ Gagal menyiapkan penyimpanan data: ' . mysqli_error($conn), 'error', 'input');
  }

  mysqli_stmt_bind_param($stmt, 'sssss', $tanggalBaru, $pelatihan, $peserta, $unit, $sertifikat);
  if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    redirectWithFlash('✅ Sertifikat berhasil disimpan!', 'success', 'rekap');
  }

  $errorMessage = mysqli_stmt_error($stmt);
  mysqli_stmt_close($stmt);
  if (!empty($uploadedFiles)) {
    deleteSertifikatFiles(implode(', ', $uploadedFiles));
  }
  redirectWithFlash('❌ Query Error: ' . $errorMessage, 'error', 'input');
}

if (isset($_GET['hapus'])) {
  $id = (int) $_GET['hapus'];
  $fileToDelete = '';

  $fetchStmt = mysqli_prepare($conn, "SELECT sertifikat FROM tb_sertifikat WHERE id = ? LIMIT 1");
  if ($fetchStmt) {
    mysqli_stmt_bind_param($fetchStmt, 'i', $id);
    mysqli_stmt_execute($fetchStmt);
    $fetchResult = mysqli_stmt_get_result($fetchStmt);
    $fetchRow = mysqli_fetch_assoc($fetchResult);
    $fileToDelete = $fetchRow['sertifikat'] ?? '';
    mysqli_stmt_close($fetchStmt);
  }

  $stmt = mysqli_prepare($conn, "DELETE FROM tb_sertifikat WHERE id = ?");
  if (!$stmt) {
    redirectWithFlash('❌ Gagal menyiapkan penghapusan data.', 'error', 'rekap');
  }

  mysqli_stmt_bind_param($stmt, 'i', $id);
  $deleted = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  if ($deleted) {
    deleteSertifikatFiles($fileToDelete);
    redirectWithFlash('🗑️ Data sertifikat berhasil dihapus!', 'success', 'rekap');
  }

  redirectWithFlash('❌ Gagal menghapus data!', 'error', 'rekap');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Penyimpanan Sertifikat Pelatihan | PPI PHBW</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    :root {
      --navy: #1a237e;
      --blue: #3b49df;
      --sky: #eef1ff;
      --red: #d32f2f;
      --border: #dce0f0;
    }

    .container-lap { padding: 30px 40px; }

    .page-hero {
      background: linear-gradient(135deg, #1e3a8a, #2563eb);
      padding: 28px 32px;
      border-radius: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      box-shadow: 0 20px 50px rgba(37, 99, 235, .25);
    }

    .page-hero h1 { font-size: 22px; font-weight: 600; color: white; margin: 0; }
    .page-hero small { display: block; opacity: .8; font-size: 13px; margin-top: 4px; color: white; }

    .hero-btn {
      background: white;
      color: #1e3a8a;
      border: none;
      padding: 10px 18px;
      font-weight: 600;
      border-radius: 999px;
      cursor: pointer;
      transition: .2s;
    }

    .hero-btn:hover { transform: translateY(-3px); }

    .tab-nav {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .tab-btn {
      background: #e0e7ff;
      color: var(--navy);
      border: none;
      padding: 10px 22px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.95em;
      transition: 0.2s;
    }

    .tab-btn:hover { background: #c7d2fe; }
    .tab-btn.active {
      background: linear-gradient(135deg, var(--navy), var(--blue));
      color: white;
    }

    .lap-content {
      background: white;
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.07);
    }

    .tab { display: none; }
    .tab.active { display: block; }

    h2 { color: var(--navy); border-bottom: 3px solid var(--blue); padding-bottom: 6px; margin-top: 0; }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px 18px;
    }

    .form-field { min-width: 0; }
    .form-field-full { grid-column: 1 / -1; }

    label { display: block; margin-top: 12px; font-weight: 600; color: #1e293b; }

    input, textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid var(--border);
      border-radius: 10px;
      font-size: 0.95em;
      margin-top: 5px;
      box-sizing: border-box;
    }

    button { font-weight: 600; cursor: pointer; color: white; }

    .save {
      background: var(--blue);
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      margin-top: 20px;
      margin-bottom: 30px;
    }

    .save:hover { background: #283593; }

    input[type="file"] { border: 2px dashed var(--border); background-color: #f5f7ff; padding: 10px; }
    .file-help { margin-top: 8px; font-size: 0.88em; color: #64748b; }
    .file-preview { margin-top: 10px; background: #f9faff; border-radius: 8px; padding: 10px; border: 1px solid var(--border); font-size: 0.9em; }
    .file-preview ul { list-style: none; padding: 0; margin: 0; }
    .file-preview li { margin-bottom: 5px; display: flex; align-items: center; gap: 6px; }
    .file-preview li span { color: var(--blue); font-weight: 500; }

    .current-files { margin-top: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 12px 14px; }
    .current-files strong { display: block; margin-bottom: 8px; color: #1e3a8a; }
    .current-files ul { margin: 0; padding-left: 18px; }
    .current-files li { margin-bottom: 6px; }
    .current-files a { color: #1d4ed8; text-decoration: none; font-weight: 600; }
    .current-files a:hover { text-decoration: underline; }

    .table-wrapper { margin-top: 15px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 0.95em; }
    th, td { padding: 10px 8px; border: 1px solid var(--border); text-align: left; vertical-align: top; }
    th { background: var(--sky); color: var(--navy); font-weight: 600; text-align: center; }
    tr:nth-child(even) { background-color: #f8fafc; }

    .doc-btn { background: #1d4ed8; border: none; border-radius: 8px; padding: 7px 12px; }
    .doc-btn:hover { background: #1e40af; }
    .delete-btn { background: var(--red); border: none; border-radius: 8px; padding: 7px 12px; }
    .edit-btn { background: #f59e0b; border: none; border-radius: 8px; padding: 7px 12px; }
    .edit-btn:hover { background: #d97706; }
    .doc-empty { color: #94a3b8; font-weight: 600; }
    .aksi-group { display: flex; gap: 8px; flex-wrap: wrap; }

    .rekap-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 14px; }
    .rekap-filter-group { display: flex; align-items: center; gap: 12px; flex: 1; flex-wrap: wrap; }
    .rekap-search { flex: 1; min-width: 220px; }
    .rekap-date { min-width: 170px; }
    .rekap-reset { background: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 14px; }
    .rekap-reset:hover { background: #cbd5e1; }
    .rekap-export { background: #0f766e; color: white; border: 1px solid #0f766e; border-radius: 10px; padding: 10px 14px; }
    .rekap-export:hover { background: #115e59; }
    .rekap-count { white-space: nowrap; background: #eef2ff; color: #1e3a8a; border: 1px solid #c7d2fe; border-radius: 999px; padding: 10px 14px; font-weight: 600; font-size: 0.92em; }

    .pagination-bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 18px; flex-wrap: wrap; }
    .pagination-info { color: #475569; font-size: 0.92em; font-weight: 600; }
    .pagination-actions { display: flex; align-items: center; gap: 10px; }
    .pagination-btn { background: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 14px; }
    .pagination-btn:disabled { opacity: 0.55; cursor: not-allowed; }
    .pagination-page { min-width: 110px; text-align: center; color: #1e3a8a; font-weight: 700; }

    .scroll-hint { display: none; font-size: 11px; color: #94a3b8; text-align: right; margin: 4px 0; }
    main { min-width: 0; overflow-x: hidden; }

    .alert {
      padding: 12px 16px;
      margin-bottom: 20px;
      border-radius: 10px;
      font-weight: 600;
      animation: slideDown 0.3s ease;
    }

    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      z-index: 9999;
    }

    .modal-overlay.active { display: flex; }

    .modal-box {
      width: min(100%, 420px);
      background: #ffffff;
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
    }

    .modal-box h3 { margin: 0 0 10px; color: var(--navy); font-size: 1.15rem; }
    .modal-box p { margin: 0; color: #475569; line-height: 1.55; }
    .modal-doc-list { margin-top: 16px; display: grid; gap: 10px; }

    .modal-doc-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      text-decoration: none;
      background: #eff6ff;
      color: #1e3a8a;
      border: 1px solid #bfdbfe;
      border-radius: 12px;
      padding: 12px 14px;
      font-weight: 600;
    }

    .modal-doc-link:hover { background: #dbeafe; }

    .modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 22px; }
    .modal-btn { border: none; border-radius: 10px; padding: 10px 16px; font-weight: 600; cursor: pointer; }
    .modal-btn-cancel { background: #e2e8f0; color: #1e293b; }
    .modal-btn-delete { background: var(--red); color: #ffffff; }
    .modal-btn-primary { background: var(--blue); color: #ffffff; }

    @keyframes slideDown {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 900px) {
      .container-lap { padding: 12px; overflow-x: hidden; width: 100%; max-width: 100%; box-sizing: border-box; }
      .page-hero { flex-direction: column; align-items: flex-start; gap: 14px; padding: 18px 20px; border-radius: 14px; }
      .page-hero h1 { font-size: 17px; }
      .page-hero small { font-size: 12px; }
      .hero-btn { align-self: stretch; text-align: center; padding: 10px; }
      .tab-nav { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 4px; gap: 8px; scrollbar-width: none; }
      .tab-nav::-webkit-scrollbar { display: none; }
      .tab-btn { white-space: nowrap; flex-shrink: 0; padding: 9px 14px; font-size: 0.85em; }
      .lap-content { padding: 14px; }
      .form-grid { grid-template-columns: 1fr; gap: 14px; }
      .form-field-full { grid-column: auto; }
      .rekap-toolbar { flex-direction: column; align-items: stretch; }
      .rekap-filter-group { flex-direction: column; align-items: stretch; }
      .rekap-search, .rekap-date, .rekap-count, .rekap-reset, .rekap-export { width: 100%; }
      .pagination-bar { flex-direction: column; align-items: stretch; }
      .pagination-actions { width: 100%; justify-content: space-between; }
      .pagination-btn { flex: 1; }
      .pagination-page, .pagination-info { text-align: center; }
      input, textarea { font-size: 16px !important; }
      .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
      .scroll-hint { display: block; }
      table { font-size: 0.82em; }
      th, td { padding: 8px 6px; }
      h2 { font-size: 1.1em; }
    }
    /* ===== DARK MODE: PREMIUM ===== */
    body.dark-mode main {
      background:
        radial-gradient(circle at top, rgba(37, 99, 235, .12), transparent 36%),
        linear-gradient(180deg, #09111d, #0f1b2d 45%, #0d1728 100%);
    }
    body.dark-mode .container-lap,
    body.dark-mode .lap-content,
    body.dark-mode .lap-wrapper {
      background: linear-gradient(170deg, #16263b, #1b2d45);
      border: 1.5px solid rgba(59, 130, 246, .32);
      box-shadow: 0 14px 34px rgba(2, 6, 23, .36), inset 0 0 18px rgba(59, 130, 246, .08);
      color: #e2e8f0;
    }
    body.dark-mode .page-hero {
      box-shadow: 0 20px 48px rgba(2, 6, 23, .45);
    }
    body.dark-mode h2,
    body.dark-mode label,
    body.dark-mode .pagination-info,
    body.dark-mode .pagination-page,
    body.dark-mode .file-help,
    body.dark-mode .doc-empty {
      color: #e2e8f0;
    }
    body.dark-mode .tab-nav,
    body.dark-mode .table-wrapper,
    body.dark-mode .form-grid,
    body.dark-mode .modal-overlay .modal-box,
    body.dark-mode .file-preview,
    body.dark-mode .current-files,
    body.dark-mode .rekap-count {
      background: #1a2a40;
      border: 1.5px solid rgba(59, 130, 246, .3);
      color: #e2e8f0;
    }
    body.dark-mode .tab-btn {
      background: linear-gradient(180deg, rgba(20, 34, 56, .96), rgba(16, 28, 46, .96));
      color: #dbeafe;
      border: 1px solid rgba(96, 165, 250, .22);
      box-shadow: 0 8px 18px rgba(2, 6, 23, .2);
    }
    body.dark-mode .tab-btn:hover {
      background: linear-gradient(180deg, rgba(28, 45, 72, .98), rgba(20, 34, 56, .98));
      border-color: rgba(96, 165, 250, .42);
    }
    body.dark-mode .tab-btn.active {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #ffffff;
    }
    body.dark-mode table,
    body.dark-mode thead,
    body.dark-mode tbody tr {
      background: #142238;
      color: #e2e8f0;
    }
    body.dark-mode tbody tr:nth-child(even) {
      background: #16273f;
    }
    body.dark-mode th,
    body.dark-mode td {
      border-color: rgba(96, 165, 250, .2);
    }
    body.dark-mode th {
      background: linear-gradient(135deg, #1d4ed8, #2563eb);
      color: #eff6ff;
    }
    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea {
      background: #122035;
      color: #e2e8f0;
      border: 1px solid rgba(59, 130, 246, .34);
    }
    body.dark-mode input::placeholder,
    body.dark-mode textarea::placeholder {
      color: #94a3b8;
    }
    body.dark-mode input:focus,
    body.dark-mode select:focus,
    body.dark-mode textarea:focus {
      border-color: rgba(96, 165, 250, .78);
      box-shadow: 0 0 0 3px rgba(96, 165, 250, .2);
    }
    body.dark-mode input[type="file"] {
      background: linear-gradient(180deg, #122035, #0f1d31) !important;
      color: #cbd5e1;
      border: 1.5px dashed rgba(96, 165, 250, .38);
      box-shadow: inset 0 0 0 1px rgba(59, 130, 246, .08);
    }
    body.dark-mode input[type="file"]::file-selector-button {
      background: linear-gradient(180deg, #ffffff, #dbeafe);
      color: #0f172a;
      border: none;
      border-radius: 8px;
      padding: 9px 12px;
      margin-right: 12px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 8px 18px rgba(15, 23, 42, .18);
    }
    body.dark-mode input[type="file"]::-webkit-file-upload-button {
      background: linear-gradient(180deg, #ffffff, #dbeafe);
      color: #0f172a;
      border: none;
      border-radius: 8px;
      padding: 9px 12px;
      margin-right: 12px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 8px 18px rgba(15, 23, 42, .18);
    }
    body.dark-mode .file-preview li span,
    body.dark-mode .current-files strong,
    body.dark-mode .current-files a,
    body.dark-mode .modal-doc-link,
    body.dark-mode .rekap-count {
      color: #bfdbfe;
    }
    body.dark-mode .modal-doc-link {
      background: rgba(30, 64, 175, .18);
      border: 1px solid rgba(96, 165, 250, .26);
    }
    body.dark-mode .modal-doc-link:hover {
      background: rgba(37, 99, 235, .22);
    }
    body.dark-mode .alert-success {
      background: rgba(22, 101, 52, .18);
      color: #bbf7d0;
      border-color: rgba(74, 222, 128, .3);
    }
    body.dark-mode .alert-error {
      background: rgba(127, 29, 29, .22);
      color: #fecaca;
      border-color: rgba(248, 113, 113, .3);
    }
    body.dark-mode .pagination-btn,
    body.dark-mode .rekap-reset {
      background: #1e293b;
      color: #e2e8f0;
      border-color: rgba(96, 165, 250, .22);
    }
    body.dark-mode .rekap-export,
    body.dark-mode .hero-btn {
      background: linear-gradient(180deg, #ffffff, #dbeafe);
      color: #0f172a;
      border: none;
    }
  </style>
</head>
<body>
<div class="layout">
  <?php include_once '../sidebar.php'; ?>
  <main>
    <?php include_once '../topbar.php'; ?>
    <div class="container-lap">

      <div class="page-hero">
        <div>
          <h1>Penyimpanan Sertifikat</h1>
          <small>Manajemen Sertifikat Pelatihan Komite PPI</small>
        </div>
        <button class="hero-btn" onclick="kembaliDashboard()">🏠 Dashboard</button>
      </div>

      <div class="tab-nav">
        <button class="tab-btn <?php echo $activeTab === 'input' ? 'active' : ''; ?>" onclick="showTab('input')">🧾 Input Sertifikat</button>
        <button class="tab-btn <?php echo $activeTab === 'rekap' ? 'active' : ''; ?>" onclick="showTab('rekap')">📋 Rekap Sertifikat</button>
      </div>

      <div class="lap-content">
        <div id="input" class="tab <?php echo $activeTab === 'input' ? 'active' : ''; ?>">
          <?php if ($flashMessage && $activeTab === 'input'): ?>
          <div class="alert alert-<?php echo htmlspecialchars($flashType); ?>"><?php echo htmlspecialchars($flashMessage); ?></div>
          <?php endif; ?>

          <h2><?php echo $editData ? '✏️ Edit Data Sertifikat Pelatihan' : '🧾 Form Input Sertifikat Pelatihan'; ?></h2>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" value="<?php echo $editData ? (int) $editData['id'] : 0; ?>">
            <div class="form-grid">
              <div class="form-field">
                <label>Tanggal Pelatihan</label>
                <input type="date" name="tanggal" value="<?php echo $editData ? htmlspecialchars($editData['tanggal']) : ''; ?>" required>
              </div>

              <div class="form-field">
                <label>Unit / Bagian</label>
                <input type="text" name="unit" placeholder="Contoh: ICU, Rawat Inap" value="<?php echo $editData ? htmlspecialchars($editData['unit']) : ''; ?>" required>
              </div>

              <div class="form-field form-field-full">
                <label>Nama Pelatihan</label>
                <input type="text" name="pelatihan" placeholder="Contoh: Pelatihan Hand Hygiene" value="<?php echo $editData ? htmlspecialchars($editData['pelatihan']) : ''; ?>" required>
              </div>

              <div class="form-field form-field-full">
                <label>Nama Peserta</label>
                <input type="text" name="peserta" placeholder="Contoh: Siti Rahma, A.Md.Kep" value="<?php echo $editData ? htmlspecialchars($editData['peserta']) : ''; ?>" required>
              </div>

              <div class="form-field form-field-full">
                <label>Upload Sertifikat<?php echo $editData ? ' - upload baru untuk mengganti file lama' : ''; ?></label>
                <input type="file" id="sertifikatInput" name="sertifikat[]" multiple accept=".jpg,.jpeg,.png,.pdf" <?php echo $editData ? '' : 'required'; ?>>
                <div class="file-help">Format: JPG, JPEG, PNG, PDF. Maksimal 5 MB per file.</div>
                <div class="file-preview" id="preview"></div>
                <?php if ($editData && !empty($editData['sertifikat'])): ?>
                <div class="current-files">
                  <strong>Berkas saat ini</strong>
                  <ul>
                    <?php foreach (array_filter(array_map('trim', explode(', ', $editData['sertifikat']))) as $file): ?>
                    <li><a href="../uploads/sertifikat/<?php echo rawurlencode($file); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($file); ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>
              </div>

              <div class="form-field form-field-full">
                <button type="submit" class="save" name="submit"><?php echo $editData ? '💾 Update Data' : '💾 Simpan Sertifikat'; ?></button>
              </div>
            </div>
          </form>
        </div>

        <div id="rekap" class="tab <?php echo $activeTab === 'rekap' ? 'active' : ''; ?>">
          <?php if ($flashMessage && $activeTab === 'rekap'): ?>
          <div class="alert alert-<?php echo htmlspecialchars($flashType); ?>"><?php echo htmlspecialchars($flashMessage); ?></div>
          <?php endif; ?>

          <h2>📋 Rekap Sertifikat Pelatihan</h2>
          <div class="rekap-toolbar">
            <div class="rekap-filter-group">
              <input type="search" id="rekapSearch" class="rekap-search" placeholder="Cari pelatihan, peserta, unit...">
              <input type="date" id="rekapDateStart" class="rekap-date" aria-label="Tanggal mulai filter">
              <input type="date" id="rekapDateEnd" class="rekap-date" aria-label="Tanggal akhir filter">
              <button type="button" class="rekap-reset" onclick="resetRekapFilter()">Reset Filter</button>
              <button type="button" class="rekap-export" onclick="exportRekapCsv()">Export CSV</button>
            </div>
            <div class="rekap-count" id="rekapCount">Total data: 0</div>
          </div>

          <p class="scroll-hint">← geser tabel →</p>
          <div class="table-wrapper">
            <table id="rekapTable">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal</th>
                  <th>Nama Pelatihan</th>
                  <th>Peserta</th>
                  <th>Unit</th>
                  <th>Berkas</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                $result = mysqli_query($conn, "SELECT * FROM tb_sertifikat ORDER BY tanggal DESC");
                if ($result && mysqli_num_rows($result) > 0) {
                  while ($row = mysqli_fetch_assoc($result)) {
                    $rowId = (int) $row['id'];
                    $rowTanggal = htmlspecialchars($row['tanggal']);
                    $rowPelatihan = htmlspecialchars($row['pelatihan']);
                    $rowPeserta = htmlspecialchars($row['peserta']);
                    $rowUnit = htmlspecialchars($row['unit']);

                    echo "<tr>
                      <td>{$no}</td>
                      <td>{$rowTanggal}</td>
                      <td>{$rowPelatihan}</td>
                      <td>{$rowPeserta}</td>
                      <td>{$rowUnit}</td>
                      <td>";

                    if (!empty($row['sertifikat'])) {
                      $files = explode(', ', $row['sertifikat']);
                      $encodedFiles = htmlspecialchars(json_encode($files), ENT_QUOTES, 'UTF-8');
                      echo "<button type='button' class='doc-btn' data-files='" . $encodedFiles . "' onclick='bukaModalDokumen(this)'>📂 Lihat Dokumen (" . count($files) . ")</button>";
                    } else {
                      echo "<span class='doc-empty'>-</span>";
                    }

                    echo "</td>
                      <td>
                        <div class='aksi-group'>
                          <button type='button' class='edit-btn' onclick=\"editData({$rowId})\">✏️ Edit</button>
                          <button type='button' class='delete-btn' onclick=\"hapusData({$rowId})\">🗑️ Hapus</button>
                        </div>
                      </td>
                    </tr>";
                    $no++;
                  }
                } else {
                  echo "<tr><td colspan='7' style='text-align:center;'>Belum ada data sertifikat</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

          <div class="pagination-bar">
            <div class="pagination-info" id="paginationInfo">Menampilkan 0 data</div>
            <div class="pagination-actions">
              <button type="button" class="pagination-btn" id="prevPageBtn">Sebelumnya</button>
              <div class="pagination-page" id="paginationPage">Halaman 1 / 1</div>
              <button type="button" class="pagination-btn" id="nextPageBtn">Berikutnya</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal-overlay" id="deleteModal" aria-hidden="true">
  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <h3 id="deleteModalTitle">Konfirmasi Hapus</h3>
    <p>Data sertifikat yang dihapus tidak bisa dikembalikan. Lanjutkan hapus data ini?</p>
    <div class="modal-actions">
      <button type="button" class="modal-btn modal-btn-cancel" onclick="tutupModalHapus()">Batal</button>
      <button type="button" class="modal-btn modal-btn-delete" onclick="konfirmasiHapus()">Hapus</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="dokumenModal" aria-hidden="true">
  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="dokumenModalTitle">
    <h3 id="dokumenModalTitle">Dokumen Sertifikat</h3>
    <p>Daftar berkas sertifikat pada data ini.</p>
    <div class="modal-doc-list" id="dokumenList"></div>
    <div class="modal-actions">
      <button type="button" class="modal-btn modal-btn-cancel" onclick="tutupModalDokumen()">Tutup</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="infoModal" aria-hidden="true">
  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="infoModalTitle">
    <h3 id="infoModalTitle">Informasi</h3>
    <p id="infoModalMessage">Pesan informasi.</p>
    <div class="modal-actions">
      <button type="button" class="modal-btn modal-btn-primary" onclick="tutupModalInfo()">Tutup</button>
    </div>
  </div>
</div>

<script>
let deleteTargetId = null;
const uploadBasePath = '../uploads/sertifikat/';
const maxFileSize = 5 * 1024 * 1024;
const rekapRowsPerPage = 10;
let rekapCurrentPage = 1;

function showTab(tabId) {
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
  document.querySelector(`.tab-btn[onclick="showTab('${tabId}')"]`).classList.add('active');
  document.getElementById(tabId).classList.add('active');

  const url = new URL(window.location.href);
  url.searchParams.set('tab', tabId);
  if (tabId === 'rekap') {
    url.searchParams.delete('edit');
  }
  window.history.replaceState({}, '', url);
}

document.getElementById('sertifikatInput').addEventListener('change', function() {
  const preview = document.getElementById('preview');
  preview.innerHTML = '';
  const invalidFiles = [];

  if (this.files.length > 0) {
    const ul = document.createElement('ul');
    for (const file of this.files) {
      if (file.size > maxFileSize) {
        invalidFiles.push(file.name);
        continue;
      }

      const li = document.createElement('li');
      const sizeLabel = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
      li.innerHTML = `📎 <span>${file.name}</span> ${sizeLabel}`;
      ul.appendChild(li);
    }

    if (ul.children.length > 0) {
      preview.appendChild(ul);
    }
  }

  if (invalidFiles.length > 0) {
    this.value = '';
    preview.innerHTML = '';
    bukaModalInfo('File berikut melebihi batas 5 MB: ' + invalidFiles.join(', '));
  }
});

function updateRekapCount() {
  const rows = document.querySelectorAll('#rekapTable tbody tr');
  const visibleRows = Array.from(rows).filter(row => row.dataset.filtered !== 'true');
  document.getElementById('rekapCount').textContent = 'Total data: ' + visibleRows.length;
}

function updatePagination() {
  const rows = Array.from(document.querySelectorAll('#rekapTable tbody tr'));
  const filteredRows = rows.filter(row => row.dataset.filtered !== 'true' && row.children.length > 1);
  const emptyStateRow = rows.find(row => row.children.length === 1);
  const totalRows = filteredRows.length;
  const totalPages = Math.max(1, Math.ceil(totalRows / rekapRowsPerPage));

  if (rekapCurrentPage > totalPages) {
    rekapCurrentPage = totalPages;
  }

  const startIndex = (rekapCurrentPage - 1) * rekapRowsPerPage;
  const endIndex = startIndex + rekapRowsPerPage;

  rows.forEach((row) => {
    if (row.children.length === 1) {
      row.style.display = totalRows === 0 ? '' : 'none';
      return;
    }

    if (row.dataset.filtered === 'true') {
      row.style.display = 'none';
      return;
    }

    const visibleIndex = filteredRows.indexOf(row);
    row.style.display = visibleIndex >= startIndex && visibleIndex < endIndex ? '' : 'none';
  });

  if (emptyStateRow && totalRows > 0) {
    emptyStateRow.style.display = 'none';
  }

  const startLabel = totalRows === 0 ? 0 : startIndex + 1;
  const endLabel = totalRows === 0 ? 0 : Math.min(endIndex, totalRows);
  document.getElementById('paginationInfo').textContent = `Menampilkan ${startLabel}-${endLabel} dari ${totalRows} data`;
  document.getElementById('paginationPage').textContent = `Halaman ${rekapCurrentPage} / ${totalPages}`;
  document.getElementById('prevPageBtn').disabled = rekapCurrentPage === 1;
  document.getElementById('nextPageBtn').disabled = rekapCurrentPage === totalPages || totalRows === 0;
}

function filterRekap() {
  const keyword = document.getElementById('rekapSearch').value.toLowerCase().trim();
  const startDate = document.getElementById('rekapDateStart').value;
  const endDate = document.getElementById('rekapDateEnd').value;
  const rows = document.querySelectorAll('#rekapTable tbody tr');

  rows.forEach((row) => {
    const isEmptyState = row.children.length === 1;
    if (isEmptyState) {
      row.dataset.filtered = 'false';
      return;
    }

    const text = row.textContent.toLowerCase();
    const rowDateCell = row.children[1];
    const rowDate = rowDateCell ? rowDateCell.textContent.trim() : '';
    const matchesKeyword = text.includes(keyword);
    const matchesStart = !startDate || rowDate >= startDate;
    const matchesEnd = !endDate || rowDate <= endDate;

    row.dataset.filtered = matchesKeyword && matchesStart && matchesEnd ? 'false' : 'true';
  });

  rekapCurrentPage = 1;
  updateRekapCount();
  updatePagination();
}

function resetRekapFilter() {
  document.getElementById('rekapSearch').value = '';
  document.getElementById('rekapDateStart').value = '';
  document.getElementById('rekapDateEnd').value = '';
  filterRekap();
}

function exportRekapCsv() {
  const table = document.getElementById('rekapTable');
  const rows = Array.from(table.querySelectorAll('tr')).filter(row => row.style.display !== 'none');

  if (rows.length <= 1) {
    bukaModalInfo('Tidak ada data yang bisa diexport dari hasil filter saat ini.');
    return;
  }

  const csvRows = rows.map((row) => {
    const cells = Array.from(row.querySelectorAll('th, td'));
    return cells.slice(0, 6).map((cell, index) => {
      let text = cell.textContent.trim().replace(/\s+/g, ' ');
      if (index === 5 && text === '-') {
        text = 'Tidak ada berkas';
      }
      return '"' + text.replace(/"/g, '""') + '"';
    }).join(',');
  });

  const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  const today = new Date().toISOString().slice(0, 10);

  link.href = url;
  link.download = 'rekap-sertifikat-' + today + '.csv';
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

function editData(id) {
  const url = new URL(window.location.href);
  url.searchParams.set('tab', 'input');
  url.searchParams.set('edit', id);
  url.searchParams.delete('hapus');
  window.location.href = url.toString();
}

function hapusData(id) {
  deleteTargetId = id;
  document.getElementById('deleteModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function konfirmasiHapus() {
  if (!deleteTargetId) {
    return;
  }

  const url = new URL(window.location.href);
  url.searchParams.set('hapus', deleteTargetId);
  url.searchParams.set('tab', 'rekap');
  window.location.href = url.toString();
}

function tutupModalHapus() {
  deleteTargetId = null;
  document.getElementById('deleteModal').classList.remove('active');
  document.body.style.overflow = '';
}

function bukaModalDokumen(button) {
  const files = JSON.parse(button.dataset.files || '[]');
  const dokumenList = document.getElementById('dokumenList');
  dokumenList.innerHTML = '';

  files.forEach((fileName) => {
    const link = document.createElement('a');
    link.className = 'modal-doc-link';
    link.href = uploadBasePath + encodeURIComponent(fileName);
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    link.innerHTML = '<span>📄 ' + escapeHtml(fileName) + '</span><span>Buka</span>';
    dokumenList.appendChild(link);
  });

  document.getElementById('dokumenModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function tutupModalDokumen() {
  document.getElementById('dokumenModal').classList.remove('active');
  document.body.style.overflow = '';
}

function bukaModalInfo(message) {
  document.getElementById('infoModalMessage').textContent = message;
  document.getElementById('infoModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function tutupModalInfo() {
  document.getElementById('infoModal').classList.remove('active');
  document.body.style.overflow = '';
}

function kembaliDashboard() {
  window.location.href = '/dashboard.php';
}

function escapeHtml(value) {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

document.getElementById('deleteModal').addEventListener('click', function(event) {
  if (event.target === this) {
    tutupModalHapus();
  }
});

document.getElementById('dokumenModal').addEventListener('click', function(event) {
  if (event.target === this) {
    tutupModalDokumen();
  }
});

document.getElementById('infoModal').addEventListener('click', function(event) {
  if (event.target === this) {
    tutupModalInfo();
  }
});

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    tutupModalHapus();
    tutupModalDokumen();
    tutupModalInfo();
  }
});

document.getElementById('rekapSearch').addEventListener('input', filterRekap);
document.getElementById('rekapDateStart').addEventListener('input', filterRekap);
document.getElementById('rekapDateEnd').addEventListener('input', filterRekap);
document.getElementById('prevPageBtn').addEventListener('click', function() {
  if (rekapCurrentPage > 1) {
    rekapCurrentPage -= 1;
    updatePagination();
  }
});
document.getElementById('nextPageBtn').addEventListener('click', function() {
  rekapCurrentPage += 1;
  updatePagination();
});

updateRekapCount();
filterRekap();
</script>
<script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
