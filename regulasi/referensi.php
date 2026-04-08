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

    // Validate and sanitize inputs
    $judul = trim($_POST['judul'] ?? '');
    $jenis = trim($_POST['jenis'] ?? '');
    $tahun = intval($_POST['tahun'] ?? 0);
    $sumber = trim($_POST['sumber'] ?? '');
    $berkas = '';

    // Validate input length
    if (strlen($judul) > 255 || strlen($jenis) > 255 || strlen($sumber) > 255) {
        echo "<script>alert('Beberapa field terlalu panjang.'); window.location.href='referensi.php';</script>";
        exit;
    }

    if (empty($judul) || empty($jenis) || $tahun < 1900 || $tahun > 2100 || empty($sumber)) {
        echo "<script>alert('Data tidak valid. Pastikan semua field diisi dengan benar.'); window.location.href='referensi.php';</script>";
        exit;
    }

    // Validate: must have either file or link
    if (empty($_FILES['berkas']['name']) && empty($_POST['link'])) {
        echo "<script>alert('Anda harus mengisi file atau link. Salah satu harus ada.'); window.location.href='referensi.php';</script>";
        exit;
    }

    // Prevent both file and link being filled
    if (!empty($_FILES['berkas']['name']) && !empty($_POST['link'])) {
        echo "<script>alert('Pilih salah satu: upload file atau isi link. Tidak boleh keduanya.'); window.location.href='referensi.php';</script>";
        exit;
    }

    // Handle file upload
    if (!empty($_FILES['berkas']['name'])) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowedExt = ['pdf', 'doc', 'docx'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($_FILES['berkas']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Error uploading file.'); window.location.href='referensi.php';</script>";
            exit;
        }

        // Validate extension
        $ext = strtolower(pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            echo "<script>alert('Ekstensi file tidak diizinkan. Hanya PDF, DOC, DOCX.'); window.location.href='referensi.php';</script>";
            exit;
        }

        // Use finfo for secure MIME type detection
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['berkas']['tmp_name']);
        finfo_close($finfo);

        // Ensure MIME and extension match
        $mimeMap = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!isset($mimeMap[$ext]) || $mimeMap[$ext] !== $mime) {
            echo "<script>alert('File tidak valid (ekstensi dan tipe MIME tidak cocok).'); window.location.href='referensi.php';</script>";
            exit;
        }

        if (!in_array($mime, $allowedTypes) || $_FILES['berkas']['size'] > $maxSize) {
            echo "<script>alert('File tidak valid. Hanya PDF, DOC, DOCX dengan ukuran maksimal 5MB.'); window.location.href='referensi.php';</script>";
            exit;
        }

        $uploadDir = '../uploads/referensi/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . bin2hex(random_bytes(5)) . '_' . preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $_FILES['berkas']['name']);
        $filename = substr($filename, 0, 100); // Limit filename length
        $filePath = $uploadDir . $filename;
        if (!move_uploaded_file($_FILES['berkas']['tmp_name'], $filePath)) {
            echo "<script>alert('Gagal menyimpan file.'); window.location.href='referensi.php';</script>";
            exit;
        }
        $berkas = $filename;
    } else {
        $link = trim($_POST['link'] ?? '');
        if (!empty($link)) {
            if (!filter_var($link, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//', $link)) {
                echo "<script>alert('Link tidak valid. Harus dimulai dengan http:// atau https://.'); window.location.href='referensi.php';</script>";
                exit;
            }
        }
        $berkas = $link;
    }

    // Use prepared statement
    $stmt = mysqli_prepare($conn, "INSERT INTO tb_referensi (judul, jenis, tahun, sumber, berkas) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiss", $judul, $jenis, $tahun, $sumber, $berkas);
    if (mysqli_stmt_execute($stmt)) {
        // Rotate CSRF token after successful submission
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        echo "<script>alert('Referensi berhasil disimpan!'); window.location.href='referensi.php';</script>";
    } else {
        error_log("Database error: " . mysqli_error($conn));
        echo "<script>alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'); window.location.href='referensi.php';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ===============================
// HAPUS DATA
// ===============================
if (isset($_POST['hapus'])) {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $id = intval($_POST['id']);
    if ($id <= 0) {
        echo "<script>alert('ID tidak valid.'); window.location.href='referensi.php';</script>";
        exit;
    }

    // Use prepared statement
    $stmt = mysqli_prepare($conn, "SELECT berkas FROM tb_referensi WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Safely delete file with path traversal protection
    if ($data && !preg_match('/^https?:\/\//', $data['berkas'])) {
        $uploadBase = realpath("../uploads/referensi/");
        $filePath = realpath($uploadBase . DIRECTORY_SEPARATOR . $data['berkas']);
        
        if ($filePath && strpos($filePath, $uploadBase) === 0 && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM tb_referensi WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        // Rotate CSRF token after successful deletion
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        echo "<script>alert('Referensi berhasil dihapus!'); window.location.href='referensi.php';</script>";
    } else {
        error_log("Database error: " . mysqli_error($conn));
        echo "<script>alert('Terjadi kesalahan saat menghapus data. Silakan coba lagi.'); window.location.href='referensi.php';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}

$jenisReferensiRows = [];
$jenisReferensi = safe_mysqli_query($conn, "
  SELECT id, nama_referensi
  FROM tb_jenis_referensi
  WHERE status='aktif'
  ORDER BY nama_referensi ASC
");
if ($jenisReferensi) {
  while ($row = mysqli_fetch_assoc($jenisReferensi)) {
    $jenisReferensiRows[] = $row;
  }
}

$sumberReferensiRows = [];
$sumberReferensi = safe_mysqli_query($conn, "
  SELECT id, nama_sumber
  FROM tb_sumber_referensi
  WHERE status='aktif'
  ORDER BY nama_sumber ASC
");
if ($sumberReferensi) {
  while ($row = mysqli_fetch_assoc($sumberReferensi)) {
    $sumberReferensiRows[] = $row;
  }
}

// ===============================
// PAGINATION
// ===============================
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_referensi");
$totalReferensi = mysqli_fetch_assoc($totalQuery)['total'];

$isAll = ($limit === 'all');
if ($isAll && $totalReferensi > 500) {
    $limit = 500;
    $limit_sql = "LIMIT 500";
} elseif ($isAll) {
    $limit_sql = "";
} else {
    $limit = (int)$limit;
    if ($limit <= 0) {
        $limit = 10;
    }
    $offset = ($page - 1) * $limit;
    $limit_sql = "LIMIT $offset, $limit";
}

$totalPages = $isAll ? 1 : ceil($totalReferensi / $limit);

$referensiRows = [];
$referensiQuery = mysqli_query($conn, "SELECT * FROM tb_referensi ORDER BY id DESC $limit_sql");
if ($referensiQuery) {
  while ($referensi = mysqli_fetch_assoc($referensiQuery)) {
    $referensiRows[] = $referensi;
  }
}

$currentDisplayCount = count($referensiRows);
?>

<?php
$pageTitle = "REFERENSI";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Referensi | PPI PHBW</title>
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
      min-width: 210px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-end;
      gap: 0;
    }

    .hero-stat {
      min-width: 150px;
      padding: 12px 14px;
      border-radius: 20px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.26), rgba(255, 255, 255, 0.18));
      border: 1px solid rgba(255, 255, 255, 0.34);
      text-align: center;
      box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.28),
        0 8px 18px rgba(15, 23, 42, 0.08);
      margin-top: 12px;
    }

    .hero-stat strong {
      display: block;
      font-size: 36px;
      line-height: 1;
      margin-bottom: 6px;
    }

    .hero-stat span {
      font-size: 14px;
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

    thead th:nth-child(1) { width: 5%; }
    thead th:nth-child(2) { width: 40%; }
    thead th:nth-child(3) { width: 10%; }
    thead th:nth-child(4) { width: 8%; }
    thead th:nth-child(5) { width: 13%; }
    thead th:nth-child(6) { width: 12%; }
    thead th:nth-child(7) { width: 12%; }

    thead th:nth-child(3),
    thead th:nth-child(4),
    thead th:nth-child(5),
    thead th:nth-child(6),
    thead th:nth-child(7) {
      text-align: center;
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

    tbody td:nth-child(3),
    tbody td:nth-child(4),
    tbody td:nth-child(5) {
      text-align: center;
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

    .file-link:hover::before {
      background-color: rgba(255, 255, 255, 0.88);
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231d4ed8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M15 10l4.55-4.55a2.121 2.121 0 10-3-3L12 7m3 3l-6 6m0 0l-4.55 4.55a2.121 2.121 0 103 3L12 17m-3-1l6-6'/%3E%3C/svg%3E");
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

    button.delete:hover::before {
      background-color: rgba(255, 255, 255, 0.92);
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23dc2626' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-7 0l1 12h6l1-12M10 11v5M14 11v5'/%3E%3C/svg%3E");
    }

    .file-link:focus-visible,
    button.delete:focus-visible {
      outline: none;
      box-shadow:
        0 0 0 4px rgba(255, 255, 255, 0.9),
        0 0 0 7px rgba(37, 99, 235, 0.22);
    }

    button.delete:focus-visible {
      box-shadow:
        0 0 0 4px rgba(255, 255, 255, 0.9),
        0 0 0 7px rgba(220, 38, 38, 0.2);
    }

    td[data-label="Berkas"],
    td[data-label="Aksi"] {
      text-align: center;
      vertical-align: middle;
    }

    td[data-label="Berkas"] .file-link,
    td[data-label="Aksi"] .delete {
      margin: 0 auto;
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
      max-width: 480px;
      box-shadow: var(--shadow-lg);
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

    /* DARK MODE KHUSUS HALAMAN REFERENSI */
    body.dark-mode.referensi-page {
      --ink: #e2e8f0;
      --muted: #9fb3ca;
      --line: #334155;
      --line-strong: #475569;
      background:
        radial-gradient(circle at top right, rgba(30, 64, 175, 0.22), transparent 26%),
        linear-gradient(180deg, #030d24 0%, #071632 100%);
      color: var(--ink);
    }

    body.dark-mode.referensi-page .container {
      background: transparent;
    }

    body.dark-mode.referensi-page .hero-header {
      box-shadow: 0 24px 50px rgba(2, 6, 23, 0.45);
      border: 1px solid rgba(59, 130, 246, 0.28);
    }

    body.dark-mode.referensi-page .search-wrap {
      background: rgba(15, 23, 42, 0.9);
      border-color: rgba(71, 85, 105, 0.9);
      box-shadow: 0 12px 24px rgba(2, 6, 23, 0.38);
    }

    body.dark-mode.referensi-page .search-wrap input {
      color: #e2e8f0;
    }

    body.dark-mode.referensi-page .search-wrap input::placeholder {
      color: #94a3b8;
    }

    body.dark-mode.referensi-page .table-card {
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(15, 23, 42, 0.9));
      border: 1px solid rgba(51, 65, 85, 0.95);
      box-shadow: 0 18px 40px rgba(2, 6, 23, 0.44);
    }

    body.dark-mode.referensi-page .table-card-title {
      color: #e2e8f0;
    }

    body.dark-mode.referensi-page .table-card-note {
      color: #9fb3ca;
    }

    body.dark-mode.referensi-page thead th {
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
      color: #f8fafc;
    }

    body.dark-mode.referensi-page tbody tr {
      box-shadow: 0 4px 12px rgba(2, 6, 23, 0.24);
    }

    body.dark-mode.referensi-page tbody td {
      background: #0f172a;
      border-top: 1px solid #1e293b;
      border-bottom: 1px solid #1e293b;
      color: #dbeafe !important;
    }

    body.dark-mode.referensi-page tbody tr:nth-child(even) td {
      background: #172033;
    }

    body.dark-mode.referensi-page tbody tr:hover td {
      background: #1e293b;
    }

    body.dark-mode.referensi-page tbody td:first-child {
      border-left: 1px solid #1e293b;
      color: #93c5fd !important;
    }

    body.dark-mode.referensi-page tbody td:last-child {
      border-right: 1px solid #1e293b;
    }

    body.dark-mode.referensi-page .title-cell {
      color: #f1f5f9 !important;
    }

    body.dark-mode.referensi-page .meta-pill {
      background: rgba(37, 99, 235, 0.22);
      color: #bfdbfe;
      border: 1px solid rgba(96, 165, 250, 0.35);
    }

    body.dark-mode.referensi-page .empty-state {
      background: #0f172a;
      border-color: #334155;
      color: #cbd5e1;
    }

    body.dark-mode.referensi-page .form-box {
      background: #0f172a;
      border: 1px solid #334155;
    }

    body.dark-mode.referensi-page .form-box h3,
    body.dark-mode.referensi-page .form-box label {
      color: #e2e8f0;
    }

    body.dark-mode.referensi-page .form-box input,
    body.dark-mode.referensi-page .form-box select {
      background: #1e293b;
      border-color: #334155;
      color: #e2e8f0;
    }

    body.dark-mode.referensi-page footer {
      color: #94a3b8 !important;
      border-top-color: #1e293b !important;
      background: #0b1220 !important;
    }

    @media (max-width: 768px) {
      body.dark-mode.referensi-page tbody tr {
        background: #0f172a;
        border: 1px solid #334155;
        box-shadow: 0 12px 24px rgba(2, 6, 23, 0.35);
      }

      body.dark-mode.referensi-page tbody td {
        color: #dbeafe !important;
      }

      body.dark-mode.referensi-page tbody td::before {
        color: #93c5fd;
      }
    }

    @media (max-width: 992px) {
      .container {
        padding: 22px 18px 34px;
      }

      .hero-header {
        padding: 22px;
      }
    }

    @media (max-width: 768px) {
      .hero-header {
        flex-direction: column;
      }

      .hero-actions {
        width: 100%;
        align-items: stretch;
        justify-content: flex-start;
      }

      .hero-stat {
        width: 100%;
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

      tbody td:nth-child(3),
      tbody td:nth-child(4),
      tbody td:nth-child(5) {
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

      .title-cell {
        text-align: left;
        flex-direction: column;
        align-items: flex-start;
      }

      .title-cell::before {
        width: 100%;
        margin-bottom: 4px;
      }

      .file-link,
      button.delete,
      .meta-pill {
        margin-left: auto;
      }

      .file-link,
      button.delete {
        min-width: 92px;
        min-height: 34px;
        padding: 8px 10px;
      }
    }
  </style>
</head>

<body class="referensi-page">
  <div class="layout">
    <?php include_once '../sidebar.php'; ?>

    <main>
      <?php include_once '../topbar.php'; ?>

      <div class="container">
        <section class="hero-header">
          <div class="hero-content">
            <div class="hero-badge">📚 Pusat Dokumen PPI</div>
            <h1>Daftar Referensi Rumah Sakit</h1>
            <p class="subtitle">Kelola dokumen referensi PPI secara rapi dan mudah dicari.</p>
          </div>

          <div class="hero-actions">
            <div class="hero-stat">
              <strong><?= $totalReferensi; ?></strong>
              <span>Dokumen referensi aktif</span>
            </div>
          </div>
        </section>

        <div class="toolbar">
          <div class="toolbar-group">
            <label class="search-wrap" for="searchInput">
              <span>🔎</span>
              <input type="text" placeholder="Cari berdasarkan judul atau jenis referensi..." id="searchInput">
            </label>
          </div>

          <div class="toolbar-group">
            <button class="btn btn-primary" id="openForm">+ Tambah Referensi</button>
          </div>
        </div>

        <section class="table-card">
          <div class="table-card-header">
            <h3 class="table-card-title">Daftar Dokumen Referensi</h3>
            <span class="table-card-note">Klik berkas untuk melihat dokumen, atau hapus jika data sudah tidak dipakai.</span>
          </div>

          <div class="table-container">
            <table id="refTable">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Judul Referensi</th>
                  <th>Jenis</th>
                  <th>Tahun</th>
                  <th>Sumber / Penerbit</th>
                  <th>Berkas</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                if (!empty($referensiRows)) {
                  foreach ($referensiRows as $row) {
                    echo "<tr>
                      <td data-label='No'>{$no}</td>
                      <td data-label='Judul Referensi' class='title-cell'>" . htmlspecialchars($row['judul']) . "</td>
                      <td data-label='Jenis'><span class='meta-pill'>" . htmlspecialchars($row['jenis']) . "</span></td>
                      <td data-label='Tahun'>{$row['tahun']}</td>
                      <td data-label='Sumber / Penerbit'>" . htmlspecialchars($row['sumber']) . "</td>
                      <td data-label='Berkas'>";

                    if (preg_match('/^https?:\/\//', $row['berkas']) && filter_var($row['berkas'], FILTER_VALIDATE_URL)) {
                      echo "<a href='" . htmlspecialchars($row['berkas']) . "' target='_blank' class='file-link'>Lihat</a>";
                    } elseif (!preg_match('/^https?:\/\//', $row['berkas'])) {
                      // Prevent path traversal attacks
                      $namaFile = basename($row['berkas']);
                      $filePath = "../uploads/referensi/" . $namaFile;
                      
                      // Check if file exists
                      if (file_exists($filePath)) {
                        echo "<a href='" . htmlspecialchars($filePath) . "' target='_blank' class='file-link'>Lihat</a>";
                      } else {
                        echo "<span style='color:#dc2626; font-weight:700;'>File tidak ditemukan</span>";
                      }
                    }

                    echo "</td>
                      <td data-label='Aksi'><button class='delete' onclick=\"hapusData({$row['id']})\">Hapus</button></td>
                    </tr>";
                    $no++;
                  }
                } else {
                  echo "<tr><td colspan='7'><div class='empty-state'>Belum ada data referensi. Tambahkan dokumen pertama untuk mulai mengelola referensi.</div></td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

          <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; padding: 0 4px;">
            <div style="font-size:13px; color: var(--muted); font-weight: 600;">
              Menampilkan <strong style="color: var(--ink);"><?= $currentDisplayCount ?></strong> dari <strong style="color: var(--ink);"><?= $totalReferensi ?></strong> data
            </div>

            <div style="font-size:14px; color: var(--muted);">
              <span style="font-weight: 700;">Tampilkan:</span>
              <a href="?limit=10" class="btn btn-secondary" style="margin-left:8px;">10</a>
              <a href="?limit=20" class="btn btn-secondary">20</a>
              <a href="?limit=50" class="btn btn-secondary">50</a>
              <a href="?limit=all" class="btn btn-secondary">Semua</a>
            </div>

            <div style="font-size:14px;">
              <?php if (!$isAll): ?>
                <?php if ($page > 1): ?>
                  <a href="?page=<?= $page - 1 ?>&limit=<?= htmlspecialchars($limit) ?>" class="btn btn-secondary">&laquo; Sebelumnya</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                  <?php if ($p == $page): ?>
                    <span style="font-weight:700; margin:0 6px;"><?= $p ?></span>
                  <?php else: ?>
                    <a href="?page=<?= $p ?>&limit=<?= htmlspecialchars($limit) ?>" class="btn btn-secondary" style="margin:0 3px;"><?= $p ?></a>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <a href="?page=<?= $page + 1 ?>&limit=<?= htmlspecialchars($limit) ?>" class="btn btn-secondary">Berikutnya &raquo;</a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </section>
      </div>

      <div class="form-modal" id="formModal">
        <div class="form-box">
          <h3>Tambah Referensi Baru</h3>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <label>Judul Referensi</label>
            <input type="text" name="judul" required>

            <label>Jenis Referensi</label>
            <select name="jenis" required>
              <option value="">Pilih Jenis Referensi...</option>
              <?php foreach ($jenisReferensiRows as $j): ?>
                <option value="<?= $j['nama_referensi']; ?>">
                  <?= htmlspecialchars($j['nama_referensi']); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label>Tahun Terbit</label>
            <input type="number" name="tahun" min="1900" max="2100" required>

            <label>Sumber / Penerbit</label>
            <select name="sumber" required>
              <option value="">Pilih Sumber Referensi...</option>
              <?php foreach ($sumberReferensiRows as $s): ?>
                <option value="<?= $s['nama_sumber']; ?>">
                  <?= htmlspecialchars($s['nama_sumber']); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label>Berkas (PDF) atau Link</label>
            <input type="file" name="berkas" accept=".pdf,.doc,.docx">
            <input type="text" name="link" placeholder="https://contoh-link.pdf">

            <div class="btn-group">
              <button type="button" id="closeForm" class="btn-neutral">Batal</button>
              <button type="submit" class="btn-add" name="submit">Simpan</button>
            </div>
          </form>
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
        � <?= date('Y') ?> PPI RS Primaya Bhaktiwara Pangkalpinang  
        <br>
        Sistem Manajemen Dokumen & Regulasi
      </footer>
    </main>
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

    function hapusData(id) {
      if (confirm('Apakah Anda yakin ingin menghapus referensi ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;

        const inputHapus = document.createElement('input');
        inputHapus.type = 'hidden';
        inputHapus.name = 'hapus';
        inputHapus.value = '1';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf_token';
        csrf.value = "<?= $_SESSION['csrf_token']; ?>";

        form.appendChild(inputId);
        form.appendChild(inputHapus);
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
      }
    }

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', () => {
      const term = searchInput.value.toLowerCase();
      document.querySelectorAll('#refTable tbody tr').forEach((row) => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
