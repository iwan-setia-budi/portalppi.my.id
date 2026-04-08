<?php
// ===========================================================
// CONFIGURATION FILE
// AUTO DETEK LOCALHOST / SERVER
// DATABASE + BASE URL
// ===========================================================

// ===========================================================
// DETEKSI ENVIRONMENT
// ===========================================================

$hostName   = $_SERVER['HTTP_HOST']  ?? '';
$serverAddr = $_SERVER['SERVER_ADDR'] ?? '';

$isLocal = (
    $hostName === 'localhost' ||
    strpos($hostName, 'localhost') !== false ||
    $serverAddr === '127.0.0.1' ||
    $serverAddr === '::1'
);

if (!function_exists('ppi_env')) {
    function ppi_env($key, $default = '')
    {
        $value = getenv($key);
        return ($value !== false && $value !== '') ? $value : $default;
    }
}

// ===========================================================
// KONFIGURASI BASE URL
// ===========================================================

// GANTI 'portalppi.my.id' sesuai nama folder di htdocs
$BASE_URL = $isLocal ? '/portalppi.my.id' : '';

// ===========================================================
// KONFIGURASI DATABASE
// ===========================================================

if ($isLocal) {
    // ===== LOCALHOST (XAMPP) =====
    $DB_HOST = ppi_env('PPI_DB_HOST', '127.0.0.1');
    $DB_USER = ppi_env('PPI_DB_USER', 'root');
    $DB_PASS = ppi_env('PPI_DB_PASS', '');
    $DB_NAME = ppi_env('PPI_DB_NAME', 'porx9725_myppi');
    $DB_PORT = (int) ppi_env('PPI_DB_PORT', '3306');
} else {
    // ===== SERVER / HOSTING =====
    $DB_HOST = ppi_env('PPI_DB_HOST', 'localhost');
    $DB_USER = ppi_env('PPI_DB_USER', 'porx9725_ppi_user');
    $DB_PASS = ppi_env('PPI_DB_PASS', 'Ppi@2025!');
    $DB_NAME = ppi_env('PPI_DB_NAME', 'porx9725_myppi');
    $DB_PORT = (int) ppi_env('PPI_DB_PORT', '3306');
}

// ===========================================================
// MEMBUAT KONEKSI DATABASE
// ===========================================================

$koneksi = mysqli_connect(
    $DB_HOST,
    $DB_USER,
    $DB_PASS,
    $DB_NAME,
    $DB_PORT
);

// ===========================================================
// CEK KONEKSI DATABASE
// ===========================================================

if (!$koneksi) {
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>
        ⚠️ Tidak dapat terhubung ke database.<br>
        Silakan hubungi administrator sistem.
    </h3>");
}

// ===========================================================
// SET CHARSET
// ===========================================================

mysqli_set_charset($koneksi, "utf8mb4");

// ===========================================================
// ALIAS (KOMPATIBILITAS FILE LAMA)
// ===========================================================

$conn = $koneksi;

// ===========================================================
// HELPER URL APLIKASI
// ===========================================================

if (!function_exists('base_url')) {
    function base_url($path = '')
    {
        global $BASE_URL;

        $base = rtrim((string) ($BASE_URL ?? ''), '/');
        $path = ltrim((string) $path, '/');

        if ($path === '') {
            return $base !== '' ? $base : '/';
        }

        return ($base !== '' ? $base : '') . '/' . $path;
    }
}

if (!function_exists('redirect_url')) {
    function redirect_url($path = '')
    {
        header('Location: ' . base_url($path));
        exit;
    }
}

if (!function_exists('safe_query')) {
    function safe_query($conn, $sql)
    {
        try {
            return $conn->query($sql);
        } catch (Throwable $e) {
            error_log('Query gagal: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return false;
        }
    }
}

if (!function_exists('ppi_apply_security_headers')) {
    function ppi_apply_security_headers()
    {
        if (headers_sent()) {
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input()
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_validate')) {
    function csrf_validate($token)
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['csrf_token']) || !is_string($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('ppi_abort_csrf')) {
    function ppi_abort_csrf($responseType = 'html')
    {
        http_response_code(419);

        if ($responseType === 'text') {
            exit('csrf');
        }

        exit("<script>alert('Sesi formulir berakhir. Silakan muat ulang halaman lalu coba lagi.');history.back();</script>");
    }
}

if (!function_exists('ppi_store_uploaded_pdf')) {
    function ppi_store_uploaded_pdf($file, $targetDir, &$errorMessage = '')
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errorMessage = 'File gagal diunggah.';
            return false;
        }

        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $errorMessage = 'Ukuran file maksimal 5 MB.';
            return false;
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $errorMessage = 'Hanya file PDF yang diizinkan.';
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!in_array($mimeType, ['application/pdf', 'application/x-pdf'], true)) {
            $errorMessage = 'Tipe file tidak valid.';
            return false;
        }

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
            $errorMessage = 'Folder upload tidak tersedia.';
            return false;
        }

        $targetDir = rtrim(str_replace('\\', '/', $targetDir), '/');
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.pdf';
        $targetFile = $targetDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            $errorMessage = 'Gagal memindahkan file upload.';
            return false;
        }

        return $targetFile;
    }
}

if (!function_exists('ppi_store_uploaded_file')) {
    function ppi_store_uploaded_file($file, $targetDir, array $allowedMap, &$errorMessage = '', $maxSize = 5242880)
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errorMessage = 'File gagal diunggah.';
            return false;
        }

        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxSize) {
            $errorMessage = 'Ukuran file melebihi batas.';
            return false;
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!isset($allowedMap[$extension])) {
            $errorMessage = 'Ekstensi file tidak diizinkan.';
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!in_array($mimeType, $allowedMap[$extension], true)) {
            $errorMessage = 'Tipe file tidak valid.';
            return false;
        }

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
            $errorMessage = 'Folder upload tidak tersedia.';
            return false;
        }

        $targetDir = rtrim(str_replace('\\', '/', $targetDir), '/');
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetFile = $targetDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            $errorMessage = 'Gagal memindahkan file upload.';
            return false;
        }

        return $targetFile;
    }
}

if (!function_exists('ppi_unlink_upload')) {
    function ppi_unlink_upload($filePath, $allowedDir)
    {
        if (!is_string($filePath) || $filePath === '') {
            return;
        }

        $targetReal = realpath($filePath);
        $allowedReal = realpath($allowedDir);

        if ($targetReal === false || $allowedReal === false) {
            return;
        }

        $allowedPrefix = rtrim(str_replace('\\', '/', $allowedReal), '/') . '/';
        $targetNormalized = str_replace('\\', '/', $targetReal);

        if (strpos($targetNormalized, $allowedPrefix) === 0 && is_file($targetReal)) {
            unlink($targetReal);
        }
    }
}

ppi_apply_security_headers();

if (!function_exists('safe_mysqli_query')) {
    function safe_mysqli_query($conn, $sql)
    {
        try {
            return mysqli_query($conn, $sql);
        } catch (Throwable $e) {
            error_log('Query gagal: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return false;
        }
    }
}

// ===========================================================
// AUTO-REWRITE URL ABSOLUT PADA OUTPUT HTML
// Membuat href/src/action "/..." mengikuti BASE_URL saat lokal.
// ===========================================================

if (!defined('PPI_BASE_URL_REWRITE_ACTIVE')) {
    define('PPI_BASE_URL_REWRITE_ACTIVE', true);

    $basePrefix = rtrim((string) ($BASE_URL ?? ''), '/');

    if ($basePrefix !== '' && PHP_SAPI !== 'cli') {
        ob_start(function ($html) use ($basePrefix) {
            if (!is_string($html) || $html === '') {
                return $html;
            }

            $prefixUrl = static function ($url) use ($basePrefix) {
                if ($url === '' || strpos($url, '/') !== 0 || strpos($url, '//') === 0) {
                    return $url;
                }

                if ($url === $basePrefix || strpos($url, $basePrefix . '/') === 0) {
                    return $url;
                }

                return $basePrefix . $url;
            };

            $html = preg_replace_callback(
                '/\b(href|src|action)\s*=\s*(["\'])(\/(?!\/)[^"\']*)\2/i',
                static function ($m) use ($prefixUrl) {
                    return $m[1] . '=' . $m[2] . $prefixUrl($m[3]) . $m[2];
                },
                $html
            );

            $html = preg_replace_callback(
                '/\b((?:window\.)?location\.href\s*=\s*)(["\'])(\/(?!\/)[^"\']*)\2/i',
                static function ($m) use ($prefixUrl) {
                    return $m[1] . $m[2] . $prefixUrl($m[3]) . $m[2];
                },
                $html
            );

            $html = preg_replace_callback(
                '/\burl\(\s*(["\']?)(\/(?!\/)[^)"\']+)\1\s*\)/i',
                static function ($m) use ($prefixUrl) {
                    return 'url(' . $m[1] . $prefixUrl($m[2]) . $m[1] . ')';
                },
                $html
            );

            return $html;
        });
    }
}
