<?php
require_once __DIR__ . '/../../config/assets.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';

$conn = $koneksi;
$pageTitle = "DETAIL AUDIT APD";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
  die("ID audit tidak valid.");
}

$jawabanMap = [
  "ya" => ["label" => "Ya", "class" => "success"],
  "tidak" => ["label" => "Tidak", "class" => "danger"],
  "na" => ["label" => "NA", "class" => "muted"]
];

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

$stmt = mysqli_prepare($conn, "SELECT * FROM audit_apd WHERE id = ?");
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

$stmtDetail = mysqli_prepare($conn, "
  SELECT *
  FROM audit_apd_detail
  WHERE audit_id = ?
  ORDER BY kategori ASC, indikator_label ASC, indikator_key ASC
");
if (!$stmtDetail) {
  die("Gagal menyiapkan query detail audit.");
}
mysqli_stmt_bind_param($stmtDetail, "i", $id);
mysqli_stmt_execute($stmtDetail);
$resultDetail = mysqli_stmt_get_result($stmtDetail);

$detailRows = [];
$totalObservasi = 0;
$totalYa = 0;
$totalTidak = 0;
$totalNA = 0;

while ($row = mysqli_fetch_assoc($resultDetail)) {
  $detailRows[] = $row;
  $totalObservasi++;
  $jawaban = strtolower($row['jawaban'] ?? '');
  if ($jawaban === 'ya') {
    $totalYa++;
  } elseif ($jawaban === 'tidak') {
    $totalTidak++;
  } elseif ($jawaban === 'na') {
    $totalNA++;
  }
}
mysqli_stmt_close($stmtDetail);

$denum = $totalYa + $totalTidak;
$persentase = $denum > 0 ? round(($totalYa / $denum) * 100, 2) : 0;
$tanggalIndo = formatTanggalIndonesia($data['tanggal_audit'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Audit APD</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    .audit-page { background: #eef3f7; min-height: 100vh; }
    .page { width: 100%; padding: 20px; }
    .container { width: 100%; max-width: none; margin: 0; }
    .card {
      background: #fff; border: 1px solid #d9e5f4; border-radius: 20px;
      box-shadow: 0 10px 24px rgba(30, 64, 128, .08); margin-bottom: 16px; padding: 18px;
    }
    .title { font-size: 24px; font-weight: 800; color: #173f79; margin-bottom: 8px; }
    .subtitle { color: #5b7499; margin-bottom: 12px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .label { font-size: 12px; color: #5b7499; font-weight: 700; margin-bottom: 4px; text-transform: uppercase; }
    .value { font-size: 15px; font-weight: 700; color: #173f79; }
    .stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
    .stat {
      border: 1px solid #dbe7f5; border-radius: 14px; padding: 12px; text-align: center;
      background: linear-gradient(180deg, #ffffff, #f8fbff);
    }
    .stat strong { display: block; font-size: 24px; color: #173f79; line-height: 1.1; }
    .stat span { font-size: 12px; color: #5b7499; font-weight: 700; }
    .thumb {
      margin-top: 10px; max-width: 260px; border-radius: 12px; border: 1px solid #dbe7f5;
      display: block;
    }
    .table-wrap { overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; min-width: 760px; }
    .table th {
      background: #2b55c6; color: #fff; font-weight: 800; padding: 10px; font-size: 13px; text-align: left;
    }
    .table td { padding: 10px; border-bottom: 1px solid #e4ebf5; font-size: 13px; color: #173f79; }
    .pill {
      display: inline-flex; align-items: center; justify-content: center; min-width: 52px;
      padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 800;
    }
    .pill.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .pill.danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .pill.muted { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
    .btn {
      display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
      border-radius: 999px; padding: 10px 14px; font-weight: 800; border: 1px solid #c7d7ed;
      color: #24436c; background: #fff;
    }
    @media (max-width: 768px) {
      .page { padding: 12px; }
      .card { padding: 12px; border-radius: 14px; }
      .title { font-size: 19px; }
      .grid { grid-template-columns: 1fr; gap: 10px; }
      .stats { grid-template-columns: repeat(2, 1fr); }
      .table { min-width: 640px; }
      .actions { flex-direction: column; }
      .btn { width: 100%; }
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
          <div class="card">
            <div class="title">Detail Audit APD</div>
            <div class="subtitle">Ringkasan data audit, kepatuhan, dan detail indikator/APD.</div>
            <div class="grid">
              <div><div class="label">Tanggal Audit</div><div class="value"><?= htmlspecialchars($tanggalIndo) ?></div></div>
              <div><div class="label">Nama Petugas</div><div class="value"><?= htmlspecialchars($data['nama_petugas'] ?? '-') ?></div></div>
              <div><div class="label">Profesi</div><div class="value"><?= htmlspecialchars($data['profesi'] ?? '-') ?></div></div>
              <div><div class="label">Ruangan</div><div class="value"><?= htmlspecialchars($data['ruangan'] ?? '-') ?></div></div>
              <div><div class="label">Tindakan</div><div class="value"><?= htmlspecialchars($data['tindakan'] ?? '-') ?></div></div>
              <div><div class="label">Keterangan</div><div class="value"><?= nl2br(htmlspecialchars($data['keterangan'] ?? '-')) ?></div></div>
            </div>
            <?php if (!empty($data['foto'])): ?>
              <div style="margin-top:10px;">
                <div class="label">Foto Audit</div>
                <a href="../uploads_apd/<?= htmlspecialchars($data['foto']) ?>" target="_blank">
                  <img src="../uploads_apd/<?= htmlspecialchars($data['foto']) ?>" alt="Foto Audit APD" class="thumb">
                </a>
              </div>
            <?php endif; ?>
            <div class="actions">
              <a href="../apd.php?tab=tab-data" class="btn">← Kembali ke Data</a>
              <a href="edit_audit.php?id=<?= (int) $data['id'] ?>" class="btn">✏ Edit</a>
            </div>
          </div>

          <div class="card">
            <div class="stats">
              <div class="stat"><strong><?= $totalObservasi ?></strong><span>Total Observasi</span></div>
              <div class="stat"><strong><?= $totalYa ?></strong><span>Ya</span></div>
              <div class="stat"><strong><?= $totalTidak ?></strong><span>Tidak</span></div>
              <div class="stat"><strong><?= $totalNA ?></strong><span>NA</span></div>
              <div class="stat"><strong><?= $persentase ?>%</strong><span>Kepatuhan</span></div>
            </div>
          </div>

          <div class="card">
            <div class="title" style="font-size:20px;">Detail Observasi APD</div>
            <?php if (!empty($detailRows)): ?>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Kategori</th>
                      <th>Indikator</th>
                      <th>Jawaban</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($detailRows as $row): ?>
                      <?php
                      $kategori = ($row['kategori'] ?? '') === 'indikator_penilaian' ? 'Indikator Penilaian' : 'APD Digunakan';
                      $jawaban = strtolower($row['jawaban'] ?? '');
                      $jawabanText = $jawabanMap[$jawaban]['label'] ?? strtoupper($jawaban);
                      $jawabanClass = $jawabanMap[$jawaban]['class'] ?? 'muted';
                      ?>
                      <tr>
                        <td><?= htmlspecialchars($kategori) ?></td>
                        <td><?= htmlspecialchars($row['indikator_label'] ?? '-') ?></td>
                        <td><span class="pill <?= htmlspecialchars($jawabanClass) ?>"><?= htmlspecialchars($jawabanText) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="subtitle">Belum ada detail observasi.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
