<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;
require_once __DIR__ . '/diklat_helpers.php';

$csrfToken = csrf_token();
$pageTitle = "DIKLAT PPI";
$projectParent = dirname(__DIR__);
$allowedUploadMap = [
    'pdf' => ['application/pdf', 'application/x-pdf'],
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png' => ['image/png'],
];

/**
 * Cek apakah kolom ada pada tabel (kompatibel DB lama).
 */
function ppi_db_has_column(mysqli $conn, string $table, string $column): bool
{
    $tableEsc = mysqli_real_escape_string($conn, $table);
    $colEsc = mysqli_real_escape_string($conn, $column);
    $sql = "SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$colEsc}'";
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

$hasTanggalDiklatCol = ppi_db_has_column($conn, 'tb_uman_diklat', 'tanggal_diklat');
$hasUploadRelDirCol = ppi_db_has_column($conn, 'tb_uman_diklat', 'upload_rel_dir');

// ============ SIMPAN DATA ============
if (isset($_POST['simpan'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf();
    }

    $namaDiklat = trim($_POST['nama_diklat'] ?? '');
    $tanggalDiklat = trim($_POST['tanggal_diklat'] ?? '');
    if ($tanggalDiklat === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalDiklat)) {
        echo "<script>alert('❌ Tanggal diklat wajib diisi dengan format yang benar.');window.location='uman_diklat.php';</script>";
        exit;
    }

    $uploadsReal = realpath($projectParent . '/uploads');
    if ($uploadsReal === false) {
        echo "<script>alert('❌ Folder uploads tidak ditemukan.');window.location='uman_diklat.php';</script>";
        exit;
    }

    mysqli_begin_transaction($conn);

    if ($hasTanggalDiklatCol && $hasUploadRelDirCol) {
        $insertStmt = mysqli_prepare($conn, "INSERT INTO tb_uman_diklat 
            (nama_diklat, tanggal_diklat, upload_rel_dir, file_undangan, file_materi, file_absensi, file_pretest, file_posttest, file_sertifikat, file_foto)
            VALUES (?, ?, '', '', '', '', '', '', '', '[]')");
        mysqli_stmt_bind_param($insertStmt, "ss", $namaDiklat, $tanggalDiklat);
    } elseif ($hasTanggalDiklatCol) {
        $insertStmt = mysqli_prepare($conn, "INSERT INTO tb_uman_diklat 
            (nama_diklat, tanggal_diklat, file_undangan, file_materi, file_absensi, file_pretest, file_posttest, file_sertifikat, file_foto)
            VALUES (?, ?, '', '', '', '', '', '', '[]')");
        mysqli_stmt_bind_param($insertStmt, "ss", $namaDiklat, $tanggalDiklat);
    } else {
        $insertStmt = mysqli_prepare($conn, "INSERT INTO tb_uman_diklat 
            (nama_diklat, file_undangan, file_materi, file_absensi, file_pretest, file_posttest, file_sertifikat, file_foto)
            VALUES (?, '', '', '', '', '', '', '[]')");
        mysqli_stmt_bind_param($insertStmt, "s", $namaDiklat);
    }
    if (!mysqli_stmt_execute($insertStmt)) {
        mysqli_rollback($conn);
        mysqli_stmt_close($insertStmt);
        header("Location: uman_diklat.php?error=1");
        exit;
    }
    $newId = (int) mysqli_insert_id($conn);
    mysqli_stmt_close($insertStmt);

    $uploadRel = ppi_diklat_upload_rel_dir($tanggalDiklat, $namaDiklat, $newId);
    $targetDir = ppi_diklat_abs_upload_dir($projectParent, $uploadRel);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
        mysqli_rollback($conn);
        echo "<script>alert('❌ Gagal membuat folder upload.');window.location='uman_diklat.php';</script>";
        exit;
    }

    $fields = ['undangan', 'materi', 'absensi', 'pretest', 'posttest', 'sertifikat'];
    $uploaded = [];
    $createdFiles = [];
    foreach ($fields as $f) {
        $uploaded[$f] = '';
        if (!empty($_FILES["file_$f"]['name'])) {
            $uploadError = '';
            $stored = ppi_store_uploaded_file($_FILES["file_$f"], $targetDir, $allowedUploadMap, $uploadError);
            if ($stored === false) {
                foreach ($createdFiles as $createdFile) {
                    ppi_unlink_upload($createdFile, $uploadsReal);
                }
                mysqli_rollback($conn);
                echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='uman_diklat.php';</script>";
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
                        ppi_unlink_upload($createdFile, $uploadsReal);
                    }
                    mysqli_rollback($conn);
                    echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='uman_diklat.php';</script>";
                    exit;
                }
                $fotoArr[] = $stored;
                $createdFiles[] = $stored;
            }
        }
    }
    $fotoJson = json_encode($fotoArr);

    if ($hasUploadRelDirCol) {
        $updateStmt = mysqli_prepare($conn, "UPDATE tb_uman_diklat SET
                upload_rel_dir = ?,
                file_undangan = ?, file_materi = ?, file_absensi = ?, file_pretest = ?, file_posttest = ?, file_sertifikat = ?, file_foto = ?
            WHERE id = ?");
        mysqli_stmt_bind_param(
            $updateStmt,
            "ssssssssi",
            $uploadRel,
            $uploaded['undangan'],
            $uploaded['materi'],
            $uploaded['absensi'],
            $uploaded['pretest'],
            $uploaded['posttest'],
            $uploaded['sertifikat'],
            $fotoJson,
            $newId
        );
    } else {
        $updateStmt = mysqli_prepare($conn, "UPDATE tb_uman_diklat SET
                file_undangan = ?, file_materi = ?, file_absensi = ?, file_pretest = ?, file_posttest = ?, file_sertifikat = ?, file_foto = ?
            WHERE id = ?");
        mysqli_stmt_bind_param(
            $updateStmt,
            "sssssssi",
            $uploaded['undangan'],
            $uploaded['materi'],
            $uploaded['absensi'],
            $uploaded['pretest'],
            $uploaded['posttest'],
            $uploaded['sertifikat'],
            $fotoJson,
            $newId
        );
    }
    $okUp = mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);

    if ($okUp) {
        mysqli_commit($conn);
        header("Location: uman_diklat.php?success=1");
        exit;
    }

    foreach ($createdFiles as $createdFile) {
        ppi_unlink_upload($createdFile, $uploadsReal);
    }
    mysqli_rollback($conn);
    header("Location: uman_diklat.php?error=1");
    exit;
}

// ============ UPDATE DATA (LENGKAPI BERTAHAP) ============
if (isset($_POST['update'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf();
    }

    $id = intval($_POST['id'] ?? 0);
    $namaDiklat = trim($_POST['nama_diklat'] ?? '');
    $tanggalDiklat = trim($_POST['tanggal_diklat'] ?? '');
    if ($tanggalDiklat === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalDiklat)) {
        echo "<script>alert('❌ Tanggal diklat tidak valid.');window.location='uman_diklat.php';</script>";
        exit;
    }

    $uploadsReal = realpath($projectParent . '/uploads');
    if ($uploadsReal === false) {
        echo "<script>alert('❌ Folder uploads tidak ditemukan.');window.location='uman_diklat.php';</script>";
        exit;
    }

    $oldStmt = mysqli_prepare($conn, "SELECT * FROM tb_uman_diklat WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($oldStmt, "i", $id);
    mysqli_stmt_execute($oldStmt);
    $oldQ = mysqli_stmt_get_result($oldStmt);
    $old = mysqli_fetch_assoc($oldQ);
    mysqli_stmt_close($oldStmt);

    if (!$old) {
        header("Location: uman_diklat.php?error=1");
        exit;
    }

    $targetDir = ppi_diklat_resolve_target_dir($old, $projectParent);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
        echo "<script>alert('❌ Folder upload tidak tersedia.');window.location='uman_diklat.php';</script>";
        exit;
    }

    $fieldsMap = [
        'undangan' => 'file_undangan',
        'materi' => 'file_materi',
        'absensi' => 'file_absensi',
        'pretest' => 'file_pretest',
        'posttest' => 'file_posttest',
        'sertifikat' => 'file_sertifikat',
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
                    ppi_unlink_upload($createdFile, $uploadsReal);
                }
                echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='uman_diklat.php';</script>";
                exit;
            }

            if (!empty($old[$dbField])) {
                ppi_unlink_upload($old[$dbField], $uploadsReal);
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
                        ppi_unlink_upload($createdFile, $uploadsReal);
                    }
                    echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='uman_diklat.php';</script>";
                    exit;
                }
                $oldFoto[] = $stored;
                $createdFiles[] = $stored;
            }
        }
    }
    $fotoJson = json_encode($oldFoto);

    if ($hasTanggalDiklatCol) {
        $updateStmt = mysqli_prepare($conn, "UPDATE tb_uman_diklat SET
                nama_diklat      = ?,
                tanggal_diklat   = ?,
                file_undangan    = ?,
                file_materi      = ?,
                file_absensi     = ?,
                file_pretest     = ?,
                file_posttest    = ?,
                file_sertifikat  = ?,
                file_foto        = ?
            WHERE id = ?");
        mysqli_stmt_bind_param(
            $updateStmt,
            "sssssssssi",
            $namaDiklat,
            $tanggalDiklat,
            $updated['file_undangan'],
            $updated['file_materi'],
            $updated['file_absensi'],
            $updated['file_pretest'],
            $updated['file_posttest'],
            $updated['file_sertifikat'],
            $fotoJson,
            $id
        );
    } else {
        $updateStmt = mysqli_prepare($conn, "UPDATE tb_uman_diklat SET
                nama_diklat      = ?,
                file_undangan    = ?,
                file_materi      = ?,
                file_absensi     = ?,
                file_pretest     = ?,
                file_posttest    = ?,
                file_sertifikat  = ?,
                file_foto        = ?
            WHERE id = ?");
        mysqli_stmt_bind_param(
            $updateStmt,
            "ssssssssi",
            $namaDiklat,
            $updated['file_undangan'],
            $updated['file_materi'],
            $updated['file_absensi'],
            $updated['file_pretest'],
            $updated['file_posttest'],
            $updated['file_sertifikat'],
            $fotoJson,
            $id
        );
    }
    $update = mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
    if ($update) {
        header("Location: uman_diklat.php?updated=1");
        exit;
    }

    foreach ($createdFiles as $createdFile) {
        ppi_unlink_upload($createdFile, $uploadsReal);
    }

    header("Location: uman_diklat.php?error=1");
    exit;
}

// ============ HAPUS DATA ============
if (isset($_GET['hapus'])) {
    if (!csrf_validate($_GET['csrf'] ?? '')) {
        ppi_abort_csrf();
    }

    $id = intval($_GET['hapus']);
    $selectStmt = mysqli_prepare($conn, "SELECT * FROM tb_uman_diklat WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($selectStmt, "i", $id);
    mysqli_stmt_execute($selectStmt);
    $q = mysqli_stmt_get_result($selectStmt);
    $d = mysqli_fetch_assoc($q);
    mysqli_stmt_close($selectStmt);
    $uploadsRealDel = realpath($projectParent . '/uploads');
    if ($uploadsRealDel !== false) {
        foreach (['file_undangan', 'file_materi', 'file_absensi', 'file_pretest', 'file_posttest', 'file_sertifikat'] as $f) {
            if (!empty($d[$f])) {
                ppi_unlink_upload($d[$f], $uploadsRealDel);
            }
        }
        if (!empty($d['file_foto'])) {
            $ff = json_decode($d['file_foto'], true);
            if (is_array($ff)) {
                foreach ($ff as $f) {
                    ppi_unlink_upload($f, $uploadsRealDel);
                }
            }
        }
    }
    $deleteStmt = mysqli_prepare($conn, "DELETE FROM tb_uman_diklat WHERE id = ?");
    mysqli_stmt_bind_param($deleteStmt, "i", $id);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
    $redir = 'uman_diklat.php';
    $rq = [];
    if (isset($_GET['bulan'])) {
        $rq['bulan'] = max(0, min(12, (int) $_GET['bulan']));
    }
    if (isset($_GET['tahun'])) {
        $rq['tahun'] = max(2000, min(2100, (int) $_GET['tahun']));
    }
    if (!empty($_GET['q']) && is_string($_GET['q'])) {
        $rq['q'] = trim($_GET['q']);
    }
    if ($rq !== []) {
        $redir .= '?' . http_build_query($rq);
    }
    header('Location: ' . $redir);
    exit;
}

// ============ AMBIL DATA ============
$filterBulan = max(0, min(12, (int) ($_GET['bulan'] ?? 0)));
$filterTahun = max(2000, min(2100, (int) ($_GET['tahun'] ?? date('Y'))));
$filterQ = trim($_GET['q'] ?? '');

$yrRow = ['y1' => null, 'y2' => null];
$dateExpr = $hasTanggalDiklatCol ? "COALESCE(tanggal_diklat, DATE(created_at))" : "DATE(created_at)";
$selectTanggal = $hasTanggalDiklatCol ? "tanggal_diklat" : "NULL";
$selectUploadRelDir = $hasUploadRelDirCol ? "upload_rel_dir" : "NULL";

$yrQ = mysqli_query($conn, "SELECT MIN(YEAR({$dateExpr})) AS y1, MAX(YEAR({$dateExpr})) AS y2 FROM tb_uman_diklat");
if ($yrQ) {
    $yrRow = mysqli_fetch_assoc($yrQ) ?: $yrRow;
}
$yLo = (int) ($yrRow['y1'] ?? $filterTahun);
$yHi = max((int) ($yrRow['y2'] ?? $filterTahun), 2040);
if ($yLo === 0 || $yLo < 2000) {
    $yLo = (int) date('Y');
}
if ($yHi < $yLo) {
    $yHi = $yLo;
}

$sqlList = "SELECT id, nama_diklat, {$selectTanggal} AS tanggal_diklat, {$selectUploadRelDir} AS upload_rel_dir, file_undangan, file_materi, file_absensi, file_pretest, file_posttest, file_sertifikat, file_foto, created_at
    FROM tb_uman_diklat
    WHERE YEAR({$dateExpr}) = ?";
if ($filterBulan > 0) {
    $sqlList .= " AND MONTH({$dateExpr}) = ?";
}
if ($filterQ !== '') {
    $sqlList .= " AND nama_diklat LIKE ?";
}
$sqlList .= " ORDER BY {$dateExpr} DESC, id DESC";

$listStmt = mysqli_prepare($conn, $sqlList);
if ($filterBulan > 0 && $filterQ !== '') {
    $like = '%' . $filterQ . '%';
    mysqli_stmt_bind_param($listStmt, "iis", $filterTahun, $filterBulan, $like);
} elseif ($filterBulan > 0) {
    mysqli_stmt_bind_param($listStmt, "ii", $filterTahun, $filterBulan);
} elseif ($filterQ !== '') {
    $like = '%' . $filterQ . '%';
    mysqli_stmt_bind_param($listStmt, "is", $filterTahun, $like);
} else {
    mysqli_stmt_bind_param($listStmt, "i", $filterTahun);
}
mysqli_stmt_execute($listStmt);
$listResult = mysqli_stmt_get_result($listStmt);
$allRows = mysqli_fetch_all($listResult, MYSQLI_ASSOC);
mysqli_stmt_close($listStmt);

$groupedByDate = [];
foreach ($allRows as $r) {
    $dk = ppi_diklat_sort_date_ymd($r);
    $groupedByDate[$dk][] = $r;
}
krsort($groupedByDate, SORT_STRING);

$bulanJudul = $filterBulan > 0
    ? ppi_diklat_label_bulan_tahun($filterBulan, $filterTahun)
    : ('Tahun ' . $filterTahun);

$persistParams = [
    'bulan' => $filterBulan,
    'tahun' => $filterTahun,
];
if ($filterQ !== '') {
    $persistParams['q'] = $filterQ;
}
$persistQuery = http_build_query($persistParams);

$total = count($allRows);

$rowTotalDb = mysqli_query($conn, "SELECT COUNT(*) AS c FROM tb_uman_diklat");
$totalAllDb = 0;
if ($rowTotalDb) {
    $totalAllDb = (int) (mysqli_fetch_assoc($rowTotalDb)['c'] ?? 0);
}

// Hitung stats
$totalDocs = 0; $totalFoto = 0;
foreach ($allRows as $r) {
    foreach (['file_undangan','file_materi','file_absensi','file_pretest','file_posttest','file_sertifikat'] as $f) if (!empty($r[$f])) $totalDocs++;
    if (!empty($r['file_foto'])) { $arr = json_decode($r['file_foto'],true); $totalFoto += count($arr); }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uman Diklat – Dokumen Pelatihan | PPI PHBW</title>
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

        /* toolbar filter + view mode */
        .toolbar-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .toolbar-diklat-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }
        .toolbar-select-wrap select {
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #fff;
            min-width: 150px;
            cursor: pointer;
        }
        .toolbar-select-wrap select:focus {
            outline: none;
            border-color: #1e88e5;
            box-shadow: 0 0 0 3px rgba(30,136,229,.12);
        }
        .view-toggle {
            display: inline-flex;
            border: 1.5px solid #dbeafe;
            border-radius: 14px;
            overflow: hidden;
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            box-shadow: 0 8px 20px rgba(30, 64, 175, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.85);
            padding: 3px;
        }
        .view-toggle button {
            border: none;
            border-radius: 10px;
            background: transparent;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            color: #64748b;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
        }
        .view-toggle button:hover {
            color: #1d4ed8;
            background: rgba(255, 255, 255, 0.75);
            box-shadow: inset 0 0 0 1px rgba(147, 197, 253, 0.65);
            transform: translateY(-1px);
        }
        .view-toggle button.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 60%, #1e40af 100%);
            color: #fff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .view-toggle button.active:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(30, 64, 175, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        .toolbar-actions-row {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .diklat-bulan-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 16px;
            letter-spacing: -0.02em;
        }
        .diklat-grid-wrap {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(520px, 1fr));
            gap: 18px;
            align-items: start;
        }
        .diklat-date-section {
            margin: 0;
            min-width: 0;
        }
        .diklat-date-heading {
            font-size: 0.95rem;
            font-weight: 700;
            color: #334155;
            margin: 0 0 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .diklat-date-section .meeting-grid {
            grid-template-columns: repeat(auto-fill, minmax(360px, 360px));
            gap: 14px;
            justify-content: flex-start;
        }

        .diklat-layout-grid .diklat-list-wrap { display: none; }
        .diklat-layout-list .diklat-grid-wrap { display: none; }
        .diklat-layout-list .diklat-list-wrap { display: block; }

        .diklat-table-wrap {
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
            background: #fff;
        }
        .diklat-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .diklat-table th {
            text-align: left;
            padding: 10px 10px;
            font-weight: 700;
            color: #eff6ff;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 60%, #1e3a8a 100%);
            border-bottom: 1px solid rgba(191, 219, 254, 0.55);
            white-space: nowrap;
            letter-spacing: 0.01em;
        }
        .diklat-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #0f172a;
            line-height: 1.3;
        }
        .diklat-table tbody tr:nth-child(odd) td { background: #f4f8ff; }
        .diklat-table tbody tr:nth-child(even) td { background: #e8f1ff; }
        .diklat-table tbody tr:hover {
            filter: brightness(0.985);
        }
        .diklat-table tbody tr:last-child td {
            border-bottom: none;
        }
        .diklat-table .col-status {
            font-weight: 700;
        }
        .diklat-table .col-status.st-full { color: #047857; }
        .diklat-table .col-status.st-part { color: #475569; }
        .diklat-table .col-status.st-empty { color: #b45309; }
        .diklat-table-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: nowrap;
        }

        /* ===== CARD GRID (premium diklat cards) ===== */
        .meeting-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .meeting-card.card-diklat {
            background: #fff;
            border-radius: 16px;
            padding: 22px 22px 20px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.22);
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .meeting-card.card-diklat:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
            border-color: rgba(59, 130, 246, 0.28);
        }

        .mc-top { display: flex; align-items: flex-start; gap: 14px; }
        .mc-avatar {
            width: 48px; height: 48px; border-radius: 14px;
            background: linear-gradient(145deg, #1565c0 0%, #1e88e5 100%);
            display: grid; place-items: center;
            font-size: 22px;
            line-height: 1;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(30, 136, 229, 0.28);
        }
        .mc-head-text { min-width: 0; flex: 1; }
        .mc-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            letter-spacing: -0.02em;
            margin: 0;
        }
        @media (min-width: 640px) {
            .mc-title { font-size: 1.25rem; }
        }
        .mc-num {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin: 8px 0 0;
            letter-spacing: 0.02em;
            line-height: 1.45;
        }

        /* Status ringkas + progress */
        .mc-status-block {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .mc-status-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .mc-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.2;
            border: 1px solid transparent;
        }
        .mc-pill-doc {
            background: #f8fafc;
            color: #475569;
            border-color: #e2e8f0;
        }
        .mc-pill-doc.mc-pill-doc--full {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }
        .mc-pill-doc.mc-pill-doc--mid {
            background: #f8fafc;
            color: #334155;
            border-color: #cbd5e1;
        }
        .mc-pill-doc.mc-pill-doc--empty {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }
        .mc-pill-photo {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        .mc-pill-photo.mc-pill-photo--none {
            background: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
        }

        .mc-progress-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .mc-progress-track {
            flex: 1;
            height: 8px;
            border-radius: 999px;
            background: #f1f5f9;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .mc-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            transition: width 0.35s ease;
        }
        .meeting-card.card-diklat .mc-progress-fill.mc-progress-fill--warn {
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
        }
        .meeting-card.card-diklat .mc-progress-fill.mc-progress-fill--muted {
            background: linear-gradient(90deg, #94a3b8, #64748b);
        }
        .mc-progress-pct {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            min-width: 38px;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .mc-status-line {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
        }
        .mc-status-line.mc-status-line--full { color: #047857; }
        .mc-status-line.mc-status-line--partial { color: #475569; }
        .mc-status-line.mc-status-line--empty { color: #b45309; }

        /* card footer: primary + icon secondary */
        .mc-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            border-top: 1px solid rgba(241, 245, 249, 0.95);
            padding-top: 18px;
            margin-top: auto;
            flex-wrap: nowrap;
        }
        .mc-btn-detail {
            flex: 1;
            justify-content: center;
            min-width: 0;
            font-weight: 600;
        }
        .mc-actions-icons {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .btn-icon-only {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid #d97706;
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 70%, #ea580c 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 6px 12px rgba(249, 115, 22, 0.3), inset 0 1px 0 rgba(254, 243, 199, 0.32);
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        }
        .btn-icon-only:hover {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 75%, #c2410c 100%);
            border-color: #c2410c;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 9px 16px rgba(234, 88, 12, 0.38), inset 0 1px 0 rgba(254, 243, 199, 0.35);
        }
        .btn-icon-only.btn-icon-del {
            border-color: #dc2626;
            color: #ffffff;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 68%, #b91c1c 100%);
            box-shadow: 0 6px 12px rgba(220, 38, 38, 0.28), inset 0 1px 0 rgba(254, 202, 202, 0.25);
        }
        .btn-icon-only.btn-icon-del:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 70%, #991b1b 100%);
            border-color: #991b1b;
            color: #ffffff;
            box-shadow: 0 9px 16px rgba(153, 27, 27, 0.4), inset 0 1px 0 rgba(254, 202, 202, 0.3);
        }
        .meeting-card.card-diklat:hover .mc-actions .btn-primary {
            box-shadow: 0 6px 18px rgba(30, 136, 229, 0.38);
        }

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
        .btn-sm { padding: 8px 14px; font-size: 13px; border-radius: 9px; }
        .btn-icon { padding: 8px; aspect-ratio: 1; justify-content: center; border-radius: 10px; }

        /* Back btn in hero */
        .btn-back {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            color: #0f172a;
            border: 1px solid rgba(148,163,184,.5);
            box-shadow: 0 10px 22px rgba(15,23,42,.24);
            backdrop-filter: blur(6px);
        }
        .btn-back:hover {
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            color: #020617;
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(15,23,42,.28);
        }

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
        .form-group input[type="text"],
        .form-group input[type="date"] {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e5e7eb; border-radius: 11px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s; background: #fafafa;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus {
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

        /* ===== DARK MODE (SOFT) ===== */
        body.dark-mode main {
            background: linear-gradient(180deg, #0c1a2d 0%, #0f1f35 100%);
        }

        body.dark-mode .umanf-wrap {
            background: linear-gradient(180deg, rgba(20, 35, 58, 0.72), rgba(17, 31, 51, 0.7));
            border: 1.5px solid rgba(59, 130, 246, 0.5);
            border-radius: 20px;
            box-shadow: 0 14px 34px rgba(2, 6, 23, 0.35), inset 0 0 20px rgba(59, 130, 246, 0.12);
        }

        body.dark-mode .stat-card,
        body.dark-mode .meeting-card,
        body.dark-mode .empty-state {
            background: #1b2a40;
            border: 1.5px solid rgba(59, 130, 246, 0.45);
            box-shadow: 0 10px 26px rgba(2, 6, 23, 0.32), inset 0 0 15px rgba(59, 130, 246, 0.08);
        }

        body.dark-mode .stat-val,
        body.dark-mode .mc-title,
        body.dark-mode .empty-state h4 {
            color: #e2e8f0;
        }

        body.dark-mode .stat-lbl,
        body.dark-mode .mc-num,
        body.dark-mode .empty-state p {
            color: #9fb2c9;
        }

        body.dark-mode .search-wrap input {
            background: #1a2a3f;
            border: 1.2px solid rgba(59, 130, 246, 0.4);
            color: #e2e8f0;
        }

        body.dark-mode .search-wrap input::placeholder {
            color: #9fb2c9;
        }

        body.dark-mode .search-wrap input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }

        body.dark-mode .toolbar-select-wrap select {
            background: #1a2a3f;
            border-color: rgba(59, 130, 246, 0.4);
            color: #e2e8f0;
        }
        body.dark-mode .view-toggle {
            border-color: rgba(96, 165, 250, 0.45);
            background: linear-gradient(180deg, #0f1a2a 0%, #152238 100%);
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.45), inset 0 1px 0 rgba(148, 163, 184, 0.08);
        }
        body.dark-mode .view-toggle button {
            color: #b6c8df;
        }
        body.dark-mode .view-toggle button:hover {
            color: #dbeafe;
            background: rgba(37, 99, 235, 0.18);
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.45);
        }
        body.dark-mode .view-toggle button.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 60%, #1d4ed8 100%);
            color: #eff6ff;
            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.45), inset 0 1px 0 rgba(219, 234, 254, 0.35);
        }
        body.dark-mode .diklat-bulan-title {
            color: #e2e8f0;
        }
        body.dark-mode .diklat-date-heading {
            color: #cbd5e1;
            border-bottom-color: #2f435c;
        }
        body.dark-mode .diklat-table-wrap {
            border-color: rgba(59, 130, 246, 0.35);
            background: #1b2a40;
        }
        body.dark-mode .diklat-table th {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 55%, #1e3a8a 100%);
            color: #e2e8f0;
            border-bottom-color: rgba(147, 197, 253, 0.45);
        }
        body.dark-mode .diklat-table td {
            border-bottom-color: #24364e;
            color: #e2e8f0;
        }
        body.dark-mode .diklat-table tbody tr:nth-child(odd) td { background: rgba(30, 58, 138, 0.24); }
        body.dark-mode .diklat-table tbody tr:nth-child(even) td { background: rgba(14, 116, 144, 0.24); }
        body.dark-mode .diklat-table tbody tr:hover {
            filter: brightness(1.07);
        }

        body.dark-mode .mc-actions {
            border-top-color: #2f435c;
        }

        body.dark-mode .mc-progress-track {
            background: #24364e;
            border-color: #38506b;
        }
        body.dark-mode .mc-progress-pct { color: #9fb2c9; }
        body.dark-mode .mc-status-line--partial { color: #cbd5e1; }
        body.dark-mode .mc-status-line--full { color: #86efac; }
        body.dark-mode .mc-status-line--empty { color: #fcd34d; }

        body.dark-mode .mc-pill-doc {
            background: #24364e;
            color: #cbd5e1;
            border-color: #38506b;
        }
        body.dark-mode .mc-pill-doc.mc-pill-doc--full {
            background: #14532d;
            color: #86efac;
            border-color: #166534;
        }
        body.dark-mode .mc-pill-doc.mc-pill-doc--mid {
            background: #24364e;
            color: #e2e8f0;
            border-color: #475569;
        }
        body.dark-mode .mc-pill-doc.mc-pill-doc--empty {
            background: #451a03;
            color: #fcd34d;
            border-color: #92400e;
        }
        body.dark-mode .mc-pill-photo {
            background: #1e3a5f;
            color: #93c5fd;
            border-color: #2563eb;
        }
        body.dark-mode .mc-pill-photo.mc-pill-photo--none {
            background: #24364e;
            color: #9fb2c9;
            border-color: #38506b;
        }

        body.dark-mode .btn-icon-only {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 70%, #ea580c 100%);
            border-color: #fdba74;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(234, 88, 12, 0.42), inset 0 1px 0 rgba(254, 243, 199, 0.26);
        }
        body.dark-mode .btn-icon-only:hover {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 70%, #c2410c 100%);
            border-color: #fed7aa;
            color: #ffffff;
            box-shadow: 0 12px 20px rgba(234, 88, 12, 0.5), inset 0 1px 0 rgba(255, 237, 213, 0.3);
        }
        body.dark-mode .btn-icon-only.btn-icon-del {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 68%, #b91c1c 100%);
            border-color: #f87171;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(153, 27, 27, 0.45), inset 0 1px 0 rgba(254, 202, 202, 0.22);
        }
        body.dark-mode .btn-icon-only.btn-icon-del:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 70%, #991b1b 100%);
            border-color: #fca5a5;
            color: #fff;
            box-shadow: 0 12px 20px rgba(153, 27, 27, 0.5), inset 0 1px 0 rgba(254, 226, 226, 0.3);
        }

        body.dark-mode .btn-ghost {
            background: #24364e;
            color: #cbd5e1;
            border: 1px solid #38506b;
        }

        body.dark-mode .btn-ghost:hover {
            background: #2a3f5b;
        }

        body.dark-mode .umanf-hero .btn-back {
            background: linear-gradient(135deg, #ffffff, #e5ebf3) !important;
            color: #0b1220 !important;
            border-color: rgba(148,163,184,.55) !important;
            box-shadow: 0 10px 22px rgba(15,23,42,.22) !important;
        }

        body.dark-mode .umanf-hero .btn-back:hover {
            background: linear-gradient(135deg, #ffffff, #f1f5f9) !important;
            color: #020617 !important;
        }

        body.dark-mode .modal-box {
            background: #1b2a40;
            border: 1.5px solid rgba(59, 130, 246, 0.4);
            box-shadow: 0 28px 60px rgba(2, 6, 23, 0.52), inset 0 0 20px rgba(59, 130, 246, 0.1);
        }

        body.dark-mode .form-group label,
        body.dark-mode .upload-lbl {
            color: #dbe6f2;
        }

        body.dark-mode .upload-hint {
            color: #9fb2c9;
        }

        body.dark-mode .form-group input[type="text"],
        body.dark-mode .form-group input[type="date"] {
            background: #16253a;
            border: 1.2px solid rgba(59, 130, 246, 0.35);
            color: #e2e8f0;
        }

        body.dark-mode .form-group input[type="text"]:focus,
        body.dark-mode .form-group input[type="date"]:focus {
            background: #1a2d45;
            border: 1.2px solid rgba(96, 165, 250, 0.7);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25), inset 0 0 10px rgba(96, 165, 250, 0.1);
        }

        body.dark-mode .upload-field {
            background: #16253a;
            border: 1.2px solid rgba(59, 130, 246, 0.35);
        }

        body.dark-mode .upload-field:hover {
            background: #1b2f48;
            border: 1.2px solid rgba(59, 130, 246, 0.5);
        }

        body.dark-mode .upload-field.has-file {
            background: #1a3a31;
            border: 1px solid rgba(34, 197, 94, 0.5);
        }

        body.dark-mode .upload-thumb {
            background: #1f3653;
        }

        body.dark-mode .modal-footer {
            border-top-color: #2f435c;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .umanf-wrap { padding: 14px 15px; gap: 16px; }
            .stat-row { grid-template-columns: 1fr 1fr; }
            .meeting-grid { grid-template-columns: 1fr; }
            .diklat-grid-wrap { grid-template-columns: 1fr; }
            .diklat-date-section .meeting-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .umanf-hero { flex-direction: column; align-items: flex-start; gap: 14px; }
            .hero-right { width: 100%; }
            .hero-right .btn { flex: 1; justify-content: center; }
            .stat-row { grid-template-columns: 1fr; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .search-wrap { max-width: 100%; }
            .modal-header, .modal-body, .modal-footer { padding-left: 18px; padding-right: 18px; }
            .mc-actions { flex-wrap: wrap; }
            .mc-btn-detail { flex: 1 1 160px; }
            .mc-actions-icons { margin-left: auto; }
        }
    </style>
</head>

<body>

<?php if (isset($_GET['success'])): ?>
    <script>
        alert('✅ Data berhasil disimpan');
        window.history.replaceState({}, document.title, 'uman_diklat.php');
    </script>
<?php elseif (isset($_GET['updated'])): ?>
    <script>
        alert('✅ Data berhasil diperbarui');
        window.history.replaceState({}, document.title, 'uman_diklat.php');
    </script>
<?php elseif (isset($_GET['error'])): ?>
    <script>
        alert('❌ Proses gagal');
        window.history.replaceState({}, document.title, 'uman_diklat.php');
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
                        <h2>Uman Diklat – Dokumen Pelatihan</h2>
                        <p>Kelola undangan, materi, absensi, pretest, posttest, sertifikat & dokumentasi foto diklat PPI PHBW</p>
                    </div>
                </div>
                <div class="hero-right">
                    <a href="<?= base_url('dashboard.php') ?>" class="btn btn-back">🏠 Dashboard</a>
                </div>
            </div>

            <!-- STATS -->
            <div class="stat-row">
                <div class="stat-card">
                    <div class="stat-icon blue">📋</div>
                    <div>
                        <div class="stat-val"><?= $total ?></div>
                        <div class="stat-lbl">Total Kegiatan Diklat</div>
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

            <!-- TOOLBAR + FILTER -->
            <div class="toolbar-stack">
                <form method="get" action="" class="toolbar-diklat-row" id="filterFormDiklat">
                    <div class="toolbar-select-wrap">
                        <label class="visually-hidden" for="filterBulan">Bulan</label>
                        <select name="bulan" id="filterBulan" onchange="this.form.submit()" aria-label="Filter bulan">
                            <option value="0" <?= (int) $filterBulan === 0 ? 'selected' : '' ?>>Semua bulan</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m === (int) $filterBulan ? 'selected' : '' ?>><?= htmlspecialchars(ppi_diklat_label_bulan_tahun($m, $filterTahun), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="toolbar-select-wrap">
                        <label class="visually-hidden" for="filterTahun">Tahun</label>
                        <select name="tahun" id="filterTahun" onchange="this.form.submit()" aria-label="Filter tahun">
                            <?php for ($y = $yHi; $y >= $yLo; $y--): ?>
                                <option value="<?= $y ?>" <?= $y === (int) $filterTahun ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="search-wrap" style="flex:1; min-width:200px; max-width:380px;">
                        <span class="search-icon">🔍</span>
                        <input type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cari nama diklat…" autocomplete="off" aria-label="Cari nama diklat">
                    </div>
                    <div class="view-toggle" role="group" aria-label="Tampilan">
                        <button type="button" id="btnViewGrid" class="active">Grid</button>
                        <button type="button" id="btnViewList">List</button>
                    </div>
                    <button type="submit" class="btn btn-ghost btn-sm" style="display:none" tabindex="-1" aria-hidden="true">Cari</button>
                </form>
                <div class="toolbar-actions-row">
                    <button class="btn btn-success" id="btnTambah" type="button">
                        <span>＋</span> Tambah Data Diklat
                    </button>
                </div>
            </div>

            <?php if ($totalAllDb === 0): ?>
            <div class="empty-state">
                <div class="es-icon">📂</div>
                <h4>Belum ada data diklat</h4>
                <p>Klik tombol "Tambah Data Diklat" untuk mulai mengelola dokumen pelatihan.</p>
                <button class="btn btn-success" id="btnTambahEmpty" type="button">＋ Tambah Data Diklat</button>
            </div>
            <?php elseif ($total === 0): ?>
            <div class="empty-state">
                <div class="es-icon">📅</div>
                <h4>Tidak ada diklat di <?= htmlspecialchars($bulanJudul, ENT_QUOTES, 'UTF-8') ?></h4>
                <p>Ubah bulan/tahun di atas atau kosongkan pencarian untuk melihat data lain.</p>
                <button class="btn btn-success" id="btnTambahEmptyMonth" type="button">＋ Tambah Data Diklat</button>
            </div>
            <?php else: ?>
            <div id="diklatMainArea" class="diklat-layout-grid">
                <h2 class="diklat-bulan-title"><?= htmlspecialchars($bulanJudul, ENT_QUOTES, 'UTF-8') ?></h2>

                <div class="diklat-grid-wrap">
                    <?php foreach ($groupedByDate as $dateKey => $rowsDate): ?>
                    <section class="diklat-date-section">
                        <h3 class="diklat-date-heading">📅 <?= htmlspecialchars(ppi_diklat_format_tanggal_id($dateKey), ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="meeting-grid">
                            <?php foreach ($rowsDate as $r): ?>
                                <?= ppi_diklat_render_card($r, $csrfToken, $persistQuery) ?>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endforeach; ?>
                </div>

                <div class="diklat-list-wrap">
                    <div class="diklat-table-wrap">
                        <table class="diklat-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama diklat</th>
                                    <th>File</th>
                                    <th>Foto</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allRows as $idx => $r):
                                    $lm = ppi_diklat_row_metrics($r);
                                    $stClass = $lm['jumlahTotal'] === 7 ? 'st-full' : ($lm['jumlahTotal'] === 0 ? 'st-empty' : 'st-part');
                                    $hapusRow = htmlspecialchars('?' . ppi_diklat_hapus_query($r, $csrfToken, $persistQuery), ENT_QUOTES, 'UTF-8');
                                    $tanggalTampil = htmlspecialchars(ppi_diklat_format_tanggal_id(ppi_diklat_sort_date_ymd($r)), ENT_QUOTES, 'UTF-8');
                                    $namaAttr = htmlspecialchars($r['nama_diklat'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $tanggalIso = htmlspecialchars(ppi_diklat_sort_date_ymd($r), ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td><strong><?= (int) ($idx + 1) ?></strong></td>
                                    <td><?= $tanggalTampil ?></td>
                                    <td><strong><?= htmlspecialchars($r['nama_diklat'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= (int) $lm['jumlahDok'] ?>/6</td>
                                    <td><?= (int) $lm['jumlahFoto'] ?></td>
                                    <td class="col-status <?= $stClass ?>"><?= htmlspecialchars($lm['statusLabel'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="diklat-table-actions">
                                            <a href="uman_diklat_view.php?id=<?= (int) $r['id'] ?>" class="btn btn-primary btn-sm">Detail</a>
                                            <button type="button" class="btn-icon-only" onclick="openEditModal(this)" data-id="<?= (int) $r['id'] ?>" data-nama="<?= $namaAttr ?>" data-tanggal="<?= $tanggalIso ?>" title="Edit">&#9998;</button>
                                            <a href="<?= $hapusRow ?>" class="btn-icon-only btn-icon-del" onclick="return confirm('Hapus data diklat ini beserta semua file?')" title="Hapus">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /umanf-wrap -->

        <!-- ===== MODAL FORM ===== -->
        <div class="modal-overlay" id="formOverlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>📋 Tambah Data Diklat</h3>
                    <button class="btn-close" id="btnBatal" aria-label="Tutup">✕</button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Diklat <span class="req">*</span></label>
                            <input type="text" name="nama_diklat" required placeholder="Contoh: Pelatihan PPI Dasar Batch 3">
                        </div>

                        <div class="form-group">
                            <label>📅 Tanggal diklat <span class="req">*</span></label>
                            <input type="date" name="tanggal_diklat" required value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                            <small style="color:#64748b;">Digunakan untuk filter bulan dan nama folder upload.</small>
                        </div>

                        <?php
                        $uploadFields = [
                            ['file_undangan',  '📄', 'Undangan',    'PDF, JPG, PNG'],
                            ['file_materi',    '🧾', 'Materi',      'PDF, JPG, PNG'],
                            ['file_absensi',   '👥', 'Absensi',     'PDF, JPG, PNG'],
                            ['file_pretest',   '📝', 'Pretest',     'PDF, JPG, PNG'],
                            ['file_posttest',  '📋', 'Posttest',    'PDF, JPG, PNG'],
                            ['file_sertifikat','🏅', 'Sertifikat',  'PDF, JPG, PNG'],
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
                    <h3>✏️ Edit Data Diklat</h3>
                    <button class="btn-close" id="btnEditClose" type="button" aria-label="Tutup">✕</button>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" id="edit_id">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Diklat <span class="req">*</span></label>
                            <input type="text" name="nama_diklat" id="edit_nama_diklat" required>
                        </div>

                        <div class="form-group">
                            <label>📅 Tanggal diklat <span class="req">*</span></label>
                            <input type="date" name="tanggal_diklat" id="edit_tanggal_diklat" required>
                            <small style="color:#64748b;">Sesuaikan jika jadwal diklat berubah (path file tetap mengikuti folder saat upload).</small>
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
                            <label>📝 Pretest</label>
                            <input type="file" name="file_pretest" accept=".pdf,.jpg,.jpeg,.png">
                            <small style="color:#64748b;">Upload jika ingin mengganti file lama</small>
                        </div>

                        <div class="form-group">
                            <label>📋 Posttest</label>
                            <input type="file" name="file_posttest" accept=".pdf,.jpg,.jpeg,.png">
                            <small style="color:#64748b;">Upload jika ingin mengganti file lama</small>
                        </div>

                        <div class="form-group">
                            <label>🏅 Sertifikat</label>
                            <input type="file" name="file_sertifikat" accept=".pdf,.jpg,.jpeg,.png">
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
        document.getElementById('edit_nama_diklat').value = btn.dataset.nama || '';
        const tgl = document.getElementById('edit_tanggal_diklat');
        if (tgl) tgl.value = btn.dataset.tanggal || '';
        editOverlay.classList.add('show');
    }

    const btnEmpty = document.getElementById('btnTambahEmpty');
    if (btnEmpty) btnEmpty.onclick = openModal;
    const btnEmptyMonth = document.getElementById('btnTambahEmptyMonth');
    if (btnEmptyMonth) btnEmptyMonth.onclick = openModal;

    const diklatMainArea = document.getElementById('diklatMainArea');
    const btnViewGrid = document.getElementById('btnViewGrid');
    const btnViewList = document.getElementById('btnViewList');
    if (diklatMainArea && btnViewGrid && btnViewList) {
        const STORAGE_VIEW = 'diklat_view_mode';
        function applyDiklatView(mode) {
            const listMode = mode === 'list';
            diklatMainArea.classList.toggle('diklat-layout-grid', !listMode);
            diklatMainArea.classList.toggle('diklat-layout-list', listMode);
            btnViewGrid.classList.toggle('active', !listMode);
            btnViewList.classList.toggle('active', listMode);
            try { localStorage.setItem(STORAGE_VIEW, listMode ? 'list' : 'grid'); } catch (e) {}
        }
        let saved = 'grid';
        try { saved = localStorage.getItem(STORAGE_VIEW) || 'grid'; } catch (e) {}
        applyDiklatView(saved === 'list' ? 'list' : 'grid');
        btnViewGrid.addEventListener('click', () => applyDiklatView('grid'));
        btnViewList.addEventListener('click', () => applyDiklatView('list'));
    }

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

    // Pencarian nama diklat lewat form GET (Enter atau ubah bulan/tahun).
</script>
</body>
</html>