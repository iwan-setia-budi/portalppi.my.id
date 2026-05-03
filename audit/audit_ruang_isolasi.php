<?php
require_once __DIR__ . '/../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__ . '/../koneksi.php';
include __DIR__ . '/../cek_akses.php';
$conn = $koneksi;

$pageTitle = "AUDIT RUANG ISOLASI";
$activeTab = $_GET['tab'] ?? 'tab-form';
$message = '';
if (!empty($_SESSION['flash_audit_ruang_isolasi_ok'])) {
  unset($_SESSION['flash_audit_ruang_isolasi_ok']);
  $message = '<div class="info-box success">Data audit ruang isolasi berhasil disimpan.</div>';
}

$opsiJawaban = [
  'ya' => 'Ya',
  'tidak' => 'Tidak',
  'na' => 'NA'
];

$ruanganDiauditOptions = require __DIR__ . '/inc_ruangan_kewaspadaan_transmisi.php';

$checklistSections = [
  'K00' => [
    'title' => 'Checklist Ruang Isolasi',
    'items' => [
      'Tersedia poster penandaan di depan pintu',
      'Terdapat edukasi Pasien dan keluarga',
      'Tersedia APD diruang isolasi',
      'Tersedia Fasilitas kebersihan tangan',
      'Suhu ruangan sesuai',
      'Ada bukti dokumentasi untuk suhu dan tekanan',
      'Pembersihan ruangan dilakukan sesuai standar',
      'Tekanan ruangan sesuai',
      'Pintu kamar selalu tertutup (transmisi airborne)',
    ]
  ],
];

if (isset($_POST['simpan'])) {
  $tanggalAudit = $_POST['tanggal_audit'] ?? '';
  $ruanganDiaudit = $_POST['ruangan_diaudit'] ?? '';
  $catatanAudit = trim($_POST['catatan_audit'] ?? '');
  $namaPetugasUnit = trim($_POST['nama_petugas_unit'] ?? '');
  $tandaTanganPetugas = '';
  $signatureData = $_POST['signature_data'] ?? '';
  $jawaban = $_POST['jawaban'] ?? [];

  if (!$tanggalAudit || !$namaPetugasUnit || $ruanganDiaudit === '' || !in_array($ruanganDiaudit, $ruanganDiauditOptions, true)) {
    $message = '<div class="info-box error">Lengkapi tanggal audit, ruangan yang diaudit, dan nama petugas unit.</div>';
  } else {
    mysqli_begin_transaction($conn);
    try {
      if (!preg_match('/^data:image\/png;base64,/', $signatureData)) {
        throw new RuntimeException('Tanda tangan belum diisi.');
      }

      $uploadDir = __DIR__ . '/../uploads/audit_ruang_isolasi/';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      $signatureBase64 = substr($signatureData, strpos($signatureData, ',') + 1);
      $signatureBinary = base64_decode(str_replace(' ', '+', $signatureBase64), true);
      if ($signatureBinary === false || strlen($signatureBinary) === 0) {
        throw new RuntimeException('Format tanda tangan tidak valid.');
      }

      $signatureFileName = 'ttd_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
      $signaturePathAbs = $uploadDir . $signatureFileName;
      if (file_put_contents($signaturePathAbs, $signatureBinary) === false) {
        throw new RuntimeException('Gagal menyimpan tanda tangan.');
      }

      $tandaTanganPetugas = 'uploads/audit_ruang_isolasi/' . $signatureFileName;

      $stmt = mysqli_prepare($conn, "INSERT INTO audit_ruang_isolasi (tanggal_audit, ruangan_diaudit, catatan_audit, nama_petugas_unit, tanda_tangan_petugas) VALUES (?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "sssss", $tanggalAudit, $ruanganDiaudit, $catatanAudit, $namaPetugasUnit, $tandaTanganPetugas);
      if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException(mysqli_stmt_error($stmt) ?: 'Gagal menyimpan header audit.');
      }
      $auditId = (int) mysqli_insert_id($conn);
      if ($auditId <= 0) {
        throw new RuntimeException('ID audit tidak valid setelah simpan header.');
      }

      $stmtDetail = mysqli_prepare($conn, "INSERT INTO detail_audit_ruang_isolasi (audit_id, kode_bagian, urutan_item, item_text, jawaban) VALUES (?, ?, ?, ?, ?)");
      foreach ($checklistSections as $kode => $section) {
        foreach ($section['items'] as $idx => $item) {
          $urutan = $idx + 1;
          $jawab = $jawaban[$kode][$urutan] ?? 'na';
          if (!isset($opsiJawaban[$jawab])) {
            $jawab = 'na';
          }
          mysqli_stmt_bind_param($stmtDetail, "isiss", $auditId, $kode, $urutan, $item, $jawab);
          if (!mysqli_stmt_execute($stmtDetail)) {
            throw new RuntimeException(mysqli_stmt_error($stmtDetail) ?: 'Gagal menyimpan baris checklist.');
          }
        }
      }

      if (!empty($_FILES['dokumentasi_foto']['name'][0])) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $maxFiles = 5;
        $maxSize = 10 * 1024 * 1024;

        $stmtFoto = mysqli_prepare($conn, "INSERT INTO audit_ruang_isolasi_foto (audit_id, nama_file, path_file, ukuran_file) VALUES (?, ?, ?, ?)");
        $jumlahFile = min(count($_FILES['dokumentasi_foto']['name']), $maxFiles);

        for ($i = 0; $i < $jumlahFile; $i++) {
          if ($_FILES['dokumentasi_foto']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
          }
          $original = $_FILES['dokumentasi_foto']['name'][$i];
          $tmp = $_FILES['dokumentasi_foto']['tmp_name'][$i];
          $size = (int) $_FILES['dokumentasi_foto']['size'][$i];
          $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
          if (!in_array($ext, $allowedExt, true) || $size > $maxSize) {
            continue;
          }

          $newName = 'ri_' . $auditId . '_' . time() . '_' . $i . '.' . $ext;
          $target = $uploadDir . $newName;
          if (move_uploaded_file($tmp, $target)) {
            $relativePath = 'uploads/audit_ruang_isolasi/' . $newName;
            mysqli_stmt_bind_param($stmtFoto, "issi", $auditId, $original, $relativePath, $size);
            if (!mysqli_stmt_execute($stmtFoto)) {
              throw new RuntimeException(mysqli_stmt_error($stmtFoto) ?: 'Gagal menyimpan data foto dokumentasi.');
            }
          }
        }
      }

      mysqli_commit($conn);
      $_SESSION['flash_audit_ruang_isolasi_ok'] = true;
      header('Location: audit_ruang_isolasi.php?tab=tab-data');
      exit;
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $dbErr = mysqli_error($conn);
      $hint = trim($dbErr !== '' ? $dbErr : $e->getMessage());
      $message = '<div class="info-box error">Gagal menyimpan data audit ruang isolasi.'
        . ($hint !== '' ? ' <small style="display:block;margin-top:8px;font-weight:600;">' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '</small>' : '')
        . '</div>';
    }
  }
}

$keywordData = trim($_GET['keyword_data'] ?? '');
$filterBulan = $_GET['bulan'] ?? '';
$filterTahun = $_GET['tahun'] ?? '';
$filterRuangan = $_GET['ruangan'] ?? '';
$whereData = [];
if ($filterBulan !== '') {
  $whereData[] = "MONTH(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filterBulan) . "'";
}
if ($filterTahun !== '') {
  $whereData[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filterTahun) . "'";
}
if ($filterRuangan !== '' && in_array($filterRuangan, $ruanganDiauditOptions, true)) {
  $whereData[] = "a.ruangan_diaudit = '" . mysqli_real_escape_string($conn, $filterRuangan) . "'";
}
if ($keywordData !== '') {
  $keywordEsc = mysqli_real_escape_string($conn, $keywordData);
  $whereData[] = "(a.nama_petugas_unit LIKE '%$keywordEsc%' OR a.catatan_audit LIKE '%$keywordEsc%' OR a.ruangan_diaudit LIKE '%$keywordEsc%')";
}
$whereDataSql = count($whereData) ? 'WHERE ' . implode(' AND ', $whereData) : '';

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sortBy = $_GET['sort_by'] ?? 'tanggal';
$sortDir = strtolower($_GET['sort_dir'] ?? 'desc');
$allowedSortBy = [
  'tanggal' => 'a.tanggal_audit',
  'petugas' => 'a.nama_petugas_unit',
  'ruangan' => 'a.ruangan_diaudit',
  'num' => 'num',
  'denum' => 'denum'
];
$sortColumn = $allowedSortBy[$sortBy] ?? 'a.tanggal_audit';
$sortDirSql = $sortDir === 'asc' ? 'ASC' : 'DESC';

$qTotalData = mysqli_query($conn, "SELECT COUNT(*) AS total FROM audit_ruang_isolasi a $whereDataSql");
$totalData = mysqli_fetch_assoc($qTotalData)['total'] ?? 0;
$totalPages = max(1, ceil($totalData / $limit));

$qData = mysqli_query($conn, "
  SELECT
    a.*,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(d.audit_id) AS denum
  FROM audit_ruang_isolasi a
  LEFT JOIN detail_audit_ruang_isolasi d ON a.id = d.audit_id
  $whereDataSql
  GROUP BY a.id
  ORDER BY $sortColumn $sortDirSql, a.id DESC
  LIMIT $limit OFFSET $offset
");

$rekapPeriode = $_GET['rekap_periode'] ?? 'tahunan';
$rekapBulan = isset($_GET['rekap_bulan']) ? (int) $_GET['rekap_bulan'] : (int) date('n');
$rekapTriwulan = isset($_GET['rekap_triwulan']) ? (int) $_GET['rekap_triwulan'] : (int) ceil(((int) date('n')) / 3);
$rekapTahun = isset($_GET['rekap_tahun']) ? (int) $_GET['rekap_tahun'] : (int) date('Y');
$rekapPeriode = in_array($rekapPeriode, ['bulanan', 'triwulan', 'tahunan'], true) ? $rekapPeriode : 'tahunan';
$rekapBulan = max(1, min(12, $rekapBulan));
$rekapTriwulan = max(1, min(4, $rekapTriwulan));
$rekapTahun = max(2020, min(2100, $rekapTahun));

$rekapWhere = [];
if ($rekapPeriode === 'bulanan') {
  $rekapWhere[] = "MONTH(a.tanggal_audit) = $rekapBulan";
  $rekapWhere[] = "YEAR(a.tanggal_audit) = $rekapTahun";
} elseif ($rekapPeriode === 'triwulan') {
  $startMonth = (($rekapTriwulan - 1) * 3) + 1;
  $endMonth = $startMonth + 2;
  $rekapWhere[] = "MONTH(a.tanggal_audit) BETWEEN $startMonth AND $endMonth";
  $rekapWhere[] = "YEAR(a.tanggal_audit) = $rekapTahun";
} else {
  $rekapWhere[] = "YEAR(a.tanggal_audit) = $rekapTahun";
}
$rekapWhereSql = count($rekapWhere) ? 'WHERE ' . implode(' AND ', $rekapWhere) : '';

$qRekapBagian = mysqli_query($conn, "
  SELECT
    CONCAT(d.kode_bagian, LPAD(d.urutan_item, 4, '0')) AS kode_bagian,
    MAX(d.item_text) AS item_text,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum
  FROM audit_ruang_isolasi a
  JOIN detail_audit_ruang_isolasi d ON a.id = d.audit_id
  $rekapWhereSql
  GROUP BY d.kode_bagian, d.urutan_item
  ORDER BY d.kode_bagian ASC, d.urutan_item ASC
");

$bagianLabelsExport = [];
foreach ($checklistSections as $kode => $section) {
  foreach (($section['items'] ?? []) as $idx => $itemText) {
    $indikatorKode = sprintf('%s%04d', (string) $kode, $idx + 1);
    $bagianLabelsExport[$indikatorKode] = (string) $itemText;
  }
}

if (isset($_GET['download_rekap']) && $_GET['download_rekap'] !== '') {
  $downloadType = $_GET['download_rekap'];
  if (!in_array($downloadType, ['bagian', 'periode'], true)) {
    $downloadType = 'bagian';
  }

  $periodeLabel = strtoupper($rekapPeriode);
  if ($rekapPeriode === 'bulanan') {
    $periodeLabel .= '_B' . str_pad((string) $rekapBulan, 2, '0', STR_PAD_LEFT);
  } elseif ($rekapPeriode === 'triwulan') {
    $periodeLabel .= '_TW' . $rekapTriwulan;
  }
  $periodeLabel .= '_T' . $rekapTahun;

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="rekap_audit_ruang_isolasi_' . $downloadType . '_' . $periodeLabel . '.csv"');

  $output = fopen('php://output', 'w');
  if ($output === false) {
    exit;
  }

  fputs($output, "\xEF\xBB\xBF");

  if ($downloadType === 'bagian') {
    fputcsv($output, ['Periode', 'Tahun', 'Bulan', 'Triwulan', 'Kode Bagian', 'Nama Bagian', 'Num', 'Denum', 'Persentase']);
    $qExportBagian = mysqli_query($conn, "
      SELECT
        CONCAT(d.kode_bagian, LPAD(d.urutan_item, 4, '0')) AS kode_bagian,
        MAX(d.item_text) AS item_text,
        SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
        COUNT(*) AS denum
      FROM audit_ruang_isolasi a
      JOIN detail_audit_ruang_isolasi d ON a.id = d.audit_id
      $rekapWhereSql
      GROUP BY d.kode_bagian, d.urutan_item
      ORDER BY d.kode_bagian ASC, d.urutan_item ASC
    ");

    while ($row = mysqli_fetch_assoc($qExportBagian)) {
      $num = (int) ($row['num'] ?? 0);
      $den = (int) ($row['denum'] ?? 0);
      $pct = $den > 0 ? round(($num / $den) * 100, 1) : 0;
      $kode = $row['kode_bagian'] ?? '';
      fputcsv($output, [
        $rekapPeriode,
        $rekapTahun,
        $rekapPeriode === 'bulanan' ? $rekapBulan : '',
        $rekapPeriode === 'triwulan' ? $rekapTriwulan : '',
        $kode,
        $bagianLabelsExport[$kode] ?? ($row['item_text'] ?? 'Bagian Audit'),
        $num,
        $den,
        $pct
      ]);
    }
  } else {
    $matrixWhere = ["YEAR(a.tanggal_audit) = $rekapTahun"];
    if ($rekapPeriode === 'bulanan') {
      $matrixWhere[] = "MONTH(a.tanggal_audit) = $rekapBulan";
    } elseif ($rekapPeriode === 'triwulan') {
      $startMonthExport = (($rekapTriwulan - 1) * 3) + 1;
      $endMonthExport = $startMonthExport + 2;
      $matrixWhere[] = "MONTH(a.tanggal_audit) BETWEEN $startMonthExport AND $endMonthExport";
    }
    $matrixWhereSql = 'WHERE ' . implode(' AND ', $matrixWhere);

    $qExportPeriode = mysqli_query($conn, "
      SELECT
        CONCAT(d.kode_bagian, LPAD(d.urutan_item, 4, '0')) AS kode_bagian,
        MAX(d.item_text) AS item_text,
        MONTH(a.tanggal_audit) AS bulan,
        SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
        COUNT(*) AS denum
      FROM audit_ruang_isolasi a
      JOIN detail_audit_ruang_isolasi d ON a.id = d.audit_id
      $matrixWhereSql
      GROUP BY d.kode_bagian, d.urutan_item, MONTH(a.tanggal_audit)
      ORDER BY d.kode_bagian ASC, d.urutan_item ASC, MONTH(a.tanggal_audit) ASC
    ");

    fputcsv($output, ['Periode', 'Tahun', 'Bulan', 'Triwulan', 'Kode Bagian', 'Nama Bagian', 'Bulan Data', 'Num', 'Denum', 'Persentase']);
    while ($row = mysqli_fetch_assoc($qExportPeriode)) {
      $num = (int) ($row['num'] ?? 0);
      $den = (int) ($row['denum'] ?? 0);
      $pct = $den > 0 ? round(($num / $den) * 100, 1) : 0;
      $kode = $row['kode_bagian'] ?? '';
      fputcsv($output, [
        $rekapPeriode,
        $rekapTahun,
        $rekapPeriode === 'bulanan' ? $rekapBulan : '',
        $rekapPeriode === 'triwulan' ? $rekapTriwulan : '',
        $kode,
        $bagianLabelsExport[$kode] ?? ($row['item_text'] ?? 'Bagian Audit'),
        (int) ($row['bulan'] ?? 0),
        $num,
        $den,
        $pct
      ]);
    }
  }

  fclose($output);
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Audit Ruang Isolasi | PPI PHBW</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    :root {
      --bg: #eef3f7;
      --card: #ffffff;
      --ink: #0f172a;
      --line: rgba(148, 163, 184, 0.35);
      --primary: #1e40af;
      --primary-2: #1e3a8a;
      --ring: rgba(30, 64, 175, 0.15);
      --radius-lg: 20px;
      --radius-md: 14px;
      --shadow-md: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    body.dark-mode {
      --bg: #0b1220;
      --card: #111827;
      --ink: #e5e7eb;
      --line: #334155;
      --primary: #3b82f6;
      --primary-2: #1d4ed8;
      --ring: rgba(59, 130, 246, 0.2);
      --shadow-md: 0 12px 28px rgba(2, 6, 23, 0.55);
    }

    .audit-page {
      background: radial-gradient(900px 420px at 18% -10%, rgba(37, 99, 235, 0.12), transparent 62%), var(--bg);
      min-height: 100vh;
      color: var(--ink);
    }

    .audit-wrapper {
      width: 100%;
      margin: 20px auto 34px;
      padding: 0 14px;
      box-sizing: border-box;
    }

    .hero-header,
    .section-card {
      background: var(--card);
      border-radius: var(--radius-lg);
      border: 1px solid var(--line);
      box-shadow: var(--shadow-md);
      padding: 20px;
      margin-bottom: 14px;
    }

    .hero-header {
      background:
        radial-gradient(700px 220px at -8% -45%, rgba(255, 255, 255, 0.22), transparent 62%),
        linear-gradient(135deg, #0f2f78 0%, #1e40af 52%, #60a5fa 100%);
      border: 1px solid rgba(37, 99, 235, 0.52);
      box-shadow: 0 12px 26px rgba(30, 64, 175, 0.28);
    }

    .hero-header h1 {
      margin: 0;
      font-size: 26px;
      line-height: 1.15;
      letter-spacing: -0.3px;
      color: #ffffff;
    }

    .subtitle {
      color: rgba(226, 232, 240, 0.95);
      margin: 10px 0 0;
      font-size: 14px;
      line-height: 1.5;
      font-weight: 600;
    }

    .tab-menu {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .tab-btn {
      padding: 10px 16px;
      border-radius: 999px;
      text-decoration: none;
      background: var(--card);
      border: 1px solid var(--line);
      color: var(--ink);
      font-weight: 800;
      transition: all .2s ease;
    }

    .tab-btn.active {
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
      border-color: transparent;
      box-shadow: 0 8px 18px rgba(30, 64, 175, 0.24);
    }

    .form-control,
    textarea.form-control {
      width: 100%;
      border: 1.7px solid rgba(100, 116, 139, 0.58);
      border-radius: var(--radius-md);
      padding: 12px 14px;
      font-size: 15px;
      color: var(--ink);
      outline: none;
      transition: .2s ease;
      background: var(--card);
      box-sizing: border-box;
    }

    .form-control:focus,
    textarea.form-control:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 3px var(--ring);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      padding: 10px 14px;
      font-weight: 700;
      text-decoration: none;
      border: 1px solid transparent;
      cursor: pointer;
      transition: .2s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
    }

    .btn-warning {
      background: linear-gradient(135deg, #d97706, #b45309);
      color: #fff;
    }

    .btn-danger {
      background: linear-gradient(135deg, #dc2626, #b91c1c);
      color: #fff;
    }

    .btn-secondary {
      background: var(--card);
      border-color: var(--line);
      color: var(--ink);
    }

    .info-box {
      border-radius: 12px;
      padding: 12px 14px;
      margin-bottom: 10px;
      border: 1px solid #dbe3ee;
    }

    .info-box.success {
      background: #f0fdf4;
      color: #166534;
      border-color: #bbf7d0;
    }

    .info-box.error {
      background: #fef2f2;
      color: #991b1b;
      border-color: #fecaca;
    }

    body.dark-mode .hero-header {
      background:
        radial-gradient(700px 220px at -8% -45%, rgba(255, 255, 255, 0.16), transparent 62%),
        linear-gradient(135deg, #102451 0%, #1d4ed8 50%, #3b82f6 100%);
      border-color: rgba(59, 130, 246, 0.42);
      box-shadow: 0 14px 30px rgba(2, 6, 23, 0.5);
    }

    body.dark-mode .subtitle {
      color: rgba(219, 234, 254, 0.94);
    }

    body.dark-mode .tab-btn {
      color: #e2e8f0;
    }

    body.dark-mode .tab-btn.active {
      color: #fff;
    }

    body.dark-mode .form-control,
    body.dark-mode textarea.form-control {
      border-color: #5b6b80;
      color: #e5e7eb;
      background: #0f172a;
    }

    body.dark-mode .form-control::placeholder,
    body.dark-mode textarea.form-control::placeholder {
      color: #94a3b8;
    }

    body.dark-mode #tab-data h3,
    body.dark-mode #tab-rekap h3,
    body.dark-mode #tab-grafik h3 {
      color: #e5e7eb;
    }

    body.dark-mode #tab-data table,
    body.dark-mode #tab-rekap table {
      border-color: #334155 !important;
      background: #111827;
    }

    body.dark-mode #tab-data th,
    body.dark-mode #tab-rekap th {
      border-bottom-color: #475569 !important;
      color: #e5e7eb;
    }

    body.dark-mode #tab-data td,
    body.dark-mode #tab-rekap td,
    body.dark-mode #tab-data tr,
    body.dark-mode #tab-rekap tr {
      background: #111827 !important;
      color: #e5e7eb;
      border-bottom-color: #334155 !important;
    }

    @media (max-width: 768px) {
      .audit-wrapper {
        padding: 0 8px;
        margin-top: 14px;
      }

      .hero-header,
      .section-card {
        padding: 14px;
        border-radius: 12px;
      }

      .hero-header h1 {
        font-size: 21px;
      }

      .tab-menu {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 4px;
      }

      .tab-btn {
        white-space: nowrap;
        flex: 0 0 auto;
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
          <h1>Audit Ruang Isolasi</h1>
          <p class="subtitle">Form checklist K0001–K0009, data audit, rekap, dan grafik.</p>
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
          case 'tab-data':
            include __DIR__ . '/tabs_ruang_isolasi/tab_data_audit.php';
            break;
          case 'tab-rekap':
            include __DIR__ . '/tabs_ruang_isolasi/tab_rekap_audit.php';
            break;
          case 'tab-grafik':
            include __DIR__ . '/tabs_ruang_isolasi/tab_grafik_audit.php';
            break;
          case 'tab-form':
          default:
            include __DIR__ . '/tabs_ruang_isolasi/tab_form_audit.php';
            break;
        }
        ?>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
