<?php
require_once __DIR__ . '/../../config/assets.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';


$conn = $koneksi;
$pageTitle = "DETAIL AUDIT KEBERSIHAN TANGAN";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
  die("ID audit tidak valid.");
}

$momentsMap = [
  "m1" => "M1 - Sebelum Kontak Pasien",
  "m2" => "M2 - Sebelum Tindakan Aseptik",
  "m3" => "M3 - Setelah Kontak Cairan Tubuh",
  "m4" => "M4 - Setelah Kontak Pasien",
  "m5" => "M5 - Setelah lingkungan sekitar pasien"
];

$hasilMap = [
  "alkohol_6l" => ["label" => "Alkohol + 6 Langkah", "class" => "success"],
  "alkohol_biasa" => ["label" => "Alkohol Biasa", "class" => "info"],
  "sabun_6l" => ["label" => "Sabun + 6 Langkah", "class" => "primary"],
  "sabun_biasa" => ["label" => "Sabun Biasa", "class" => "warning"],
  "missed" => ["label" => "MISSED / tidak melakukan", "class" => "danger"]
];

/**
 * Format tanggal Indonesia
 */
function formatTanggalIndonesia($tanggal)
{
  if (!$tanggal) {
    return '-';
  }

  $bulan = [
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

  $timestamp = strtotime($tanggal);

  if (!$timestamp) {
    return '-';
  }

  return date('d', $timestamp) . ' ' . $bulan[(int) date('m', $timestamp)] . ' ' . date('Y', $timestamp);
}

/**
 * Ambil data utama audit
 */
$stmt = mysqli_prepare($conn, "
    SELECT *
    FROM audit_hand_hygiene
    WHERE id = ?
");

if (!$stmt) {
  die("Gagal menyiapkan query data audit.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
  die("Data audit tidak ditemukan.");
}

/**
 * Ambil detail observasi
 */
$stmtDetail = mysqli_prepare($conn, "
    SELECT *
    FROM audit_hand_hygiene_detail
    WHERE audit_id = ?
    ORDER BY opportunity_ke ASC, moment_key ASC
");

if (!$stmtDetail) {
  die("Gagal menyiapkan query detail audit.");
}

mysqli_stmt_bind_param($stmtDetail, "i", $id);
mysqli_stmt_execute($stmtDetail);
$resultDetail = mysqli_stmt_get_result($stmtDetail);

$detailRows = [];
$totalObservasi = 0;
$totalPatuh = 0;
$totalMissed = 0;

while ($row = mysqli_fetch_assoc($resultDetail)) {
  $detailRows[] = $row;
  $totalObservasi++;

  if (($row['hasil_observasi'] ?? '') === 'missed') {
    $totalMissed++;
  } else {
    $totalPatuh++;
  }
}

mysqli_stmt_close($stmtDetail);

$persentase = $totalObservasi > 0
  ? round(($totalPatuh / $totalObservasi) * 100, 2)
  : 0;

$tanggalIndo = formatTanggalIndonesia($data['tanggal_audit'] ?? '');

/**
 * Optional status message
 */
$statusMessage = '';
if (isset($_GET['status'])) {
  if ($_GET['status'] === 'updated') {
    $statusMessage = '<div class="info-box" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;margin-bottom:18px;">Data audit berhasil diperbarui.</div>';
  } elseif ($_GET['status'] === 'deleted') {
    $statusMessage = '<div class="info-box" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;margin-bottom:18px;">Data audit berhasil dihapus.</div>';
  } elseif ($_GET['status'] === 'error') {
    $statusMessage = '<div class="info-box" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;margin-bottom:18px;">Terjadi kesalahan saat memproses data.</div>';
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Audit Kebersihan Tangan</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    /* ⛔ JANGAN override body layout utama */
    body.audit-page {
      font-family: "Segoe UI", Arial, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(59, 130, 246, 0.12), transparent 28%),
        radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.10), transparent 30%),
        linear-gradient(180deg, #eef4fb 0%, #e7eef8 100%);
      min-height: 100vh;
      color: #16325c;
    }

    /* wrapper isi konten */
    .page {
      width: 100%;
      padding: 24px;
    }

    .container {
      max-width: 1440px;
      margin: 0 auto;
    }

    /* HERO */
    .hero-card {
      background: linear-gradient(135deg, #173f95 0%, #2459cc 52%, #4d8dff 100%);
      border-radius: 30px;
      padding: 30px 34px;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      box-shadow: 0 20px 40px rgba(37, 88, 190, 0.25);
      margin-bottom: 28px;
      flex-wrap: wrap;
    }

    .hero-badge {
      padding: 10px 18px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.10);
      border: 1px solid rgba(255, 255, 255, 0.22);
      font-weight: 700;
      font-size: 14px;
      margin-bottom: 16px;
      display: inline-block;
    }

    .hero-title {
      font-size: 36px;
      font-weight: 800;
      margin-bottom: 12px;
    }

    .hero-subtitle {
    16px;
      color: rgba(255, 255, 255, 0.92);
      max-width: 760px;
    }

    .hero-id {
      min-width: 210px;
      text-align: center;
      padding: 24px;
      border-radius: 24px;
      background: rgba(255, 255, 255, 0.16);
      border: 1px solid rgba(255, 255, 255, 0.22);
    }

    .id-number {
      font-size: 36px;
      font-weight: 800;
    }

    .id-label {
      font-size: 18px;
      font-weight: 600;
    }

    /* GRID */
    .content-grid {
      display: grid;
      grid-template-columns: 390px 1fr;
      gap: 24px;
    }

    /* CARD */
    .card {
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid #d9e5f4;
      border-radius: 28px;
      box-shadow: 0 14px 34px rgba(30, 64, 128, 0.08);
    }

    .info-card,
    .stats-card,
    .table-card {
      padding: 24px;
    }

    /* INFO */
    .info-section {
      padding: 14px 0;
      border-bottom: 1px solid #dbe7f4;
    }

    .info-label {
      font-size: 14px;
      color: #5873a3;
    }

    .info-value {
      font-size: 18px;
      font-weight: 700;
      color: #173f79;
    }

    /* STATS */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
    }

    .stat-item {
      text-align: center;
      padding: 20px;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      margin: 0 auto 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }

    .icon-blue {
      background: #3b82f6;
    }

    .icon-green {
      background: #22c55e;
    }

    .icon-red {
      background: #ef4444;
    }

    .icon-cyan {
      background: #06b6d4;
    }

    .stat-number {
      font-size: 28px;
      font-weight: 800;
    }

    /* TABLE */
    .table-responsive {
      overflow-x: auto;
    }

    .audit-table {
      width: 100%;
      border-collapse: collapse;
    }

    .audit-table th {
      background: #2b55c6;
      color: white;
      padding: 12px;
    }

    .audit-table td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
    }

    /* BUTTON */
    .btn-modern {
      padding: 12px 18px;
      border-radius: 999px;
      border: 1px solid #c7d7ed;
      background: #fff;
      font-weight: 700;
      text-decoration: none;
      color: #24436c;
    }

    .action-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 22px;
    }

    .pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 16px;
      border-radius: 999px;
      font-weight: 800;
      font-size: 14px;
      color: #fff;
      min-width: 180px;
      text-align: center;
      line-height: 1.4;
    }

    .pill.success {
      background: linear-gradient(135deg, #26c96f, #199f59);
    }

    .pill.info {
      background: linear-gradient(135deg, #3dbbd8, #1b91b3);
    }

    .pill.primary {
      background: linear-gradient(135deg, #4d8dff, #2d63d6);
    }

    .pill.warning {
      background: linear-gradient(135deg, #f7b34c, #eb8d1f);
    }

    .pill.danger {
      background: linear-gradient(135deg, #ff7373, #e14646);
    }

    .mobile-observation-list {
      display: none;
      gap: 14px;
      flex-direction: column;
    }

    .mobile-observation-card {
      border: 1px solid #d8e4f3;
      border-radius: 18px;
      padding: 16px;
      background: linear-gradient(180deg, #ffffff, #f7fbff);
      box-shadow: 0 8px 20px rgba(30, 64, 128, 0.05);
    }

    .mobile-observation-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }

    .mobile-opportunity-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 42px;
      height: 42px;
      padding: 0 12px;
      border-radius: 999px;
      background: linear-gradient(135deg, #2b55c6, #4d8dff);
      color: #fff;
      font-weight: 800;
      font-size: 14px;
    }

    .mobile-label {
      font-size: 12px;
      color: #6a84ab;
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: 4px;
      font-weight: 700;
    }

    .mobile-value {
      font-size: 15px;
      color: #1f467e;
      font-weight: 700;
      line-height: 1.45;
    }

    .mobile-row {
      margin-bottom: 12px;
    }

    .mobile-row:last-child {
      margin-bottom: 0;
    }

    .empty-state {
      padding: 28px;
      text-align: center;
      font-size: 18px;
      color: #6780a6;
      border-radius: 20px;
      background: #f8fbff;
      border: 1px dashed #c9d9ee;
    }

    /* MOBILE */
    @media (max-width: 768px) {
      .content-grid {
        grid-template-columns: 1fr;
      }

      .hero-title {
        font-size: 26px;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .action-row {
        flex-direction: column;
        gap: 10px;
      }

      .btn-modern {
        width: 100%;
        text-align: center;
      }

      .table-responsive {
        display: none;
      }

      .mobile-observation-list {
        display: flex;
      }

      .pill {
        min-width: 100%;
        width: 100%;
      }
    }
  </style>
</head>

<body class="audit-page">
  <div class="layout">

    <?php include_once __DIR__ . '/../../sidebar.php'; ?>

    <main>
      <?php include_once __DIR__ . '/../../topbar.php'; ?>


      <div class="page">
        <div class="container">

          <div class="hero-card">
            <div>
              <div class="hero-badge">🩺 AUDIT KEBERSIHAN TANGAN</div>
              <h1 class="hero-title">Detail Audit Kebersihan Tangan</h1>
              <p class="hero-subtitle">
                Berikut adalah detail observasi audit kebersihan tangan petugas secara lengkap,
                modern, premium, dan nyaman dibuka dari desktop maupun HP.
              </p>
            </div>

            <div class="hero-id">
              <div class="id-number"><?= (int) $data['id'] ?></div>
              <div class="id-label">Audit ID</div>
            </div>
          </div>

          <div class="content-grid">
            <div class="card info-card">
              <div class="info-section">
                <div class="info-label">Tanggal Audit</div>
                <div class="info-value"><?= htmlspecialchars($tanggalIndo) ?></div>
              </div>

              <div class="info-section">
                <div class="info-label">Nama Petugas</div>
                <div class="info-value"><?= htmlspecialchars($data['nama_petugas'] ?? '-') ?></div>
              </div>

              <div class="info-section">
                <div class="info-label">Profesi</div>
                <div class="info-value"><?= htmlspecialchars($data['profesi'] ?? '-') ?></div>
              </div>

              <div class="info-section">
                <div class="info-label">Ruangan</div>
                <div class="info-value"><?= htmlspecialchars($data['ruangan'] ?? '-') ?></div>
              </div>

              <div class="info-section">
                <div class="info-label">Keterangan</div>
                <div class="info-value note">
                  <?= !empty($data['keterangan']) ? nl2br(htmlspecialchars($data['keterangan'])) : '-' ?>
                </div>
              </div>

              <div class="action-row">
                <a href="../kebersihantangan.php?tab=tab-data" class="btn-modern">← Kembali</a>
                <a href="edit_audit.php?id=<?= (int) $data['id'] ?>" class="btn-modern">✏ Edit</a>
              </div>
            </div>

            <div>
              <div class="card stats-card">
                <div class="stats-grid">
                  <div class="stat-item">
                    <div class="stat-icon icon-blue">📋</div>
                    <div class="stat-number"><?= $totalObservasi ?></div>
                    <div class="stat-title">Total Observasi</div>
                  </div>

                  <div class="stat-item">
                    <div class="stat-icon icon-green">✓</div>
                    <div class="stat-number"><?= $totalPatuh ?></div>
                    <div class="stat-title">Observasi Tepat</div>
                  </div>

                  <div class="stat-item">
                    <div class="stat-icon icon-red">✕</div>
                    <div class="stat-number"><?= $totalMissed ?></div>
                    <div class="stat-title">Observasi Terlewat</div>
                  </div>

                  <div class="stat-item">
                    <div class="stat-icon icon-cyan">%</div>
                    <div class="stat-number"><?= $persentase ?>%</div>
                    <div class="stat-title">Kepatuhan</div>
                  </div>
                </div>
              </div>

              <div class="card table-card">
                <h2 class="section-title">Detail Observasi</h2>

                <?php if (!empty($detailRows)): ?>
                  <div class="table-responsive">
                    <table class="audit-table">
                      <thead>
                        <tr>
                          <th class="col-small">Opportunity</th>
                          <th>Moment</th>
                          <th>Hasil</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($detailRows as $d): ?>
                          <?php
                          $momentKey = $d['moment_key'] ?? '';
                          $hasilKey = $d['hasil_observasi'] ?? '';
                          $momentLabel = $momentsMap[$momentKey] ?? strtoupper($momentKey);
                          $hasilLabel = $hasilMap[$hasilKey]['label'] ?? $hasilKey;
                          $hasilClass = $hasilMap[$hasilKey]['class'] ?? 'info';
                          ?>
                          <tr>
                            <td class="col-small"><?= (int) $d['opportunity_ke'] ?></td>
                            <td><?= htmlspecialchars($momentLabel) ?></td>
                            <td>
                              <span class="pill <?= htmlspecialchars($hasilClass) ?>">
                                <?= htmlspecialchars($hasilLabel) ?>
                              </span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>

                  <div class="mobile-observation-list">
                    <?php foreach ($detailRows as $d): ?>
                      <?php
                      $momentKey = $d['moment_key'] ?? '';
                      $hasilKey = $d['hasil_observasi'] ?? '';
                      $momentLabel = $momentsMap[$momentKey] ?? strtoupper($momentKey);
                      $hasilLabel = $hasilMap[$hasilKey]['label'] ?? $hasilKey;
                      $hasilClass = $hasilMap[$hasilKey]['class'] ?? 'info';
                      ?>
                      <div class="mobile-observation-card">
                        <div class="mobile-observation-top">
                          <div>
                            <div class="mobile-label">Opportunity</div>
                            <div class="mobile-value"><?= (int) $d['opportunity_ke'] ?></div>
                          </div>
                          <div class="mobile-opportunity-badge">#<?= (int) $d['opportunity_ke'] ?></div>
                        </div>

                        <div class="mobile-row">
                          <div class="mobile-label">Moment</div>
                          <div class="mobile-value"><?= htmlspecialchars($momentLabel) ?></div>
                        </div>

                        <div class="mobile-row">
                          <div class="mobile-label">Hasil</div>
                          <span class="pill <?= htmlspecialchars($hasilClass) ?>">
                            <?= htmlspecialchars($hasilLabel) ?>
                          </span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="empty-state">Belum ada detail observasi untuk audit ini.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>

  <script src="<?= asset('assets/js/utama.js') ?>"></script>

</body>

</html>