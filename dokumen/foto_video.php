<?php
session_start();
include_once '../koneksi.php';
include "../cek_akses.php";

$conn = $koneksi;

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ===============================
// UPLOAD FILE
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $file = $_FILES['file'] ?? null;
    $elemen = trim($_POST['elemen'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Validasi input
    if (empty($elemen) || empty($keterangan)) {
        echo "<script>alert('Elemen dan keterangan harus diisi.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    if (strlen($elemen) > 100 || strlen($keterangan) > 500) {
        echo "<script>alert('Elemen atau keterangan terlalu panjang.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    // Validasi file
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Error uploading file. Please try again.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    $allowedMimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'
    ];
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov', 'avi'];
    $maxSize = 100 * 1024 * 1024; // 100MB

    // Check file size
    if ($file['size'] > $maxSize) {
        echo "<script>alert('File terlalu besar. Maksimal 100MB.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        echo "<script>alert('Tipe file tidak diizinkan. Hanya image atau video.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    // Check MIME type using finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        echo "<script>alert('MIME type tidak diizinkan.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    // Determine file type
    $jenis = (strpos($mimeType, 'image') !== false) ? 'Foto' : 'Video';

    // Sanitize elemen
    $elemenClean = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $elemen);
    $folder = strtolower(str_replace(' ', '_', $jenis . "/" . $elemenClean));
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/media/$folder/";

    // Validate path to prevent traversal
    $realTargetDir = realpath(dirname(__DIR__) . "/uploads/media");
    if ($realTargetDir === false) {
        mkdir(dirname(__DIR__) . "/uploads/media", 0755, true);
        $realTargetDir = realpath(dirname(__DIR__) . "/uploads/media");
    }

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Double-check path is within allowed directory
    $realTargetPath = realpath($targetDir);
    if ($realTargetPath === false || strpos($realTargetPath, $realTargetDir) !== 0) {
        echo "<script>alert('Invalid directory. Access denied.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    // Generate unique filename
    $namaFile = time() . '_' . bin2hex(random_bytes(5)) . '_' . preg_replace("/[^a-zA-Z0-9_\.-]/", "_", basename($file['name']));
    $namaFile = substr($namaFile, 0, 150);
    $pathFile = $targetDir . $namaFile;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $pathFile)) {
        echo "<script>alert('Gagal menyimpan file.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    // Build file URL (store relative path)
    $urlFile = "/uploads/media/$folder/" . $namaFile;
    $ukuran = round($file['size'] / 1024, 2); // KB

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO tb_media_ppi (jenis_file, elemen, keterangan, nama_file, tipe_file, ukuran_file, path_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssdds", $jenis, $elemen, $keterangan, $namaFile, $mimeType, $ukuran, $urlFile);
        if ($stmt->execute()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
            exit;
        }
        $stmt->close();
    }

    echo "<script>alert('Terjadi kesalahan saat menyimpan ke database.'); window.location.href='foto_video.php';</script>";
    exit;
}

// ===============================
// HAPUS FILE
// ===============================
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo "<script>alert('ID tidak valid.'); window.location.href='foto_video.php';</script>";
        exit;
    }

    // Get file info
    $stmt = $conn->prepare("SELECT path_file FROM tb_media_ppi WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if ($data) {
            // Build absolute path from relative path
            $filePath = dirname(__DIR__) . $data['path_file'];

            // Validate path
            $realUploadDir = realpath(dirname(__DIR__) . "/uploads");
            $realFilePath = realpath($filePath);

            if ($realFilePath && $realUploadDir && strpos($realFilePath, $realUploadDir) === 0 && is_file($realFilePath)) {
                unlink($realFilePath);
            }

            // Delete from database
            $stmt = $conn->prepare("DELETE FROM tb_media_ppi WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
                $stmt->close();
            }
        }
    }

    echo "<script>alert('Terjadi kesalahan saat menghapus file.'); window.location.href='foto_video.php';</script>";
    exit;
}

// ===============================
// FILTER & PAGINATION
// ===============================
$filter_jenis = isset($_GET['jenis']) ? trim($_GET['jenis']) : 'semua';
$filter_elemen = isset($_GET['elemen']) ? trim($_GET['elemen']) : 'semua';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 12;

// Validate filter values
$allowed_jenis = ['semua', 'Foto', 'Video'];
if (!in_array($filter_jenis, $allowed_jenis)) {
    $filter_jenis = 'semua';
}

// Count total
$countQuery = "SELECT COUNT(*) as total FROM tb_media_ppi WHERE 1";
$countParams = [];

if ($filter_jenis !== 'semua') {
    $countQuery .= " AND jenis_file = ?";
    $countParams[] = $filter_jenis;
}
if ($filter_elemen !== 'semua') {
    $countQuery .= " AND elemen = ?";
    $countParams[] = $filter_elemen;
}

$countStmt = $conn->prepare($countQuery);
if ($countParams) {
    $types = str_repeat("s", count($countParams));
    $countStmt->bind_param($types, ...$countParams);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalData = $countResult->fetch_assoc()['total'];
$countStmt->close();

$totalPages = ceil($totalData / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

$offset = ($page - 1) * $limit;

// Get data
$query = "SELECT * FROM tb_media_ppi WHERE 1";
$params = [];

if ($filter_jenis !== 'semua') {
    $query .= " AND jenis_file = ?";
    $params[] = $filter_jenis;
}
if ($filter_elemen !== 'semua') {
    $query .= " AND elemen = ?";
    $params[] = $filter_elemen;
}
$query .= " ORDER BY id DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;

$stmt = $conn->prepare($query);
if ($params) {
    $types = str_repeat("s", count($params) - 2) . "ii";
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$media = $stmt->get_result();
$mediaRows = [];
while ($row = $media->fetch_assoc()) {
    $mediaRows[] = $row;
}
$stmt->close();

// Get elemen list for filter
$elemenQuery = $conn->prepare("SELECT DISTINCT elemen FROM tb_media_ppi ORDER BY elemen ASC");
$elemenQuery->execute();
$elemenList = $elemenQuery->get_result();
$elemenQuery->close();

$pageTitle = "DOKUMEN DAN MEDIA";
include '../layout.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?> | PPI PHBW</title>
    <link rel="stylesheet" href="/assets/css/utama.css?v=10">
    
    <style>
        :root {
            --brand: #2563eb;
            --brand-dark: #0f3a79;
            --brand-soft: #dbeafe;
            --bg: #eef4fb;
            --card: #ffffff;
            --line: #d7e3f1;
            --ink: #0f172a;
            --muted: #5f7187;
            --danger: #dc2626;
            --shadow-lg: 0 24px 50px rgba(15, 23, 42, 0.10);
            --shadow-md: 0 12px 30px rgba(37, 99, 235, 0.12);
            --shadow-sm: 0 8px 24px rgba(15, 23, 42, 0.08);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-pill: 999px;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 22%),
                linear-gradient(180deg, #f7fbff 0%, var(--bg) 100%);
            color: var(--ink);
        }

        main { min-width: 0; }

        .container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 28px 24px 40px;
        }

        .hero-header {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 20px;
            padding: 16px 20px;
            margin-bottom: 22px;
            min-height: 140px;
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, rgba(15, 58, 121, 0.98), rgba(37, 99, 235, 0.94)), linear-gradient(135deg, #1e40af, #2563eb);
            color: #fff;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .hero-content { position: relative; z-index: 1; }
        .hero-actions {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            gap: 12px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            border-radius: var(--radius-pill);
            background: rgba(187, 247, 208, 0.22);
            border: 1px solid rgba(187, 247, 208, 0.38);
            font-size: 11px;
            font-weight: 700;
            color: #f0fdf4;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 14px;
        }

        .hero-header h1 {
            margin: 0 0 6px;
            color: #fff;
            font-size: 22px;
            line-height: 1.2;
            letter-spacing: -0.03em;
        }

        .subtitle {
            margin: 0;
            max-width: 600px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 14px;
            line-height: 1.5;
        }

        .hero-stat {
            padding: 12px 14px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.26), rgba(255, 255, 255, 0.18));
            border: 1px solid rgba(255, 255, 255, 0.34);
            text-align: center;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .hero-stat strong {
            display: block;
            font-size: 28px;
            line-height: 1;
            margin-bottom: 6px;
        }

        .hero-stat span {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.78);
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 14px;
            text-decoration: none;
            white-space: nowrap;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn:hover { transform: translateY(-2px); }

        .btn-primary {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.28);
        }

        .btn-primary:hover {
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.34);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: none;
            opacity: 0.8;
            font-size: 12px;
        }

        .upload-box {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0.92));
            border: 1px solid rgba(191, 211, 232, 0.85);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .upload-box label {
            display: block;
            font-weight: 700;
            color: #29415f;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .upload-box input,
        .upload-box select,
        .upload-box textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            color: var(--ink);
            box-sizing: border-box;
            font-size: 14px;
        }

        .upload-box input:focus,
        .upload-box select:focus,
        .upload-box textarea:focus {
            border-color: #93c5fd;
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .upload-box textarea { resize: vertical; }

        .upload-submit {
            grid-column: 1 / -1;
            text-align: right;
        }

        .filter-box {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.92);
            border-radius: var(--radius-md);
            border: 1px solid var(--line);
            margin-bottom: 18px;
        }

        .filter-box label {
            font-weight: 700;
            color: var(--brand-dark);
            font-size: 14px;
        }

        .filter-box select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background-color: white;
            font-size: 14px;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .thumb {
            height: 180px;
            background: #f1f4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .thumb img,
        .thumb video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-body {
            padding: 12px;
            text-align: center;
        }

        .card-body strong {
            display: block;
            color: var(--brand-dark);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-body small {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 6px;
        }

        .actions a,
        .actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .view {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }

        .view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
        }

        .delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(220, 38, 38, 0.3);
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 40px 20px;
            background: #f8fbff;
            border-radius: var(--radius-lg);
            border: 1px dashed var(--line);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
        }

        .pagination a {
            background: white;
            color: var(--brand);
            border: 1px solid var(--line);
            transition: all 0.2s ease;
        }

        .pagination a:hover {
            background: var(--brand);
            color: white;
        }

        .pagination span.current {
            background: var(--brand);
            color: white;
            border: 1px solid var(--brand);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .alert.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        @media (max-width: 768px) {
            .container { padding: 16px 16px 30px; }
            .hero-header { flex-direction: column; }
            .hero-actions { width: 100%; align-items: stretch; }
            .upload-box { grid-template-columns: 1fr; }
            .gallery { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        }
    </style>
</head>

<body>
    <div class="layout">
        <?php include_once '../sidebar.php'; ?>

        <main>
            <?php include_once '../topbar.php'; ?>

            <div class="container">
                <section class="hero-header">
                    <div class="hero-content">
                        <div class="hero-badge">📷 Manajemen Media</div>
                        <h1>Foto dan Video PPI</h1>
                        <p class="subtitle">Kelola koleksi foto dan video dokumentasi elemen PPI dengan mudah.</p>
                    </div>
                    <div class="hero-actions">
                        <div class="hero-stat">
                            <strong><?= $totalData; ?></strong>
                            <span>Total File</span>
                        </div>
                    </div>
                </section>

                <div class="toolbar">
                    <div class="toolbar-group">
                        <button class="btn btn-primary" id="openUpload">➕ Upload Media</button>
                    </div>
                </div>

                <!-- Filter -->
                <div class="filter-box">
                    <label>Filter Jenis:</label>
                    <select onchange="updateFilter('jenis', this.value)">
                        <option value="semua" <?= $filter_jenis === 'semua' ? 'selected' : '' ?>>Semua</option>
                        <option value="Foto" <?= $filter_jenis === 'Foto' ? 'selected' : '' ?>>📷 Foto</option>
                        <option value="Video" <?= $filter_jenis === 'Video' ? 'selected' : '' ?>>🎥 Video</option>
                    </select>

                    <label>Filter Elemen:</label>
                    <select onchange="updateFilter('elemen', this.value)">
                        <option value="semua" <?= $filter_elemen === 'semua' ? 'selected' : '' ?>>Semua</option>
                        <?php while ($elemen = $elemenList->fetch_assoc()): ?>
                          <option value="<?= htmlspecialchars($elemen['elemen']) ?>" 
                            <?= $filter_elemen === $elemen['elemen'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($elemen['elemen']) ?>
                          </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="alert success">✅ File berhasil diunggah!</div>
                <?php endif; ?>

                <!-- Gallery -->
                <div class="gallery">
                    <?php if (empty($mediaRows)): ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            Belum ada file media. Upload file pertama untuk mulai.
                        </div>
                    <?php else: ?>
                        <?php foreach ($mediaRows as $row): ?>
                        <div class="card">
                            <div class="thumb">
                                <?php
                                // Root-relative path (starts with /) — same-origin, no hardcoded domain
                                $thumbUrl = $row['path_file'];
                                $viewUrl  = 'https://portalppi.my.id' . $row['path_file'];
                                ?>

                                <?php if (strpos($row['tipe_file'], 'image') !== false): ?>
                                <img src="<?= htmlspecialchars($thumbUrl) ?>"
                                     alt="<?= htmlspecialchars($row['elemen']) ?>"
                                     onerror="console.error('Gagal load gambar:', this.src); this.onerror=null; this.style.display='none'; this.parentElement.innerHTML+='<span style=\'color:#94a3b8;font-size:11px;padding:8px;display:block;text-align:center\'>📷 Tidak dapat ditampilkan</span>'">
                                <?php elseif (strpos($row['tipe_file'], 'video') !== false): ?>
                                    <video controls preload="metadata" style="width:100%; height:100%; object-fit:cover;">
                                        <source src="<?= htmlspecialchars($thumbUrl) ?>" type="<?= htmlspecialchars($row['tipe_file']) ?>">
                                        Browser Anda tidak mendukung video.
                                    </video>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <strong><?= htmlspecialchars($row['elemen']) ?></strong>
                                <small><?= htmlspecialchars($row['keterangan']) ?></small>

                                <div class="actions">
                                    <a href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" class="view" title="View">👁️</a>
                                    <a href="<?= htmlspecialchars($viewUrl) ?>" download class="view" title="Download">⬇️</a>
                                    <button type="button" class="delete" onclick="deleteFile(<?= $row['id'] ?>)" title="Delete">🗑️</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?jenis=<?= htmlspecialchars($filter_jenis) ?>&elemen=<?= htmlspecialchars($filter_elemen) ?>&page=1">&laquo;</a>
                        <a href="?jenis=<?= htmlspecialchars($filter_jenis) ?>&elemen=<?= htmlspecialchars($filter_elemen) ?>&page=<?= $page - 1 ?>">&lsaquo;</a>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p === $page): ?>
                            <span class="current"><?= $p ?></span>
                        <?php else: ?>
                            <a href="?jenis=<?= htmlspecialchars($filter_jenis) ?>&elemen=<?= htmlspecialchars($filter_elemen) ?>&page=<?= $p ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?jenis=<?= htmlspecialchars($filter_jenis) ?>&elemen=<?= htmlspecialchars($filter_elemen) ?>&page=<?= $page + 1 ?>">&rsaquo;</a>
                        <a href="?jenis=<?= htmlspecialchars($filter_jenis) ?>&elemen=<?= htmlspecialchars($filter_elemen) ?>&page=<?= $totalPages ?>">&raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <footer style="
                margin-top:30px;
                padding:16px;
                text-align:center;
                font-size:13px;
                color:#64748b;
                border-top:1px solid #e2e8f0;
                background:#f8fafc;
            ">
                © <?= date('Y') ?> PPI RS Primaya Bhaktiwara Pangkalpinang  
                <br>
                Sistem Manajemen Dokumen & Media
            </footer>
        </main>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(6px); align-items:center; justify-content:center; z-index:50;">
        <div style="background:#fff; border-radius:20px; padding:24px; width:100%; max-width:520px; box-shadow:0 24px 50px rgba(15,23,42,0.1);">
            <h3 style="margin-top:0; color:#0f3a79; font-size:17px;">Upload Media Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="upload">

                <label style="display:block; margin-bottom:6px; font-weight:700; color:#29415f; font-size:14px;">Pilih Elemen</label>
                <select name="elemen" required style="width:100%; padding:11px 12px; border:1px solid #d7e3f1; border-radius:12px; margin-bottom:14px; font-size:14px;">
                    <option value="">-- Pilih Elemen --</option>
                    <?php 
                    $elemenQ = $conn->prepare("SELECT DISTINCT elemen FROM tb_media_ppi ORDER BY elemen ASC");
                    $elemenQ->execute();
                    $elemenR = $elemenQ->get_result();
                    while ($e = $elemenR->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($e['elemen']) ?>"><?= htmlspecialchars($e['elemen']) ?></option>
                    <?php endwhile; $elemenQ->close(); ?>
                </select>

                <label style="display:block; margin-bottom:6px; font-weight:700; color:#29415f; font-size:14px;">Keterangan</label>
                <textarea name="keterangan" rows="3" required style="width:100%; padding:11px 12px; border:1px solid #d7e3f1; border-radius:12px; margin-bottom:14px; font-size:14px; box-sizing:border-box;" placeholder="Uraian singkat tentang file..."></textarea>

                <label style="display:block; margin-bottom:6px; font-weight:700; color:#29415f; font-size:14px;">Pilih File (Max 100MB)</label>
                <input type="file" name="file" accept="image/*,video/*" required style="width:100%; padding:11px 12px; border:1px solid #d7e3f1; border-radius:12px; margin-bottom:14px; font-size:14px; box-sizing:border-box;">

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                    <button type="button" onclick="closeUploadModal()" style="background:#f1f5f9; color:#334155; padding:11px 16px; border-radius:12px; border:none; cursor:pointer; font-weight:700;">Batal</button>
                    <button type="submit" style="background:linear-gradient(135deg,#1d4ed8,#2563eb); color:#fff; padding:12px 18px; border-radius:14px; border:none; cursor:pointer; font-weight:700; box-shadow:0 10px 24px rgba(37,99,235,0.24);">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/assets/js/utama.js?v=5"></script>
    <script>
        const uploadModal = document.getElementById('uploadModal');
        
        document.getElementById('openUpload').onclick = () => {
            uploadModal.style.display = 'flex';
        };

        function closeUploadModal() {
            uploadModal.style.display = 'none';
        }

        window.onclick = (e) => {
            if (e.target === uploadModal) {
                uploadModal.style.display = 'none';
            }
        };

        function updateFilter(type, value) {
            const params = new URLSearchParams(window.location.search);
            params.set(type, value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        function deleteFile(id) {
            if (confirm('Yakin ingin menghapus file ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = 'csrf_token';
                csrf.value = "<?= $_SESSION['csrf_token']; ?>";

                const action = document.createElement('input');
                action.type = 'hidden';
                action.name = 'action';
                action.value = 'delete';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                form.appendChild(csrf);
                form.appendChild(action);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>
