<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;
$csrfToken = csrf_token();
$targetDir = __DIR__ . '/../uploads/umanf';
$allowedUploadMap = [
    'pdf' => ['application/pdf', 'application/x-pdf'],
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png' => ['image/png'],
];

// ============ SIMPAN DATA ============
if (isset($_POST['simpan'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf();
    }

    $jenis = trim($_POST['jenis_rapat'] ?? '');

    $fields = ['undangan', 'materi', 'absensi', 'notulen'];
    $uploaded = [];
    $createdFiles = [];
    foreach ($fields as $f) {
        $uploaded[$f] = '';
        if (!empty($_FILES["file_$f"]['name'])) {
            $uploadError = '';
            $stored = ppi_store_uploaded_file($_FILES["file_$f"], $targetDir, $allowedUploadMap, $uploadError);
            if ($stored === false) {
                foreach ($createdFiles as $createdFile) {
                    ppi_unlink_upload($createdFile, $targetDir);
                }
                echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='umanf.php';</script>";
                exit;
            }
            $uploaded[$f] = $stored;
            $createdFiles[] = $stored;
        }
    }

    $fotoArr = [];
    if (!empty($_FILES['file_foto']['name'][0])) {
        foreach ($_FILES['file_foto']['name'] as $i => $fn) {
            if ($fn !== '') {
                $file = [
                    'name' => $_FILES['file_foto']['name'][$i] ?? '',
                    'type' => $_FILES['file_foto']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['file_foto']['tmp_name'][$i] ?? '',
                    'error' => $_FILES['file_foto']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['file_foto']['size'][$i] ?? 0,
                ];
                $uploadError = '';
                $stored = ppi_store_uploaded_file($file, $targetDir, $allowedUploadMap, $uploadError);
                if ($stored === false) {
                    foreach ($createdFiles as $createdFile) {
                        ppi_unlink_upload($createdFile, $targetDir);
                    }
                    echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='umanf.php';</script>";
                    exit;
                }
                $fotoArr[] = $stored;
                $createdFiles[] = $stored;
            }
        }
    }
    $fotoJson = json_encode($fotoArr);

    $insertStmt = mysqli_prepare($conn, "INSERT INTO tb_umanf 
    (jenis_rapat,file_undangan,file_materi,file_absensi,file_notulen,file_foto)
    VALUES(?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param(
        $insertStmt,
        "ssssss",
        $jenis,
        $uploaded['undangan'],
        $uploaded['materi'],
        $uploaded['absensi'],
        $uploaded['notulen'],
        $fotoJson
    );
    $insert = mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);

    if ($insert) {
        header("Location: umanf.php?success=1");
        exit;
    }

    foreach ($createdFiles as $createdFile) {
        ppi_unlink_upload($createdFile, $targetDir);
    }

    header("Location: umanf.php?error=1");
    exit;
}

// ============ UPDATE DATA (LENGKAPI BERTAHAP) ============
if (isset($_POST['update'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf();
    }

    $id = intval($_POST['id'] ?? 0);
    $jenis = trim($_POST['jenis_rapat'] ?? '');

    $oldStmt = mysqli_prepare($conn, "SELECT * FROM tb_umanf WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($oldStmt, "i", $id);
    mysqli_stmt_execute($oldStmt);
    $oldQ = mysqli_stmt_get_result($oldStmt);
    $old = mysqli_fetch_assoc($oldQ);
    mysqli_stmt_close($oldStmt);

    if (!$old) {
        header("Location: umanf.php?error=1");
        exit;
    }

    $fieldsMap = [
        'undangan' => 'file_undangan',
        'materi' => 'file_materi',
        'absensi' => 'file_absensi',
        'notulen' => 'file_notulen',
    ];

    $updated = [];
    $createdFiles = [];
    foreach ($fieldsMap as $short => $dbField) {
        $updated[$dbField] = $old[$dbField] ?? '';

        if (!empty($_FILES["file_$short"]['name'])) {
            $uploadError = '';
            $stored = ppi_store_uploaded_file($_FILES["file_$short"], $targetDir, $allowedUploadMap, $uploadError);
            if ($stored === false) {
                foreach ($createdFiles as $createdFile) {
                    ppi_unlink_upload($createdFile, $targetDir);
                }
                echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='umanf.php';</script>";
                exit;
            }

            if (!empty($old[$dbField])) {
                ppi_unlink_upload($old[$dbField], $targetDir);
            }
            $updated[$dbField] = $stored;
            $createdFiles[] = $stored;
        }
    }

    // Foto: append ke foto lama jika ada upload baru.
    $oldFoto = !empty($old['file_foto']) ? json_decode($old['file_foto'], true) : [];
    if (!is_array($oldFoto)) $oldFoto = [];

    if (!empty($_FILES['file_foto']['name'][0])) {
        foreach ($_FILES['file_foto']['name'] as $i => $fn) {
            if ($fn !== '') {
                $file = [
                    'name' => $_FILES['file_foto']['name'][$i] ?? '',
                    'type' => $_FILES['file_foto']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['file_foto']['tmp_name'][$i] ?? '',
                    'error' => $_FILES['file_foto']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['file_foto']['size'][$i] ?? 0,
                ];
                $uploadError = '';
                $stored = ppi_store_uploaded_file($file, $targetDir, $allowedUploadMap, $uploadError);
                if ($stored === false) {
                    foreach ($createdFiles as $createdFile) {
                        ppi_unlink_upload($createdFile, $targetDir);
                    }
                    echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='umanf.php';</script>";
                    exit;
                }
                $oldFoto[] = $stored;
                $createdFiles[] = $stored;
            }
        }
    }
    $fotoJson = json_encode($oldFoto);

    $updateStmt = mysqli_prepare($conn, "UPDATE tb_umanf SET
            jenis_rapat   = ?,
            file_undangan = ?,
            file_materi   = ?,
            file_absensi  = ?,
            file_notulen  = ?,
            file_foto     = ?
        WHERE id = ?");
    mysqli_stmt_bind_param(
        $updateStmt,
        "ssssssi",
        $jenis,
        $updated['file_undangan'],
        $updated['file_materi'],
        $updated['file_absensi'],
        $updated['file_notulen'],
        $fotoJson,
        $id
    );
    $update = mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
    if ($update) {
        header("Location: umanf.php?updated=1");
        exit;
    }

    foreach ($createdFiles as $createdFile) {
        ppi_unlink_upload($createdFile, $targetDir);
    }

    header("Location: umanf.php?error=1");
    exit;
}

// ============ HAPUS DATA ============
if (isset($_GET['hapus'])) {
    if (!csrf_validate($_GET['csrf'] ?? '')) {
        ppi_abort_csrf();
    }

    $id = intval($_GET['hapus']);
    $selectStmt = mysqli_prepare($conn, "SELECT * FROM tb_umanf WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($selectStmt, "i", $id);
    mysqli_stmt_execute($selectStmt);
    $q = mysqli_stmt_get_result($selectStmt);
    $d = mysqli_fetch_assoc($q);
    mysqli_stmt_close($selectStmt);
    foreach (['file_undangan', 'file_materi', 'file_absensi', 'file_notulen'] as $f)
        if (!empty($d[$f])) ppi_unlink_upload($d[$f], $targetDir);
    if (!empty($d['file_foto'])) {
        $ff = json_decode($d['file_foto'], true);
        foreach ($ff as $f) ppi_unlink_upload($f, $targetDir);
    }
    $deleteStmt = mysqli_prepare($conn, "DELETE FROM tb_umanf WHERE id = ?");
    mysqli_stmt_bind_param($deleteStmt, "i", $id);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
    header("Location: umanf.php");
    exit;
}

// ============ AMBIL DATA ============
$data     = mysqli_query($conn, "SELECT id, jenis_rapat, file_undangan, file_materi, file_absensi, file_notulen, file_foto FROM tb_umanf ORDER BY id DESC");
$allRows  = mysqli_fetch_all($data, MYSQLI_ASSOC);
$total    = count($allRows);

// Hitung stats
$totalDocs = 0; $totalFoto = 0;
foreach ($allRows as $r) {
    foreach (['file_undangan','file_materi','file_absensi','file_notulen'] as $f) if (!empty($r[$f])) $totalDocs++;
    if (!empty($r['file_foto'])) { $arr = json_decode($r['file_foto'],true); $totalFoto += count($arr); }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UmanF – Dokumen Rapat | PPI PHBW</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        /* ===== WRAPPER ===== */
        .umanf-wrap { padding: 24px 28px; display: flex; flex-direction: column; gap: 22px; }

        /* ===== HERO HEADER ===== */
        .umanf-hero {
            background: linear-gradient(135deg, #0b3c5d 0%, #1565c0 55%, #1e88e5 100%);
            border-radius: 20px;
            padding: 28px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 12px 32px rgba(11,60,93,.35);
            position: relative;
            overflow: hidden;
        }
        .umanf-hero::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,.06);
            border-radius: 50%;
        }
        .umanf-hero::after {
            content: '';
            position: absolute;
            bottom: -60px; right: 100px;
            width: 160px; height: 160px;
            background: rgba(255,255,255,.04);
            border-radius: 50%;
        }
        .hero-left { display: flex; align-items: center; gap: 18px; }
        .hero-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,.15);
            border-radius: 16px;
            display: grid; place-items: center;
            font-size: 26px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.2);
            flex-shrink: 0;
        }
        .hero-text h2 { color: #fff; font-size: 22px; font-weight: 700; margin: 0 0 4px; }
        .hero-text p  { color: rgba(255,255,255,.75); font-size: 13px; margin: 0; }
        .hero-right { display: flex; gap: 10px; flex-shrink: 0; }

        /* ===== STAT CARDS ===== */
        .stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px 22px;
            display: flex; align-items: center; gap: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,.07);
            border: 1px solid rgba(0,0,0,.04);
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,.11); }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 14px;
            display: grid; place-items: center; font-size: 22px; flex-shrink: 0;
        }
        .stat-icon.blue  { background: #eff6ff; }
        .stat-icon.green { background: #f0fdf4; }
        .stat-icon.amber { background: #fffbeb; }
        .stat-val  { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-lbl  { font-size: 12px; color: #64748b; margin-top: 4px; font-weight: 500; }

        /* ===== TOOLBAR ===== */
        .toolbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap;
        }
        .search-wrap {
            position: relative; flex: 1; min-width: 200px; max-width: 380px;
        }
        .search-wrap input {
            width: 100%; padding: 10px 14px 10px 40px;
            border: 1.5px solid #e2e8f0; border-radius: 12px;
            font-size: 14px; background: #fff; color: #0f172a;
            transition: border-color .2s, box-shadow .2s;
            font-family: 'Inter', sans-serif;
        }
        .search-wrap input:focus { outline: none; border-color: #1e88e5; box-shadow: 0 0 0 3px rgba(30,136,229,.12); }
        .search-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 16px; pointer-events: none;
        }

        /* ===== CARD GRID ===== */
        .meeting-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 18px;
        }
        .meeting-card {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 4px 18px rgba(0,0,0,.07);
            border: 1px solid rgba(0,0,0,.05);
            display: flex; flex-direction: column; gap: 16px;
            transition: transform .2s, box-shadow .2s;
        }
        .meeting-card:hover { transform: translateY(-4px); box-shadow: 0 14px 36px rgba(0,0,0,.12); }

        .mc-top { display: flex; align-items: flex-start; gap: 14px; }
        .mc-avatar {
            width: 46px; height: 46px; border-radius: 14px;
            background: linear-gradient(135deg, #1565c0, #1e88e5);
            display: grid; place-items: center; font-size: 20px;
            color: white; flex-shrink: 0;
        }
        .mc-title { font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1.4; }
        .mc-num  { font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 2px; }

        /* doc badges */
        .doc-badges { display: flex; flex-wrap: wrap; gap: 7px; }
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 600;
            text-decoration: none; transition: opacity .15s;
        }
        .badge:hover { opacity: .82; }
        .badge-ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-no  { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }
        .badge-foto { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .badge-ok  .badge-dot { background: #16a34a; }
        .badge-no  .badge-dot { background: #cbd5e1; }
        .badge-foto .badge-dot { background: #3b82f6; }

        /* card footer actions */
        .mc-actions { display: flex; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 2px; }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 9px 18px; border: none; border-radius: 11px;
            font-size: 13px; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center;
            gap: 6px; cursor: pointer; transition: all .2s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1565c0, #1e88e5);
            color: white; box-shadow: 0 4px 12px rgba(30,136,229,.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(30,136,229,.4); }
        .btn-success {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white; box-shadow: 0 4px 12px rgba(22,163,74,.3);
        }
        .btn-success:hover { transform: translateY(-2px); }
        .btn-ghost {
            background: #f1f5f9; color: #475569;
        }
        .btn-ghost:hover { background: #e2e8f0; }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
        }
        .btn-danger:hover { transform: translateY(-2px); }
        .btn-sm { padding: 7px 13px; font-size: 12px; border-radius: 9px; }
        .btn-icon { padding: 8px; aspect-ratio: 1; justify-content: center; border-radius: 10px; }

        /* Back btn in hero */
        .btn-back {
            background: rgba(255,255,255,.15);
            color: white; border: 1px solid rgba(255,255,255,.25);
            backdrop-filter: blur(6px);
        }
        .btn-back:hover { background: rgba(255,255,255,.25); }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center; padding: 60px 20px;
            background: #fff; border-radius: 18px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
        }
        .empty-state .es-icon { font-size: 52px; margin-bottom: 14px; }
        .empty-state h4 { font-size: 18px; color: #334155; margin: 0 0 8px; }
        .empty-state p  { font-size: 14px; color: #94a3b8; margin: 0 0 22px; }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,.55); backdrop-filter: blur(4px);
            justify-content: center; align-items: center;
            z-index: 1000; padding: 16px;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: 22px;
            width: 100%; max-width: 540px;
            box-shadow: 0 30px 70px rgba(0,0,0,.22);
            overflow: hidden; animation: slideUp .25s ease;
            max-height: 90vh; overflow-y: auto;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .modal-header {
            padding: 22px 28px 18px;
            background: linear-gradient(135deg, #0b3c5d, #1565c0);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-header h3 { color: white; margin: 0; font-size: 17px; }
        .btn-close {
            width: 32px; height: 32px; border-radius: 50%; border: none;
            background: rgba(255,255,255,.15); color: white;
            cursor: pointer; font-size: 18px; display: grid;
            place-items: center; transition: background .2s;
        }
        .btn-close:hover { background: rgba(255,255,255,.3); }
        .modal-body { padding: 26px 28px; display: flex; flex-direction: column; gap: 16px; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 6px;
        }
        /* upload-field juga <label> — paksa kembali ke flex */
        label.upload-field {
            display: flex !important;
            font-size: inherit; font-weight: inherit;
            color: inherit; margin-bottom: 0;
        }
        .form-group .req { color: #ef4444; }
        .form-group input[type="text"] {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e5e7eb; border-radius: 11px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s; background: #fafafa;
        }
        .form-group input[type="text"]:focus {
            outline: none; border-color: #1e88e5;
            box-shadow: 0 0 0 3px rgba(30,136,229,.1); background: #fff;
        }
        .upload-field {
            border: 1.5px solid #e5e7eb; border-radius: 11px;
            padding: 11px 14px; background: #fafafa;
            align-items: center; gap: 13px;
            transition: border-color .2s, background .2s; cursor: pointer;
        }
        .upload-field:hover { border-color: #93c5fd; background: #eff6ff; }
        .upload-field.has-file { border-color: #86efac; background: #f0fdf4; }
        .upload-field input[type="file"] { display: none; }
        .upload-thumb {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            background: #eff6ff; display: grid; place-items: center;
            font-size: 16px; line-height: 1; transition: background .2s;
        }
        .upload-field.has-file .upload-thumb { background: #dcfce7; }
        .upload-info { flex: 1; min-width: 0; }
        .upload-lbl { font-size: 13px; font-weight: 600; color: #374151;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .upload-hint { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .upload-chosen { font-size: 11.5px; color: #16a34a; font-weight: 600;
            margin-top: 3px; display: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .upload-btn-lbl {
            padding: 6px 14px; border-radius: 8px;
            background: linear-gradient(135deg, #1565c0, #1e88e5);
            color: white; font-size: 12px; font-weight: 600;
            cursor: pointer; flex-shrink: 0; white-space: nowrap;
        }
        .modal-footer {
            padding: 18px 28px; border-top: 1px solid #f1f5f9;
            display: flex; gap: 10px; justify-content: flex-end;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .umanf-wrap { padding: 14px 15px; gap: 16px; }
            .stat-row { grid-template-columns: 1fr 1fr; }
            .meeting-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .umanf-hero { flex-direction: column; align-items: flex-start; gap: 14px; }
            .hero-right { width: 100%; }
            .hero-right .btn { flex: 1; justify-content: center; }
            .stat-row { grid-template-columns: 1fr; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .search-wrap { max-width: 100%; }
            .modal-header, .modal-body, .modal-footer { padding-left: 18px; padding-right: 18px; }
        }
    </style>
</head>

<body>
<?php if (isset($_GET['success'])): ?>
    <script>
        alert('✅ Data berhasil disimpan');
        window.history.replaceState({}, document.title, 'umanf.php');
    </script>
<?php elseif (isset($_GET['updated'])): ?>
    <script>
        alert('✅ Data berhasil diperbarui');
        window.history.replaceState({}, document.title, 'umanf.php');
    </script>
<?php elseif (isset($_GET['error'])): ?>
    <script>
        alert('❌ Proses gagal');
        window.history.replaceState({}, document.title, 'umanf.php');
    </script>
<?php endif; ?>

<div class="layout">
    <?php include_once '../sidebar.php'; ?>
    <main>
        <?php include_once '../topbar.php'; ?>

        <div class="umanf-wrap">

            <!-- HERO -->
            <div class="umanf-hero">
                <div class="hero-left">
                    <div class="hero-icon">📋</div>
                    <div class="hero-text">
                        <h2>UmanF – Dokumen Rapat</h2>
                        <p>Kelola undangan, materi, absensi, notulen & dokumentasi foto rapat PPI PHBW</p>
                    </div>
                </div>
                <div class="hero-right">
                    <a href="/dashboard.php" class="btn btn-back">🏠 Dashboard</a>
                </div>
            </div>

            <!-- STATS -->
            <div class="stat-row">
                <div class="stat-card">
                    <div class="stat-icon blue">📋</div>
                    <div>
                        <div class="stat-val"><?= $total ?></div>
                        <div class="stat-lbl">Total Rapat</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">📁</div>
                    <div>
                        <div class="stat-val"><?= $totalDocs ?></div>
                        <div class="stat-lbl">File Dokumen</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber">📷</div>
                    <div>
                        <div class="stat-val"><?= $totalFoto ?></div>
                        <div class="stat-lbl">Foto Dokumentasi</div>
                    </div>
                </div>
            </div>

            <!-- TOOLBAR -->
            <div class="toolbar">
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Cari jenis rapat…">
                </div>
                <button class="btn btn-success" id="btnTambah">
                    <span>＋</span> Tambah Data Rapat
                </button>
            </div>

            <!-- CARD GRID -->
            <?php if (count($allRows) === 0): ?>
            <div class="empty-state">
                <div class="es-icon">📂</div>
                <h4>Belum ada data rapat</h4>
                <p>Klik tombol "Tambah Data Rapat" untuk mulai mengelola dokumen rapat.</p>
                <button class="btn btn-success" id="btnTambahEmpty">＋ Tambah Data Rapat</button>
            </div>
            <?php else: ?>
            <?php
            function docBadge($file, $label, $icon) {
                if (!empty($file)) {
                    $url = str_replace('../', '/', $file);
                    return "<a href='$url' target='_blank' class='badge badge-ok'><span class='badge-dot'></span>$icon $label</a>";
                }
                return "<span class='badge badge-no'><span class='badge-dot'></span>$icon $label</span>";
            }
            ?>
            <div class="meeting-grid" id="meetingGrid">
                <?php foreach ($allRows as $i => $r):
                    $fotos = !empty($r['file_foto']) ? json_decode($r['file_foto'], true) : [];
                    $jumlahFoto = count($fotos);
                ?>
                <div class="meeting-card" data-title="<?= strtolower(htmlspecialchars($r['jenis_rapat'])) ?>">
                    <div class="mc-top">
                        <div class="mc-avatar">🗂️</div>
                        <div>
                            <div class="mc-title"><?= htmlspecialchars($r['jenis_rapat']) ?></div>
                            <div class="mc-num">Rapat #<?= $r['id'] ?></div>
                        </div>
                    </div>

                    <div class="doc-badges">
                        <?= docBadge($r['file_undangan'], 'Undangan', '📄') ?>
                        <?= docBadge($r['file_materi'],   'Materi',   '🧾') ?>
                        <?= docBadge($r['file_absensi'],  'Absensi',  '👥') ?>
                        <?= docBadge($r['file_notulen'],  'Notulen',  '🖋️') ?>
                        <?php if ($jumlahFoto > 0): ?>
                            <span class="badge badge-foto"><span class="badge-dot"></span>📷 <?= $jumlahFoto ?> Foto</span>
                        <?php else: ?>
                            <span class="badge badge-no"><span class="badge-dot"></span>📷 Foto</span>
                        <?php endif; ?>
                    </div>

                    <div class="mc-actions">
                        <a href="umanf_view.php?id=<?= $r['id'] ?>" class="btn btn-primary btn-sm" style="flex:1; justify-content:center;">
                            🔍 Lihat Detail
                        </a>
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm"
                            onclick="openEditModal(this)"
                            data-id="<?= (int)$r['id'] ?>"
                            data-jenis="<?= htmlspecialchars($r['jenis_rapat'], ENT_QUOTES) ?>">
                            ✏️ Edit
                        </button>
                                <a href="?hapus=<?= (int) $r['id'] ?>&csrf=<?= urlencode($csrfToken) ?>" onclick="return confirm('Hapus data rapat ini beserta semua file?')"
                           class="btn btn-danger btn-sm btn-icon" title="Hapus">🗑️</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /umanf-wrap -->

        <!-- ===== MODAL FORM ===== -->
        <div class="modal-overlay" id="formOverlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>📋 Tambah Data Rapat</h3>
                    <button class="btn-close" id="btnBatal" aria-label="Tutup">✕</button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Jenis Rapat <span class="req">*</span></label>
                            <input type="text" name="jenis_rapat" required placeholder="Contoh: Rapat Evaluasi Bulanan PPI">
                        </div>

                        <?php
                        $uploadFields = [
                            ['file_undangan', '📄', 'Undangan',  'PDF, JPG, PNG'],
                            ['file_materi',   '🧾', 'Materi',    'PDF, JPG, PNG'],
                            ['file_absensi',  '👥', 'Absensi',   'PDF, JPG, PNG'],
                            ['file_notulen',  '🖋️', 'Notulen',   'PDF, JPG, PNG'],
                        ];
                        foreach ($uploadFields as [$name, $icon, $lbl, $hint]):
                        ?>
                        <div class="form-group">
                            <label><?= $icon ?> <?= $lbl ?></label>
                            <label class="upload-field" id="lbl_<?= $name ?>">
                                <input type="file" name="<?= $name ?>" accept=".pdf,.jpg,.jpeg,.png"
                                       onchange="markUpload(this,'lbl_<?= $name ?>')">
                                <span class="upload-thumb"><?= $icon ?></span>
                                <div class="upload-info">
                                    <div class="upload-lbl">Pilih file <?= $lbl ?></div>
                                    <div class="upload-hint"><?= $hint ?> — maks 5MB</div>
                                    <div class="upload-chosen" id="chosen_<?= $name ?>"></div>
                                </div>
                                <span class="upload-btn-lbl">Browse</span>
                            </label>
                        </div>
                        <?php endforeach; ?>

                        <div class="form-group">
                            <label>🖼️ Foto Dokumentasi <small style="color:#94a3b8;font-weight:400">(boleh lebih dari 1)</small></label>
                            <label class="upload-field" id="lbl_file_foto">
                                <input type="file" name="file_foto[]" accept=".jpg,.jpeg,.png" multiple
                                       onchange="markUpload(this,'lbl_file_foto')">
                                <span class="upload-thumb">🖼️</span>
                                <div class="upload-info">
                                    <div class="upload-lbl">Pilih foto dokumentasi</div>
                                    <div class="upload-hint">JPG, PNG — boleh pilih banyak sekaligus</div>
                                    <div class="upload-chosen" id="chosen_file_foto"></div>
                                </div>
                                <span class="upload-btn-lbl">Browse</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" id="btnBatalFooter">Batal</button>
                        <button type="submit" name="simpan" class="btn btn-success">💾 Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===== MODAL EDIT ===== -->
        <div class="modal-overlay" id="editOverlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>✏️ Edit Data Rapat</h3>
                    <button class="btn-close" id="btnEditClose" type="button" aria-label="Tutup">✕</button>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" id="edit_id">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Jenis Rapat <span class="req">*</span></label>
                            <input type="text" name="jenis_rapat" id="edit_jenis_rapat" required>
                        </div>

                        <div class="form-group">
                            <label>📄 Undangan</label>
                            <input type="file" name="file_undangan" accept=".pdf,.jpg,.jpeg,.png">
                            <small style="color:#64748b;">Upload jika ingin mengganti file lama</small>
                        </div>

                        <div class="form-group">
                            <label>🧾 Materi</label>
                            <input type="file" name="file_materi" accept=".pdf,.jpg,.jpeg,.png">
                            <small style="color:#64748b;">Upload jika ingin mengganti file lama</small>
                        </div>

                        <div class="form-group">
                            <label>👥 Absensi</label>
                            <input type="file" name="file_absensi" accept=".pdf,.jpg,.jpeg,.png">
                            <small style="color:#64748b;">Upload jika ingin mengganti file lama</small>
                        </div>

                        <div class="form-group">
                            <label>🖋️ Notulen</label>
                            <input type="file" name="file_notulen" accept=".pdf,.jpg,.jpeg,.png">
                            <small style="color:#64748b;">Upload jika ingin mengganti file lama</small>
                        </div>

                        <div class="form-group">
                            <label>🖼️ Tambah Foto Dokumentasi</label>
                            <input type="file" name="file_foto[]" accept=".jpg,.jpeg,.png" multiple>
                            <small style="color:#64748b;">Foto baru akan ditambahkan ke foto lama</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" id="btnEditBatal">Batal</button>
                        <button type="submit" name="update" class="btn btn-success">💾 Update Data</button>
                    </div>
                </form>
            </div>
        </div>

    </main>
</div>

<script src="<?= asset('assets/js/utama.js') ?>"></script>
<script>
    // Modal
    const overlay  = document.getElementById('formOverlay');
    const editOverlay = document.getElementById('editOverlay');
    const openModal = () => overlay.classList.add('show');
    const closeModal = () => overlay.classList.remove('show');
    const closeEditModal = () => editOverlay.classList.remove('show');

    document.getElementById('btnTambah').onclick = openModal;
    document.getElementById('btnBatal').onclick   = closeModal;
    document.getElementById('btnBatalFooter').onclick = closeModal;
    overlay.onclick = e => { if (e.target === overlay) closeModal(); };

    document.getElementById('btnEditClose').onclick = closeEditModal;
    document.getElementById('btnEditBatal').onclick = closeEditModal;
    editOverlay.onclick = e => { if (e.target === editOverlay) closeEditModal(); };

    function openEditModal(btn) {
        document.getElementById('edit_id').value = btn.dataset.id || '';
        document.getElementById('edit_jenis_rapat').value = btn.dataset.jenis || '';
        editOverlay.classList.add('show');
    }

    const btnEmpty = document.getElementById('btnTambahEmpty');
    if (btnEmpty) btnEmpty.onclick = openModal;

    // Upload label feedback
    function markUpload(input, lblId) {
        const lbl    = document.getElementById(lblId);
        const nameId = 'chosen_' + input.name.replace('[]','').trim();
        const chosen = document.getElementById(nameId);
        if (input.files.length > 0) {
            lbl.classList.add('has-file');
            if (chosen) {
                chosen.style.display = 'block';
                chosen.textContent = input.files.length > 1
                    ? input.files.length + ' file dipilih'
                    : input.files[0].name;
            }
        } else {
            lbl.classList.remove('has-file');
            if (chosen) chosen.style.display = 'none';
        }
    }

    // Search
    document.getElementById('searchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.meeting-card').forEach(card => {
            card.style.display = card.dataset.title.includes(q) ? '' : 'none';
        });
    });
</script>
</body>
</html>