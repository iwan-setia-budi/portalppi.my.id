<?php
require_once __DIR__ . '/../config/assets.php';
session_start();
include_once '../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ===============================
// SIMPAN DATA
// ===============================
if (isset($_POST['submit'])) {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $kategori = trim($_POST['kategori'] ?? '');
    $berkas = '';

    if ($kategori === 'mou') {
        // MOU validation
        $nama_mitra = trim($_POST['nama_mitra'] ?? '');
        $jenis_kerjasama = trim($_POST['jenis_kerjasama'] ?? '');
        $nomor_dokumen = trim($_POST['nomor_dokumen'] ?? '');
        $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
        $tanggal_berakhir = trim($_POST['tanggal_berakhir'] ?? '');

        if (strlen($nama_mitra) > 255 || strlen($jenis_kerjasama) > 255 || strlen($nomor_dokumen) > 100) {
            echo "<script>alert('Beberapa field terlalu panjang.'); window.location.href='mou.php';</script>";
            exit;
        }

        if (empty($nama_mitra) || empty($jenis_kerjasama) || empty($nomor_dokumen) || empty($tanggal_mulai) || empty($tanggal_berakhir)) {
            echo "<script>alert('Data tidak valid. Pastikan semua field diisi dengan benar.'); window.location.href='mou.php';</script>";
            exit;
        }
    } else {
        // IZIN validation
        $jenis_izin = trim($_POST['jenis_izin'] ?? '');
        $nomor_izin = trim($_POST['nomor_izin'] ?? '');
        $nomor_dokumen = trim($_POST['nomor_dokumen'] ?? '');
        $tanggal_terbit = trim($_POST['tanggal_terbit'] ?? '');
        $tanggal_berlaku = trim($_POST['tanggal_berlaku'] ?? '');

        if (strlen($jenis_izin) > 255 || strlen($nomor_izin) > 100 || strlen($nomor_dokumen) > 100) {
            echo "<script>alert('Beberapa field terlalu panjang.'); window.location.href='mou.php';</script>";
            exit;
        }

        if (empty($jenis_izin) || empty($nomor_izin) || empty($nomor_dokumen) || empty($tanggal_terbit) || empty($tanggal_berlaku)) {
            echo "<script>alert('Data tidak valid. Pastikan semua field diisi dengan benar.'); window.location.href='mou.php';</script>";
            exit;
        }
    }

    // Validate: must have either file or link
    if (empty($_FILES['berkas']['name']) && empty($_POST['link'])) {
        echo "<script>alert('Anda harus mengisi file atau link. Salah satu harus ada.'); window.location.href='mou.php';</script>";
        exit;
    }

    // Prevent both file and link being filled
    if (!empty($_FILES['berkas']['name']) && !empty($_POST['link'])) {
        echo "<script>alert('Pilih salah satu: upload file atau isi link. Tidak boleh keduanya.'); window.location.href='mou.php';</script>";
        exit;
    }

    // Handle file upload
    if (!empty($_FILES['berkas']['name'])) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowedExt = ['pdf', 'doc', 'docx'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($_FILES['berkas']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Error uploading file.'); window.location.href='mou.php';</script>";
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            echo "<script>alert('Ekstensi file tidak diizinkan. Hanya PDF, DOC, DOCX.'); window.location.href='mou.php';</script>";
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['berkas']['tmp_name']);
        finfo_close($finfo);

        $mimeMap = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!isset($mimeMap[$ext]) || $mimeMap[$ext] !== $mime) {
            echo "<script>alert('File tidak valid (ekstensi dan tipe MIME tidak cocok).'); window.location.href='mou.php';</script>";
            exit;
        }

        if (!in_array($mime, $allowedTypes) || $_FILES['berkas']['size'] > $maxSize) {
            echo "<script>alert('File tidak valid. Hanya PDF, DOC, DOCX dengan ukuran maksimal 5MB.'); window.location.href='mou.php';</script>";
            exit;
        }

        $uploadDir = $kategori === 'mou' ? '../uploads/mou/' : '../uploads/izin/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . bin2hex(random_bytes(5)) . '_' . preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $_FILES['berkas']['name']);
        $filename = substr($filename, 0, 100);
        $filePath = $uploadDir . $filename;
        if (!move_uploaded_file($_FILES['berkas']['tmp_name'], $filePath)) {
            echo "<script>alert('Gagal menyimpan file.'); window.location.href='mou.php';</script>";
            exit;
        }
        $berkas = $filename;
    } else {
        $link = trim($_POST['link'] ?? '');
        if (!empty($link)) {
            if (!filter_var($link, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//', $link)) {
                echo "<script>alert('Link tidak valid. Harus dimulai dengan http:// atau https://.'); window.location.href='mou.php';</script>";
                exit;
            }
        }
        $berkas = $link;
    }

    // Insert data
    if ($kategori === 'mou') {
        $stmt = mysqli_prepare($conn, "INSERT INTO tb_mou (nama_mitra, jenis_kerjasama, nomor_dokumen, tanggal_mulai, tanggal_berakhir, berkas) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $nama_mitra, $jenis_kerjasama, $nomor_dokumen, $tanggal_mulai, $tanggal_berakhir, $berkas);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO tb_izin (jenis_izin, nomor_izin, nomor_dokumen, tanggal_terbit, tanggal_berlaku, berkas) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $jenis_izin, $nomor_izin, $nomor_dokumen, $tanggal_terbit, $tanggal_berlaku, $berkas);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $msg = $kategori === 'mou' ? 'MOU' : 'Izin';
        echo "<script>alert('$msg berhasil disimpan!'); window.location.href='mou.php';</script>";
    } else {
        error_log("Database error: " . mysqli_error($conn));
        echo "<script>alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'); window.location.href='mou.php';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ===============================
// HAPUS DATA
// ===============================
if (isset($_POST['hapus'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $id = intval($_POST['id']);
    $kategori = trim($_POST['kategori'] ?? '');

    if ($id <= 0 || !in_array($kategori, ['mou', 'izin'])) {
        echo "<script>alert('Parameter tidak valid.'); window.location.href='mou.php';</script>";
        exit;
    }

    $table = $kategori === 'mou' ? 'tb_mou' : 'tb_izin';
    $folder = $kategori === 'mou' ? 'mou' : 'izin';

    $stmt = mysqli_prepare($conn, "SELECT berkas FROM $table WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($data && !preg_match('/^https?:\/\//', $data['berkas'])) {
        $uploadBase = realpath("../uploads/$folder/");
        $filePath = realpath($uploadBase . DIRECTORY_SEPARATOR . $data['berkas']);
        
        if ($filePath && strpos($filePath, $uploadBase) === 0 && is_file($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM $table WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $msg = $kategori === 'mou' ? 'MOU' : 'Izin';
        echo "<script>alert('$msg berhasil dihapus!'); window.location.href='mou.php';</script>";
    } else {
        error_log("Database error: " . mysqli_error($conn));
        echo "<script>alert('Terjadi kesalahan saat menghapus data. Silakan coba lagi.'); window.location.href='mou.php';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ===============================
// PAGINATION - MOU
// ===============================
$limitMou = isset($_GET['limit_mou']) ? $_GET['limit_mou'] : 10;
$pageMou = isset($_GET['page_mou']) ? (int)$_GET['page_mou'] : 1;
if ($pageMou < 1) $pageMou = 1;

$totalMouQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_mou");
$totalMou = mysqli_fetch_assoc($totalMouQuery)['total'];

$isAllMou = ($limitMou === 'all');
if ($isAllMou && $totalMou > 500) {
    $limitMou = 500;
    $limit_sql_mou = "LIMIT 500";
} elseif ($isAllMou) {
    $limit_sql_mou = "";
} else {
    $limitMou = (int)$limitMou;
    if ($limitMou <= 0) $limitMou = 10;
    $offsetMou = ($pageMou - 1) * $limitMou;
    $limit_sql_mou = "LIMIT $offsetMou, $limitMou";
}
$totalPagesMou = $isAllMou ? 1 : ceil($totalMou / $limitMou);

$mouRows = [];
$mouQuery = mysqli_query($conn, "SELECT * FROM tb_mou ORDER BY id DESC $limit_sql_mou");
if ($mouQuery) {
    while ($mou = mysqli_fetch_assoc($mouQuery)) {
        $mouRows[] = $mou;
    }
}
$currentDisplayMou = count($mouRows);

// ===============================
// PAGINATION - IZIN
// ===============================
$limitIzin = isset($_GET['limit_izin']) ? $_GET['limit_izin'] : 10;
$pageIzin = isset($_GET['page_izin']) ? (int)$_GET['page_izin'] : 1;
if ($pageIzin < 1) $pageIzin = 1;

$totalIzinQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_izin");
$totalIzin = mysqli_fetch_assoc($totalIzinQuery)['total'];

$isAllIzin = ($limitIzin === 'all');
if ($isAllIzin && $totalIzin > 500) {
    $limitIzin = 500;
    $limit_sql_izin = "LIMIT 500";
} elseif ($isAllIzin) {
    $limit_sql_izin = "";
} else {
    $limitIzin = (int)$limitIzin;
    if ($limitIzin <= 0) $limitIzin = 10;
    $offsetIzin = ($pageIzin - 1) * $limitIzin;
    $limit_sql_izin = "LIMIT $offsetIzin, $limitIzin";
}
$totalPagesIzin = $isAllIzin ? 1 : ceil($totalIzin / $limitIzin);

$izinRows = [];
$izinQuery = mysqli_query($conn, "SELECT * FROM tb_izin ORDER BY id DESC $limit_sql_izin");
if ($izinQuery) {
    while ($izin = mysqli_fetch_assoc($izinQuery)) {
        $izinRows[] = $izin;
    }
}
$currentDisplayIzin = count($izinRows);

$pageTitle = "MOU & IZIN";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>MOU & Izin Resmi | PPI PHBW</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

  <style>
    :root {
      --brand: #2563eb;
      --brand-dark: #0f3a79;
      --brand-soft: #dbeafe;
      --accent: #0ea5e9;
      --bg: #eef4fb;
      --card: #ffffff;
      --line: #d7e3f1;
      --line-strong: #bfd3e8;
      --ink: #0f172a;
      --muted: #5f7187;
      --danger: #dc2626;
      --danger-soft: #fee2e2;
      --shadow-lg: 0 24px 50px rgba(15, 23, 42, 0.10);
      --shadow-md: 0 12px 30px rgba(37, 99, 235, 0.12);
      --shadow-sm: 0 8px 24px rgba(15, 23, 42, 0.08);
      --radius-xl: 24px;
      --radius-lg: 18px;
      --radius-md: 14px;
      --radius-pill: 999px;
    }

    body {
      background:
        radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 22%),
        linear-gradient(180deg, #f7fbff 0%, var(--bg) 100%);
      color: var(--ink);
    }

    main {
      min-width: 0;
    }

    .container {
      width: 100%;
      max-width: none;
      margin: 0;
      padding: 28px 24px 40px;
    }

    .hero-header {
      display: flex;
      align-items: stretch;
      justify-content: space-between;
      gap: 20px;
      padding: 16px 20px;
      margin-bottom: 22px;
      min-height: 140px;
      border-radius: var(--radius-xl);
      background:
        linear-gradient(135deg, rgba(15, 58, 121, 0.98), rgba(37, 99, 235, 0.94)),
        linear-gradient(135deg, #1e40af, #2563eb);
      color: #fff;
      box-shadow: var(--shadow-lg);
      position: relative;
      overflow: hidden;
    }

    .hero-header::before,
    .hero-header::after {
      display: none;
    }

    .hero-content,
    .hero-actions {
      position: relative;
      z-index: 1;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 10px;
      border-radius: var(--radius-pill);
      background: rgba(187, 247, 208, 0.22);
      border: 1px solid rgba(187, 247, 208, 0.38);
      font-size: 11px;
      font-weight: 700;
      color: #f0fdf4;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 14px;
    }

    .hero-header h1 {
      margin: 0 0 6px;
      color: #fff;
      font-size: 22px;
      line-height: 1.2;
      letter-spacing: -0.03em;
    }

    .subtitle {
      margin: 0;
      max-width: 600px;
      color: rgba(255, 255, 255, 0.86);
      font-size: 14px;
      line-height: 1.5;
    }

    .hero-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      min-width: 220px;
    }

    .hero-stat {
      padding: 12px 14px;
      border-radius: 20px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.26), rgba(255, 255, 255, 0.18));
      border: 1px solid rgba(255, 255, 255, 0.34);
      text-align: center;
      box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.28),
        0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .hero-stat strong {
      display: block;
      font-size: 28px;
      line-height: 1;
      margin-bottom: 6px;
    }

    .hero-stat span {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.78);
    }

    .toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 18px;
    }

    .toolbar-group {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .search-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
      width: min(100%, 320px);
      padding: 12px 16px;
      border-radius: var(--radius-pill);
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid var(--line);
      box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    }

    .search-wrap span {
      font-size: 1rem;
      color: var(--brand);
    }

    .search-wrap input {
      width: 100%;
      border: none;
      outline: none;
      padding: 0;
      background: transparent;
      color: var(--ink);
      font-size: 14px;
    }

    .search-wrap input::placeholder {
      color: #70839a;
    }

    a.btn,
    button {
      border: none;
      cursor: pointer;
      font-weight: 700;
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    a.btn:hover,
    button:hover {
      transform: translateY(-2px);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 12px 18px;
      border-radius: 14px;
      text-decoration: none;
      white-space: nowrap;
      font-size: 14px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #1d4ed8, #2563eb);
      color: #fff;
      box-shadow: 0 10px 24px rgba(37, 99, 235, 0.28);
    }

    .btn-primary:hover {
      box-shadow: 0 16px 32px rgba(37, 99, 235, 0.34);
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.16);
      box-shadow: none;
      opacity: 0.8;
    }

    .table-card {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0.92));
      border: 1px solid rgba(191, 211, 232, 0.85);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-sm);
      padding: 18px;
      overflow: hidden;
      margin-bottom: 20px;
    }

    .table-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 14px;
      padding: 0 4px;
    }

    .table-card-title {
      margin: 0;
      color: var(--brand-dark);
      font-size: 17px;
      font-weight: 800;
    }

    .table-card-note {
      color: var(--muted);
      font-size: 14px;
    }

    .tab-buttons {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .tab-btn {
      padding: 10px 16px;
      border-radius: 10px;
      border: 2px solid var(--line-strong);
      background: #fff;
      color: var(--ink);
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .tab-btn.active {
      background: linear-gradient(135deg, #1d4ed8, #2563eb);
      color: #fff;
      border-color: #1d4ed8;
    }

    .tab-btn:hover {
      border-color: var(--brand);
      background: var(--brand-soft);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .table-container {
      width: 100%;
      overflow-x: auto;
    }

    table {
      width: 100%;
      min-width: 0;
      table-layout: fixed;
      border-collapse: separate;
      border-spacing: 0 4px;
      font-size: 14px;
    }

    thead th {
      padding: 10px 10px;
      background: linear-gradient(135deg, #24499b 0%, #2563eb 100%);
      color: #fff;
      text-align: left;
      font-size: 14px;
      font-weight: 800;
      line-height: 1.2;
      border: none;
    }

    thead th:first-child {
      border-top-left-radius: 16px;
      border-bottom-left-radius: 16px;
    }

    thead th:last-child {
      border-top-right-radius: 16px;
      border-bottom-right-radius: 16px;
    }

    tbody tr {
      background: transparent;
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
    }

    tbody tr:hover {
      background: #eef5ff;
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(37, 99, 235, 0.10);
    }

    tbody td {
      background: #ffffff;
      padding: 8px 10px;
      border-top: 1px solid #e8f0fa;
      border-bottom: 1px solid #e8f0fa;
      line-height: 1.2;
      vertical-align: middle;
      font-size: 14px;
      overflow-wrap: anywhere;
    }

    tbody tr:nth-child(even) td {
      background: #dbe5f1;
    }

    tbody tr:hover td {
      background: #eef5ff;
    }

    tbody td:first-child {
      border-left: 1px solid #e8f0fa;
      border-top-left-radius: 16px;
      border-bottom-left-radius: 16px;
      font-weight: 700;
      color: var(--brand-dark);
    }

    tbody td:last-child {
      border-right: 1px solid #e8f0fa;
      border-top-right-radius: 16px;
      border-bottom-right-radius: 16px;
    }

    .title-cell {
      font-weight: 700;
      color: #122742;
      line-height: 1.22;
      font-size: 14px;
    }

    .meta-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 4px 8px;
      border-radius: 999px;
      background: #edf4ff;
      color: var(--brand-dark);
      font-weight: 700;
      font-size: 12px;
    }

    .file-link,
    button.delete {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-width: 88px;
      min-height: 34px;
      padding: 7px 10px;
      border-radius: 12px;
      position: relative;
      overflow: hidden;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.015em;
      text-decoration: none;
      white-space: nowrap;
      transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        background 0.22s ease,
        color 0.22s ease,
        border-color 0.22s ease;
    }

    .file-link::before,
    button.delete::before {
      content: "";
      width: 16px;
      height: 16px;
      flex: 0 0 16px;
      border-radius: 8px;
      display: inline-block;
      background-position: center;
      background-repeat: no-repeat;
      background-size: 10px 10px;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .file-link {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border: 1px solid #1d4ed8;
      color: #fff;
      box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
    }

    .file-link::before {
      background-color: rgba(255, 255, 255, 0.18);
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23ffffff' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M15 10l4.55-4.55a2.121 2.121 0 10-3-3L12 7m3 3l-6 6m0 0l-4.55 4.55a2.121 2.121 0 103 3L12 17m-3-1l6-6'/%3E%3C/svg%3E");
    }

    .file-link:hover {
      transform: translateY(-1px);
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      border-color: #93c5fd;
      color: #1d4ed8;
      box-shadow: 0 10px 18px rgba(37, 99, 235, 0.12);
    }

    button.delete {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      border: 1px solid #dc2626;
      color: #fff;
      box-shadow: 0 8px 18px rgba(220, 38, 38, 0.16);
    }

    button.delete::before {
      background-color: rgba(255, 255, 255, 0.18);
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23ffffff' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-7 0l1 12h6l1-12M10 11v5M14 11v5'/%3E%3C/svg%3E");
    }

    button.delete:hover {
      transform: translateY(-1px);
      background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
      border-color: #fda4af;
      color: #dc2626;
      box-shadow: 0 10px 18px rgba(220, 38, 38, 0.1);
    }

    .empty-state {
      text-align: center;
      color: var(--muted);
      padding: 30px 16px;
      background: #f8fbff;
      border-radius: 18px;
      border: 1px dashed var(--line-strong);
    }

    .form-modal {
      display: none;
      position: fixed;
      inset: 0;
      padding: 20px;
      background: rgba(15, 23, 42, 0.5);
      align-items: center;
      justify-content: center;
      z-index: 50;
      backdrop-filter: blur(6px);
    }

    .form-box {
      background: #fff;
      border-radius: 20px;
      padding: 24px;
      width: 100%;
      max-width: 520px;
      box-shadow: var(--shadow-lg);
      max-height: 90vh;
      overflow-y: auto;
    }

    .form-box h3 {
      margin-top: 0;
      color: var(--brand-dark);
      font-size: 17px;
    }

    .form-box label {
      display: block;
      margin-bottom: 6px;
      font-weight: 700;
      color: #29415f;
      font-size: 14px;
    }

    .form-box input,
    .form-box select {
      width: 100%;
      margin-bottom: 14px;
      padding: 11px 12px;
      border: 1px solid var(--line);
      border-radius: 12px;
      background: #fff;
      color: var(--ink);
      box-sizing: border-box;
      font-size: 14px;
    }

    .form-box input:focus,
    .form-box select:focus {
      border-color: #93c5fd;
      outline: none;
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .btn-group {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 10px;
    }

    .btn-neutral {
      background: #f1f5f9;
      color: #334155;
      padding: 11px 16px;
      border-radius: 12px;
    }

    .btn-add {
      padding: 12px 18px;
      border-radius: 14px;
      background: linear-gradient(135deg, #1d4ed8, #2563eb);
      color: #fff;
      box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
    }

    .pagination-section {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
      padding: 0 4px;
    }

    @media (max-width: 992px) {
      .container {
        padding: 22px 18px 34px;
      }

      .hero-header {
        padding: 22px;
      }

      .hero-actions {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .hero-header {
        flex-direction: column;
      }

      .hero-actions {
        width: 100%;
        grid-template-columns: 1fr 1fr;
      }

      .toolbar,
      .toolbar-group {
        flex-direction: column;
        align-items: stretch;
      }

      .search-wrap {
        width: 100%;
      }

      .table-card {
        padding: 14px;
      }

      .table-card-header {
        align-items: flex-start;
        flex-direction: column;
      }

      table thead {
        display: none;
      }

      table,
      tbody,
      tr,
      td {
        display: block;
        width: 100%;
      }

      table {
        min-width: 0;
        border-spacing: 0;
      }

      tbody tr {
        margin-bottom: 16px;
        padding: 14px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid #e7eef7;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
      }

      tbody td {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 9px 0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        text-align: right;
      }

      tbody tr:nth-child(even) td,
      tbody tr:hover td {
        background: transparent;
      }

      tbody td:first-child,
      tbody td:last-child {
        border: none;
        border-radius: 0;
      }

      tbody td::before {
        content: attr(data-label);
        color: #48627f;
        font-weight: 700;
        text-align: left;
        flex: 0 0 42%;
      }

      .file-link,
      button.delete {
        margin-left: auto;
      }
    }
  </style>
</head>

<body>
  <div class="layout">
    <?php include_once '../sidebar.php'; ?>

    <main>
      <?php include_once '../topbar.php'; ?>

      <div class="container">
        <section class="hero-header">
          <div class="hero-content">
            <div class="hero-badge">📑 Manajemen Kerjasama & Izin</div>
            <h1>MOU & Izin Resmi</h1>
            <p class="subtitle">Kelola Memorandum of Understanding dan izin rumah sakit dengan mudah dan terorganisir.</p>
          </div>

          <div class="hero-actions">
            <div class="hero-stat">
              <strong><?= $totalMou; ?></strong>
              <span>Dokumen MOU</span>
            </div>
            <div class="hero-stat">
              <strong><?= $totalIzin; ?></strong>
              <span>Izin Resmi</span>
            </div>
          </div>
        </section>

        <div class="toolbar">
          <div class="toolbar-group">
            <label class="search-wrap" for="searchInput">
              <span>🔎</span>
              <input type="text" placeholder="Cari berdasarkan nama, jenis, atau nomor..." id="searchInput">
            </label>
          </div>

          <div class="toolbar-group">
            <button class="btn btn-primary" id="openForm">+ Tambah Data</button>
          </div>
        </div>

        <!-- TAB BUTTONS -->
        <div class="tab-buttons">
          <button class="tab-btn active" onclick="switchTab('mou')">🤝 MOU</button>
          <button class="tab-btn" onclick="switchTab('izin')">🪪 Izin Resmi</button>
        </div>

        <!-- TAB MOU -->
        <div id="tab-mou" class="tab-content active">
          <section class="table-card">
            <div class="table-card-header">
              <h3 class="table-card-title">Daftar Memorandum of Understanding</h3>
              <span class="table-card-note">Klik berkas untuk melihat dokumen MOU, atau hapus jika data sudah tidak dipakai.</span>
            </div>

            <div class="table-container">
              <table id="mouTable">
                <thead>
                  <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:25%;">Nama Mitra</th>
                    <th style="width:18%;">Jenis Kerjasama</th>
                    <th style="width:12%;">Nomor Dokumen</th>
                    <th style="width:10%;">Mulai</th>
                    <th style="width:10%;">Berakhir</th>
                    <th style="width:10%;">Berkas</th>
                    <th style="width:10%;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $no = 1;
                  if (!empty($mouRows)) {
                    foreach ($mouRows as $row) {
                      echo "<tr>
                        <td data-label='No'>{$no}</td>
                        <td data-label='Nama Mitra' class='title-cell'>" . htmlspecialchars($row['nama_mitra']) . "</td>
                        <td data-label='Jenis Kerjasama'><span class='meta-pill'>" . htmlspecialchars($row['jenis_kerjasama']) . "</span></td>
                        <td data-label='Nomor Dokumen'>" . htmlspecialchars($row['nomor_dokumen']) . "</td>
                        <td data-label='Mulai'>" . $row['tanggal_mulai'] . "</td>
                        <td data-label='Berakhir'>" . $row['tanggal_berakhir'] . "</td>
                        <td data-label='Berkas'>";

                      if (preg_match('/^https?:\/\//', $row['berkas']) && filter_var($row['berkas'], FILTER_VALIDATE_URL)) {
                        echo "<a href='" . htmlspecialchars($row['berkas']) . "' target='_blank' class='file-link'>Lihat</a>";
                      } elseif (!preg_match('/^https?:\/\//', $row['berkas'])) {
                        $namaFile = basename($row['berkas']);
                        $filePath = "../uploads/mou/" . $namaFile;
                        if (file_exists($filePath)) {
                          echo "<a href='" . htmlspecialchars($filePath) . "' target='_blank' class='file-link'>Lihat</a>";
                        } else {
                          echo "<span style='color:#dc2626; font-weight:700;'>File tidak ditemukan</span>";
                        }
                      }

                      echo "</td>
                        <td data-label='Aksi'><button class='delete' onclick=\"hapusData('mou', {$row['id']})\">Hapus</button></td>
                      </tr>";
                      $no++;
                    }
                  } else {
                    echo "<tr><td colspan='8'><div class='empty-state'>Belum ada data MOU. Tambahkan dokumen pertama untuk mulai mengelola kerjasama.</div></td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- PAGINATION MOU -->
            <div class="pagination-section">
              <div style="font-size:13px; color: var(--muted); font-weight: 600;">
                Menampilkan <strong style="color: var(--ink);"><?= $currentDisplayMou ?></strong> dari <strong style="color: var(--ink);"><?= $totalMou ?></strong> data
              </div>

              <div style="font-size:14px; color: var(--muted);">
                <span style="font-weight: 700;">Tampilkan:</span>
                <a href="?limit_mou=10" class="btn btn-secondary" style="margin-left:8px; font-size:12px;">10</a>
                <a href="?limit_mou=20" class="btn btn-secondary" style="font-size:12px;">20</a>
                <a href="?limit_mou=50" class="btn btn-secondary" style="font-size:12px;">50</a>
                <a href="?limit_mou=all" class="btn btn-secondary" style="font-size:12px;">Semua</a>
              </div>

              <div style="font-size:14px;">
                <?php if (!$isAllMou): ?>
                  <?php if ($pageMou > 1): ?>
                    <a href="?page_mou=<?= $pageMou - 1 ?>&limit_mou=<?= htmlspecialchars($limitMou) ?>" class="btn btn-secondary" style="font-size:12px;">&laquo; Sebelumnya</a>
                  <?php endif; ?>

                  <?php for ($p = 1; $p <= $totalPagesMou; $p++): ?>
                    <?php if ($p == $pageMou): ?>
                      <span style="font-weight:700; margin:0 6px;"><?= $p ?></span>
                    <?php else: ?>
                      <a href="?page_mou=<?= $p ?>&limit_mou=<?= htmlspecialchars($limitMou) ?>" class="btn btn-secondary" style="margin:0 3px; font-size:12px;"><?= $p ?></a>
                    <?php endif; ?>
                  <?php endfor; ?>

                  <?php if ($pageMou < $totalPagesMou): ?>
                    <a href="?page_mou=<?= $pageMou + 1 ?>&limit_mou=<?= htmlspecialchars($limitMou) ?>" class="btn btn-secondary" style="font-size:12px;">Berikutnya &raquo;</a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          </section>
        </div>

        <!-- TAB IZIN -->
        <div id="tab-izin" class="tab-content">
          <section class="table-card">
            <div class="table-card-header">
              <h3 class="table-card-title">Daftar Izin Resmi</h3>
              <span class="table-card-note">Klik berkas untuk melihat dokumen izin, atau hapus jika data sudah tidak dipakai.</span>
            </div>

            <div class="table-container">
              <table id="izinTable">
                <thead>
                  <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:20%;">Jenis Izin</th>
                    <th style="width:15%;">Nomor Izin</th>
                    <th style="width:15%;">Nomor Dokumen</th>
                    <th style="width:10%;">Terbit</th>
                    <th style="width:10%;">Berlaku</th>
                    <th style="width:10%;">Berkas</th>
                    <th style="width:10%;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $no = 1;
                  if (!empty($izinRows)) {
                    foreach ($izinRows as $row) {
                      echo "<tr>
                        <td data-label='No'>{$no}</td>
                        <td data-label='Jenis Izin' class='title-cell'>" . htmlspecialchars($row['jenis_izin']) . "</td>
                        <td data-label='Nomor Izin'><span class='meta-pill'>" . htmlspecialchars($row['nomor_izin']) . "</span></td>
                        <td data-label='Nomor Dokumen'>" . htmlspecialchars($row['nomor_dokumen']) . "</td>
                        <td data-label='Terbit'>" . $row['tanggal_terbit'] . "</td>
                        <td data-label='Berlaku'>" . $row['tanggal_berlaku'] . "</td>
                        <td data-label='Berkas'>";

                      if (preg_match('/^https?:\/\//', $row['berkas']) && filter_var($row['berkas'], FILTER_VALIDATE_URL)) {
                        echo "<a href='" . htmlspecialchars($row['berkas']) . "' target='_blank' class='file-link'>Lihat</a>";
                      } elseif (!preg_match('/^https?:\/\//', $row['berkas'])) {
                        $namaFile = basename($row['berkas']);
                        $filePath = "../uploads/izin/" . $namaFile;
                        if (file_exists($filePath)) {
                          echo "<a href='" . htmlspecialchars($filePath) . "' target='_blank' class='file-link'>Lihat</a>";
                        } else {
                          echo "<span style='color:#dc2626; font-weight:700;'>File tidak ditemukan</span>";
                        }
                      }

                      echo "</td>
                        <td data-label='Aksi'><button class='delete' onclick=\"hapusData('izin', {$row['id']})\">Hapus</button></td>
                      </tr>";
                      $no++;
                    }
                  } else {
                    echo "<tr><td colspan='8'><div class='empty-state'>Belum ada data izin resmi. Tambahkan dokumen izin untuk mulai mengelola perizinan.</div></td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- PAGINATION IZIN -->
            <div class="pagination-section">
              <div style="font-size:13px; color: var(--muted); font-weight: 600;">
                Menampilkan <strong style="color: var(--ink);"><?= $currentDisplayIzin ?></strong> dari <strong style="color: var(--ink);"><?= $totalIzin ?></strong> data
              </div>

              <div style="font-size:14px; color: var(--muted);">
                <span style="font-weight: 700;">Tampilkan:</span>
                <a href="?limit_izin=10" class="btn btn-secondary" style="margin-left:8px; font-size:12px;">10</a>
                <a href="?limit_izin=20" class="btn btn-secondary" style="font-size:12px;">20</a>
                <a href="?limit_izin=50" class="btn btn-secondary" style="font-size:12px;">50</a>
                <a href="?limit_izin=all" class="btn btn-secondary" style="font-size:12px;">Semua</a>
              </div>

              <div style="font-size:14px;">
                <?php if (!$isAllIzin): ?>
                  <?php if ($pageIzin > 1): ?>
                    <a href="?page_izin=<?= $pageIzin - 1 ?>&limit_izin=<?= htmlspecialchars($limitIzin) ?>" class="btn btn-secondary" style="font-size:12px;">&laquo; Sebelumnya</a>
                  <?php endif; ?>

                  <?php for ($p = 1; $p <= $totalPagesIzin; $p++): ?>
                    <?php if ($p == $pageIzin): ?>
                      <span style="font-weight:700; margin:0 6px;"><?= $p ?></span>
                    <?php else: ?>
                      <a href="?page_izin=<?= $p ?>&limit_izin=<?= htmlspecialchars($limitIzin) ?>" class="btn btn-secondary" style="margin:0 3px; font-size:12px;"><?= $p ?></a>
                    <?php endif; ?>
                  <?php endfor; ?>

                  <?php if ($pageIzin < $totalPagesIzin): ?>
                    <a href="?page_izin=<?= $pageIzin + 1 ?>&limit_izin=<?= htmlspecialchars($limitIzin) ?>" class="btn btn-secondary" style="font-size:12px;">Berikutnya &raquo;</a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          </section>
        </div>
      </div>

      <footer style="
        margin-top:30px;
        padding:16px;
        text-align:center;
        font-size:13px;
        color:#64748b;
        border-top:1px solid #e2e8f0;
        background:#f8fafc;
      ">
        © <?= date('Y') ?> PPI RS Primaya Bhaktiwara Pangkalpinang  
        <br>
        Sistem Manajemen Dokumen & Regulasi
      </footer>
    </main>
  </div>

  <!-- FORM MODAL -->
  <div class="form-modal" id="formModal">
    <div class="form-box">
      <h3>Tambah Data Baru</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

        <label>Kategori</label>
        <select name="kategori" id="kategoriSelect" required onchange="updateFormFields()">
          <option value="">-- Pilih Kategori --</option>
          <option value="mou">🤝 MOU</option>
          <option value="izin">🪪 Izin Resmi</option>
        </select>

        <!-- MOU Fields -->
        <div id="moFields" style="display:none;">
          <label>Nama Mitra</label>
          <input type="text" name="nama_mitra" placeholder="Contoh: Universitas Bangka Belitung">

          <label>Jenis Kerjasama</label>
          <input type="text" name="jenis_kerjasama" placeholder="Contoh: Pendidikan & Penelitian">

          <label>Nomor Dokumen</label>
          <input type="text" name="nomor_dokumen" placeholder="Contoh: MOU/PHBW/001/2025">

          <label>Tanggal Mulai</label>
          <input type="date" name="tanggal_mulai">

          <label>Tanggal Berakhir</label>
          <input type="date" name="tanggal_berakhir">
        </div>

        <!-- IZIN Fields -->
        <div id="izinFields" style="display:none;">
          <label>Jenis Izin</label>
          <input type="text" name="jenis_izin" placeholder="Contoh: Izin Operasional">

          <label>Nomor Izin</label>
          <input type="text" name="nomor_izin" placeholder="Contoh: 123456789">

          <label>Nomor Dokumen</label>
          <input type="text" name="nomor_dokumen" placeholder="Contoh: IZN/PHBW/2025">

          <label>Tanggal Terbit</label>
          <input type="date" name="tanggal_terbit">

          <label>Tanggal Berlaku</label>
          <input type="date" name="tanggal_berlaku">
        </div>

        <label>Berkas (PDF/DOC/DOCX) atau Link</label>
        <input type="file" name="berkas" accept=".pdf,.doc,.docx">
        <input type="text" name="link" placeholder="https://contoh-link.pdf">

        <div class="btn-group">
          <button type="button" class="btn-neutral" id="closeForm">Batal</button>
          <button type="submit" class="btn-add" name="submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?= asset('assets/js/utama.js') ?>"></script>

  <script>
    const modal = document.getElementById('formModal');
    document.getElementById('openForm').onclick = () => {
      modal.style.display = 'flex';
    };
    document.getElementById('closeForm').onclick = () => {
      modal.style.display = 'none';
    };
    window.onclick = (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    };

    function switchTab(tab) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.getElementById('tab-' + tab).classList.add('active');
      document.querySelector(`.tab-btn[onclick="switchTab('${tab}')"]`).classList.add('active');
    }

    function updateFormFields() {
      const kategori = document.getElementById('kategoriSelect').value;
      document.getElementById('moFields').style.display = kategori === 'mou' ? 'block' : 'none';
      document.getElementById('izinFields').style.display = kategori === 'izin' ? 'block' : 'none';
    }

    function hapusData(kategori, id) {
      if (confirm('Yakin ingin menghapus data ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;

        const inputKategori = document.createElement('input');
        inputKategori.type = 'hidden';
        inputKategori.name = 'kategori';
        inputKategori.value = kategori;

        const inputHapus = document.createElement('input');
        inputHapus.type = 'hidden';
        inputHapus.name = 'hapus';
        inputHapus.value = '1';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf_token';
        csrf.value = "<?= $_SESSION['csrf_token']; ?>";

        form.appendChild(inputId);
        form.appendChild(inputKategori);
        form.appendChild(inputHapus);
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
      }
    }

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', () => {
      const term = searchInput.value.toLowerCase();
      const activeTab = document.querySelector('.tab-content.active');
      const table = activeTab.querySelector('table');
      
      table.querySelectorAll('tbody tr').forEach((row) => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
