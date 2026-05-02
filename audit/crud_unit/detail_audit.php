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

$qAudit = mysqli_query($conn, "SELECT * FROM audit_unit WHERE id = $id");
$audit = mysqli_fetch_assoc($qAudit);
if (!$audit) {
  die('Data audit tidak ditemukan.');
}

$checklistSections = require __DIR__ . '/../inc_checklist_audit_unit.php';

$qDetail = mysqli_query($conn, "SELECT * FROM detail_audit_unit WHERE audit_id = $id ORDER BY kode_bagian, urutan_item");
$detailByBagian = [];
while ($d = mysqli_fetch_assoc($qDetail)) {
  $kb = $d['kode_bagian'] ?? '';
  $ur = (int) ($d['urutan_item'] ?? 0);
  if ($kb !== '' && $ur > 0) {
    $detailByBagian[$kb][$ur] = $d;
  }
}

$qFoto = mysqli_query($conn, "SELECT * FROM audit_unit_foto WHERE audit_id = $id ORDER BY id DESC");

$totalNum = 0;
$totalDenum = 0;
foreach ($checklistSections as $kb => $sec) {
  foreach (($sec['items'] ?? []) as $idx => $_) {
    $ur = $idx + 1;
    $row = $detailByBagian[$kb][$ur] ?? null;
    $jwb = $row['jawaban'] ?? 'na';
    $totalDenum++;
    if ($jwb === 'ya') {
      $totalNum++;
    }
  }
}
$totalPct = $totalDenum > 0 ? round(($totalNum / $totalDenum) * 100, 1) : 0;

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
  <title>Detail Audit Unit</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    :root {
      --bg: #e8eef5;
      --card: #ffffff;
      --card-2: #f8fafc;
      --card-3: #f1f5f9;
      --ink: #0f172a;
      --muted: #64748b;
      --line: #e2e8f0;
      --line-strong: #cbd5e1;
      --accent-teal: #0f766e;
      --accent-teal-light: #14b8a6;
      --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.05);
      --shadow-md: 0 8px 24px rgba(15, 23, 42, 0.08);
      --shadow-lg: 0 16px 40px rgba(15, 23, 42, 0.1);
      --radius-lg: 20px;
      --radius-md: 14px;
    }
    body.dark-mode {
      --bg: #0b1220;
      --card: #111827;
      --card-2: #0f172a;
      --card-3: #1e293b;
      --ink: #e5e7eb;
      --muted: #94a3b8;
      --line: #334155;
      --line-strong: #475569;
      --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.35);
      --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.35);
      --shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.45);
    }
    .audit-page {
      background:
        radial-gradient(1000px 480px at 12% -8%, rgba(15, 118, 110, 0.09), transparent 55%),
        radial-gradient(800px 360px at 88% 0%, rgba(37, 99, 235, 0.08), transparent 50%),
        var(--bg);
      min-height: 100vh;
      color: var(--ink);
      font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .detail-audit-page {
      padding: 20px 18px 28px;
      max-width: 1040px;
      margin: 0 auto;
    }
    .hero-card, .section-card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: var(--radius-lg);
      padding: 22px 22px 20px;
      box-shadow: var(--shadow-md);
      margin-bottom: 16px;
      position: relative;
    }
    .hero-card::before {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      top: 0;
      height: 4px;
      border-radius: var(--radius-lg) var(--radius-lg) 0 0;
      background: linear-gradient(90deg, #0f766e, #14b8a6, #2563eb);
      opacity: 0.95;
    }
    .hero-card__title-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .hero-card h1 {
      margin: 0;
      font-size: clamp(1.35rem, 2.5vw, 1.75rem);
      font-weight: 800;
      letter-spacing: -0.025em;
      line-height: 1.2;
    }
    .hero-badge {
      display: inline-flex;
      align-items: center;
      padding: 5px 11px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #0f766e;
      background: rgba(15, 118, 110, 0.1);
      border: 1px solid rgba(15, 118, 110, 0.22);
    }
    body.dark-mode .hero-badge {
      color: #5eead4;
      background: rgba(45, 212, 191, 0.12);
      border-color: rgba(94, 234, 212, 0.28);
    }
    .subtitle { color: var(--muted); margin: 10px 0 0; font-weight: 600; font-size: 14px; line-height: 1.45; }
    .section-title {
      margin: 0;
      font-size: 1.05rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: var(--ink);
      padding-bottom: 10px;
      border-bottom: 1px solid var(--line);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-title::before {
      content: "";
      width: 4px;
      height: 1.1em;
      border-radius: 4px;
      background: linear-gradient(180deg, #0f766e, #2563eb);
      flex-shrink: 0;
    }
    .section-lead {
      margin: 10px 0 0;
      color: var(--muted);
      font-size: 13px;
      font-weight: 600;
      line-height: 1.5;
    }
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(148px, 1fr));
      gap: 12px;
      margin-top: 18px;
    }
    .stat-item {
      background: var(--card-2);
      border: 1px solid var(--line);
      border-radius: var(--radius-md);
      padding: 12px 12px 11px;
      text-align: center;
      box-shadow: var(--shadow-sm);
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .stat-item:hover {
      border-color: var(--line-strong);
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    body.dark-mode .stat-item:hover { box-shadow: var(--shadow-sm); }
    .stat-item strong {
      display: block;
      font-size: 1.125rem;
      font-weight: 800;
      color: #1e40af;
      line-height: 1.25;
      word-break: break-word;
    }
    body.dark-mode .stat-item strong { color: #93c5fd; }
    .stat-item.stat-item--skor {
      grid-column: 1 / -1;
      background:
        linear-gradient(135deg, rgba(15, 118, 110, 0.07) 0%, rgba(37, 99, 235, 0.05) 100%),
        var(--card-2);
      border: 1px solid rgba(15, 118, 110, 0.28);
      padding: 18px 16px;
      box-shadow: 0 4px 16px rgba(15, 118, 110, 0.12);
    }
    @media (min-width: 640px) {
      .stat-item.stat-item--skor { grid-column: span 2; }
    }
    @media (min-width: 960px) {
      .stat-item.stat-item--skor { grid-column: span 3; }
    }
    .stat-item.stat-item--skor strong {
      font-size: clamp(1.75rem, 4vw, 2.25rem);
      color: #0f766e;
      letter-spacing: -0.03em;
    }
    .stat-item.stat-item--skor .stat-sub {
      display: block;
      margin-top: 8px;
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      line-height: 1.45;
      max-width: 52ch;
      margin-left: auto;
      margin-right: auto;
    }
    body.dark-mode .stat-item.stat-item--skor {
      background: linear-gradient(135deg, rgba(15, 118, 110, 0.2), rgba(37, 99, 235, 0.12));
      border-color: rgba(94, 234, 212, 0.35);
      box-shadow: var(--shadow-md);
    }
    body.dark-mode .stat-item.stat-item--skor strong { color: #5eead4; }
    .label {
      color: var(--muted);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      margin-top: 6px;
    }
    .action-row { display:flex; gap:10px; flex-wrap: wrap; margin-top: 16px; align-items: center; }
    .btn-download-export {
      background: linear-gradient(135deg, #2563eb, #7c3aed);
      color: #fff;
      border: 1px solid rgba(99, 102, 241, 0.45);
      box-shadow: 0 6px 18px rgba(37, 99, 235, 0.28);
      cursor: pointer;
      font-family: inherit;
    }
    .btn-download-export:hover { filter: brightness(1.06); box-shadow: 0 8px 22px rgba(37, 99, 235, 0.32); }
    .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 16px; border-radius:12px; text-decoration:none; font-weight:700; border:1px solid transparent; font-size: 13px; }
    .btn-primary { background: linear-gradient(135deg,#1e40af,#1e3a8a); color:#fff; }
    .btn-warning { background: linear-gradient(135deg,#d97706,#b45309); color:#fff; }
    .btn-secondary { background:var(--card); color:var(--ink); border-color:var(--line-strong); }
    .table-wrap {
      overflow-x: auto;
      border-radius: var(--radius-md);
      border: 1px solid var(--line);
      background: var(--card);
      box-shadow: var(--shadow-sm);
    }
    .indikator-table {
      width: 100%;
      min-width: 720px;
      border-collapse: separate;
      border-spacing: 0;
    }
    .indikator-table thead th {
      background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
      color: #f8fafc;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    body.dark-mode .indikator-table thead th {
      background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
      color: #f1f5f9;
    }
    .indikator-table thead th:first-child { border-radius: 12px 0 0 0; }
    .indikator-table thead th:last-child { border-radius: 0 12px 0 0; }
    .indikator-table tbody td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--line);
      font-size: 13px;
      line-height: 1.5;
      vertical-align: top;
      background: var(--card);
    }
    .indikator-table tbody tr:last-child td { border-bottom: none; }
    .indikator-table tbody tr:nth-child(even) td { background: var(--card-2); }
    .indikator-table tbody tr:hover td {
      background: rgba(15, 118, 110, 0.04);
    }
    body.dark-mode .indikator-table tbody tr:hover td {
      background: rgba(94, 234, 212, 0.06);
    }
    .indikator-table td.kode-cell {
      font-variant-numeric: tabular-nums;
      font-weight: 700;
      font-size: 12px;
      color: #475569;
      letter-spacing: 0.02em;
    }
    body.dark-mode .indikator-table td.kode-cell { color: #94a3b8; }
    .center { text-align: center; }
    .pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 3.25rem;
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      border: 1px solid transparent;
      box-shadow: var(--shadow-sm);
    }
    .pill.ya { background: #ecfdf5; color: #047857; border-color: rgba(16, 185, 129, 0.35); }
    .pill.tidak { background: #fef2f2; color: #b91c1c; border-color: rgba(248, 113, 113, 0.4); }
    .pill.na { background: var(--card-3); color: #475569; border-color: var(--line-strong); }
    body.dark-mode .pill.ya { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border-color: rgba(52, 211, 153, 0.35); }
    body.dark-mode .pill.tidak { background: rgba(248, 113, 113, 0.15); color: #fca5a5; border-color: rgba(248, 113, 113, 0.35); }
    body.dark-mode .pill.na { background: var(--card-3); color: #cbd5e1; }
    .foto-grid { display: flex; flex-wrap: wrap; gap: 14px; }
    .foto-grid a {
      display: block;
      border-radius: 14px;
      overflow: hidden;
      border: 1px solid var(--line);
      box-shadow: var(--shadow-md);
      transition: transform 0.18s ease, box-shadow 0.18s ease;
      background: var(--card-2);
    }
    .foto-grid a:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }
    .foto-grid img {
      width: 200px;
      height: 132px;
      object-fit: cover;
      display: block;
    }
    .signature-panel {
      margin-top: 12px;
      padding: 14px;
      border-radius: var(--radius-md);
      border: 1px dashed var(--line-strong);
      background: linear-gradient(180deg, var(--card-2), var(--card));
    }
    .signature-img {
      display: block;
      max-width: 100%;
      height: auto;
      max-height: 100px;
      object-fit: contain;
      margin: 0 auto;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: #fff;
      padding: 8px;
      box-shadow: var(--shadow-sm);
    }
    .empty-hint {
      margin: 10px 0 0;
      padding: 14px 16px;
      border-radius: var(--radius-md);
      background: var(--card-2);
      border: 1px solid var(--line);
      color: var(--muted);
      font-size: 13px;
      font-weight: 600;
      text-align: center;
    }
    .empty-hint--full { width: 100%; margin-top: 12px; }
    .info-ok {
      background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
      color: #166534;
      border: 1px solid #86efac;
      border-radius: var(--radius-md);
      padding: 12px 14px;
      margin-bottom: 12px;
      font-weight: 700;
      font-size: 13px;
      box-shadow: var(--shadow-sm);
    }
    body.dark-mode .info-ok {
      background: rgba(16, 185, 129, 0.12);
      color: #86efac;
      border-color: rgba(52, 211, 153, 0.35);
    }
    .subbab-block { margin-top: 22px; }
    .subbab-block:first-of-type { margin-top: 16px; }
    .subbab-head {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px 14px;
      margin: 0 0 12px;
      padding: 12px 14px;
      background: linear-gradient(135deg, #115e59 0%, #0d9488 42%, #0e7490 100%);
      color: #fff;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 800;
      letter-spacing: -0.015em;
      box-shadow: 0 6px 18px rgba(15, 118, 110, 0.28);
    }
    body.dark-mode .subbab-head {
      background: linear-gradient(135deg, #134e4a, #0f766e 55%, #155e75);
      box-shadow: var(--shadow-md);
    }
    .subbab-code {
      display: inline-flex;
      padding: 4px 10px;
      border-radius: 8px;
      background: rgba(255,255,255,.18);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }
    .subbab-name { flex: 1; min-width: 0; line-height: 1.4; }
    .subbab-score {
      margin-left: auto;
      font-size: 12px;
      font-weight: 800;
      white-space: nowrap;
      background: rgba(255,255,255,.2);
      padding: 7px 12px;
      border-radius: 999px;
      letter-spacing: 0.02em;
    }
    .download-hint {
      margin-top: 10px;
      font-size: 12px;
      color: var(--muted);
      font-weight: 600;
      line-height: 1.45;
    }
    @media (max-width: 768px) {
      .subbab-head { flex-direction: column; align-items: flex-start; }
      .subbab-score { margin-left: 0; width: 100%; text-align: center; }
      .detail-audit-page { padding: 10px 10px 20px; }
      .stat-grid { grid-template-columns: 1fr; }
      .section-card, .hero-card { padding: 16px; border-radius: 16px; }
      .hero-card::before { border-radius: 16px 16px 0 0; }
      .btn { width: 100%; }
      .table-wrap { overflow: visible; border: none; background: transparent; box-shadow: none; }
      .indikator-table { min-width: 0; width: 100%; }
      .indikator-table thead { display: none; }
      .indikator-table tbody { display: grid; gap: 10px; }
      .indikator-table tbody tr {
        display: block;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: var(--card);
        padding: 12px 12px;
        box-shadow: var(--shadow-sm);
      }
      .indikator-table tbody td {
        display: block;
        border: none;
        padding: 4px 0;
        background: transparent !important;
        font-size: 12px;
      }
      .indikator-table tbody td:first-child,
      .indikator-table tbody td:last-child {
        border-radius: 0;
      }
      .indikator-table tbody td:nth-child(1)::before { content: 'Kode: '; font-weight: 800; color: var(--muted); }
      .indikator-table tbody td:nth-child(2)::before { content: 'Item: '; font-weight: 800; color: var(--muted); }
      .indikator-table tbody td:nth-child(3)::before { content: 'Jawaban: '; font-weight: 800; color: var(--muted); }
      .foto-grid a { width: 100%; max-width: 100%; }
      .foto-grid img { width: 100%; height: 200px; object-fit: cover; }
    }
  </style>
</head>
<body class="audit-page">
  <div class="layout">
    <?php include_once __DIR__ . '/../../sidebar.php'; ?>
    <main>
      <?php include_once __DIR__ . '/../../topbar.php'; ?>
      <div class="page-wrap detail-audit-page" id="detail-audit-export">
        <?php if ($statusMessage): ?>
          <div class="info-ok"><?= htmlspecialchars($statusMessage) ?></div>
        <?php endif; ?>

        <div class="hero-card">
          <div class="hero-card__title-row">
            <h1>Detail Audit Unit #<?= (int) $audit['id'] ?></h1>
            <span class="hero-badge">Audit unit</span>
          </div>
          <p class="subtitle">Ringkasan data audit unit.</p>
          <div class="stat-grid">
            <div class="stat-item stat-item--skor">
              <strong><?= htmlspecialchars((string) $totalPct) ?>%</strong>
              <span class="label">Skor kepatuhan akhir</span>
              <span class="stat-sub">Num <?= (int) $totalNum ?> / Denum <?= (int) $totalDenum ?> · jawaban &quot;Ya&quot; dibanding seluruh item</span>
            </div>
            <div class="stat-item"><strong><?= htmlspecialchars($audit['tanggal_audit']) ?></strong><span class="label">Tanggal Audit</span></div>
            <div class="stat-item"><strong><?= htmlspecialchars($audit['ruangan_diaudit'] ?? '-') ?></strong><span class="label">Ruangan yang Diaudit</span></div>
            <div class="stat-item"><strong><?= htmlspecialchars($audit['nama_petugas_unit']) ?></strong><span class="label">Petugas Unit</span></div>
            <div class="stat-item"><strong><?= !empty($audit['tanda_tangan_petugas']) ? 'Ada' : '-' ?></strong><span class="label">Tanda Tangan</span></div>
            <div class="stat-item"><strong><?= (int) mysqli_num_rows($qFoto) ?></strong><span class="label">Foto</span></div>
          </div>
          <div class="action-row detail-export-skip">
            <a class="btn btn-secondary" href="../audit_unit.php?tab=tab-data">Kembali ke Data</a>
            <a class="btn btn-warning" href="edit_audit.php?id=<?= (int) $audit['id'] ?>">Edit Audit</a>
            <button type="button" class="btn btn-download-export" id="btnDownloadDetailAudit" data-filename-base="audit_unit_detail_<?= (int) $audit['id'] ?>_<?= htmlspecialchars(preg_replace('/[^0-9_-]/', '', (string) ($audit['tanggal_audit'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
              Download gambar (tampilan seperti layar)
            </button>
          </div>
          <p class="subtitle detail-export-skip download-hint">Unduhan berupa file PNG: ringkasan skor, tanda tangan, indikator per sub bab, dan dokumentasi foto.</p>
        </div>

        <div class="section-card">
          <h3 class="section-title">Tanda tangan petugas</h3>
          <?php if (!empty($audit['tanda_tangan_petugas'])): ?>
            <div class="signature-panel">
              <img class="signature-img" src="../../<?= htmlspecialchars($audit['tanda_tangan_petugas']) ?>" alt="Tanda tangan petugas">
            </div>
          <?php else: ?>
            <p class="empty-hint">Tanda tangan tidak tersedia.</p>
          <?php endif; ?>
        </div>

        <div class="section-card">
          <h3 class="section-title">Item indikator</h3>
          <p class="section-lead">Rincian per sub bab (skor akhir sudah ditampilkan di ringkasan atas).</p>
          <?php foreach ($checklistSections as $kodeBagian => $section): ?>
            <?php
            $itemsTpl = $section['items'] ?? [];
            if (count($itemsTpl) === 0) {
              continue;
            }
            $judulSubbab = (string) ($section['title'] ?? $kodeBagian);
            $subNum = 0;
            $subDenum = 0;
            foreach ($itemsTpl as $ix => $_t) {
              $u = $ix + 1;
              $r = $detailByBagian[$kodeBagian][$u] ?? null;
              $jw = $r['jawaban'] ?? 'na';
              $subDenum++;
              if ($jw === 'ya') {
                $subNum++;
              }
            }
            $subPct = $subDenum > 0 ? round(($subNum / $subDenum) * 100, 1) : 0;
            ?>
            <div class="subbab-block">
              <div class="subbab-head">
                <span class="subbab-code"><?= htmlspecialchars($kodeBagian) ?></span>
                <span class="subbab-name"><?= htmlspecialchars($judulSubbab) ?></span>
                <span class="subbab-score"><?= $subPct ?>% · <?= (int) $subNum ?> / <?= (int) $subDenum ?></span>
              </div>
              <div class="table-wrap">
                <table class="indikator-table">
                  <thead>
                    <tr>
                      <th class="center" style="width:90px;">Kode</th>
                      <th>Item</th>
                      <th class="center" style="width:110px;">Jawaban</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($itemsTpl as $idx => $textTpl): ?>
                      <?php
                      $urutan = $idx + 1;
                      $d = $detailByBagian[$kodeBagian][$urutan] ?? null;
                      $kodeItem = $kodeBagian . str_pad((string) $urutan, 2, '0', STR_PAD_LEFT);
                      $teksItem = $d['item_text'] ?? $textTpl;
                      $jwb = $d['jawaban'] ?? 'na';
                      ?>
                      <tr>
                        <td class="center kode-cell"><?= htmlspecialchars($kodeItem) ?></td>
                        <td><?= htmlspecialchars($teksItem) ?></td>
                        <td class="center"><span class="pill <?= htmlspecialchars($jwb) ?>"><?= htmlspecialchars($jwb) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="section-card">
          <h3 class="section-title">Dokumentasi foto</h3>
          <div class="foto-grid">
            <?php if (mysqli_num_rows($qFoto) > 0): ?>
              <?php while ($f = mysqli_fetch_assoc($qFoto)): ?>
                <a href="../../<?= htmlspecialchars($f['path_file']) ?>" target="_blank" rel="noopener">
                  <img src="../../<?= htmlspecialchars($f['path_file']) ?>" alt="foto audit">
                </a>
              <?php endwhile; ?>
            <?php else: ?>
              <p class="empty-hint empty-hint--full">Belum ada foto dokumentasi untuk audit ini.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
  <script>
    (function () {
      const btn = document.getElementById('btnDownloadDetailAudit');
      const target = document.getElementById('detail-audit-export');
      if (!btn || !target || typeof html2canvas !== 'function') return;

      btn.addEventListener('click', function () {
        const prevText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Membuat gambar…';

        const baseName = btn.getAttribute('data-filename-base') || 'audit_unit_detail';

        html2canvas(target, {
          scale: Math.min(2, window.devicePixelRatio || 2),
          useCORS: true,
          allowTaint: false,
          backgroundColor: '#ffffff',
          logging: false,
          width: target.scrollWidth,
          height: target.scrollHeight,
          windowWidth: target.scrollWidth,
          windowHeight: target.scrollHeight,
          scrollX: 0,
          scrollY: 0,
          onclone: function (clonedDoc) {
            clonedDoc.querySelectorAll('.detail-export-skip').forEach(function (el) {
              el.remove();
            });
            const wrap = clonedDoc.getElementById('detail-audit-export');
            if (wrap) {
              wrap.style.boxSizing = 'border-box';
              wrap.style.padding = '16px';
              wrap.style.maxWidth = 'none';
            }
          }
        }).then(function (canvas) {
          try {
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = baseName + '.png';
            link.click();
          } catch (e) {
            alert('Gagal membuat file gambar. Coba nonaktifkan pemblokir pada browser atau refresh halaman.');
          }
        }).catch(function () {
          alert('Gagal mengambil tampilan untuk diunduh.');
        }).finally(function () {
          btn.disabled = false;
          btn.textContent = prevText;
        });
      });
    })();
  </script>
</body>
</html>
