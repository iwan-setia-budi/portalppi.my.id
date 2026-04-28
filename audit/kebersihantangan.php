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
  ORDER BY a.tanggal_audit DESC, a.id DESC
  LIMIT $limit OFFSET $offset
");

$namaBulanLengkap = [
  1 => 'Januari',
  2 => 'Februari',
  3 => 'Maret',
  4 => 'April',
  5 => 'Mei',
  6 => 'Juni',
  7 => 'Juli',
  8 => 'Agustus',
  9 => 'September',
  10 => 'Oktober',
  11 => 'November',
  12 => 'Desember'
];

$qPeriodeTerkini = mysqli_query($conn, "
  SELECT 
    MONTH(MAX(tanggal_audit)) AS bulan,
    YEAR(MAX(tanggal_audit)) AS tahun
  FROM audit_hand_hygiene
");

$periodeTerkini = mysqli_fetch_assoc($qPeriodeTerkini);

$bulanOpportunity = (int) ($periodeTerkini['bulan'] ?? date('n'));
$tahunOpportunity = (int) ($periodeTerkini['tahun'] ?? date('Y'));

$qOpportunityBulanBerjalan = mysqli_query($conn, "
  SELECT COUNT(d.id) AS total_opportunity
  FROM audit_hand_hygiene a
  JOIN audit_hand_hygiene_detail d ON a.id = d.audit_id
  WHERE MONTH(a.tanggal_audit) = '$bulanOpportunity'
    AND YEAR(a.tanggal_audit) = '$tahunOpportunity'
");

$opportunityBulanBerjalan = mysqli_fetch_assoc($qOpportunityBulanBerjalan);

$totalOpportunityBulanBerjalan = (int) ($opportunityBulanBerjalan['total_opportunity'] ?? 0);
$labelOpportunityBulanBerjalan = 'Opportunity ' . ($namaBulanLengkap[$bulanOpportunity] ?? '') . ' ' . $tahunOpportunity;

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
?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Audit Kebersihan Tangan | PPI PHBW</title>
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

    .hero-content,
    .hero-actions {
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

    .hero-actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 12px;
      flex-wrap: wrap;
    }

    .hero-stat {
      width: 190px;
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid rgba(148, 163, 184, 0.42);
      border-radius: 18px;
      padding: 14px 16px;
      box-shadow: 0 14px 30px rgba(15, 23, 42, 0.10);
      text-align: center;
    }

    .hero-stat strong {
      display: block;
      font-size: 32px;
      font-weight: 900;
      color: var(--primary-2);
      line-height: 1;
      margin-bottom: 6px;
    }

    .hero-stat span {
      display: block;
      font-size: 12px;
      color: rgba(51, 65, 85, 0.85);
      font-weight: 800;
      letter-spacing: 0.25px;
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
      border: 1.8px solid rgba(148, 163, 184, 0.65);
      border-radius: var(--radius-md);
      background: #ffffff;
      color: var(--ink);
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
      accent-color: var(--primary);
      flex-shrink: 0;
      margin: 0;
    }

    .required {
      color: #e11d48;
    }

    .intro-bar {
      background: linear-gradient(135deg, var(--primary), var(--accent));
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
      border: 1px solid rgba(148, 163, 184, 0.35);
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
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
      font-weight: 700;
      padding: 14px 12px;
      font-size: 14px;
      border: none;
    }

    .audit-table thead th:first-child {
      background: rgba(29, 78, 216, 0.92);
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
      accent-color: var(--primary);
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

    .btn:active {
      transform: translateY(0px);
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

    .filter-row+.filter-row {
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

    .table-scroll-x .summary-table {
      min-width: 0;
      width: 100%;
    }

    .data-table-wrap::after,
    .table-scroll-x::after {
      content: "Geser tabel ke samping";
      display: none;
      text-align: right;
      font-size: 12px;
      color: #64748b;
      padding: 8px 10px 10px;
      font-weight: 700;
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

    .data-table-wrap .summary-table thead th,
    .data-table-wrap .summary-table tbody td {
      white-space: nowrap;
    }

    .table-scroll-x .summary-table thead th,
    .table-scroll-x .summary-table tbody td {
      white-space: normal;
      word-break: break-word;
    }

    /* KHUSUS TAB DATA */
    #tab-data .summary-table th:nth-child(5),
    #tab-data .summary-table th:nth-child(6),
    #tab-data .summary-table th:nth-child(7),
    #tab-data .summary-table td:nth-child(5),
    #tab-data .summary-table td:nth-child(6),
    #tab-data .summary-table td:nth-child(7) {
      text-align: center;
    }

    /* tombol aksi biar di tengah */
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

      .hero-actions {
        width: 100%;
        justify-content: flex-start;
      }

      .hero-stat {
        width: 100%;
      }

      .hero-content h1 {
        font-size: 22px;
      }

      .hero-content .subtitle {
        font-size: 13px;
      }

      .section-card,
      .opportunity-card {
        padding: 14px;
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

      .filter-row+.filter-row {
        grid-template-columns: 1fr !important;
        align-items: stretch;
      }

      .data-table-wrap,
      .table-scroll-x,
      .table-responsive {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x pan-y;
      }

      .summary-table {
        min-width: 0;
      }

      .data-table-wrap .summary-table {
        min-width: 820px;
      }

      .data-table-wrap::after,
      .table-scroll-x::after {
        display: block;
      }

      .aksi-group {
        flex-direction: row;
        flex-wrap: nowrap;
        gap: 6px;
      }

      .aksi-group .btn {
        width: auto;
        padding: 7px 10px;
        font-size: 11px;
      }

      .filter-row .button-row {
        margin-top: 0;
      }

      .pagination-row {
        align-items: stretch;
      }

      .pagination-row>.button-row {
        margin-top: 8px !important;
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
            <div class="hero-badge">🧼 Audit PPI • Kebersihan Tangan</div>
            <h1>Audit Hand Hygiene RS Primaya Bhakti Wara</h1>
            <p class="subtitle">
              Kelola audit kebersihan tangan dengan rapi: input observasi 5 moment, rekap kepatuhan, dan grafik untuk
              evaluasi berkala.
            </p>
          </div>

          <div class="hero-actions">
            <div class="hero-stat">
              <strong><?= $totalOpportunityBulanBerjalan ?></strong>
              <span><?= htmlspecialchars($labelOpportunityBulanBerjalan) ?></span>
            </div>
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
  <script src="<?= asset('assets/js/utama.js') ?>"></script>

</body>

</html>