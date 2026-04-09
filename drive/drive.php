<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include '../cek_akses.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$conn = $koneksi;

const DRIVE_MAX_UPLOAD_BYTES = 25 * 1024 * 1024;
const DRIVE_ITEMS_PER_PAGE = 24;

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function driveSessionUser()
{
    $candidates = ['nama', 'name', 'username', 'user', 'nama_lengkap'];
    foreach ($candidates as $key) {
        if (!empty($_SESSION[$key])) {
            return (string) $_SESSION[$key];
        }
    }
    return 'unknown';
}

function driveCsrfToken()
{
    if (empty($_SESSION['drive_csrf'])) {
        $_SESSION['drive_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['drive_csrf'];
}

function verifyDriveCsrfOrExit()
{
    $token = trim((string) ($_REQUEST['csrf_token'] ?? ''));
    $sessionToken = (string) ($_SESSION['drive_csrf'] ?? '');
    $ok = $token !== '' && $sessionToken !== '' && hash_equals($sessionToken, $token);

    if ($ok) {
        return;
    }

    if (isAjaxRequest()) {
        http_response_code(403);
        echo 'csrf';
        exit;
    }

    http_response_code(403);
    echo "<script>alert('Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.'); window.location.href='drive.php';</script>";
    exit;
}

function ensureDriveAuditTable($conn)
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS drive_activity_log (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        actor VARCHAR(120) NOT NULL,
        action VARCHAR(40) NOT NULL,
        item_id VARCHAR(80) DEFAULT NULL,
        item_name VARCHAR(255) DEFAULT NULL,
        detail TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $conn->query($sql);
    $ensured = true;
}

function logDriveActivity($conn, $action, $itemId = null, $itemName = null, $detail = null)
{
    ensureDriveAuditTable($conn);

    $actor = driveSessionUser();
    $action = trim((string) $action);
    $itemId = $itemId !== null ? (string) $itemId : null;
    $itemName = $itemName !== null ? (string) $itemName : null;
    $detail = $detail !== null ? (string) $detail : null;

    $stmt = $conn->prepare('INSERT INTO drive_activity_log (actor, action, item_id, item_name, detail) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('sssss', $actor, $action, $itemId, $itemName, $detail);
    $stmt->execute();
    $stmt->close();
}

function driveAllowedExtensions()
{
    return [
        'pdf', 'txt', 'csv', 'html', 'htm', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', '7z',
        'mp3', 'wav', 'm4a', 'ogg', 'mp4', 'mov', 'avi', 'mkv'
    ];
}

function driveAllowedMimePrefixes()
{
    return [
        'image/', 'audio/', 'video/', 'text/'
    ];
}

function driveAllowedMimeEquals()
{
    return [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/octet-stream'
    ];
}

function isAllowedUploadFile($tmpPath, $fileName)
{
    $ext = strtolower((string) pathinfo((string) $fileName, PATHINFO_EXTENSION));
    if ($ext === '' || !in_array($ext, driveAllowedExtensions(), true)) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo ? (string) finfo_file($finfo, (string) $tmpPath) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    if ($detectedMime === '') {
        return false;
    }

    if (in_array($detectedMime, driveAllowedMimeEquals(), true)) {
        return true;
    }

    foreach (driveAllowedMimePrefixes() as $prefix) {
        if (strpos($detectedMime, $prefix) === 0) {
            return true;
        }
    }

    return false;
}

function normalizeUploadRelativePath($path)
{
    $path = trim((string) $path);
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#/+#', '/', $path);
    $parts = array_values(array_filter(explode('/', $path), static function ($part) {
        return $part !== '' && $part !== '.' && $part !== '..';
    }));
    return implode('/', $parts);
}

function findExistingDriveFolderId($conn, $folderName, $parentId)
{
    if ($parentId === null || $parentId === '') {
        $stmt = $conn->prepare("SELECT id FROM drive_files WHERE type = 'folder' AND parent_id IS NULL AND name = ? LIMIT 1");
        $stmt->bind_param('s', $folderName);
    } else {
        $stmt = $conn->prepare("SELECT id FROM drive_files WHERE type = 'folder' AND parent_id = ? AND name = ? LIMIT 1");
        $stmt->bind_param('ss', $parentId, $folderName);
    }

    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row['id'] ?? null;
}

function findOrCreateDriveFolder($conn, $folderName, $parentId)
{
    $folderName = trim((string) $folderName);
    if ($folderName === '') {
        return $parentId;
    }

    $existingId = findExistingDriveFolderId($conn, $folderName, $parentId);
    if (!empty($existingId)) {
        return $existingId;
    }

    $id = uniqid('fld_', true);
    $type = 'folder';
    $size = 0;
    $stmt = $conn->prepare('INSERT INTO drive_files (id, name, type, size, parent_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssis', $id, $folderName, $type, $size, $parentId);
    $stmt->execute();
    $stmt->close();

    logDriveActivity($conn, 'create_folder_upload', $id, $folderName, 'parent=' . ($parentId ?? 'root'));
    return $id;
}

function ensureDriveFolderPath($conn, $relativeDir, $parentId)
{
    $relativeDir = normalizeUploadRelativePath($relativeDir);
    if ($relativeDir === '') {
        return $parentId;
    }

    $cursor = $parentId;
    foreach (explode('/', $relativeDir) as $segment) {
        $cursor = findOrCreateDriveFolder($conn, $segment, $cursor);
    }
    return $cursor;
}

function collectDriveUploadEntries()
{
    $entries = [];

    foreach (['file', 'folder_file'] as $field) {
        if (!isset($_FILES[$field])) {
            continue;
        }

        $names = $_FILES[$field]['name'];
        $tmpNames = $_FILES[$field]['tmp_name'];
        $errors = $_FILES[$field]['error'];
        $types = $_FILES[$field]['type'];
        $sizes = $_FILES[$field]['size'];

        if (!is_array($names)) {
            $names = [$names];
            $tmpNames = [$tmpNames];
            $errors = [$errors];
            $types = [$types];
            $sizes = [$sizes];
        }

        $fullPaths = [];
        if (isset($_FILES[$field]['full_path'])) {
            $fp = $_FILES[$field]['full_path'];
            if (!is_array($fp)) {
                $fp = [$fp];
            }
            $fullPaths = $fp;
        }

        foreach ($names as $index => $name) {
            $relativePath = isset($fullPaths[$index]) ? $fullPaths[$index] : $name;

            $entries[] = [
                'field' => $field,
                'name' => (string) $name,
                'relative_path' => (string) $relativePath,
                'tmp_name' => (string) ($tmpNames[$index] ?? ''),
                'error' => $errors[$index] ?? UPLOAD_ERR_NO_FILE,
                'type' => (string) ($types[$index] ?? 'application/octet-stream'),
                'size' => (int) ($sizes[$index] ?? 0),
            ];
        }
    }

    return $entries;
}

function redirectDrive($parentId = null)
{
    $sort = isset($_REQUEST['sort']) ? trim((string) $_REQUEST['sort']) : 'latest';
    $params = [];

    if (!empty($parentId)) {
        $params['parent'] = $parentId;
    }

    if ($sort !== '' && $sort !== 'latest') {
        $params['sort'] = $sort;
    }

    if (!empty($params)) {
        header('Location: drive.php?' . http_build_query($params));
    } else {
        header('Location: drive.php');
    }
    exit;
}

function normalizeDisplayName($name)
{
    return preg_replace('/^[a-f0-9]+_/', '', (string) $name);
}

function formatBytes($bytes)
{
    $bytes = (int) $bytes;
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    $units = ['KB', 'MB', 'GB', 'TB'];
    $value = $bytes / 1024;
    $idx = 0;
    while ($value >= 1024 && $idx < count($units) - 1) {
        $value /= 1024;
        $idx++;
    }
    return number_format($value, 1) . ' ' . $units[$idx];
}

function itemIcon($type)
{
    if ($type === 'folder') {
        return '📁';
    }
    if (strpos((string) $type, 'image/') === 0) {
        return '🖼️';
    }
    if (strpos((string) $type, 'video/') === 0) {
        return '🎬';
    }
    if (strpos((string) $type, 'audio/') === 0) {
        return '🎵';
    }
    if (strpos((string) $type, 'pdf') !== false) {
        return '📕';
    }
    if (strpos((string) $type, 'word') !== false || strpos((string) $type, 'document') !== false) {
        return '📘';
    }
    if (strpos((string) $type, 'sheet') !== false || strpos((string) $type, 'excel') !== false || strpos((string) $type, 'csv') !== false) {
        return '📗';
    }
    if (strpos((string) $type, 'zip') !== false || strpos((string) $type, 'rar') !== false || strpos((string) $type, '7z') !== false) {
        return '🗜️';
    }
    return '📄';
}

function menuIconSvg($name)
{
    $icons = [
        'rename' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" /></svg>',
        'open' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7" /><path d="M10 14 21 3" /><path d="M21 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /></svg>',
        'google' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.2a9 9 0 1 1-2.64-6.36" /><path d="M21 12h-9" /></svg>',
        'download' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12" /><path d="m7 10 5 5 5-5" /><path d="M5 21h14" /></svg>',
        'delete' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="m19 6-1 14H6L5 6" /><path d="M10 11v6" /><path d="M14 11v6" /></svg>'
    ];

    return $icons[$name] ?? $icons['open'];
}

function buildAbsoluteUrl($path)
{
    $path = (string) $path;
    if ($path === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'portalppi.my.id';

    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

function isGoogleViewerType($type, $name)
{
    $type = strtolower((string) $type);
    $extension = strtolower((string) pathinfo((string) $name, PATHINFO_EXTENSION));
    $extensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

    if (in_array($extension, $extensions, true)) {
        return true;
    }

    $mimeMatches = [
        'msword',
        'officedocument.wordprocessingml',
        'vnd.ms-excel',
        'officedocument.spreadsheetml',
        'vnd.ms-powerpoint',
        'officedocument.presentationml'
    ];

    foreach ($mimeMatches as $needle) {
        if (strpos($type, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function buildViewerUrl($type, $name, $url)
{
    if (isGoogleViewerType($type, $name)) {
        return 'https://docs.google.com/gview?embedded=1&url=' . rawurlencode(buildAbsoluteUrl($url));
    }

    return (string) $url;
}

function previewMode($type, $name)
{
    $type = strtolower((string) $type);

    if (strpos($type, 'image/') === 0) {
        return 'image';
    }

    if (strpos($type, 'audio/') === 0) {
        return 'audio';
    }

    if (strpos($type, 'video/') === 0) {
        return 'video';
    }

    if (strpos($type, 'pdf') !== false) {
        return 'pdf';
    }

    if (isGoogleViewerType($type, $name)) {
        return 'office';
    }

    return '';
}

function isAjaxRequest()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function respondUpload($success, $message, $parentId = null)
{
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'redirect' => !empty($parentId)
                ? 'drive.php?' . http_build_query([
                    'parent' => $parentId,
                    'sort' => (isset($_REQUEST['sort']) && $_REQUEST['sort'] !== 'latest') ? $_REQUEST['sort'] : null
                ])
                : 'drive.php' . ((isset($_REQUEST['sort']) && $_REQUEST['sort'] !== 'latest') ? ('?' . http_build_query(['sort' => $_REQUEST['sort']])) : '')
        ]);
        exit;
    }

    if ($success) {
        redirectDrive($parentId);
    }

    echo "<script>alert('" . h($message) . "'); window.history.back();</script>";
    exit;
}

function uploadDriveFiles($conn, $uploadDir, $parentId)
{
    if (!isset($_FILES['file']) && !isset($_FILES['folder_file'])) {
        return ['success' => false, 'message' => 'Tidak ada file yang dipilih.'];
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $entries = collectDriveUploadEntries();

    $uploadedCount = 0;
    $failedNames = [];
    $blockedHtmlNames = [];
    $insertStmt = $conn->prepare('INSERT INTO drive_files (id, name, type, size, url, parent_id) VALUES (?, ?, ?, ?, ?, ?)');

    foreach ($entries as $entry) {
        $originalName = trim((string) ($entry['name'] ?? ''));
        $relativePath = trim((string) ($entry['relative_path'] ?? $originalName));
        $error = $entry['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE || $originalName === '') {
            continue;
        }

        if ($error !== UPLOAD_ERR_OK) {
            $failedNames[] = $originalName;
            continue;
        }

        $size = (int) ($entry['size'] ?? 0);
        if ($size <= 0 || $size > DRIVE_MAX_UPLOAD_BYTES) {
            $failedNames[] = $originalName;
            continue;
        }

        $normalizedPath = normalizeUploadRelativePath($relativePath);
        $relativeDir = dirname($normalizedPath);
        if ($relativeDir === '.' || $relativeDir === DIRECTORY_SEPARATOR) {
            $relativeDir = '';
        }
        $baseName = basename($normalizedPath);

        if ($baseName === '') {
            $failedNames[] = $originalName;
            continue;
        }

        $ext = strtolower((string) pathinfo($baseName, PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, driveAllowedExtensions(), true)) {
            $failedNames[] = $originalName;
            if ($ext === 'html' || $ext === 'htm') {
                $blockedHtmlNames[] = $originalName;
            }
            continue;
        }

        if (!isAllowedUploadFile($entry['tmp_name'] ?? '', $baseName)) {
            $failedNames[] = $originalName;
            continue;
        }

        $targetParentId = ensureDriveFolderPath($conn, $relativeDir, $parentId);

        $cleanName = $baseName !== '' ? $baseName : 'tanpa_nama';
        $safeName = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $cleanName);
        $targetPath = $uploadDir . $safeName;
        $url = '/drive/uploads/' . rawurlencode($safeName);

        if (!move_uploaded_file($entry['tmp_name'] ?? '', $targetPath)) {
            $failedNames[] = $cleanName;
            continue;
        }

        $id = uniqid('drv_', true);
        $type = (string) ($entry['type'] ?? 'application/octet-stream');
        $insertStmt->bind_param('sssiss', $id, $cleanName, $type, $size, $url, $targetParentId);

        if ($insertStmt->execute()) {
            $uploadedCount++;
            $detail = 'size=' . $size . '; parent=' . ($targetParentId ?? 'root');
            if ($relativeDir !== '') {
                $detail .= '; path=' . $relativeDir;
            }
            logDriveActivity($conn, 'upload', $id, $cleanName, $detail);
        } else {
            if (is_file($targetPath)) {
                unlink($targetPath);
            }
            $failedNames[] = $cleanName;
        }
    }

    $insertStmt->close();

    if ($uploadedCount === 0 && count($failedNames) > 0) {
        if (count($blockedHtmlNames) > 0) {
            return [
                'success' => false,
                'message' => 'File HTML (.html/.htm) tidak diizinkan untuk upload demi keamanan. Gunakan PDF/DOCX/TXT atau format lain yang didukung.'
            ];
        }
        return ['success' => false, 'message' => 'Semua upload gagal. File bermasalah: ' . implode(', ', array_slice($failedNames, 0, 5))];
    }

    if ($uploadedCount === 0) {
        return ['success' => false, 'message' => 'Tidak ada file yang dipilih.'];
    }

    $message = $uploadedCount . ' file berhasil diupload';
    if (count($failedNames) > 0) {
        $message .= ', ' . count($failedNames) . ' file gagal';
        if (count($blockedHtmlNames) > 0) {
            $message .= ' (file .html/.htm diblok demi keamanan)';
        }
    }

    return ['success' => true, 'message' => $message];
}

function buildSortOptions()
{
    return [
        'latest' => [
            'label' => 'Terbaru',
            'order' => "CASE WHEN type = 'folder' THEN 0 ELSE 1 END ASC, updated_at DESC, name ASC"
        ],
        'name_asc' => [
            'label' => 'Nama A-Z',
            'order' => "CASE WHEN type = 'folder' THEN 0 ELSE 1 END ASC, name ASC, updated_at DESC"
        ],
        'name_desc' => [
            'label' => 'Nama Z-A',
            'order' => "CASE WHEN type = 'folder' THEN 0 ELSE 1 END ASC, name DESC, updated_at DESC"
        ],
        'size_desc' => [
            'label' => 'Ukuran Terbesar',
            'order' => "CASE WHEN type = 'folder' THEN 0 ELSE 1 END ASC, size DESC, name ASC"
        ],
        'size_asc' => [
            'label' => 'Ukuran Terkecil',
            'order' => "CASE WHEN type = 'folder' THEN 0 ELSE 1 END ASC, size ASC, name ASC"
        ]
    ];
}

function deleteRecursive($conn, $id, $uploadDir)
{
    $childStmt = $conn->prepare('SELECT id FROM drive_files WHERE parent_id = ?');
    $childStmt->bind_param('s', $id);
    $childStmt->execute();
    $childResult = $childStmt->get_result();
    while ($child = $childResult->fetch_assoc()) {
        deleteRecursive($conn, $child['id'], $uploadDir);
    }
    $childStmt->close();

    $itemStmt = $conn->prepare('SELECT name, type, url FROM drive_files WHERE id = ? LIMIT 1');
    $itemStmt->bind_param('s', $id);
    $itemStmt->execute();
    $item = $itemStmt->get_result()->fetch_assoc();
    $itemStmt->close();

    if ($item && $item['type'] !== 'folder' && !empty($item['url'])) {
        $diskName = basename(parse_url($item['url'], PHP_URL_PATH));
        $diskPath = $uploadDir . $diskName;
        if (is_file($diskPath)) {
            unlink($diskPath);
        }
    }

    if ($item) {
        logDriveActivity($conn, 'delete', $id, (string) ($item['name'] ?? ''), 'type=' . (string) ($item['type'] ?? 'unknown'));
    }

    $deleteStmt = $conn->prepare('DELETE FROM drive_files WHERE id = ?');
    $deleteStmt->bind_param('s', $id);
    $deleteStmt->execute();
    $deleteStmt->close();
}

function wouldCreateLoop($conn, $movingId, $targetId)
{
    if (empty($targetId) || $movingId === $targetId) {
        return true;
    }

    $cursor = $targetId;
    $loopGuard = 0;
    while (!empty($cursor) && $loopGuard < 100) {
        if ($cursor === $movingId) {
            return true;
        }
        $loopGuard++;

        $stmt = $conn->prepare('SELECT parent_id FROM drive_files WHERE id = ? LIMIT 1');
        $stmt->bind_param('s', $cursor);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return false;
        }
        $cursor = $row['parent_id'];
    }

    return false;
}

$currentParent = isset($_GET['parent']) && $_GET['parent'] !== '' ? $_GET['parent'] : null;
$sortOptions = buildSortOptions();
$currentSort = isset($_GET['sort']) ? trim((string) $_GET['sort']) : 'latest';
if (!isset($sortOptions[$currentSort])) {
    $currentSort = 'latest';
}
$orderBy = $sortOptions[$currentSort]['order'];
$uploadDir = __DIR__ . '/uploads/';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && (
        isset($_POST['upload'])
        || isset($_FILES['file'])
        || isset($_FILES['folder_file'])
    )
) {
    verifyDriveCsrfOrExit();
    $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

    $uploadResult = uploadDriveFiles($conn, $uploadDir, $parentId);
    respondUpload($uploadResult['success'], $uploadResult['message'], $parentId);
}

if (isset($_POST['buat_folder'])) {
    verifyDriveCsrfOrExit();
    $name = trim((string) ($_POST['nama_folder'] ?? ''));
    $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

    if ($name === '') {
        echo "<script>alert('Nama folder wajib diisi.'); window.history.back();</script>";
        exit;
    }

    $id = uniqid('fld_', true);
    $type = 'folder';
    $size = 0;
    $stmt = $conn->prepare('INSERT INTO drive_files (id, name, type, size, parent_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssis', $id, $name, $type, $size, $parentId);
    $stmt->execute();
    $stmt->close();

    logDriveActivity($conn, 'create_folder', $id, $name, 'parent=' . ($parentId ?? 'root'));

    redirectDrive($parentId);
}

if (isset($_GET['hapus']) && $_GET['hapus'] !== '') {
    verifyDriveCsrfOrExit();
    $id = $_GET['hapus'];
    deleteRecursive($conn, $id, $uploadDir);
    redirectDrive($currentParent);
}

if (isset($_POST['move_file']) && isset($_POST['id'])) {
    verifyDriveCsrfOrExit();
    $id = trim((string) $_POST['id']);
    $targetFolder = isset($_POST['target_folder']) && $_POST['target_folder'] !== '' ? trim((string) $_POST['target_folder']) : null;

    if ($targetFolder !== null && wouldCreateLoop($conn, $id, $targetFolder)) {
        echo 'invalid';
        exit;
    }

    $nameStmt = $conn->prepare('SELECT name FROM drive_files WHERE id = ? LIMIT 1');
    $nameStmt->bind_param('s', $id);
    $nameStmt->execute();
    $movingRow = $nameStmt->get_result()->fetch_assoc();
    $nameStmt->close();

    $targetName = 'Root';
    if ($targetFolder !== null) {
        $targetStmt = $conn->prepare('SELECT name FROM drive_files WHERE id = ? LIMIT 1');
        $targetStmt->bind_param('s', $targetFolder);
        $targetStmt->execute();
        $targetRow = $targetStmt->get_result()->fetch_assoc();
        $targetStmt->close();
        if ($targetRow && !empty($targetRow['name'])) {
            $targetName = (string) $targetRow['name'];
        }
    }

    $stmt = $conn->prepare('UPDATE drive_files SET parent_id = ? WHERE id = ?');
    $stmt->bind_param('ss', $targetFolder, $id);
    $ok = $stmt->execute();
    if ($ok) {
        logDriveActivity($conn, 'move', $id, (string) ($movingRow['name'] ?? ''), 'target=' . $targetName);
    }
    echo $ok ? 'ok' : 'error';
    $stmt->close();
    exit;
}

if (isset($_POST['rename']) && isset($_POST['id'])) {
    verifyDriveCsrfOrExit();
    $id = trim((string) $_POST['id']);
    $newName = trim((string) ($_POST['new_name'] ?? ''));

    if ($newName === '') {
        echo 'empty';
        exit;
    }

    $oldStmt = $conn->prepare('SELECT name FROM drive_files WHERE id = ? LIMIT 1');
    $oldStmt->bind_param('s', $id);
    $oldStmt->execute();
    $oldRow = $oldStmt->get_result()->fetch_assoc();
    $oldStmt->close();

    $stmt = $conn->prepare('UPDATE drive_files SET name = ? WHERE id = ?');
    $stmt->bind_param('ss', $newName, $id);
    $ok = $stmt->execute();
    if ($ok) {
        logDriveActivity($conn, 'rename', $id, $newName, 'from=' . (string) ($oldRow['name'] ?? ''));
    }
    echo $ok ? 'ok' : 'error';
    $stmt->close();
    exit;
}

$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalItems = 0;

if ($currentParent !== null) {
    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM drive_files WHERE parent_id = ?');
    $countStmt->bind_param('s', $currentParent);
    $countStmt->execute();
    $countRow = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();
    $totalItems = (int) ($countRow['total'] ?? 0);
} else {
    $countRes = $conn->query('SELECT COUNT(*) AS total FROM drive_files WHERE parent_id IS NULL');
    $countRow = $countRes ? $countRes->fetch_assoc() : ['total' => 0];
    $totalItems = (int) ($countRow['total'] ?? 0);
}

$totalPages = max(1, (int) ceil($totalItems / DRIVE_ITEMS_PER_PAGE));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * DRIVE_ITEMS_PER_PAGE;

if ($currentParent !== null) {
    $stmt = $conn->prepare("SELECT * FROM drive_files WHERE parent_id = ? ORDER BY {$orderBy} LIMIT ? OFFSET ?");
    $limit = DRIVE_ITEMS_PER_PAGE;
    $stmt->bind_param('sii', $currentParent, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM drive_files WHERE parent_id IS NULL ORDER BY {$orderBy} LIMIT " . (int) DRIVE_ITEMS_PER_PAGE . " OFFSET " . (int) $offset);
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

$crumbs = [];
if ($currentParent !== null) {
    $cursor = $currentParent;
    $loopGuard = 0;
    while (!empty($cursor) && $loopGuard < 100) {
        $loopGuard++;
        $cStmt = $conn->prepare('SELECT id, name, parent_id FROM drive_files WHERE id = ? LIMIT 1');
        $cStmt->bind_param('s', $cursor);
        $cStmt->execute();
        $row = $cStmt->get_result()->fetch_assoc();
        $cStmt->close();

        if (!$row) {
            break;
        }

        array_unshift($crumbs, $row);
        $cursor = $row['parent_id'];
    }
}

$folderCount = 0;
$fileCount = 0;
$totalSize = 0;

if ($currentParent !== null) {
    $statsStmt = $conn->prepare("SELECT
        SUM(CASE WHEN type = 'folder' THEN 1 ELSE 0 END) AS folders,
        SUM(CASE WHEN type <> 'folder' THEN 1 ELSE 0 END) AS files,
        SUM(CASE WHEN type <> 'folder' THEN size ELSE 0 END) AS total_size
        FROM drive_files WHERE parent_id = ?");
    $statsStmt->bind_param('s', $currentParent);
    $statsStmt->execute();
    $stats = $statsStmt->get_result()->fetch_assoc();
    $statsStmt->close();
} else {
    $statsRes = $conn->query("SELECT
        SUM(CASE WHEN type = 'folder' THEN 1 ELSE 0 END) AS folders,
        SUM(CASE WHEN type <> 'folder' THEN 1 ELSE 0 END) AS files,
        SUM(CASE WHEN type <> 'folder' THEN size ELSE 0 END) AS total_size
        FROM drive_files WHERE parent_id IS NULL");
    $stats = $statsRes ? $statsRes->fetch_assoc() : null;
}

if ($stats) {
    $folderCount = (int) ($stats['folders'] ?? 0);
    $fileCount = (int) ($stats['files'] ?? 0);
    $totalSize = (int) ($stats['total_size'] ?? 0);
}

$csrfToken = driveCsrfToken();

$pageTitle = 'PENYIMPANAN DATA';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Drive PPI</title>
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
    <style>
        .drive-wrapper {
            padding: 22px;
            display: grid;
            gap: 18px;
        }

        .hero {
            background: linear-gradient(135deg, #08334f 0%, #0b5b8c 45%, #16a34a 100%);
            border-radius: 20px;
            padding: 22px;
            color: #fff;
            box-shadow: 0 18px 34px rgba(8, 51, 79, 0.25);
            position: relative;
            overflow: hidden;
        }

        .hero::before,
        .hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .hero::before {
            width: 220px;
            height: 220px;
            right: -50px;
            top: -90px;
        }

        .hero::after {
            width: 160px;
            height: 160px;
            right: 180px;
            bottom: -90px;
        }

        .hero h1 {
            font-size: 24px;
            margin-bottom: 6px;
            letter-spacing: .3px;
        }

        .hero p {
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 16px;
        }

        .stats {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            max-width: 760px;
        }

        .stat {
            background: rgba(255, 255, 255, 0.13);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 14px;
            padding: 12px;
            backdrop-filter: blur(6px);
        }

        .stat b {
            display: block;
            font-size: 19px;
            margin-bottom: 3px;
        }

        .controls {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #dbeafe;
            box-shadow: 0 8px 20px rgba(8, 51, 79, 0.07);
            padding: 16px;
        }

        .card h3 {
            font-size: 15px;
            margin-bottom: 10px;
            color: #0b3c5d;
        }

        .card p.helper {
            margin-bottom: 10px;
            font-size: 12px;
            color: #64748b;
        }

        .input,
        .file-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            outline: none;
            font-size: 14px;
            transition: border-color .2s;
        }

        .input:focus,
        .file-input:focus {
            border-color: #0f6db3;
        }

        .actions-row {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
        }

        .upload-progress {
            margin-top: 12px;
            display: none;
            gap: 8px;
        }

        .upload-progress.show {
            display: grid;
        }

        .upload-progress-bar {
            height: 10px;
            width: 100%;
            background: #dbeafe;
            border-radius: 999px;
            overflow: hidden;
        }

        .upload-progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #0f6db3, #16a34a);
            transition: width .2s ease;
        }

        .upload-progress-text {
            font-size: 12px;
            color: #475569;
            font-weight: 600;
        }

        .btn {
            border: 0;
            border-radius: 999px;
            padding: 10px 15px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0f6db3, #1e88e5);
            color: #fff;
        }

        .btn-green {
            background: linear-gradient(135deg, #15803d, #16a34a);
            color: #fff;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .view-toggle {
            display: inline-flex;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 4px;
            gap: 4px;
        }

        .view-btn {
            border: 0;
            background: transparent;
            color: #475569;
            border-radius: 999px;
            width: 36px;
            height: 34px;
            padding: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .view-btn svg {
            width: 16px;
            height: 16px;
        }

        .view-btn.active {
            background: #0f6db3;
            color: #fff;
            box-shadow: 0 8px 18px rgba(15, 109, 179, 0.24);
        }

        .search {
            flex: 1;
            min-width: 220px;
        }

        .sort-select {
            min-width: 180px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #334155;
            font-weight: 600;
        }

        .breadcrumb a {
            color: #0f5fa6;
            text-decoration: none;
        }

        .drop-root {
            border: 2px dashed #93c5fd;
            background: #eff6ff;
            color: #0f5fa6;
            border-radius: 12px;
            font-weight: 700;
            padding: 10px 12px;
            text-align: center;
        }

        .drop-root.drag-over {
            border-color: #2563eb;
            background: #dbeafe;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 14px;
        }

        .grid.list-view {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .item {
            border: 1px solid #dbeafe;
            background: #fff;
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 8px 18px rgba(15, 95, 166, 0.07);
            transition: transform .18s ease, box-shadow .18s ease;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .item:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 22px rgba(15, 95, 166, 0.14);
        }

        .item.selected {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16), 0 12px 22px rgba(15, 95, 166, 0.14);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .item.dragging {
            opacity: .5;
        }

        .grid.list-view .item {
            flex-direction: row;
            align-items: center;
            gap: 0;
            padding: 11px 16px;
            border-radius: 14px;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04), 0 6px 14px rgba(15, 23, 42, 0.03);
            border: 1px solid #dbe7f5;
            transition: background .16s ease, border-color .16s ease, box-shadow .16s ease;
        }

        .item-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .grid.list-view .item-link {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 0;
            flex-shrink: 0;
            margin-right: 14px;
        }

        .item-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
        }

        .item-name-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .item-name-link:hover .name {
            color: #0f5fa6;
        }

        .item-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }

        .item-filetype {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .type-badge {
            min-width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #0f5fa6;
            font-size: 15px;
        }

        .type-badge.folder {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            color: #c2410c;
        }

        .preview {
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(135deg, #e0f2fe, #f8fafc);
            border: 1px solid #dbeafe;
            min-height: 146px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            position: relative;
        }

        .grid.list-view .item-top {
            min-width: auto;
            max-width: none;
            margin-bottom: 0;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            gap: 0;
        }

        .preview img {
            width: 100%;
            height: 146px;
            object-fit: cover;
            display: block;
        }

        .grid.list-view .preview {
            display: none;
        }

        .grid.list-view .preview img {
            display: none;
        }

        .preview.folder-preview {
            background: linear-gradient(135deg, #dbeafe, #eff6ff 55%, #dcfce7);
            color: #0b3c5d;
            flex-direction: column;
            gap: 6px;
        }

        .preview.folder-preview strong {
            font-size: 42px;
            line-height: 1;
        }

        .preview.file-preview {
            color: #0f5fa6;
            font-size: 42px;
        }

        .grid.list-view .preview.file-preview,
        .grid.list-view .preview.folder-preview {
            display: none;
        }

        .item-main {
            min-width: 0;
        }

        .grid:not(.list-view) .item-main {
            min-height: 42px;
            display: flex;
            align-items: flex-end;
        }

        .item-main .name {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 0;
            margin-bottom: 0;
        }

        .grid.list-view .item-main .name {
            min-height: auto;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .icon {
            font-size: 24px;
        }

        .name {
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            word-break: break-word;
            margin-bottom: 2px;
            font-size: 18px;
        }

        .meta {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        .item-footer {
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 32px;
        }

        .meta-stack {
            min-width: 0;
            flex: 1;
        }

        .meta-stack .meta {
            display: block;
        }

        .item-menu-wrap {
            position: relative;
            flex-shrink: 0;
            z-index: 8;
        }

        .item-menu-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 0;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .16s ease, color .16s ease;
            box-shadow: none;
        }

        .item-menu-btn:hover {
            background: #eff6ff;
            color: #334155;
        }

        .item-menu-btn:active {
            background: #dbeafe;
        }

        .grid:not(.list-view) .item-menu-btn {
            transform: translateY(-3px);
        }

        .kebab-dots {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }

        .kebab-dots i {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: currentColor;
            display: block;
        }

        .item-menu {
            position: absolute;
            right: 0;
            bottom: calc(100% + 8px);
            min-width: 200px;
            background: #fff;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
            padding: 8px;
            display: none;
            z-index: 30;
        }

        .item-menu.show {
            display: block;
        }

        .item-menu a,
        .item-menu button {
            width: 100%;
            border: 0;
            background: transparent;
            color: #334155;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            text-align: left;
            cursor: pointer;
        }

        .item-menu a:hover,
        .item-menu button:hover {
            background: #f8fafc;
        }

        .item-menu .danger {
            color: #dc2626;
        }

        .item-menu .menu-icon {
            width: 18px;
            text-align: center;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .item-menu .menu-icon svg,
        .context-menu .menu-icon svg {
            width: 18px;
            height: 18px;
        }

        .folder-drop.drag-over {
            outline: 2px dashed #2563eb;
            outline-offset: 2px;
            background: #f0f9ff;
        }

        .empty {
            background: #fff;
            border-radius: 14px;
            border: 1px dashed #bfdbfe;
            text-align: center;
            padding: 30px 14px;
            color: #475569;
        }

        .preview-modal {
            position: fixed;
            inset: 0;
            background: rgba(2, 8, 23, 0.72);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 120;
        }

        .preview-modal.show {
            display: flex;
        }

        .preview-dialog {
            width: min(1100px, 100%);
            max-height: 92vh;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.3);
            display: flex;
            flex-direction: column;
        }

        .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(90deg, #f8fbff, #eef6ff);
        }

        .preview-title {
            min-width: 0;
        }

        .preview-title strong {
            display: block;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview-title span {
            font-size: 12px;
            color: #64748b;
        }

        .preview-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .preview-btn {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            text-decoration: none;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .preview-body {
            padding: 0;
            background: #f8fafc;
            min-height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-body iframe,
        .preview-body audio,
        .preview-body video,
        .preview-body img {
            width: 100%;
            height: 75vh;
            border: 0;
            display: block;
            background: #fff;
        }

        .preview-body img {
            object-fit: contain;
            background: #0f172a;
        }

        .preview-body video {
            background: #000;
        }

        .preview-body audio {
            height: auto;
            max-width: 720px;
            padding: 24px;
            background: transparent;
        }

        .preview-empty {
            padding: 40px 20px;
            text-align: center;
            color: #475569;
        }

        .context-menu {
            position: fixed;
            min-width: 220px;
            background: #fff;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.22);
            padding: 8px;
            display: none;
            z-index: 140;
        }

        .context-menu.show {
            display: block;
        }

        .context-menu button,
        .context-menu a {
            width: 100%;
            border: 0;
            background: transparent;
            color: #334155;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            text-align: left;
            cursor: pointer;
        }

        .context-menu button:hover,
        .context-menu a:hover {
            background: #f8fafc;
        }

        .context-menu .danger {
            color: #dc2626;
        }

        .context-menu .menu-icon {
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .context-menu .hidden {
            display: none;
        }

        @media (max-width: 920px) {
            .drive-wrapper {
                padding: 14px;
            }

            .controls {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .item-menu {
                right: auto;
                left: 0;
            }

            .grid.list-view .item {
                padding: 10px 12px;
            }

            .grid.list-view .item-body {
                gap: 10px;
            }

            .grid.list-view .item-footer {
                gap: 8px;
                padding-left: 10px;
            }

            .grid.list-view .meta-stack .meta {
                font-size: 11px;
            }

            .list-col-header {
                padding: 7px 12px 7px calc(12px + 40px + 10px);
            }
        }

        /* ── LIST VIEW: comprehensive overrides ──────────────── */

        .grid.list-view .item:hover {
            transform: none;
            background: #f8fbff;
            border-color: #bfdbfe;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04), 0 10px 20px rgba(30, 64, 175, 0.08);
        }

        .grid.list-view .item.selected {
            background: linear-gradient(90deg, #eef5ff, #f8fbff);
            border-color: #93c5fd;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.14), 0 10px 20px rgba(59, 130, 246, 0.08);
        }

        /* Hide type text label and size meta in icon area */
        .grid.list-view .item-filetype span:last-child,
        .grid.list-view .item-top > .meta {
            display: none;
        }

        /* Larger icon badge in list row */
        .grid.list-view .type-badge {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            font-size: 20px;
        }

        /* item-body: stretch horizontally in a row */
        .grid.list-view .item-body {
            flex: 1;
            flex-direction: row;
            align-items: center;
            gap: 20px;
            min-width: 0;
        }

        /* Name link takes remaining space */
        .grid.list-view .item-name-link {
            flex: 1;
            min-width: 0;
        }

        /* Name: single line, truncated */
        .grid.list-view .name {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 0;
        }

        /* Footer: row — date on left, 3-dot on right */
        .grid.list-view .item-footer {
            flex-shrink: 0;
            flex-direction: row;
            align-items: center;
            margin-top: 0;
            min-height: auto;
            gap: 12px;
            padding-left: 14px;
            border-left: 1px solid #e2e8f0;
        }

        .grid.list-view .meta-stack {
            flex: none;
        }

        .grid.list-view .meta-stack .meta {
            white-space: nowrap;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        .grid.list-view .item-menu-wrap {
            align-self: center;
        }

        /* List view: column header row */
        .list-col-header {
            display: none;
            align-items: center;
            padding: 7px 16px 7px calc(16px + 40px + 14px);
            gap: 16px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid #dbe7f5;
            margin-bottom: 4px;
            background: linear-gradient(90deg, #f8fbff, #f1f7ff 70%, #f8fbff);
            border-radius: 10px;
        }

        .list-col-header.visible {
            display: flex;
            position: sticky;
            top: 10px;
            z-index: 11;
            backdrop-filter: blur(4px);
        }

        .list-col-header .lch-name {
            flex: 1;
            min-width: 0;
        }

        .list-col-header .lch-date {
            flex-shrink: 0;
            white-space: nowrap;
        }

        .list-col-header .lch-action {
            flex-shrink: 0;
            width: 28px;
        }

        /* ── Drop Zone ───────────────────────────────────── */
        .drop-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 22px 14px 18px;
            border: 2px dashed #bfdbfe;
            border-radius: 14px;
            background: #f8fbff;
            cursor: pointer;
            text-align: center;
            gap: 4px;
            transition: border-color .2s, background .2s;
        }

        .drop-zone:hover,
        .drop-zone.dz-over {
            border-color: #0f6db3;
            background: #eff6ff;
        }

        .drop-zone .file-input {
            display: none;
        }

        #uploadFolderInput {
            display: none;
        }

        .dz-icon {
            color: #93c5fd;
            line-height: 1;
            margin-bottom: 4px;
        }

        .dz-icon svg {
            width: 36px;
            height: 36px;
            display: block;
        }

        .dz-text {
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
        }

        .dz-hint {
            font-size: 11px;
            color: #94a3b8;
        }

        .dz-files {
            font-size: 12px;
            color: #0f6db3;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ── Card head with icon ─────────────────────────── */
        .card-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .card-head-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-head-icon svg {
            width: 18px;
            height: 18px;
        }

        .card-head-icon.blue {
            background: #eff6ff;
            color: #0f6db3;
        }

        .card-head-icon.green {
            background: #f0fdf4;
            color: #15803d;
        }

        .card-head h3 {
            font-size: 15px;
            color: #0b3c5d;
            margin: 0 0 2px;
        }

        .card-head p {
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
        }

        /* ── Breadcrumb chevron separator ────────────────── */
        .bc-sep {
            display: inline-flex;
            align-items: center;
            color: #cbd5e1;
        }

        .bc-sep svg {
            width: 14px;
            height: 14px;
        }

        /* ── Search wrap with icon ───────────────────────── */
        .search-wrap {
            position: relative;
            flex: 1;
            min-width: 220px;
        }

        .search-wrap svg {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: #94a3b8;
            pointer-events: none;
        }

        .search-wrap .input {
            padding-left: 34px;
            width: 100%;
        }

        .no-search-result {
            display: none;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
            font-size: 13px;
            font-weight: 600;
        }

        .no-search-result.show {
            display: block;
        }

        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            padding: 10px 12px;
            background: #ffffff;
        }

        .pagination .page-info {
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .pagination .page-links {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .pagination .page-link {
            text-decoration: none;
            color: #334155;
            border: 1px solid #dbe7f5;
            background: #fff;
            border-radius: 9px;
            min-width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 700;
        }

        .pagination .page-link:hover {
            background: #f8fbff;
            border-color: #bfdbfe;
            color: #0f5fa6;
        }

        .pagination .page-link.active {
            background: #0f6db3;
            border-color: #0f6db3;
            color: #fff;
            box-shadow: 0 8px 18px rgba(15, 109, 179, 0.24);
        }

        .pagination .page-link.ghost {
            color: #64748b;
            background: #f8fafc;
        }

        .toast-zone {
            position: fixed;
            right: 16px;
            bottom: 16px;
            display: grid;
            gap: 8px;
            z-index: 180;
            width: min(360px, calc(100vw - 24px));
        }

        .toast {
            border-radius: 12px;
            border: 1px solid #dbe7f5;
            background: #fff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
            padding: 10px 12px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 600;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .18s ease, transform .18s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .toast.error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .view-btn:focus-visible,
        .item-menu-btn:focus-visible,
        .page-link:focus-visible,
        .input:focus-visible,
        .drop-zone:focus-visible {
            outline: 2px solid #60a5fa;
            outline-offset: 2px;
        }

        /* ── Page Footer (Drive only) ───────────────────── */
        .drive-page-footer {
            margin-top: 8px;
            border: 1px solid rgba(110, 231, 183, 0.26);
            border-radius: 16px;
            background: linear-gradient(135deg, #08334f 0%, #0b5b8c 46%, #16a34a 100%);
            box-shadow: 0 12px 28px rgba(8, 51, 79, 0.24), inset 0 0 14px rgba(255, 255, 255, 0.06);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .drive-page-footer .footer-brand {
            min-width: 0;
        }

        .drive-page-footer .footer-brand strong {
            display: block;
            font-size: 14px;
            color: #ffffff;
            letter-spacing: .01em;
            margin-bottom: 2px;
        }

        .drive-page-footer .footer-brand span {
            display: block;
            font-size: 12px;
            color: rgba(224, 242, 254, 0.9);
        }

        .drive-page-footer .footer-meta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #e2e8f0;
            font-size: 12px;
            font-weight: 600;
            flex-wrap: wrap;
        }

        .drive-page-footer .dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(191, 219, 254, 0.75);
            display: inline-block;
        }

        @media (max-width: 920px) {
            .drive-page-footer {
                padding: 12px;
                border-radius: 14px;
            }

            .drive-page-footer .footer-meta {
                gap: 8px;
                font-size: 11px;
            }

            .pagination {
                padding: 10px;
            }

            .toast-zone {
                left: 12px;
                right: 12px;
                width: auto;
            }
        }

        /* HP fix: list view layout */
        @media (max-width: 680px) {
            .list-col-header.visible {
                display: none;
            }

            .grid.list-view {
                gap: 10px;
            }

            .grid.list-view .item {
                display: grid;
                grid-template-columns: 40px minmax(0, 1fr) auto;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
            }

            .grid.list-view .item-link {
                width: 40px;
                margin-right: 0;
                min-width: 0;
                flex-shrink: 0;
            }

            .grid.list-view .item-top {
                width: 40px;
                margin-bottom: 0;
                justify-content: flex-start;
            }

            .grid.list-view .item-filetype {
                gap: 0;
            }

            .grid.list-view .type-badge {
                width: 34px;
                height: 34px;
                font-size: 17px;
                border-radius: 10px;
            }

            .grid.list-view .item-body {
                min-width: 0;
                display: flex;
                flex-direction: row;
                align-items: center;
                gap: 8px;
            }

            .grid.list-view .item-name-link {
                flex: 1;
                min-width: 0;
            }

            .grid.list-view .item-main .name,
            .grid.list-view .name {
                font-size: 14px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .grid.list-view .item-footer {
                min-height: 0;
                margin-top: 0;
                padding-left: 0;
                border-left: 0;
                gap: 6px;
            }

            .grid.list-view .meta-stack {
                display: none;
            }

            .grid.list-view .item-menu-wrap {
                align-self: center;
            }

            .grid.list-view .item-menu {
                left: auto;
                right: 0;
            }
        }
    /* ===== DARK MODE: PREMIUM ===== */
    body.dark-mode .drive-wrapper {
        background: radial-gradient(circle at 8% -10%, rgba(59, 130, 246, .14), transparent 35%);
    }

    /* Hero tetap cerah-keren seperti mode terang */
    body.dark-mode .hero {
        background: linear-gradient(135deg, #08334f 0%, #0b5b8c 45%, #16a34a 100%);
        border: 1.5px solid rgba(110, 231, 183, .22);
        box-shadow: 0 20px 40px rgba(8, 51, 79, .36), inset 0 0 20px rgba(255, 255, 255, .04);
        color: #fff;
    }
    body.dark-mode .hero p {
        color: rgba(255, 255, 255, .92);
    }
    body.dark-mode .stat {
        background: rgba(255, 255, 255, .14);
        border-color: rgba(255, 255, 255, .22);
        color: #fff;
    }

    /* Konten dark premium tanpa putih */
    body.dark-mode .controls,
    body.dark-mode .card,
    body.dark-mode .toolbar,
    body.dark-mode .grid,
    body.dark-mode .preview-dialog,
    body.dark-mode .context-menu,
    body.dark-mode .item-menu {
        background: linear-gradient(170deg, #16263b, #1b2d45);
        border: 1.5px solid rgba(59, 130, 246, .32);
        box-shadow: 0 14px 34px rgba(2, 6, 23, .36), inset 0 0 18px rgba(59, 130, 246, .08);
        color: #e2e8f0;
    }

    body.dark-mode .card h3,
    body.dark-mode .item-filetype,
    body.dark-mode .name,
    body.dark-mode .meta,
    body.dark-mode .preview-title strong,
    body.dark-mode .preview-title span,
    body.dark-mode .upload-progress-text,
    body.dark-mode .breadcrumb,
    body.dark-mode .breadcrumb a {
        color: #dbeafe;
    }

    body.dark-mode .item {
        background: #142238;
        border: 1.5px solid rgba(59, 130, 246, .28);
        color: #e2e8f0;
    }
    body.dark-mode .item:hover {
        box-shadow: 0 14px 30px rgba(59, 130, 246, .25);
    }
    body.dark-mode .item.selected {
        background: linear-gradient(180deg, #1a2f49 0%, #203956 100%);
        border-color: rgba(96, 165, 250, .75);
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .25), 0 14px 30px rgba(59, 130, 246, .26);
    }

    body.dark-mode .preview,
    body.dark-mode .preview.folder-preview,
    body.dark-mode .preview.file-preview,
    body.dark-mode .preview-body {
        background: linear-gradient(160deg, #13243a, #1a2f49);
        border-color: rgba(96, 165, 250, .24);
        color: #dbeafe;
    }
    body.dark-mode .preview-body iframe,
    body.dark-mode .preview-body audio,
    body.dark-mode .preview-body video,
    body.dark-mode .preview-body img {
        background: #0f1b2f;
    }

    body.dark-mode .drop-zone,
    body.dark-mode .drop-root {
        background: #13243a;
        border-color: rgba(96, 165, 250, .5);
        color: #bfdbfe;
    }
    body.dark-mode .drop-root.drag-over,
    body.dark-mode .folder-drop.drag-over {
        background: #1a3150;
        border-color: rgba(125, 211, 252, .82);
    }

    body.dark-mode .view-toggle {
        background: #112239;
        border-color: rgba(96, 165, 250, .32);
    }
    body.dark-mode .view-btn {
        color: #9fb2c9;
    }
    body.dark-mode .view-btn.active {
        background: linear-gradient(135deg, #1565c0, #1e88e5);
        color: #fff;
    }

    body.dark-mode .item-menu a,
    body.dark-mode .item-menu button,
    body.dark-mode .context-menu a,
    body.dark-mode .context-menu button {
        color: #dbeafe;
    }
    body.dark-mode .item-menu a:hover,
    body.dark-mode .item-menu button:hover,
    body.dark-mode .context-menu a:hover,
    body.dark-mode .context-menu button:hover {
        background: #223754;
    }

    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea,
    body.dark-mode .input,
    body.dark-mode .file-input,
    body.dark-mode .sort-select,
    body.dark-mode .search {
        background: #122035;
        color: #e2e8f0;
        border: 1px solid rgba(59, 130, 246, .34);
    }
    body.dark-mode input::placeholder,
    body.dark-mode textarea::placeholder,
    body.dark-mode .input::placeholder {
        color: #8fa8c5;
    }
    body.dark-mode input:focus,
    body.dark-mode select:focus,
    body.dark-mode textarea:focus,
    body.dark-mode .input:focus,
    body.dark-mode .file-input:focus {
        border-color: rgba(96, 165, 250, .78);
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .2);
    }

    body.dark-mode #pickFolderBtn {
        background: #132742 !important;
        color: #dbeafe !important;
        border-color: rgba(96, 165, 250, .36) !important;
    }

    body.dark-mode .toast {
        background: #1a2a40;
        border: 1px solid rgba(59, 130, 246, .3);
        color: #e2e8f0;
    }

    body.dark-mode .drive-page-footer {
        background: linear-gradient(135deg, #072741 0%, #0a4f7c 46%, #15803d 100%);
        border-color: rgba(110, 231, 183, .24);
        box-shadow: 0 14px 30px rgba(2, 6, 23, .4), inset 0 0 14px rgba(255, 255, 255, .05);
    }
    body.dark-mode .drive-page-footer .footer-brand strong,
    body.dark-mode .drive-page-footer .footer-brand span,
    body.dark-mode .drive-page-footer .footer-meta {
        color: #e2e8f0;
    }
    body.dark-mode .drive-page-footer .dot {
        background: rgba(191, 219, 254, .78);
    }
    </style>
</head>
<body>
    <div class="layout">
        <?php include_once '../sidebar.php'; ?>

        <main>
            <?php include_once '../topbar.php'; ?>

            <div class="drive-wrapper">
                <section class="hero">
                    <h1>Drive PPI</h1>
                    <p>Satu tempat untuk menyimpan folder dan file tim PPI, dengan navigasi bertingkat seperti cloud drive.</p>
                    <div class="stats">
                        <div class="stat">
                            <b><?= $folderCount; ?></b>
                            <span>Folder di lokasi ini</span>
                        </div>
                        <div class="stat">
                            <b><?= $fileCount; ?></b>
                            <span>File di lokasi ini</span>
                        </div>
                        <div class="stat">
                            <b><?= h(formatBytes($totalSize)); ?></b>
                            <span>Total ukuran file</span>
                        </div>
                    </div>
                </section>

                <section class="controls">
                    <div class="card">
                        <div class="card-head">
                            <span class="card-head-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></span>
                            <div><h3>Upload File / Folder</h3><p>Bisa upload file biasa atau satu folder sekaligus</p></div>
                        </div>
                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <input type="hidden" name="parent_id" value="<?= h($currentParent); ?>">
                            <input type="hidden" name="sort" value="<?= h($currentSort); ?>">
                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken); ?>">
                            <label class="drop-zone" id="uploadDropZone" for="uploadInput">
                                <span class="dz-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg></span>
                                <span class="dz-text">Klik untuk pilih file</span>
                                <span class="dz-hint">Atau gunakan tombol folder untuk upload satu folder lengkap</span>
                                <span class="dz-files" id="dzFiles"></span>
                                <input class="file-input" type="file" name="file[]" id="uploadInput" multiple>
                            </label>
                            <input class="file-input" type="file" name="folder_file[]" id="uploadFolderInput" webkitdirectory directory multiple>
                            <div class="actions-row" style="justify-content:flex-start; gap:8px; margin-top:10px;">
                                <button class="btn btn-primary" type="button" id="pickFileBtn">Pilih File</button>
                                <button class="btn" type="button" id="pickFolderBtn" style="background:#eff6ff;color:#0f5fa6;border:1px solid #bfdbfe;">Pilih Folder</button>
                            </div>
                            <div class="upload-progress" id="uploadProgress">
                                <div class="upload-progress-bar">
                                    <div class="upload-progress-fill" id="uploadProgressFill"></div>
                                </div>
                                <div class="upload-progress-text" id="uploadProgressText">Menunggu upload...</div>
                            </div>
                            <div class="actions-row">
                                <button class="btn btn-primary" name="upload" id="uploadButton" type="submit">Upload Sekarang</button>
                            </div>
                        </form>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <span class="card-head-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></span>
                            <div><h3>Buat Folder Baru</h3><p>Buat folder di lokasi ini</p></div>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="parent_id" value="<?= h($currentParent); ?>">
                            <input type="hidden" name="sort" value="<?= h($currentSort); ?>">
                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken); ?>">
                            <input class="input" type="text" name="nama_folder" placeholder="Contoh: Laporan Tahunan" required>
                            <div class="actions-row">
                                <button class="btn btn-green" name="buat_folder" type="submit">Buat Folder</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="card">
                    <div class="toolbar">
                        <div class="breadcrumb">
                            <a href="drive.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width:14px;height:14px;vertical-align:-2px;margin-right:4px;"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Root</a>
                            <?php foreach ($crumbs as $crumb): ?>
                                <span class="bc-sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
                                <a href="drive.php?<?= http_build_query(array_filter(['parent' => $crumb['id'], 'sort' => $currentSort !== 'latest' ? $currentSort : null])); ?>"><?= h($crumb['name']); ?></a>
                            <?php endforeach; ?>
                        </div>
                        <div class="search-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input id="searchItem" class="input" type="text" placeholder="Cari file atau folder...">
                        </div>
                        <select id="sortSelect" class="input sort-select">
                            <?php foreach ($sortOptions as $sortKey => $sortConfig): ?>
                                <option value="<?= h($sortKey); ?>" <?= $currentSort === $sortKey ? 'selected' : ''; ?>><?= h($sortConfig['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="view-toggle">
                            <button type="button" class="view-btn active" id="gridViewBtn" title="Tampilan Grid">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            </button>
                            <button type="button" class="view-btn" id="listViewBtn" title="Tampilan List">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                            </button>
                        </div>
                    </div>
                </section>

                <section id="dropRoot" class="drop-root" data-folder-id="">
                    Seret item ke sini untuk memindahkan ke Root
                </section>

                <?php if (count($items) > 0): ?>
                    <div class="list-col-header" id="listColHeader">
                        <span class="lch-name">Nama</span>
                        <span class="lch-date">Diperbarui</span>
                        <span class="lch-action"></span>
                    </div>

                    <section class="no-search-result" id="noSearchResult">
                        Tidak ada file atau folder yang cocok dengan kata kunci pencarian.
                    </section>

                    <section class="grid" id="driveGrid">
                        <?php foreach ($items as $item): ?>
                            <?php
                                $isFolder = $item['type'] === 'folder';
                                $isImage = strpos((string) $item['type'], 'image/') === 0;
                                $itemId = $item['id'];
                                $displayName = normalizeDisplayName($item['name']);
                                $updatedText = !empty($item['updated_at']) ? date('d M Y H:i', strtotime($item['updated_at'])) : '-';
                                $previewType = previewMode($item['type'], $item['name']);
                                $viewerUrl = buildViewerUrl($item['type'], $item['name'], $item['url'] ?? '');
                                $itemHref = $isFolder
                                    ? 'drive.php?' . http_build_query(array_filter(['parent' => $itemId, 'sort' => $currentSort !== 'latest' ? $currentSort : null]))
                                    : $viewerUrl;
                                $useGoogleViewer = !$isFolder && isGoogleViewerType($item['type'], $item['name']);
                                $extension = strtoupper((string) pathinfo($item['name'], PATHINFO_EXTENSION));
                                $typeLabel = $isFolder ? 'Folder' : ($extension !== '' ? $extension : 'File');
                            ?>
                            <article
                                class="item <?= $isFolder ? 'folder-drop' : ''; ?>"
                                draggable="true"
                                tabindex="0"
                                data-id="<?= h($itemId); ?>"
                                data-name="<?= h(strtolower($displayName)); ?>"
                                data-folder-id="<?= $isFolder ? h($itemId) : ''; ?>"
                                data-item-title="<?= h($displayName); ?>"
                                data-item-href="<?= h($itemHref); ?>"
                                data-item-preview-mode="<?= h($previewType); ?>"
                                data-item-preview-url="<?= h($viewerUrl); ?>"
                                data-item-download-url="<?= h($item['url'] ?? ''); ?>"
                                data-item-delete-url="<?= h('drive.php?' . http_build_query(array_filter(['parent' => $currentParent, 'sort' => $currentSort !== 'latest' ? $currentSort : null, 'page' => $currentPage > 1 ? $currentPage : null, 'hapus' => $itemId, 'csrf_token' => $csrfToken]))); ?>"
                                data-item-google-url="<?= h($useGoogleViewer ? $viewerUrl : ''); ?>"
                                data-is-folder="<?= $isFolder ? '1' : '0'; ?>"
                            >
                                <a
                                    class="item-link"
                                    href="<?= h($itemHref); ?>"
                                    <?= $isFolder || $previewType !== '' ? '' : 'target="_blank" rel="noopener noreferrer"'; ?>
                                    <?= !$isFolder && $previewType !== '' ? 'data-preview-mode="' . h($previewType) . '"' : ''; ?>
                                    <?= !$isFolder && $previewType !== '' ? 'data-preview-url="' . h($viewerUrl) . '"' : ''; ?>
                                    <?= !$isFolder && $previewType !== '' ? 'data-download-url="' . h($item['url']) . '"' : ''; ?>
                                    <?= !$isFolder && $previewType !== '' ? 'data-title="' . h($displayName) . '"' : ''; ?>
                                >
                                    <div class="item-top">
                                        <div class="item-filetype">
                                            <span class="type-badge <?= $isFolder ? 'folder' : ''; ?>"><?= h(itemIcon($item['type'])); ?></span>
                                            <span><?= h($typeLabel); ?></span>
                                        </div>
                                        <span class="meta"><?= $isFolder ? 'Folder' : h(formatBytes($item['size'])); ?></span>
                                    </div>

                                    <?php if ($isFolder): ?>
                                        <div class="preview folder-preview">
                                            <strong>📁</strong>
                                            <span>Buka folder</span>
                                        </div>
                                    <?php elseif ($isImage): ?>
                                        <div class="preview">
                                            <img src="<?= h($item['url']); ?>" alt="<?= h($displayName); ?>">
                                        </div>
                                    <?php else: ?>
                                        <div class="preview file-preview">
                                            <span><?= h(itemIcon($item['type'])); ?></span>
                                        </div>
                                    <?php endif; ?>

                                </a>

                                <div class="item-body">
                                    <a
                                        class="item-name-link <?= !$isFolder && $previewType !== '' ? 'item-link' : ''; ?>"
                                        href="<?= h($itemHref); ?>"
                                        <?= $isFolder || $previewType !== '' ? '' : 'target="_blank" rel="noopener noreferrer"'; ?>
                                        <?= !$isFolder && $previewType !== '' ? 'data-preview-mode="' . h($previewType) . '"' : ''; ?>
                                        <?= !$isFolder && $previewType !== '' ? 'data-preview-url="' . h($viewerUrl) . '"' : ''; ?>
                                        <?= !$isFolder && $previewType !== '' ? 'data-download-url="' . h($item['url']) . '"' : ''; ?>
                                        <?= !$isFolder && $previewType !== '' ? 'data-title="' . h($displayName) . '"' : ''; ?>
                                    >
                                        <div class="item-main">
                                            <div class="name"><?= h($displayName); ?></div>
                                        </div>
                                    </a>

                                    <div class="item-footer">
                                        <div class="meta-stack">
                                            <span class="meta">Update: <?= h($updatedText); ?></span>
                                        </div>

                                        <div class="item-menu-wrap">
                                            <button type="button" class="item-menu-btn" data-menu-toggle aria-label="Menu aksi" aria-expanded="false">
                                                <span class="kebab-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                                            </button>
                                            <div class="item-menu" data-menu>
                                                <button type="button" class="rename-btn" data-id="<?= h($itemId); ?>" data-name="<?= h($displayName); ?>">
                                                    <span class="menu-icon"><?= menuIconSvg('rename'); ?></span>
                                                    <span>Ganti nama</span>
                                                </button>
                                                <a href="<?= h($itemHref); ?>" <?= $isFolder ? '' : 'target="_blank" rel="noopener noreferrer"'; ?>>
                                                    <span class="menu-icon"><?= menuIconSvg('open'); ?></span>
                                                    <span><?= $isFolder ? 'Buka folder' : 'Buka di tab baru'; ?></span>
                                                </a>
                                                <?php if (!$isFolder && $useGoogleViewer): ?>
                                                    <a href="<?= h($viewerUrl); ?>" target="_blank" rel="noopener noreferrer">
                                                        <span class="menu-icon"><?= menuIconSvg('google'); ?></span>
                                                        <span>Buka Google</span>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!$isFolder): ?>
                                                    <a href="<?= h($item['url']); ?>" download>
                                                        <span class="menu-icon"><?= menuIconSvg('download'); ?></span>
                                                        <span>Download</span>
                                                    </a>
                                                <?php endif; ?>
                                                <a class="danger" href="drive.php?<?= http_build_query(array_filter(['parent' => $currentParent, 'sort' => $currentSort !== 'latest' ? $currentSort : null, 'page' => $currentPage > 1 ? $currentPage : null, 'hapus' => $itemId, 'csrf_token' => $csrfToken])); ?>" onclick="return confirm('Hapus item ini? Jika folder, semua isi folder ikut terhapus.');">
                                                    <span class="menu-icon"><?= menuIconSvg('delete'); ?></span>
                                                    <span>Hapus</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>
                <?php else: ?>
                    <section class="empty">
                        Folder ini masih kosong. Mulai dengan membuat folder baru atau upload file.
                    </section>
                <?php endif; ?>

                <?php if ($totalPages > 1): ?>
                    <section class="pagination" aria-label="Navigasi halaman">
                        <div class="page-info">Halaman <?= h($currentPage); ?> dari <?= h($totalPages); ?> • Total <?= h($totalItems); ?> item</div>
                        <div class="page-links">
                            <?php
                                $baseQuery = ['parent' => $currentParent, 'sort' => $currentSort !== 'latest' ? $currentSort : null];
                                $prevPage = max(1, $currentPage - 1);
                                $nextPage = min($totalPages, $currentPage + 1);
                            ?>
                            <a class="page-link ghost" href="drive.php?<?= http_build_query(array_filter($baseQuery + ['page' => $prevPage > 1 ? $prevPage : null])); ?>" aria-label="Halaman sebelumnya">‹</a>
                            <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                for ($p = $startPage; $p <= $endPage; $p++):
                            ?>
                                <a class="page-link <?= $p === $currentPage ? 'active' : ''; ?>" href="drive.php?<?= http_build_query(array_filter($baseQuery + ['page' => $p > 1 ? $p : null])); ?>"><?= h($p); ?></a>
                            <?php endfor; ?>
                            <a class="page-link ghost" href="drive.php?<?= http_build_query(array_filter($baseQuery + ['page' => $nextPage > 1 ? $nextPage : null])); ?>" aria-label="Halaman berikutnya">›</a>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="drive-page-footer" aria-label="Footer halaman Drive">
                    <div class="footer-brand">
                        <strong>Drive PPI</strong>
                        <span>Pusat penyimpanan dokumen tim PPI</span>
                    </div>
                    <div class="footer-meta">
                        <span><?= date('Y'); ?> Portal PPI</span>
                        <i class="dot" aria-hidden="true"></i>
                        <span><?= h($folderCount); ?> folder</span>
                        <i class="dot" aria-hidden="true"></i>
                        <span><?= h($fileCount); ?> file</span>
                        <i class="dot" aria-hidden="true"></i>
                        <span><?= h(formatBytes($totalSize)); ?></span>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="preview-modal" id="previewModal">
        <div class="preview-dialog">
            <div class="preview-header">
                <div class="preview-title">
                    <strong id="previewTitle">Preview File</strong>
                    <span id="previewSubtitle">Pratinjau dokumen</span>
                </div>
                <div class="preview-header-actions">
                    <a id="previewOpenNew" class="preview-btn" href="#" target="_blank" rel="noopener noreferrer">Buka Tab Baru</a>
                    <a id="previewDownload" class="preview-btn" href="#" download>Download</a>
                    <button type="button" id="previewClose" class="preview-btn">Tutup</button>
                </div>
            </div>
            <div class="preview-body" id="previewBody">
                <div class="preview-empty">Preview belum tersedia.</div>
            </div>
        </div>
    </div>

    <div class="context-menu" id="itemContextMenu">
        <button type="button" id="contextRename">
            <span class="menu-icon"><?= menuIconSvg('rename'); ?></span>
            <span>Ganti nama</span>
        </button>
        <button type="button" id="contextOpen">
            <span class="menu-icon"><?= menuIconSvg('open'); ?></span>
            <span id="contextOpenLabel">Buka</span>
        </button>
        <a id="contextGoogle" href="#" target="_blank" rel="noopener noreferrer">
            <span class="menu-icon"><?= menuIconSvg('google'); ?></span>
            <span>Buka Google</span>
        </a>
        <a id="contextDownload" href="#" download>
            <span class="menu-icon"><?= menuIconSvg('download'); ?></span>
            <span>Download</span>
        </a>
        <a id="contextDelete" class="danger" href="#">
            <span class="menu-icon"><?= menuIconSvg('delete'); ?></span>
            <span>Hapus</span>
        </a>
    </div>

    <div class="toast-zone" id="toastZone" aria-live="polite" aria-atomic="false"></div>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>
    <script>
        (function () {
            const csrfToken = <?= json_encode($csrfToken); ?>;
            const grid = document.getElementById('driveGrid');
            const searchInput = document.getElementById('searchItem');
            const noSearchResult = document.getElementById('noSearchResult');
            const dropRoot = document.getElementById('dropRoot');
            const toastZone = document.getElementById('toastZone');
            const previewModal = document.getElementById('previewModal');
            const previewBody = document.getElementById('previewBody');
            const previewTitle = document.getElementById('previewTitle');
            const previewSubtitle = document.getElementById('previewSubtitle');
            const previewOpenNew = document.getElementById('previewOpenNew');
            const previewDownload = document.getElementById('previewDownload');
            const previewClose = document.getElementById('previewClose');
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');
            const sortSelect = document.getElementById('sortSelect');
            const uploadForm = document.getElementById('uploadForm');
            const uploadInput = document.getElementById('uploadInput');
            const uploadFolderInput = document.getElementById('uploadFolderInput');
            const pickFileBtn = document.getElementById('pickFileBtn');
            const pickFolderBtn = document.getElementById('pickFolderBtn');
            const uploadButton = document.getElementById('uploadButton');
            const uploadProgress = document.getElementById('uploadProgress');
            const uploadProgressFill = document.getElementById('uploadProgressFill');
            const uploadProgressText = document.getElementById('uploadProgressText');
            const menuToggleButtons = document.querySelectorAll('[data-menu-toggle]');
            const itemCards = document.querySelectorAll('.item');
            const itemContextMenu = document.getElementById('itemContextMenu');
            const contextRename = document.getElementById('contextRename');
            const contextOpen = document.getElementById('contextOpen');
            const contextOpenLabel = document.getElementById('contextOpenLabel');
            const contextGoogle = document.getElementById('contextGoogle');
            const contextDownload = document.getElementById('contextDownload');
            const contextDelete = document.getElementById('contextDelete');
            let draggingId = null;
            let activeItem = null;

            const subtitleMap = {
                image: 'Pratinjau gambar',
                audio: 'Pratinjau audio',
                video: 'Pratinjau video',
                pdf: 'Pratinjau PDF',
                office: 'Pratinjau Office via Google Viewer'
            };

            const showToast = (message, type = 'error') => {
                if (!toastZone) {
                    return;
                }
                const toast = document.createElement('div');
                toast.className = 'toast ' + type;
                toast.textContent = message;
                toastZone.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.add('show');
                });

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 220);
                }, 2600);
            };

            const isInteractiveTarget = (target) => {
                if (!target || !(target instanceof Element)) {
                    return false;
                }
                return Boolean(target.closest('a, button, input, select, textarea, [data-menu-toggle]'));
            };

            const applyView = (view) => {
                if (!grid) {
                    return;
                }

                const useList = view === 'list';
                grid.classList.toggle('list-view', useList);

                const listColHeader = document.getElementById('listColHeader');
                if (listColHeader) {
                    listColHeader.classList.toggle('visible', useList);
                }

                if (gridViewBtn) {
                    gridViewBtn.classList.toggle('active', !useList);
                    gridViewBtn.setAttribute('aria-pressed', useList ? 'false' : 'true');
                }
                if (listViewBtn) {
                    listViewBtn.classList.toggle('active', useList);
                    listViewBtn.setAttribute('aria-pressed', useList ? 'true' : 'false');
                }

                try {
                    localStorage.setItem('drive-view-mode', useList ? 'list' : 'grid');
                } catch (error) {
                }
            };

            const clearSelectedItems = () => {
                document.querySelectorAll('.item.selected').forEach((item) => {
                    item.classList.remove('selected');
                });
            };

            const selectItem = (item) => {
                if (!item) {
                    return;
                }
                clearSelectedItems();
                item.classList.add('selected');
                activeItem = item;
            };

            const hideContextMenu = () => {
                if (itemContextMenu) {
                    itemContextMenu.classList.remove('show');
                }
            };

            const renameItem = async (id, oldName) => {
                const nextName = prompt('Masukkan nama baru:', oldName || '');
                if (nextName === null) {
                    return;
                }

                const cleanName = nextName.trim();
                if (!cleanName) {
                    showToast('Nama tidak boleh kosong.', 'error');
                    return;
                }

                const body = new URLSearchParams();
                body.append('rename', '1');
                body.append('id', id);
                body.append('new_name', cleanName);
                body.append('csrf_token', csrfToken);

                const response = await fetch('drive.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                });

                const result = (await response.text()).trim();
                if (result === 'ok') {
                    showToast('Nama berhasil diperbarui.', 'success');
                    setTimeout(() => window.location.reload(), 280);
                    return;
                }
                if (result === 'empty') {
                    showToast('Nama tidak boleh kosong.', 'error');
                    return;
                }
                if (result === 'csrf') {
                    showToast('Sesi keamanan berakhir. Muat ulang halaman.', 'error');
                    return;
                }
                showToast('Gagal rename item.', 'error');
            };

            const openItemAction = (item) => {
                if (!item) {
                    return;
                }

                const isFolder = item.dataset.isFolder === '1';
                const previewMode = item.dataset.itemPreviewMode || '';
                const href = item.dataset.itemHref || '#';

                if (isFolder) {
                    window.location.href = href;
                    return;
                }

                if (previewMode) {
                    openPreview({
                        mode: previewMode,
                        title: item.dataset.itemTitle || 'Preview File',
                        previewUrl: item.dataset.itemPreviewUrl || href,
                        downloadUrl: item.dataset.itemDownloadUrl || item.dataset.itemPreviewUrl || href
                    });
                    return;
                }

                window.open(href, '_blank', 'noopener');
            };

            const showContextMenu = (event, item) => {
                if (!itemContextMenu || !item) {
                    return;
                }

                selectItem(item);
                hideContextMenu();

                const isFolder = item.dataset.isFolder === '1';
                const googleUrl = item.dataset.itemGoogleUrl || '';
                const downloadUrl = item.dataset.itemDownloadUrl || '';

                contextOpenLabel.textContent = isFolder ? 'Buka folder' : 'Buka';
                contextGoogle.classList.toggle('hidden', !googleUrl);
                contextDownload.classList.toggle('hidden', isFolder || !downloadUrl);

                contextGoogle.href = googleUrl || '#';
                contextDownload.href = downloadUrl || '#';
                contextDelete.href = item.dataset.itemDeleteUrl || '#';

                const menuWidth = 220;
                const menuHeight = 240;
                const left = Math.min(event.clientX, window.innerWidth - menuWidth - 16);
                const top = Math.min(event.clientY, window.innerHeight - menuHeight - 16);

                itemContextMenu.style.left = left + 'px';
                itemContextMenu.style.top = top + 'px';
                itemContextMenu.classList.add('show');
            };

            const currentParent = <?= json_encode($currentParent); ?>;

            const updateSortUrl = (sortValue) => {
                const url = new URL(window.location.href);

                if (currentParent) {
                    url.searchParams.set('parent', currentParent);
                } else {
                    url.searchParams.delete('parent');
                }

                if (!sortValue || sortValue === 'latest') {
                    url.searchParams.delete('sort');
                } else {
                    url.searchParams.set('sort', sortValue);
                }

                url.searchParams.delete('page');

                window.location.href = url.toString();
            };

            const closePreview = () => {
                if (!previewModal) {
                    return;
                }
                previewModal.classList.remove('show');
                previewBody.innerHTML = '<div class="preview-empty">Preview belum tersedia.</div>';
                document.body.style.overflow = '';
            };

            const openPreview = ({ mode, title, previewUrl, downloadUrl }) => {
                if (!previewModal || !previewBody) {
                    return;
                }

                previewTitle.textContent = title || 'Preview File';
                previewSubtitle.textContent = subtitleMap[mode] || 'Pratinjau file';
                previewOpenNew.href = previewUrl || '#';
                previewDownload.href = downloadUrl || previewUrl || '#';

                if (mode === 'image') {
                    previewBody.innerHTML = '<img src="' + previewUrl + '" alt="' + title.replace(/"/g, '&quot;') + '">';
                } else if (mode === 'audio') {
                    previewBody.innerHTML = '<audio controls autoplay><source src="' + previewUrl + '"></audio>';
                } else if (mode === 'video') {
                    previewBody.innerHTML = '<video controls autoplay><source src="' + previewUrl + '"></video>';
                } else if (mode === 'pdf' || mode === 'office') {
                    previewBody.innerHTML = '<iframe src="' + previewUrl + '" allowfullscreen loading="lazy"></iframe>';
                } else {
                    previewBody.innerHTML = '<div class="preview-empty">Preview belum tersedia untuk file ini.</div>';
                }

                previewModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            };

            const sendMove = async (id, targetFolder) => {
                const body = new URLSearchParams();
                body.append('move_file', '1');
                body.append('id', id);
                body.append('target_folder', targetFolder || '');
                body.append('csrf_token', csrfToken);

                const response = await fetch('drive.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                });

                const text = (await response.text()).trim();
                if (text !== 'ok') {
                    if (text === 'invalid') {
                        showToast('Tidak bisa memindahkan item ke folder itu.', 'error');
                        return;
                    }
                    if (text === 'csrf') {
                        showToast('Sesi keamanan berakhir. Muat ulang halaman.', 'error');
                        return;
                    }
                    showToast('Gagal memindahkan item. Coba lagi.', 'error');
                    return;
                }
                showToast('Item berhasil dipindahkan.', 'success');
                setTimeout(() => window.location.reload(), 280);
            };

            if (grid) {
                const items = Array.from(grid.querySelectorAll('.item'));

                items.forEach((item) => {
                    item.addEventListener('click', () => {
                        selectItem(item);
                    });

                    item.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' && !isInteractiveTarget(event.target)) {
                            event.preventDefault();
                            openItemAction(item);
                            return;
                        }

                        if (event.key === ' ' && !isInteractiveTarget(event.target)) {
                            event.preventDefault();
                            selectItem(item);
                        }
                    });

                    item.addEventListener('contextmenu', (event) => {
                        event.preventDefault();
                        showContextMenu(event, item);
                    });

                    item.addEventListener('dragstart', () => {
                        draggingId = item.dataset.id;
                        item.classList.add('dragging');
                        hideContextMenu();
                    });

                    item.addEventListener('dragend', () => {
                        draggingId = null;
                        item.classList.remove('dragging');
                    });
                });

                const folderDrops = Array.from(grid.querySelectorAll('.folder-drop'));
                folderDrops.forEach((folder) => {
                    folder.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        folder.classList.add('drag-over');
                    });

                    folder.addEventListener('dragleave', () => {
                        folder.classList.remove('drag-over');
                    });

                    folder.addEventListener('drop', (e) => {
                        e.preventDefault();
                        folder.classList.remove('drag-over');
                        const target = folder.dataset.folderId;
                        if (!draggingId || !target || draggingId === target) {
                            return;
                        }
                        sendMove(draggingId, target);
                    });
                });
            }

            if (dropRoot) {
                dropRoot.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropRoot.classList.add('drag-over');
                });

                dropRoot.addEventListener('dragleave', () => {
                    dropRoot.classList.remove('drag-over');
                });

                dropRoot.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropRoot.classList.remove('drag-over');
                    if (!draggingId) {
                        return;
                    }
                    sendMove(draggingId, '');
                });
            }

            const renameButtons = document.querySelectorAll('.rename-btn');
            renameButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    hideContextMenu();
                    await renameItem(button.dataset.id, button.dataset.name || '');
                });
            });

            if (searchInput && grid) {
                searchInput.addEventListener('input', () => {
                    const q = searchInput.value.toLowerCase().trim();
                    const items = Array.from(grid.querySelectorAll('.item'));
                    let visibleCount = 0;

                    items.forEach((item) => {
                        const name = item.dataset.name || '';
                        const isVisible = !q || name.includes(q);
                        item.style.display = isVisible ? '' : 'none';
                        if (isVisible) {
                            visibleCount += 1;
                        }
                    });

                    if (noSearchResult) {
                        noSearchResult.classList.toggle('show', q !== '' && visibleCount === 0);
                    }
                });
            }

            const previewLinks = document.querySelectorAll('.item-link[data-preview-mode]');
            previewLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    openPreview({
                        mode: link.dataset.previewMode || '',
                        title: link.dataset.title || 'Preview File',
                        previewUrl: link.dataset.previewUrl || link.getAttribute('href') || '',
                        downloadUrl: link.dataset.downloadUrl || link.dataset.previewUrl || link.getAttribute('href') || ''
                    });
                });
            });

            menuToggleButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    hideContextMenu();

                    const menu = button.parentElement ? button.parentElement.querySelector('[data-menu]') : null;
                    if (!menu) {
                        return;
                    }

                    document.querySelectorAll('[data-menu].show').forEach((openMenu) => {
                        if (openMenu !== menu) {
                            openMenu.classList.remove('show');
                            const linkedBtn = openMenu.parentElement ? openMenu.parentElement.querySelector('[data-menu-toggle]') : null;
                            if (linkedBtn) {
                                linkedBtn.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });

                    menu.classList.toggle('show');
                    button.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
                });
            });

            if (contextRename) {
                contextRename.addEventListener('click', async () => {
                    hideContextMenu();
                    if (!activeItem) {
                        return;
                    }
                    await renameItem(activeItem.dataset.id, activeItem.dataset.itemTitle || '');
                });
            }

            if (contextOpen) {
                contextOpen.addEventListener('click', () => {
                    hideContextMenu();
                    openItemAction(activeItem);
                });
            }

            if (contextDelete) {
                contextDelete.addEventListener('click', (event) => {
                    if (!confirm('Hapus item ini? Jika folder, semua isi folder ikut terhapus.')) {
                        event.preventDefault();
                        return;
                    }
                    hideContextMenu();
                });
            }

            if (contextGoogle) {
                contextGoogle.addEventListener('click', () => {
                    hideContextMenu();
                });
            }

            if (contextDownload) {
                contextDownload.addEventListener('click', () => {
                    hideContextMenu();
                });
            }

            if (sortSelect) {
                sortSelect.addEventListener('change', () => updateSortUrl(sortSelect.value));
            }

            if (gridViewBtn) {
                gridViewBtn.addEventListener('click', () => applyView('grid'));
            }

            if (listViewBtn) {
                listViewBtn.addEventListener('click', () => applyView('list'));
            }

            try {
                applyView(localStorage.getItem('drive-view-mode') || 'grid');
            } catch (error) {
                applyView('grid');
            }

            if (uploadForm) {
                uploadForm.addEventListener('submit', (event) => {
                    event.preventDefault();

                    const selectedFileCount = uploadInput && uploadInput.files ? uploadInput.files.length : 0;
                    const selectedFolderFileCount = uploadFolderInput && uploadFolderInput.files ? uploadFolderInput.files.length : 0;

                    if (selectedFileCount === 0 && selectedFolderFileCount === 0) {
                        showToast('Pilih minimal satu file atau satu folder untuk diupload.', 'error');
                        return;
                    }

                    const formData = new FormData(uploadForm);
                    formData.append('upload', '1');
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'drive.php', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    if (uploadProgress) {
                        uploadProgress.classList.add('show');
                    }
                    if (uploadButton) {
                        uploadButton.disabled = true;
                        uploadButton.textContent = 'Sedang Upload...';
                    }
                    if (uploadProgressFill) {
                        uploadProgressFill.style.width = '0%';
                    }
                    if (uploadProgressText) {
                        uploadProgressText.textContent = 'Mengupload 0%';
                    }

                    xhr.upload.addEventListener('progress', (progressEvent) => {
                        if (!progressEvent.lengthComputable) {
                            return;
                        }

                        const percent = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                        if (uploadProgressFill) {
                            uploadProgressFill.style.width = percent + '%';
                        }
                        if (uploadProgressText) {
                            uploadProgressText.textContent = 'Mengupload ' + percent + '%';
                        }
                    });

                    xhr.addEventListener('load', () => {
                        let result = null;

                        try {
                            result = JSON.parse(xhr.responseText);
                        } catch (error) {
                        }

                        if (!result || !result.success) {
                            const isForbidden = xhr.status === 403;
                            if (result && result.message) {
                                showToast(result.message, 'error');
                            } else if (isForbidden || (xhr.responseText || '').trim() === 'csrf') {
                                showToast('Sesi keamanan upload habis. Muat ulang halaman lalu coba lagi.', 'error');
                            } else {
                                showToast('Upload gagal.', 'error');
                            }
                            if (uploadButton) {
                                uploadButton.disabled = false;
                                uploadButton.textContent = 'Upload Sekarang';
                            }
                            if (uploadProgressText) {
                                uploadProgressText.textContent = 'Upload gagal.';
                            }
                            return;
                        }

                        if (uploadProgressFill) {
                            uploadProgressFill.style.width = '100%';
                        }
                        if (uploadProgressText) {
                            uploadProgressText.textContent = result.message || 'Upload selesai.';
                        }

                        showToast(result.message || 'Upload selesai.', 'success');

                        window.location.href = result.redirect || window.location.href;
                    });

                    xhr.addEventListener('error', () => {
                        showToast('Koneksi upload terputus. Coba lagi.', 'error');
                        if (uploadButton) {
                            uploadButton.disabled = false;
                            uploadButton.textContent = 'Upload Sekarang';
                        }
                        if (uploadProgressText) {
                            uploadProgressText.textContent = 'Upload gagal.';
                        }
                    });

                    xhr.send(formData);
                });
            }

            if (previewClose) {
                previewClose.addEventListener('click', closePreview);
            }

            if (previewModal) {
                previewModal.addEventListener('click', (event) => {
                    if (event.target === previewModal) {
                        closePreview();
                    }
                });
            }

            document.addEventListener('click', (event) => {
                if (!event.target.closest('.item-menu-wrap')) {
                    document.querySelectorAll('[data-menu].show').forEach((openMenu) => {
                        openMenu.classList.remove('show');
                        const linkedBtn = openMenu.parentElement ? openMenu.parentElement.querySelector('[data-menu-toggle]') : null;
                        if (linkedBtn) {
                            linkedBtn.setAttribute('aria-expanded', 'false');
                        }
                    });
                }

                if (!event.target.closest('.item') && !event.target.closest('.context-menu')) {
                    clearSelectedItems();
                    activeItem = null;
                }

                if (!event.target.closest('.context-menu')) {
                    hideContextMenu();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && previewModal && previewModal.classList.contains('show')) {
                    closePreview();
                }

                if (event.key === 'Escape') {
                    document.querySelectorAll('[data-menu].show').forEach((openMenu) => {
                        openMenu.classList.remove('show');
                        const linkedBtn = openMenu.parentElement ? openMenu.parentElement.querySelector('[data-menu-toggle]') : null;
                        if (linkedBtn) {
                            linkedBtn.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
            });
            // ── Drop zone ──────────────────────────────────
            const uploadDropZone = document.getElementById('uploadDropZone');
            const dzFiles = document.getElementById('dzFiles');

            const updateDzLabel = (files) => {
                if (!dzFiles) return;
                const fileCount = uploadInput && uploadInput.files ? uploadInput.files.length : 0;
                const folderCount = uploadFolderInput && uploadFolderInput.files ? uploadFolderInput.files.length : 0;
                const totalCount = fileCount + folderCount;

                if (totalCount === 0) {
                    dzFiles.textContent = '';
                    return;
                }

                if (folderCount > 0 && fileCount === 0) {
                    const firstPath = uploadFolderInput.files[0] && uploadFolderInput.files[0].webkitRelativePath
                        ? uploadFolderInput.files[0].webkitRelativePath.split('/')[0]
                        : 'folder';
                    dzFiles.textContent = firstPath + ' (' + folderCount + ' file)';
                    return;
                }

                if (fileCount === 1 && folderCount === 0) {
                    dzFiles.textContent = uploadInput.files[0].name;
                    return;
                }

                dzFiles.textContent = totalCount + ' item dipilih';
            };

            if (uploadDropZone && uploadInput) {
                ['dragenter', 'dragover'].forEach((ev) => {
                    uploadDropZone.addEventListener(ev, (e) => { e.preventDefault(); uploadDropZone.classList.add('dz-over'); });
                });
                ['dragleave', 'drop'].forEach((ev) => {
                    uploadDropZone.addEventListener(ev, (e) => {
                        if (ev === 'dragleave' && uploadDropZone.contains(e.relatedTarget)) return;
                        uploadDropZone.classList.remove('dz-over');
                    });
                });
                uploadDropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    const droppedFiles = e.dataTransfer.files;
                    if (droppedFiles.length > 0) {
                        try {
                            const dt = new DataTransfer();
                            for (const f of droppedFiles) dt.items.add(f);
                            uploadInput.files = dt.files;
                        } catch (_) {}
                        updateDzLabel(droppedFiles);
                    }
                });
                uploadInput.addEventListener('change', () => updateDzLabel(uploadInput.files));
                if (uploadFolderInput) {
                    uploadFolderInput.addEventListener('change', () => updateDzLabel(uploadFolderInput.files));
                }
                if (pickFileBtn) {
                    pickFileBtn.addEventListener('click', () => uploadInput.click());
                }
                if (pickFolderBtn && uploadFolderInput) {
                    pickFolderBtn.addEventListener('click', () => uploadFolderInput.click());
                }
            }
        })();
    </script>
</body>
</html>