<?php
require_once __DIR__ . '/../../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';
$conn = $koneksi;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
  die('ID audit tidak valid.');
}

$qAudit = mysqli_query($conn, "SELECT * FROM audit_bundle_vap WHERE id = $id");
$audit = mysqli_fetch_assoc($qAudit);
if (!$audit) {
  die('Data audit tidak ditemukan.');
}

$qDetail = mysqli_query($conn, "SELECT * FROM detail_audit_bundle_vap WHERE audit_id = $id ORDER BY kode_bagian, urutan_item");
$qFoto = mysqli_query($conn, "SELECT * FROM audit_bundle_vap_foto WHERE audit_id = $id ORDER BY id DESC");
$statusMessage = '';
if (isset($_GET['status']) && $_GET['status'] === 'updated') {
  $statusMessage = 'Data audit berhasil diperbarui.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Audit Bundle VAP</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    :root {
      --bg: #eef3f7;
      --card: #ffffff;
      --card-2: #f8fafc;
      --ink: #0f172a;
      --muted: #64748b;
      --line: #dbe3ee;
      --line-strong: #cbd5e1;
    }
    body.dark-mode {
      --bg: #0b1220;
      --card: #111827;
      --card-2: #0f172a;
      --ink: #e5e7eb;
      --muted: #94a3b8;
      --line: #334155;
      --line-strong: #475569;
    }
    .audit-page {
      background: radial-gradient(900px 420px at 18% -10%, rgba(37, 99, 235, 0.12), transparent 62%), var(--bg);
      min-height: 100vh;
      color: var(--ink);
    }
    .page-wrap { padding: 16px; }
    .hero-card, .section-card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 18px;
      padding: 18px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, .07);
      margin-bottom: 14px;
    }
    .hero-card h1 { margin: 0; font-size: 28px; }
    .subtitle { color: var(--muted); margin: 8px 0 0; font-weight: 600; }
    .stat-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-top: 14px;
    }
    .stat-item { background: var(--card-2); border: 1px solid var(--line); border-radius: 12px; padding: 10px; text-align: center; }
    .stat-item strong { display: block; font-size: 24px; color: #1e40af; line-height: 1; }
    .label { color: var(--muted); font-size: 12px; font-weight: 700; }
    .action-row { display:flex; gap:8px; flex-wrap: wrap; margin-top: 12px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 14px; border-radius:12px; text-decoration:none; font-weight:700; border:1px solid transparent; }
    .btn-primary { background: linear-gradient(135deg,#1e40af,#1e3a8a); color:#fff; }
    .btn-warning { background: linear-gradient(135deg,#d97706,#b45309); color:#fff; }
    .btn-secondary { background:var(--card); color:var(--ink); border-color:var(--line-strong); }
    .table-wrap { overflow-x:auto; border-radius: 12px; border: 1px solid var(--line); }
    table { width:100%; min-width:760px; border-collapse: collapse; }
    th { background: linear-gradient(135deg,#1e40af,#1e3a8a); color:#fff; font-size:13px; font-weight:800; padding:10px; text-align:left; }
    td { padding:10px; border-bottom:1px solid var(--line); font-size:13px; background: var(--card); }
    tr:nth-child(odd) td { background:var(--card-2); }
    .center { text-align:center; }
    .pill { display:inline-flex; padding:6px 10px; border-radius:999px; font-size:12px; font-weight:800; text-transform:uppercase; }
    .pill.ya { background:#dcfce7; color:#166534; }
    .pill.tidak { background:#fee2e2; color:#991b1b; }
    .pill.na { background:#e2e8f0; color:#334155; }
    .foto-grid { display:flex; flex-wrap:wrap; gap:10px; }
    .foto-grid img { width:180px; height:120px; object-fit:cover; border-radius:10px; border:1px solid var(--line); }
    .info-ok { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; border-radius:12px; padding:10px 12px; margin-bottom:10px; font-weight:700; }
    @media (max-width: 768px) {
      .page-wrap { padding: 8px; }
      .hero-card h1 { font-size: 22px; }
      .stat-grid { grid-template-columns: 1fr; }
      .section-card, .hero-card { padding: 14px; border-radius: 12px; }
      .btn { width:100%; }
      .table-wrap { overflow: visible; border: none; }
      table { min-width: 0; width: 100%; }
      table thead { display: none; }
      table tbody { display: grid; gap: 8px; }
      table tbody tr {
        display: block;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: var(--card);
        padding: 8px 10px;
      }
      table tbody td {
        display: block;
        border: none;
        padding: 3px 0;
        background: transparent !important;
        font-size: 12px;
      }
      table tbody td:first-child,
      table tbody td:last-child {
        border-radius: 0;
      }
      table tbody td:nth-child(1)::before { content: 'Kode: '; font-weight: 800; color: var(--muted); }
      table tbody td:nth-child(2)::before { content: 'Item: '; font-weight: 800; color: var(--muted); }
      table tbody td:nth-child(3)::before { content: 'Jawaban: '; font-weight: 800; color: var(--muted); }
      .foto-grid img { width: 100%; height: 170px; }
    }
  </style>
</head>
<body class="audit-page">
  <div class="layout">
    <?php include_once __DIR__ . '/../../sidebar.php'; ?>
    <main>
      <?php include_once __DIR__ . '/../../topbar.php'; ?>
      <div class="page-wrap">
        <?php if ($statusMessage): ?>
          <div class="info-ok"><?= htmlspecialchars($statusMessage) ?></div>
        <?php endif; ?>

        <div class="hero-card">
          <h1>Detail Audit Bundle VAP #<?= (int) $audit['id'] ?></h1>
          <p class="subtitle">Ringkasan data audit bundle VAP.</p>
          <div class="stat-grid">
            <div class="stat-item"><strong><?= htmlspecialchars($audit['tanggal_audit']) ?></strong><span class="label">Tanggal Audit</span></div>
            <div class="stat-item"><strong><?= htmlspecialchars($audit['ruangan_diaudit'] ?? '-') ?></strong><span class="label">Ruangan yang Diaudit</span></div>
            <div class="stat-item"><strong><?= htmlspecialchars($audit['nama_pasien'] ?? '-') ?></strong><span class="label">Nama Pasien</span></div>
            <div class="stat-item"><strong><?= htmlspecialchars($audit['nama_petugas_unit']) ?></strong><span class="label">Petugas Unit</span></div>
            <div class="stat-item"><strong><?= !empty($audit['tanda_tangan_petugas']) ? 'Ada' : '-' ?></strong><span class="label">Tanda Tangan</span></div>
            <div class="stat-item"><strong><?= (int) mysqli_num_rows($qFoto) ?></strong><span class="label">Foto</span></div>
          </div>
          <div class="action-row">
            <a class="btn btn-secondary" href="../audit_bundle_vap.php?tab=tab-data">Kembali ke Data</a>
            <a class="btn btn-warning" href="edit_audit.php?id=<?= (int) $audit['id'] ?>">Edit Audit</a>
          </div>
        </div>

        <div class="section-card">
          <h3>Tanda Tangan Petugas</h3>
          <?php if (!empty($audit['tanda_tangan_petugas'])): ?>
            <img src="../../<?= htmlspecialchars($audit['tanda_tangan_petugas']) ?>" alt="Tanda tangan petugas" style="max-width:100%;height:90px;object-fit:contain;border:1px solid #dbe3ee;border-radius:8px;background:#fff;padding:4px;">
          <?php else: ?>
            <p>Tanda tangan tidak tersedia.</p>
          <?php endif; ?>
        </div>

        <div class="section-card">
          <h3>Item Indikator</h3>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th class="center" style="width:90px;">Kode</th>
                  <th>Item</th>
                  <th class="center" style="width:110px;">Jawaban</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($d = mysqli_fetch_assoc($qDetail)): ?>
                  <?php $kodeItem = ($d['kode_bagian'] ?? '') . str_pad((string) ((int) ($d['urutan_item'] ?? 0)), 4, '0', STR_PAD_LEFT); ?>
                  <tr>
                    <td class="center"><?= htmlspecialchars($kodeItem) ?></td>
                    <td><?= htmlspecialchars($d['item_text']) ?></td>
                    <td class="center"><span class="pill <?= htmlspecialchars($d['jawaban']) ?>"><?= htmlspecialchars($d['jawaban']) ?></span></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="section-card">
          <h3>Dokumentasi Foto</h3>
          <div class="foto-grid">
            <?php if (mysqli_num_rows($qFoto) > 0): ?>
              <?php while ($f = mysqli_fetch_assoc($qFoto)): ?>
                <a href="../../<?= htmlspecialchars($f['path_file']) ?>" target="_blank" rel="noopener">
                  <img src="../../<?= htmlspecialchars($f['path_file']) ?>" alt="foto audit">
                </a>
              <?php endwhile; ?>
            <?php else: ?>
              <p>Tidak ada foto.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
