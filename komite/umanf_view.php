<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = mysqli_prepare($conn, "SELECT * FROM tb_umanf WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($q);
mysqli_stmt_close($stmt);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan');location.href='umanf.php';<\/script>";
    exit;
}

function pathToUrl($path) {
    if (empty($path)) return '';
    // Handle absolute paths returned by ppi_store_uploaded_file
    $docRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    $normalPath = str_replace('\\', '/', $path);
    if ($docRoot && strpos($normalPath, $docRoot) === 0) {
        return substr($normalPath, strlen($docRoot));
    }
    // Legacy: relative path with ../
    return str_replace('../', '/', $path);
}

// === ZIP DOWNLOAD ===
if (isset($_GET['download']) && $_GET['download'] == 'zip') {
    $zipname = "dokumen_rapat_" . $id . ".zip";
    $zip     = new ZipArchive();
    $tmpZip  = tempnam(sys_get_temp_dir(), "zip");
    if ($zip->open($tmpZip, ZipArchive::CREATE) === TRUE) {
        foreach (['file_undangan','file_materi','file_absensi','file_notulen'] as $f) {
            if (!empty($data[$f]) && file_exists($data[$f]))
                $zip->addFile($data[$f], basename($data[$f]));
        }
        if (!empty($data['file_foto'])) {
            $fotos = json_decode($data['file_foto'], true);
            foreach ($fotos as $foto)
                if (file_exists($foto)) $zip->addFile($foto, "foto/" . basename($foto));
        }
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipname . '"');
        readfile($tmpZip);
        unlink($tmpZip);
        exit;
    }
}

$fotos       = !empty($data['file_foto']) ? json_decode($data['file_foto'], true) : [];
$jumlahFoto  = count($fotos);

$docSections = [
    ['file_undangan', '📄', 'Undangan'],
    ['file_materi',   '🧾', 'Materi'],
    ['file_absensi',  '👥', 'Absensi'],
    ['file_notulen',  '🖋️', 'Notulen'],
];
$availCount = 0;
foreach ($docSections as [$key,$_,$__]) if (!empty($data[$key])) $availCount++;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rapat – <?= htmlspecialchars($data['jenis_rapat']) ?> | PPI PHBW</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        img, embed, iframe, video, canvas {
            max-width: 100%;
            height: auto;
        }

        .layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
        }

        .sidebar {
            width: 260px;
            flex-shrink: 0;
        }

        main {
            flex: 1;
            min-width: 0;
            width: 100%;
        }

        /* ===== WRAPPER ===== */
        .view-wrap { padding: 24px 28px; display: flex; flex-direction: column; gap: 22px; }
        .view-wrap,
        .view-hero,
        .summary-row,
        .tab-bar,
        .doc-card,
        .doc-card-header,
        .doc-card-body,
        .tab-panel {
            width: 100%;
            max-width: 100%;
        }

        /* ===== HERO ===== */
        .view-hero {
            background: linear-gradient(135deg, #0b3c5d 0%, #1565c0 55%, #1e88e5 100%);
            border-radius: 20px; padding: 26px 30px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; box-shadow: 0 12px 32px rgba(11,60,93,.35);
            position: relative; overflow: hidden;
        }
        .view-hero::before {
            content:''; position:absolute; top:-50px; right:-50px;
            width:220px; height:220px; background:rgba(255,255,255,.05);
            border-radius:50%;
        }
        .hero-left { display:flex; align-items:center; gap:18px; min-width: 0; }
        .hero-icon {
            width:52px; height:52px; border-radius:15px; flex-shrink:0;
            background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
            display:grid; place-items:center; font-size:24px;
        }
        .hero-label { font-size:12px; color:rgba(255,255,255,.65); font-weight:500; text-transform:uppercase; letter-spacing:.5px; }
        .hero-title { color:#fff; font-size:20px; font-weight:700; margin:4px 0 0; line-height:1.35; overflow-wrap:anywhere; word-break:break-word; }
        .hero-actions { display:flex; gap:10px; flex-shrink:0; }

        /* ===== SUMMARY ROW ===== */
        .summary-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
        .sum-card {
            background:#fff; border-radius:16px; padding:18px 20px;
            display:flex; align-items:center; gap:14px;
            box-shadow:0 4px 16px rgba(0,0,0,.07);
            border:1px solid rgba(0,0,0,.04);
        }
        .sum-icon { width:44px; height:44px; border-radius:12px; display:grid; place-items:center; font-size:20px; flex-shrink:0; }
        .sum-icon.blue  { background:#eff6ff; }
        .sum-icon.green { background:#f0fdf4; }
        .sum-icon.amber { background:#fffbeb; }
        .sum-val { font-size:24px; font-weight:800; color:#0f172a; line-height:1; }
        .sum-lbl { font-size:11.5px; color:#64748b; margin-top:3px; font-weight:500; }

        /* ===== TABS ===== */
        .tab-bar {
            display:flex; gap:6px; flex-wrap:wrap;
            background:#fff; border-radius:14px; padding:8px;
            box-shadow:0 4px 16px rgba(0,0,0,.06);
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        .tab-bar::-webkit-scrollbar {
            height: 4px;
        }
        .tab-btn {
            padding:9px 18px; border:none; border-radius:10px;
            font-size:13px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif;
            display:flex; align-items:center; gap:7px; transition:all .2s;
            background:transparent; color:#64748b;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .tab-btn:hover { background:#f1f5f9; color:#334155; }
        .tab-btn.active { background:linear-gradient(135deg,#1565c0,#1e88e5); color:#fff; box-shadow:0 4px 12px rgba(30,136,229,.3); }
        .tab-badge {
            background:rgba(255,255,255,.25); border-radius:20px;
            padding:2px 7px; font-size:11px; font-weight:700;
        }
        .tab-btn:not(.active) .tab-badge { background:#e2e8f0; color:#64748b; }

        /* ===== TAB PANEL ===== */
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        /* ===== DOC CARD ===== */
        .doc-card {
            background:#fff; border-radius:18px;
            box-shadow:0 4px 18px rgba(0,0,0,.07);
            overflow:hidden; border:1px solid rgba(0,0,0,.05);
        }
        .doc-card-header {
            padding:18px 22px; display:flex; align-items:center;
            justify-content:space-between; border-bottom:1px solid #f1f5f9;
            gap: 10px;
        }
        .doc-card-title { display:flex; align-items:center; gap:10px; min-width: 0; }
        .doc-card-title .dci { font-size:20px; }
        .doc-card-title h4 { margin:0; font-size:16px; font-weight:700; color:#0f172a; overflow-wrap:anywhere; word-break:break-word; }
        .doc-card-body { padding:20px 22px; }

        .file-frame {
            width:100%; height:65vh; min-height:320px; border-radius:12px;
            box-shadow:0 4px 14px rgba(0,0,0,.09);
            border:none; background:#f8fafc; display:block;
        }
        .file-img {
            width:100%; height:auto; max-height:70vh; object-fit:contain;
            border-radius:12px; box-shadow:0 4px 14px rgba(0,0,0,.09);
            display:block;
        }
        .desktop-only { display: block; }
        .pdf-mobile-action { display: none; margin-bottom: 12px; }
        .no-file {
            display:flex; flex-direction:column; align-items:center;
            justify-content:center; padding:48px 20px; gap:10px;
            color:#94a3b8; text-align:center;
        }
        .no-file .nf-icon { font-size:40px; }
        .no-file p { font-size:14px; margin:0; }

        /* ===== PHOTO GRID ===== */
        .photo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
        .photo-item {
            border-radius:14px; overflow:hidden;
            aspect-ratio:1; cursor:pointer;
            box-shadow:0 4px 14px rgba(0,0,0,.1);
            transition:transform .2s, box-shadow .2s;
        }
        .photo-item:hover { transform:scale(1.04); box-shadow:0 10px 28px rgba(0,0,0,.16); }
        .photo-item img { width:100%; height:100%; object-fit:cover; display:block; }

        /* ===== LIGHTBOX ===== */
        .lightbox {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.88); z-index:2000;
            align-items:center; justify-content:center;
        }
        .lightbox.show { display:flex; }
        .lb-img { max-width:90vw; max-height:90vh; border-radius:12px; box-shadow:0 0 60px rgba(0,0,0,.5); }
        .lb-close {
            position:fixed; top:20px; right:24px;
            color:#fff; font-size:32px; cursor:pointer; z-index:2001;
            width:44px; height:44px; border-radius:50%;
            background:rgba(255,255,255,.1); display:grid; place-items:center;
            transition:background .2s;
        }
        .lb-close:hover { background:rgba(255,255,255,.2); }

        /* ===== BUTTONS ===== */
        .btn {
            padding:9px 18px; border:none; border-radius:11px;
            font-size:13px; font-weight:600;
            text-decoration:none; display:inline-flex; align-items:center;
            gap:6px; cursor:pointer; transition:all .2s;
            font-family:'Inter',sans-serif;
        }
        .btn-primary { background:linear-gradient(135deg,#1565c0,#1e88e5); color:white; box-shadow:0 4px 12px rgba(30,136,229,.3); }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(30,136,229,.4); }
        .btn-success { background:linear-gradient(135deg,#16a34a,#22c55e); color:white; box-shadow:0 4px 12px rgba(22,163,74,.3); }
        .btn-success:hover { transform:translateY(-2px); }
        .btn-back { background:rgba(255,255,255,.15); color:white; border:1px solid rgba(255,255,255,.25); backdrop-filter:blur(6px); }
        .btn-back:hover { background:rgba(255,255,255,.25); }
        .btn-sm { padding:7px 13px; font-size:12px; }

        /* ===== DARK MODE (DESKTOP + GLOBAL) ===== */
        body.dark-mode main {
            background: radial-gradient(circle at 15% -10%, rgba(30, 64, 175, 0.2), transparent 42%), #0b1220;
        }
        body.dark-mode .view-wrap {
            background: linear-gradient(180deg, rgba(11, 18, 32, 0.2), rgba(11, 18, 32, 0));
        }
        body.dark-mode .view-hero {
            background: linear-gradient(140deg, #0a2540 0%, #1a3f7a 50%, #1d5fa8 100%);
            border: 1.5px solid rgba(96, 165, 250, 0.35);
            box-shadow: 0 18px 44px rgba(2, 6, 23, 0.5), inset 0 0 22px rgba(59, 130, 246, 0.14);
        }
        body.dark-mode .hero-icon {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(191, 219, 254, 0.45);
            box-shadow: 0 6px 18px rgba(2, 6, 23, 0.32);
        }

        body.dark-mode .sum-card {
            background: linear-gradient(165deg, #17263b, #1b2d45);
            border: 1.5px solid rgba(59, 130, 246, 0.42);
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.35), inset 0 0 14px rgba(59, 130, 246, 0.08);
        }
        body.dark-mode .sum-val { color: #e2e8f0; }
        body.dark-mode .sum-lbl { color: #9fb2c9; }
        body.dark-mode .sum-icon.blue  { background: rgba(59, 130, 246, 0.16); }
        body.dark-mode .sum-icon.green { background: rgba(34, 197, 94, 0.14); }
        body.dark-mode .sum-icon.amber { background: rgba(251, 191, 36, 0.14); }

        body.dark-mode .tab-bar {
            background: #16263b;
            border: 1.5px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 8px 22px rgba(2, 6, 23, 0.34);
        }
        body.dark-mode .tab-btn { color: #9fb2c9; }
        body.dark-mode .tab-btn:hover {
            background: #21354f;
            color: #e2e8f0;
        }
        body.dark-mode .tab-btn.active {
            background: linear-gradient(135deg, #1565c0, #1e88e5);
            color: #fff;
            box-shadow: 0 6px 16px rgba(30, 136, 229, 0.42);
        }
        body.dark-mode .tab-btn:not(.active) .tab-badge {
            background: #2a3d59;
            color: #9fb2c9;
        }

        body.dark-mode .doc-card {
            background: linear-gradient(165deg, #17263b, #1b2d45);
            border: 1.5px solid rgba(59, 130, 246, 0.36);
            box-shadow: 0 12px 30px rgba(2, 6, 23, 0.36), inset 0 0 16px rgba(59, 130, 246, 0.08);
        }
        body.dark-mode .doc-card-header {
            border-bottom-color: rgba(96, 165, 250, 0.2);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent);
        }
        body.dark-mode .doc-card-title h4 { color: #e2e8f0; }
        body.dark-mode #tabFoto .doc-card-header span { color: #9fb2c9 !important; }

        body.dark-mode .file-frame {
            background: #101b2d;
            box-shadow: 0 8px 24px rgba(2, 6, 23, 0.42), inset 0 0 0 1px rgba(59, 130, 246, 0.28);
        }
        body.dark-mode .file-img {
            box-shadow: 0 8px 24px rgba(2, 6, 23, 0.42), 0 0 0 1px rgba(59, 130, 246, 0.24);
        }
        body.dark-mode .no-file { color: #7f93aa; }
        body.dark-mode .photo-item {
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.4), 0 0 0 1.5px rgba(59, 130, 246, 0.3);
        }
        body.dark-mode .photo-item:hover {
            box-shadow: 0 14px 30px rgba(59, 130, 246, 0.28), 0 0 0 2px rgba(59, 130, 246, 0.54);
        }

        /* ===== RESPONSIVE ===== */
        @media(max-width:900px){
            .view-wrap { padding:14px 15px; }
            .summary-row { grid-template-columns:1fr 1fr; }
            .photo-grid { grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); }
            .file-frame { height: 62vh; min-height: 320px; }
        }
        @media(max-width:768px){
            .layout { display: block; }
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 3000;
                transition: left .3s ease;
                transform: none;
            }
            .sidebar.open,
            .sidebar.show { left: 0; }

            main {
                width: 100%;
                margin-left: 0 !important;
                padding-left: 0 !important;
                padding-top: calc(var(--topbar-height)) !important;
                min-width: 0;
            }

            .view-wrap { padding: 14px 12px; gap: 14px; }
            .view-hero {
                padding: 20px 18px;
                border-radius: 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            .hero-left { gap: 14px; width: 100%; }
            .hero-icon {
                width: 52px;
                height: 52px;
                border-radius: 14px;
                font-size: 22px;
            }
            .hero-label { font-size: 10.5px; letter-spacing: .6px; opacity: .8; }
            .hero-title { font-size: 17px; line-height: 1.35; word-break: break-word; }
            .hero-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .hero-actions .btn {
                justify-content: center;
                padding: 10px 12px;
                font-size: 12.5px;
                border-radius: 12px;
            }

            .summary-row {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            .sum-card {
                padding: 14px 10px;
                border-radius: 16px;
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 8px;
            }
            .sum-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
                border-radius: 11px;
            }
            .sum-val { font-size: 22px; }
            .sum-lbl { font-size: 10px; line-height: 1.3; }

            .tab-bar {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 7px;
                padding: 7px;
                border-radius: 16px;
                overflow: visible;
                white-space: normal;
            }
            .tab-btn {
                width: 100%;
                min-width: 0;
                justify-content: center;
                text-align: center;
                white-space: normal;
                flex: unset;
                padding: 10px 6px;
                font-size: 11px;
                border-radius: 10px;
                gap: 3px;
                line-height: 1.25;
                flex-direction: column;
                align-items: center;
            }
            .tab-btn:last-child:nth-child(3n+1) { grid-column: 1 / -1; }
            .tab-badge {
                font-size: 10px;
                padding: 2px 6px;
                border-radius: 20px;
                margin-top: 2px;
            }

            .doc-card { border-radius: 16px; }
            .doc-card-header {
                padding: 14px 16px;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .doc-card-body { padding: 14px 16px; }
            .doc-card-title .dci { font-size: 18px; }
            .doc-card-title h4 { font-size: 14px; }

            .btn { padding: 8px 12px; font-size: 12px; }
            .btn-sm { padding: 7px 11px; font-size: 11px; }
            .file-frame { height: 55vh; min-height: 280px; }
            .file-img { max-height: 65vh; }
            .desktop-only { display: none; }
            .pdf-mobile-action {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 10px;
            }

            .photo-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .photo-item { border-radius: 12px; }
        }

        @media(max-width:480px){
            .summary-row { grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .sum-card { padding: 12px 6px; border-radius: 14px; }
            .sum-val { font-size: 20px; }
            .sum-lbl { font-size: 9.5px; }
            .sum-icon { width: 36px; height: 36px; font-size: 16px; }
            .tab-bar { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; padding: 6px; }
            .tab-btn { padding: 9px 4px; font-size: 10.5px; }
            .view-hero { padding: 16px 14px; }
            .hero-title { font-size: 15px; }
        }

        @media(max-width:360px){
            .view-wrap { padding: 10px 9px; }
            .tab-btn { padding: 8px 3px; font-size: 10px; }
            .doc-card-header,
            .doc-card-body { padding: 10px 12px; }
            .photo-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include_once '../sidebar.php'; ?>
    <main>
        <?php include_once '../topbar.php'; ?>
        <div class="view-wrap">
            <!-- HERO -->
            <div class="view-hero">
                <div class="hero-left">
                    <div class="hero-icon">🗂️</div>
                    <div>
                        <div class="hero-label">Detail Dokumen Rapat</div>
                        <div class="hero-title"><?= htmlspecialchars($data['jenis_rapat']) ?></div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="?id=<?= $id ?>&download=zip" class="btn btn-success">⬇️ Unduh ZIP</a>
                    <a href="umanf.php" class="btn btn-back">← Kembali</a>
                </div>
            </div>
            <!-- SUMMARY -->
            <div class="summary-row">
                <div class="sum-card">
                    <div class="sum-icon blue">📁</div>
                    <div><div class="sum-val"><?= $availCount ?></div><div class="sum-lbl">Dokumen Tersedia</div></div>
                </div>
                <div class="sum-card">
                    <div class="sum-icon green">📷</div>
                    <div><div class="sum-val"><?= $jumlahFoto ?></div><div class="sum-lbl">Foto Dokumentasi</div></div>
                </div>
                <div class="sum-card">
                    <div class="sum-icon amber">🗂️</div>
                    <div><div class="sum-val"><?= 4 - $availCount ?></div><div class="sum-lbl">Dokumen Belum Ada</div></div>
                </div>
            </div>
            <!-- TABS -->
            <div class="tab-bar" id="tabBar">
                <?php foreach ($docSections as $i => [$key,$icon,$label]):
                    $hasFile = !empty($data[$key]);
                ?>
                <button class="tab-btn <?= $i===0?'active':'' ?>" data-tab="tab<?= $i ?>">
                    <?= $icon ?> <?= $label ?>
                    <span class="tab-badge"><?= $hasFile ? '✓' : '–' ?></span>
                </button>
                <?php endforeach; ?>
                <button class="tab-btn" data-tab="tabFoto">
                    🖼️ Foto
                    <span class="tab-badge"><?= $jumlahFoto ?></span>
                </button>
            </div>
            <!-- TAB PANELS -->
            <?php foreach ($docSections as $i => [$key,$icon,$label]): ?>
            <div class="tab-panel <?= $i===0?'active':'' ?>" id="tab<?= $i ?>">
                <div class="doc-card">
                    <div class="doc-card-header">
                        <div class="doc-card-title">
                            <span class="dci"><?= $icon ?></span>
                            <h4><?= $label ?></h4>
                        </div>
                        <?php if (!empty($data[$key])): ?>
                        <a href="<?= pathToUrl($data[$key]) ?>" target="_blank" class="btn btn-primary btn-sm">
                            ↗ Buka File
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="doc-card-body">
                        <?php if (!empty($data[$key])):
                            $url = pathToUrl($data[$key]);
                            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                            if ($ext === 'pdf'):
                        ?>
                            <div class="pdf-mobile-action">
                                <a href="<?= $url ?>" target="_blank" class="btn btn-primary btn-sm">↗ Buka PDF</a>
                            </div>
                            <embed src="<?= $url ?>" type="application/pdf" class="file-frame desktop-only">
                        <?php else: ?>
                            <img src="<?= $url ?>" class="file-img" alt="<?= $label ?>">
                        <?php endif; else: ?>
                            <div class="no-file">
                                <div class="nf-icon">📭</div>
                                <p>Belum ada file <?= strtolower($label) ?> yang diunggah.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- TAB FOTO -->
            <div class="tab-panel" id="tabFoto">
                <div class="doc-card">
                    <div class="doc-card-header">
                        <div class="doc-card-title">
                            <span class="dci">🖼️</span>
                            <h4>Dokumentasi Foto</h4>
                        </div>
                        <span style="font-size:13px;color:#64748b;font-weight:600;"><?= $jumlahFoto ?> foto</span>
                    </div>
                    <div class="doc-card-body">
                        <?php if ($jumlahFoto > 0): ?>
                        <div class="photo-grid">
                            <?php foreach ($fotos as $f):
                                $url = pathToUrl($f); ?>
                            <div class="photo-item" onclick="openLightbox('<?= $url ?>')">
                                <img src="<?= $url ?>" alt="Foto dokumentasi" loading="lazy">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="no-file">
                            <div class="nf-icon">📷</div>
                            <p>Belum ada foto dokumentasi yang diunggah.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /view-wrap -->

        <!-- LIGHTBOX -->
        <div class="lightbox" id="lightbox" onclick="closeLightbox()">
            <span class="lb-close" onclick="closeLightbox()">✕</span>
            <img src="" alt="" class="lb-img" id="lbImg">
        </div>

    </main>
</div>

<script src="<?= asset('assets/js/utama.js') ?>"></script>
<script>
    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });

    // Lightbox
    function openLightbox(src) {
        document.getElementById('lbImg').src = src;
        document.getElementById('lightbox').classList.add('show');
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('show');
        document.getElementById('lbImg').src = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
</body>
</html>