<?php
require_once __DIR__ . '/../config/assets.php';
session_start();
include_once __DIR__ . '/../koneksi.php';
include __DIR__ . '/../cek_akses.php';
$conn = $koneksi;

$pageTitle = "AUDIT KEBERSIHAN TANGAN";

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

$moments = [
  "m1" => "M1 - Sebelum Kontak Pasien",
  "m2" => "M2 - Sebelum Tindakan Aseptik",
  "m3" => "M3 - Setelah Kontak Cairan Tubuh",
  "m4" => "M4 - Setelah Kontak Pasien",
  "m5" => "M5 - Setelah lingkungan sekitar pasien"
];

$opsiCuciTangan = [
  "alkohol_6l" => "Alkohol + 6L",
  "alkohol_biasa" => "Alkohol Biasa",
  "sabun_6l" => "Sabun + 6L",
  "sabun_biasa" => "Sabun Biasa",
  "missed" => "MISSED / tidak melakukan"
];

$message = '';
$activeTab = $_GET['tab'] ?? 'tab-form';

if (isset($_POST['simpan'])) {
  $tanggal_audit = $_POST['tanggal_audit'] ?? '';
  $nama_petugas = trim($_POST['nama_petugas'] ?? '');
  $profesi = $_POST['profesi'] ?? '';
  $ruangan = $_POST['ruangan'] ?? '';
  $keterangan = trim($_POST['keterangan'] ?? '');
  $observasi = $_POST['observasi'] ?? [];

  if ($tanggal_audit && $nama_petugas && $profesi && $ruangan && !empty($observasi)) {
    mysqli_begin_transaction($conn);

    try {
      $stmt = mysqli_prepare($conn, "INSERT INTO audit_hand_hygiene (tanggal_audit, nama_petugas, profesi, ruangan, keterangan) VALUES (?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "sssss", $tanggal_audit, $nama_petugas, $profesi, $ruangan, $keterangan);
      mysqli_stmt_execute($stmt);

      $audit_id = mysqli_insert_id($conn);

      $stmtDetail = mysqli_prepare($conn, "INSERT INTO audit_hand_hygiene_detail (audit_id, opportunity_ke, moment_key, hasil_observasi) VALUES (?, ?, ?, ?)");

      foreach ($observasi as $oppKe => $momentsInput) {
        foreach ($momentsInput as $momentKey => $hasil) {
          mysqli_stmt_bind_param($stmtDetail, "iiss", $audit_id, $oppKe, $momentKey, $hasil);
          mysqli_stmt_execute($stmtDetail);
        }
      }

      mysqli_commit($conn);
      $message = '<div class="info-box" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">Data audit berhasil disimpan.</div>';
      $activeTab = 'tab-form';
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $message = '<div class="info-box" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">Gagal menyimpan data audit.</div>';
      $activeTab = 'tab-form';
    }
  } else {
    $message = '<div class="info-box" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">Lengkapi semua data wajib.</div>';
    $activeTab = 'tab-form';
  }
}
?>

<?php
$filter_tgl_awal = $_GET['tgl_awal'] ?? '';
$filter_tgl_akhir = $_GET['tgl_akhir'] ?? '';
$filter_profesi = $_GET['f_profesi'] ?? '';
$filter_ruangan = $_GET['f_ruangan'] ?? '';
$filter_moment = $_GET['f_moment'] ?? '';

$filter_bulan = $_GET['bulan'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';
$keyword_data = trim($_GET['keyword_data'] ?? '');

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

/* =========================
   QUERY KHUSUS TAB DATA
========================= */
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
    a.ruangan LIKE '%$keywordEsc%'
  )";
}

$whereDataSql = count($whereData) ? 'WHERE ' . implode(' AND ', $whereData) : '';

$qTotalData = mysqli_query($conn, "
  SELECT COUNT(*) as total
  FROM audit_hand_hygiene a
  $whereDataSql
");
$totalData = mysqli_fetch_assoc($qTotalData)['total'] ?? 0;
$totalPages = max(1, ceil($totalData / $limit));

$qData = mysqli_query($conn, "
  SELECT 
    a.*,
    SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) AS num,
    COUNT(d.id) AS denum
  FROM audit_hand_hygiene a
  LEFT JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereDataSql
  GROUP BY a.id
  ORDER BY a.id DESC
  LIMIT $limit OFFSET $offset
");

/* =========================
   QUERY UMUM REKAP/GRAFIK
========================= */
$where = [];

if ($filter_tgl_awal !== '') {
  $where[] = "a.tanggal_audit >= '" . mysqli_real_escape_string($conn, $filter_tgl_awal) . "'";
}
if ($filter_tgl_akhir !== '') {
  $where[] = "a.tanggal_audit <= '" . mysqli_real_escape_string($conn, $filter_tgl_akhir) . "'";
}
if ($filter_profesi !== '') {
  $where[] = "a.profesi = '" . mysqli_real_escape_string($conn, $filter_profesi) . "'";
}
if ($filter_ruangan !== '') {
  $where[] = "a.ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'";
}
if ($filter_bulan !== '') {
  $where[] = "MONTH(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_bulan) . "'";
}
if ($filter_tahun !== '') {
  $where[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
}
if ($filter_moment !== '') {
  $where[] = "d.moment_key = '" . mysqli_real_escape_string($conn, $filter_moment) . "'";
}

$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
?>

<!-- REKAPITULASI AUDIT KEBERSIHAN TANGAN -->
 <?php
/* =========================
   QUERY REKAP
========================= */

$qRekapProfesi = mysqli_query($conn, "
  SELECT 
    a.profesi AS label_rekap,
    SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum,
    ROUND(
      (SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
      2
    ) AS persen
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSql
  GROUP BY a.profesi
  ORDER BY a.profesi ASC
");

$qRekapUnit = mysqli_query($conn, "
  SELECT 
    a.ruangan AS label_rekap,
    SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum,
    ROUND(
      (SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
      2
    ) AS persen
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSql
  GROUP BY a.ruangan
  ORDER BY a.ruangan ASC
");

$qRekapMoment = mysqli_query($conn, "
  SELECT 
    d.moment_key AS label_rekap,
    SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum,
    ROUND(
      (SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
      2
    ) AS persen
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSql
  GROUP BY d.moment_key
  ORDER BY d.moment_key ASC
");

$qKepatuhanRS = mysqli_query($conn, "
  SELECT 
    SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum,
    ROUND(
      (SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
      2
    ) AS persen
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSql
");

$kepatuhanRS = mysqli_fetch_assoc($qKepatuhanRS);

/* =========================
   REKAP ANTISEPTIK & CARA HH
========================= */

$whereSqlFinal = $whereSql
  ? $whereSql . " AND d.hasil_observasi <> 'missed'"
  : "WHERE d.hasil_observasi <> 'missed'";

$qRekapAntiseptik = mysqli_query($conn, "
  SELECT 
    CASE
      WHEN d.hasil_observasi IN ('alkohol_6l', 'alkohol_biasa') THEN 'Alkohol'
      WHEN d.hasil_observasi IN ('sabun_6l', 'sabun_biasa') THEN 'Sabun'
    END AS label_rekap,
    COUNT(*) AS num
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSqlFinal
  GROUP BY label_rekap
  ORDER BY label_rekap ASC
");

$rekapAntiseptikRows = [];
$denumAntiseptik = 0;

while ($row = mysqli_fetch_assoc($qRekapAntiseptik)) {
  $row['num'] = (int) $row['num'];
  $denumAntiseptik += $row['num'];
  $rekapAntiseptikRows[] = $row;
}

$qRekapCaraHH = mysqli_query($conn, "
  SELECT 
    CASE
      WHEN d.hasil_observasi IN ('alkohol_6l', 'sabun_6l') THEN '6 Langkah'
      WHEN d.hasil_observasi IN ('alkohol_biasa', 'sabun_biasa') THEN 'Biasa'
    END AS label_rekap,
    COUNT(*) AS num
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSqlFinal
  GROUP BY label_rekap
  ORDER BY label_rekap ASC
");

$rekapCaraHHRows = [];
$denumCaraHH = 0;

while ($row = mysqli_fetch_assoc($qRekapCaraHH)) {
  $row['num'] = (int) $row['num'];
  $denumCaraHH += $row['num'];
  $rekapCaraHHRows[] = $row;
}

/* =========================
   DATA GRAFIK
========================= */

$grafikProfesiLabel = [];
$grafikProfesiValue = [];

$qGrafikProfesi = mysqli_query($conn, "
  SELECT 
    a.profesi,
    ROUND(
      (SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
      2
    ) AS persen
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSql
  GROUP BY a.profesi
  ORDER BY a.profesi ASC
");

while ($row = mysqli_fetch_assoc($qGrafikProfesi)) {
  $grafikProfesiLabel[] = $row['profesi'];
  $grafikProfesiValue[] = (float) $row['persen'];
}

$grafikUnitLabel = [];
$grafikUnitValue = [];

$qGrafikUnit = mysqli_query($conn, "
  SELECT 
    a.ruangan,
    ROUND(
      (SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
      2
    ) AS persen
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSql
  GROUP BY a.ruangan
  ORDER BY a.ruangan ASC
");

while ($row = mysqli_fetch_assoc($qGrafikUnit)) {
  $grafikUnitLabel[] = $row['ruangan'];
  $grafikUnitValue[] = (float) $row['persen'];
}

$grafikMomentLabel = [];
$grafikMomentValue = [];

$qGrafikMoment = mysqli_query($conn, "
  SELECT 
    d.moment_key,
    ROUND(
      (SUM(CASE WHEN d.hasil_observasi <> 'missed' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
      2
    ) AS persen
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  $whereSql
  GROUP BY d.moment_key
  ORDER BY d.moment_key ASC
");

while ($row = mysqli_fetch_assoc($qGrafikMoment)) {
  $grafikMomentLabel[] = strtoupper($row['moment_key']);
  $grafikMomentValue[] = (float) $row['persen'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Audit Kebersihan Tangan | PPI PHBW</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

  <style>
    .audit-page {
      background: #eef3f7;
      min-height: 100vh;
    }

    .audit-wrapper {
      width: 100%;
      margin: 24px auto 40px;
      padding: 0 16px;
      box-sizing: border-box;
    }

    .audit-card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
      overflow: hidden;
      margin-bottom: 18px;
      border: 1px solid #e5e7eb;
    }

    .audit-header-banner {
      background: linear-gradient(135deg, #0a6988, #0f7ea4);
      color: #fff;
      padding: 24px 20px;
      text-align: center;
    }

    .audit-header-banner h1 {
      margin: 0;
      font-size: 40px;
      font-weight: 800;
      letter-spacing: 4px;
      line-height: 1;
    }

    .audit-header-banner h2 {
      margin: 10px 0 0;
      font-size: 19px;
      letter-spacing: 8px;
      font-weight: 700;
    }

    .audit-header-banner h3 {
      margin: 10px 0 0;
      font-size: 24px;
      font-weight: 800;
      letter-spacing: 2px;
    }

    .audit-title-box {
      padding: 24px 28px;
      border-top: 5px solid #0f7ea4;
    }

    .audit-title-box h4 {
      margin: 0 0 10px;
      font-size: 34px;
      font-weight: 800;
      color: #1f2937;
      line-height: 1.25;
    }

    .audit-title-box p {
      margin: 0;
      color: #4b5563;
      line-height: 1.6;
      font-size: 15px;
    }

    .section-card {
      background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
      border-radius: 18px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06), 0 2px 8px rgba(15, 23, 42, 0.04);
      padding: 28px;
      margin-bottom: 22px;
      border: 1px solid #d9e6ee;
    }

    .section-title {
      font-size: 20px;
      font-weight: 800;
      margin-bottom: 20px;
      color: #0f172a;
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
      color: #0f172a;
      font-size: 15px;
      line-height: 1.4;
    }

    .form-control,
    .form-textarea {
      width: 100%;
      min-height: 52px;
      border: 1.5px solid #bfd0dc;
      border-radius: 16px;
      padding: 14px 16px;
      font-size: 16px;
      font-weight: 500;
      color: #0f172a;
      outline: none;
      background: #ffffff;
      transition: all 0.2s ease;
      box-sizing: border-box;
      box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .form-control:focus,
    .form-textarea:focus {
      border-color: #1591b8;
      box-shadow: 0 0 0 4px rgba(21, 145, 184, 0.12), 0 8px 20px rgba(21, 145, 184, 0.08);
    }

    .radio-list {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 14px 18px;
    }

    .radio-item {
      display: flex;
      align-items: center;
      gap: 12px;
      min-height: 50px;
      padding: 12px 14px;
      border: 1.8px solid #b6c7d2;
      border-radius: 16px;
      background: #ffffff;
      color: #0f172a;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
    }

    .radio-item:hover {
      border-color: #8fc9db;
      background: #f7fcfe;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(15, 126, 164, 0.08);
    }

    .radio-item input[type="radio"] {
      width: 19px;
      height: 19px;
      accent-color: #0f7ea4;
      flex-shrink: 0;
      margin: 0;
    }

    .required {
      color: #e11d48;
    }

    .intro-bar {
      background: #0f7ea4;
      color: #fff;
      padding: 14px 18px;
      font-weight: 700;
      border-radius: 12px;
      margin-bottom: 10px;
      line-height: 1.4;
    }

    .intro-text {
      background: #f8fafc;
      padding: 16px 18px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      color: #374151;
      line-height: 1.6;
      font-size: 14px;
    }

    .subheading-box {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      padding: 16px 18px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 18px;
      line-height: 1.5;
    }

    .opportunity-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 18px;
      margin-bottom: 18px;
      overflow: hidden;
    }

    .opportunity-title {
      font-weight: 800;
      font-size: 16px;
      color: #111827;
      margin-bottom: 14px;
      text-transform: uppercase;
      line-height: 1.4;
    }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      border-radius: 10px;
    }

    .table-responsive::after {
      content: "Geser tabel";
      display: block;
      text-align: right;
      font-size: 12px;
      color: #9ca3af;
      margin-top: 4px;
    }

    .audit-table {
      width: 100%;
      min-width: 760px;
      border-collapse: separate;
      border-spacing: 0;
      overflow: hidden;
      border-radius: 12px;
    }

    .audit-table thead th {
      background: linear-gradient(135deg, #0f7ea4, #0a6988);
      color: #fff;
      font-weight: 700;
      padding: 14px 12px;
      font-size: 14px;
      border: none;
    }

    .audit-table thead th:first-child {
      background: #0c6c8d;
    }

    .audit-table tbody td {
      padding: 14px 12px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 14px;
      transition: 0.2s ease;
      cursor: pointer;
    }

    .audit-table tbody tr:nth-child(odd) {
      background: #f9fbfc;
    }

    .audit-table tbody tr:nth-child(even) {
      background: #ffffff;
    }

    .audit-table tbody tr:hover {
      background: #e6f4f9;
    }

    .audit-table td:first-child {
      font-weight: 600;
      color: #1f2937;
      text-align: left;
      width: 240px;
      min-width: 240px;
    }

    .audit-table td {
      text-align: center;
      vertical-align: middle;
      position: relative;
    }

    .audit-table input[type="radio"] {
      width: 20px;
      height: 20px;
      accent-color: #0f7ea4;
    }

    .mobile-card {
      display: none;
    }

    .mobile-opportunity {
      background: #fff;
      border: 1px solid #dbe7ee;
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 18px;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    .mobile-opportunity-title {
      padding: 14px 16px;
      font-size: 15px;
      font-weight: 800;
      text-transform: uppercase;
      color: #1f2937;
      border-bottom: 1px solid #e5e7eb;
      background: #ffffff;
    }

    .mobile-moment-card {
      padding: 14px 16px;
      border-bottom: 1px solid #eef2f7;
      background: #fff;
    }

    .mobile-moment-card:last-child {
      border-bottom: none;
    }

    .mobile-moment-title {
      font-size: 14px;
      font-weight: 700;
      color: #1f2937;
      line-height: 1.45;
      margin-bottom: 12px;
    }

    .mobile-option-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .mobile-option {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      background: #fff;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .mobile-option input[type="radio"] {
      width: 18px;
      height: 18px;
      accent-color: #0f7ea4;
      flex-shrink: 0;
    }

    .mobile-option span {
      font-size: 13px;
      color: #374151;
      line-height: 1.4;
    }

    .button-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 18px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 20px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      text-decoration: none;
      transition: 0.2s ease;
      min-height: 44px;
    }

    .btn-primary {
      background: #0f7ea4;
      color: #fff;
    }

    .btn-secondary {
      background: #e5e7eb;
      color: #111827;
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
      border: none;
      background: #e5e7eb;
      color: #111827;
      padding: 12px 18px;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
    }

    .tab-btn.active {
      background: #0f7ea4;
      color: #fff;
    }

    .tab-pane {
      display: none;
    }

    .tab-pane.active {
      display: block;
    }

    .info-box {
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 18px;
    }

    @media (max-width: 768px) {
      .audit-wrapper {
        padding: 0 12px;
        margin: 16px auto 28px;
      }

      .audit-header-banner {
        padding: 20px 14px;
      }

      .audit-header-banner h1 {
        font-size: 28px;
        letter-spacing: 2px;
      }

      .audit-header-banner h2 {
        font-size: 13px;
        letter-spacing: 4px;
      }

      .audit-header-banner h3 {
        font-size: 18px;
        letter-spacing: 1px;
      }

      .audit-title-box {
        padding: 18px 16px;
      }

      .audit-title-box h4 {
        font-size: 22px;
      }

      .audit-title-box p {
        font-size: 14px;
      }

      .section-card,
      .opportunity-card {
        padding: 16px;
        border-radius: 12px;
      }

      .form-grid {
        grid-template-columns: 1fr;
        gap: 14px;
      }

      .radio-list {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .section-title {
        font-size: 16px;
      }

      .intro-bar,
      .intro-text,
      .subheading-box {
        padding: 14px;
        font-size: 14px;
      }

      .opportunity-card .audit-table {
        display: none;
      }

      .mobile-card {
        display: block;
      }

      .opportunity-card {
        display: none;
      }

      .button-row {
        flex-direction: column;
      }

      .button-row .btn {
        width: 100%;
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
        <div class="audit-card">
          <div class="audit-header-banner">
            <h1>PRIMAYA</h1>
            <h2>HOSPITAL</h2>
            <h3>BHAKTI WARA</h3>
          </div>

          <div class="audit-title-box">
            <h4>AUDIT HAND HYGIENE RS PRIMAYA BHAKTIWARA</h4>
            <p>
              Sebagai bahan evaluasi rumah sakit terhadap kepatuhan cuci tangan bagi petugas,
              SOP dan juga fasilitas yang tersedia.
            </p>
          </div>
        </div>

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
            include __DIR__ . '/tabs_kebersihantangan/tab_form_audit.php';
            break;

          case 'tab-data':
            include __DIR__ . '/tabs_kebersihantangan/tab_data_audit.php';
            break;

          case 'tab-rekap':
            include __DIR__ . '/tabs_kebersihantangan/tab_rekap_audit.php';
            break;

          case 'tab-grafik':
            include __DIR__ . '/tabs_kebersihantangan/tab_grafik_audit.php';
            break;

          default:
            include __DIR__ . '/tabs_kebersihantangan/tab_form_audit.php';
            break;
        }
        ?>
      </div>
    </main>
  </div>
</body>

</html>