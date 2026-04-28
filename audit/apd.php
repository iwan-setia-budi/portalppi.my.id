<?php
require_once __DIR__ . '/../config/assets.php';
session_start();
include_once __DIR__ . '/../koneksi.php';
include __DIR__ . '/../cek_akses.php';
$conn = $koneksi;

$pageTitle = "AUDIT APD";

$profesiList = [
  "Dokter Spesialis",
  "Dokter Jaga",
  "Perawat/Bidan",
  "Analis",
  "Radiografer",
  "Fisioterapis",
  "P. Kebersihan",
  "P. Gizi",
  "P. Farmasi"
];

$ruanganList = [
  "UGD",
  "HD",
  "Poli",
  "OK",
  "VK",
  "ICU",
  "Perina",
  "St. Yosef",
  "St. Teresia",
  "St. Lukas",
  "St. Anna",
  "Radiologi",
  "Laboratorium",
  "Rehabilitasi Medik",
  "Farmasi",
  "Gizi/Dapur",
  "Cleaning Service"
];

$indikatorPenilaian = [
  "kesesuaian_apd_1" => "Kesesuaian APD",
  "kesegeraan_melepas_apd_1" => "Kesegeraan melepas APD",
  "urutan_pelepasan_apd_1" => "Urutan pelepasan APD",
  "fasilitas_apd_1" => "Terdapat fasilitas APD",
];

$apdDigunakan = [
  "topi_nurse_cap_1" => "Topi (Nurse Cap)",
  "masker_bedah_1" => "Masker Bedah",
  "masker_n95_1" => "Masker N95 (Setara)",
  "goggles_1" => "Goggles",
  "face_shield_1" => "Face Shield",
  "sarung_tangan_1" => "Sarung Tangan",
  "sarung_tangan_steril_1" => "Sarung Tangan Steril",
  "sarung_tangan_rumah_tangga_1" => "Sarung Tangan Rumah tangga",
  "apron_1" => "Apron",
  "gown_1" => "Gown",
  "sepatu_boot_1" => "Sepatu tertutup/boot",

];

$opsiJawaban = [
  "ya" => "Ya",
  "tidak" => "Tidak",
  "na" => "NA"
];

$message = '';
$activeTab = $_GET['tab'] ?? 'tab-form';

if (isset($_POST['simpan'])) {
  $tanggal_audit = $_POST['tanggal_audit'] ?? '';
  $nama_petugas = trim($_POST['nama_petugas'] ?? '');
  $profesi = $_POST['profesi'] ?? '';
  $ruangan = $_POST['ruangan'] ?? '';
  $tindakan = trim($_POST['tindakan'] ?? '');
  $keterangan = trim($_POST['keterangan'] ?? '');
  $penilaian = $_POST['penilaian'] ?? [];
  $apd = $_POST['apd'] ?? [];

  $foto = '';
  if (!empty($_FILES['foto']['name'])) {
    $uploadDir = __DIR__ . '/uploads_apd/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed, true)) {
      $foto = 'apd_' . date('YmdHis') . '_' . rand(1000, 9999) . '.' . $ext;
      move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $foto);
    }
  }

  if ($tanggal_audit && $nama_petugas && $profesi && $ruangan && $tindakan && (!empty($penilaian) || !empty($apd))) {
    mysqli_begin_transaction($conn);
    try {
      $stmt = mysqli_prepare($conn, "INSERT INTO audit_apd (tanggal_audit, nama_petugas, profesi, ruangan, tindakan, keterangan, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "sssssss", $tanggal_audit, $nama_petugas, $profesi, $ruangan, $tindakan, $keterangan, $foto);
      mysqli_stmt_execute($stmt);

      $audit_id = mysqli_insert_id($conn);
      $stmtDetail = mysqli_prepare($conn, "INSERT INTO audit_apd_detail (audit_id, kategori, indikator_key, indikator_label, jawaban) VALUES (?, ?, ?, ?, ?)");

      foreach ($penilaian as $key => $jawaban) {
        $kategori = 'indikator_penilaian';
        $label = $indikatorPenilaian[$key] ?? $key;
        mysqli_stmt_bind_param($stmtDetail, "issss", $audit_id, $kategori, $key, $label, $jawaban);
        mysqli_stmt_execute($stmtDetail);
      }

      foreach ($apd as $key => $jawaban) {
        $kategori = 'apd_digunakan';
        $label = $apdDigunakan[$key] ?? $key;
        mysqli_stmt_bind_param($stmtDetail, "issss", $audit_id, $kategori, $key, $label, $jawaban);
        mysqli_stmt_execute($stmtDetail);
      }

      mysqli_commit($conn);
      $message = '<div class="info-box" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">Data audit APD berhasil disimpan.</div>';
      $activeTab = 'tab-form';
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $message = '<div class="info-box" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">Gagal menyimpan data audit APD.</div>';
      $activeTab = 'tab-form';
    }
  } else {
    $message = '<div class="info-box" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">Lengkapi data wajib dan minimal satu indikator/APD.</div>';
    $activeTab = 'tab-form';
  }
}

$filter_tgl_awal = $_GET['tgl_awal'] ?? '';
$filter_tgl_akhir = $_GET['tgl_akhir'] ?? '';
$filter_profesi = $_GET['f_profesi'] ?? '';
$filter_ruangan = $_GET['f_ruangan'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';
$keyword_data = trim($_GET['keyword_data'] ?? '');

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereData = [];
if ($filter_bulan !== '') {
  $whereData[] = "MONTH(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_bulan) . "'";
}
if ($filter_tahun !== '') {
  $whereData[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
}
if ($filter_profesi !== '') {
  $whereData[] = "a.profesi = '" . mysqli_real_escape_string($conn, $filter_profesi) . "'";
}
if ($filter_ruangan !== '') {
  $whereData[] = "a.ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'";
}
if ($keyword_data !== '') {
  $keywordEsc = mysqli_real_escape_string($conn, $keyword_data);
  $whereData[] = "(
    a.nama_petugas LIKE '%$keywordEsc%' OR
    a.keterangan LIKE '%$keywordEsc%' OR
    a.profesi LIKE '%$keywordEsc%' OR
    a.ruangan LIKE '%$keywordEsc%' OR
    a.tindakan LIKE '%$keywordEsc%'
  )";
}
$whereDataSql = count($whereData) ? 'WHERE ' . implode(' AND ', $whereData) : '';

$qTotalData = mysqli_query($conn, "SELECT COUNT(*) as total FROM audit_apd a $whereDataSql");
$totalData = mysqli_fetch_assoc($qTotalData)['total'] ?? 0;
$totalPages = max(1, (int) ceil($totalData / $limit));

$qData = mysqli_query($conn, "
  SELECT
    a.*,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    SUM(CASE WHEN d.jawaban IN ('ya', 'tidak') THEN 1 ELSE 0 END) AS denum
  FROM audit_apd a
  LEFT JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereDataSql
  GROUP BY a.id
  ORDER BY a.tanggal_audit DESC, a.id DESC
  LIMIT $limit OFFSET $offset
");

$whereRekap = [];
if ($filter_tgl_awal !== '') {
  $whereRekap[] = "a.tanggal_audit >= '" . mysqli_real_escape_string($conn, $filter_tgl_awal) . "'";
}
if ($filter_tgl_akhir !== '') {
  $whereRekap[] = "a.tanggal_audit <= '" . mysqli_real_escape_string($conn, $filter_tgl_akhir) . "'";
}
if ($filter_profesi !== '') {
  $whereRekap[] = "a.profesi = '" . mysqli_real_escape_string($conn, $filter_profesi) . "'";
}
if ($filter_ruangan !== '') {
  $whereRekap[] = "a.ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'";
}
if ($filter_bulan !== '') {
  $whereRekap[] = "MONTH(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_bulan) . "'";
}
if ($filter_tahun !== '') {
  $whereRekap[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
}
$whereRekapSql = count($whereRekap) ? 'WHERE ' . implode(' AND ', $whereRekap) : '';

$qKepatuhanAPD = mysqli_query($conn, "
  SELECT
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS ya,
    SUM(CASE WHEN d.jawaban = 'tidak' THEN 1 ELSE 0 END) AS tidak,
    SUM(CASE WHEN d.jawaban = 'na' THEN 1 ELSE 0 END) AS na
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereRekapSql
");
$kepatuhanAPD = mysqli_fetch_assoc($qKepatuhanAPD) ?: ['ya' => 0, 'tidak' => 0, 'na' => 0];

$qRekapIndikator = mysqli_query($conn, "
  SELECT
    d.indikator_key,
    d.indikator_label,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS ya,
    SUM(CASE WHEN d.jawaban = 'tidak' THEN 1 ELSE 0 END) AS tidak,
    SUM(CASE WHEN d.jawaban = 'na' THEN 1 ELSE 0 END) AS na
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereRekapSql " . ($whereRekapSql ? "AND" : "WHERE") . " d.kategori = 'indikator_penilaian'
  GROUP BY d.indikator_key, d.indikator_label
  ORDER BY d.indikator_label ASC
");

$qRekapAPD = mysqli_query($conn, "
  SELECT
    d.indikator_key,
    d.indikator_label,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS ya,
    SUM(CASE WHEN d.jawaban = 'tidak' THEN 1 ELSE 0 END) AS tidak,
    SUM(CASE WHEN d.jawaban = 'na' THEN 1 ELSE 0 END) AS na
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereRekapSql " . ($whereRekapSql ? "AND" : "WHERE") . " d.kategori = 'apd_digunakan'
  GROUP BY d.indikator_key, d.indikator_label
  ORDER BY d.indikator_label ASC
");

$qRekapProfesi = mysqli_query($conn, "
  SELECT
    a.profesi AS label_rekap,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS ya,
    SUM(CASE WHEN d.jawaban = 'tidak' THEN 1 ELSE 0 END) AS tidak,
    SUM(CASE WHEN d.jawaban = 'na' THEN 1 ELSE 0 END) AS na
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereRekapSql
  GROUP BY a.profesi
  ORDER BY a.profesi ASC
");

$qRekapUnit = mysqli_query($conn, "
  SELECT
    a.ruangan AS label_rekap,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS ya,
    SUM(CASE WHEN d.jawaban = 'tidak' THEN 1 ELSE 0 END) AS tidak,
    SUM(CASE WHEN d.jawaban = 'na' THEN 1 ELSE 0 END) AS na
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereRekapSql
  GROUP BY a.ruangan
  ORDER BY a.ruangan ASC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Audit APD | PPI PHBW</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    :root {
      --bg: #eef3f7;
      --card: #ffffff;
      --ink: #0f172a;
      --muted: #64748b;
      --line: rgba(148, 163, 184, 0.35);
      --primary: #1e40af;
      --primary-2: #1e3a8a;
      --accent: #075985;
      --ring: rgba(30, 64, 175, 0.18);
      --radius-lg: 20px;
      --radius-md: 16px;
      --shadow-lg: 0 18px 45px rgba(15, 23, 42, 0.12);
      --shadow-md: 0 10px 26px rgba(15, 23, 42, 0.08);
    }

    .audit-page {
      background: radial-gradient(900px 420px at 18% -10%, rgba(37, 99, 235, 0.18), transparent 62%),
        radial-gradient(700px 380px at 92% 0%, rgba(14, 165, 233, 0.16), transparent 60%),
        var(--bg);
      min-height: 100vh;
      color: var(--ink);
    }

    .layout,
    .layout main {
      min-width: 0;
    }

    main {
      width: 100%;
    }

    .audit-wrapper {
      width: 100%;
      max-width: none;
      margin: 22px auto 44px;
      padding: 0 16px;
      box-sizing: border-box;
    }

    .hero-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      background: linear-gradient(135deg, rgba(30, 64, 175, 0.14), rgba(7, 89, 133, 0.10)),
        linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.86));
      border-radius: var(--radius-lg);
      padding: 22px 22px;
      margin-bottom: 14px;
      border: 1px solid rgba(148, 163, 184, 0.42);
      box-shadow: var(--shadow-lg);
      position: relative;
      overflow: hidden;
    }

    .hero-header::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(560px 260px at 30% 20%, rgba(30, 64, 175, 0.22), transparent 62%),
        radial-gradient(520px 280px at 90% 10%, rgba(7, 89, 133, 0.20), transparent 60%);
      pointer-events: none;
      opacity: 0.85;
    }

    .hero-content {
      position: relative;
      z-index: 1;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(148, 163, 184, 0.42);
      color: rgba(30, 41, 59, 0.95);
      font-weight: 800;
      font-size: 12px;
      letter-spacing: 0.2px;
      box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
      margin-bottom: 10px;
    }

    .hero-content h1 {
      margin: 0;
      font-size: 28px;
      font-weight: 900;
      letter-spacing: -0.4px;
      line-height: 1.15;
      color: var(--ink);
    }

    .hero-content .subtitle {
      margin: 10px 0 0;
      color: rgba(51, 65, 85, 0.92);
      font-size: 14px;
      line-height: 1.6;
      font-weight: 600;
      max-width: none;
    }

    .section-card {
      background: linear-gradient(180deg, #ffffff 0%, rgba(255, 255, 255, 0.92) 100%);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      padding: 28px;
      margin-bottom: 22px;
      border: 1px solid rgba(148, 163, 184, 0.35);
    }

    .section-title {
      font-size: 20px;
      font-weight: 800;
      margin-bottom: 20px;
      color: var(--ink);
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 22px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 10px;
      min-width: 0;
    }

    .form-group.full {
      grid-column: 1 / -1;
    }

    .form-label {
      font-weight: 800;
      color: var(--ink);
      font-size: 15px;
      line-height: 1.4;
    }

    .form-control,
    .form-textarea {
      width: 100%;
      min-height: 52px;
      border: 1.5px solid rgba(148, 163, 184, 0.60);
      border-radius: var(--radius-md);
      padding: 14px 16px;
      font-size: 16px;
      font-weight: 500;
      color: var(--ink);
      outline: none;
      background: #ffffff;
      transition: all 0.2s ease;
      box-sizing: border-box;
      box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .form-control:focus,
    .form-textarea:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px var(--ring), 0 12px 26px rgba(37, 99, 235, 0.10);
    }

    .required {
      color: #e11d48;
    }

    .button-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 18px;
      align-items: center;
    }

    .pagination-row {
      justify-content: space-between;
      align-items: center;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 18px;
      border-radius: 14px;
      border: 1px solid transparent;
      cursor: pointer;
      font-weight: 700;
      text-decoration: none;
      transition: 0.2s ease;
      min-height: 44px;
      gap: 10px;
      box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
      font-size: 14px;
      line-height: 1.2;
      white-space: nowrap;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.92);
      border-color: rgba(148, 163, 184, 0.55);
      color: var(--ink);
      box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
    }

    .btn-warning {
      background: linear-gradient(135deg, #d97706, #b45309);
      color: #fff;
    }

    .btn-danger {
      background: linear-gradient(135deg, #dc2626, #b91c1c);
      color: #fff;
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
    }

    .small-note {
      color: #6b7280;
      font-size: 13px;
      margin-top: 8px;
      line-height: 1.5;
    }

    .tab-menu {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 18px;
    }

    .tab-btn {
      border: 1px solid rgba(148, 163, 184, 0.55);
      background: rgba(255, 255, 255, 0.92);
      color: var(--ink);
      padding: 12px 18px;
      border-radius: 999px;
      font-weight: 800;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
      transition: 0.2s ease;
    }

    .tab-btn.active {
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
      border-color: transparent;
    }

    .tab-pane {
      display: none;
    }

    .tab-pane.active {
      display: block;
    }

    .info-box {
      background: #f8fafc;
      border: 1px solid rgba(148, 163, 184, 0.40);
      border-radius: 16px;
      padding: 16px;
      margin-bottom: 18px;
    }

    .filter-row {
      display: grid;
      grid-template-columns: 1.4fr 0.7fr 0.7fr 0.9fr;
      gap: 14px;
      margin-top: 14px;
    }

    .filter-row + .filter-row {
      grid-template-columns: 1fr auto;
      align-items: center;
    }

    .data-table-wrap {
      display: block;
      width: 100%;
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
      touch-action: pan-x;
      border-radius: 14px;
      border: 1px solid rgba(148, 163, 184, 0.35);
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
      background: #fff;
    }

    .table-scroll-x {
      display: block;
      width: 100%;
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
      touch-action: pan-x;
      border-radius: 14px;
      border: 1px solid rgba(148, 163, 184, 0.30);
      background: #fff;
      box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    .summary-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      min-width: 860px;
    }

    .summary-table thead th {
      background: linear-gradient(135deg, rgba(30, 64, 175, 0.95), rgba(30, 58, 138, 0.95));
      color: #fff;
      font-weight: 800;
      padding: 14px 14px;
      font-size: 13px;
      letter-spacing: 0.2px;
      border: none;
      text-align: left;
      white-space: nowrap;
    }

    .summary-table tbody td {
      padding: 14px 14px;
      border-bottom: 1px solid rgba(148, 163, 184, 0.30);
      font-size: 14px;
      color: rgba(15, 23, 42, 0.92);
      vertical-align: middle;
      background: rgba(255, 255, 255, 0.92);
      white-space: normal;
    }

    .summary-table tbody tr:nth-child(odd) td {
      background: rgba(248, 250, 252, 0.92);
    }

    .summary-table tbody tr:hover td {
      background: rgba(219, 234, 254, 0.55);
    }

    .aksi-group {
      display: flex;
      gap: 10px;
      flex-wrap: nowrap;
      align-items: center;
    }

    .aksi-group .btn {
      min-height: 34px;
      padding: 8px 12px;
      font-size: 12px;
      border-radius: 10px;
      box-shadow: 0 6px 12px rgba(15, 23, 42, 0.08);
      flex: 0 0 auto;
    }

    #tab-data .summary-table th:nth-child(6),
    #tab-data .summary-table th:nth-child(7),
    #tab-data .summary-table th:nth-child(8),
    #tab-data .summary-table td:nth-child(6),
    #tab-data .summary-table td:nth-child(7),
    #tab-data .summary-table td:nth-child(8) {
      text-align: center;
    }

    #tab-data .aksi-group {
      justify-content: center;
    }

    .chart-box {
      position: relative;
      height: 340px;
      width: 100%;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(148, 163, 184, 0.35);
      border-radius: 18px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
      padding: 14px;
    }

    @media (max-width: 768px) {
      .audit-wrapper {
        padding: 0 8px;
        margin: 16px auto 28px;
      }

      .hero-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 18px 16px;
      }

      .hero-content h1 {
        font-size: 22px;
      }

      .hero-content .subtitle {
        font-size: 13px;
      }

      .section-card {
        padding: 14px;
        border-radius: 12px;
      }

      .form-grid {
        grid-template-columns: 1fr;
        gap: 14px;
      }

      .section-title {
        font-size: 16px;
      }

      .button-row {
        flex-direction: column;
        align-items: stretch;
      }

      .button-row .btn {
        width: 100%;
      }

      .tab-menu {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 4px;
      }

      .tab-btn {
        flex: 0 0 auto;
        white-space: nowrap;
      }

      .filter-row {
        grid-template-columns: 1fr !important;
        gap: 10px;
      }

      .filter-row + .filter-row {
        grid-template-columns: 1fr !important;
        align-items: stretch;
      }
    }
  </style>
</head>

<body class="audit-page">
  <div class="layout">
    <?php include_once '../sidebar.php'; ?>
    <main>
      <?php include_once '../topbar.php'; ?>
      <div class="audit-wrapper">
        <section class="hero-header">
          <div class="hero-content">
            <div class="hero-badge">🛡️ Audit PPI • APD</div>
            <h1>Audit Penggunaan APD RS Primaya Bhakti Wara</h1>
            <p class="subtitle">Kelola audit APD dengan rapi: input observasi, data audit, rekap kepatuhan, dan grafik untuk evaluasi berkala.</p>
          </div>
        </section>

        <?= $message ?>

        <div class="tab-menu">
          <a href="?tab=tab-form" class="tab-btn <?= $activeTab === 'tab-form' ? 'active' : '' ?>">Form</a>
          <a href="?tab=tab-data" class="tab-btn <?= $activeTab === 'tab-data' ? 'active' : '' ?>">Data</a>
          <a href="?tab=tab-rekap" class="tab-btn <?= $activeTab === 'tab-rekap' ? 'active' : '' ?>">Rekap</a>
          <a href="?tab=tab-grafik" class="tab-btn <?= $activeTab === 'tab-grafik' ? 'active' : '' ?>">Grafik</a>
        </div>

        <?php
        switch ($activeTab) {
          case 'tab-form':
            include __DIR__ . '/tabs_apd/tab_form_audit.php';
            break;
          case 'tab-data':
            include __DIR__ . '/tabs_apd/tab_data_audit.php';
            break;
          case 'tab-rekap':
            include __DIR__ . '/tabs_apd/tab_rekap_audit.php';
            break;
          case 'tab-grafik':
            include __DIR__ . '/tabs_apd/tab_grafik_audit.php';
            break;
          default:
            include __DIR__ . '/tabs_apd/tab_form_audit.php';
            break;
        }
        ?>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>

</html>
