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
    $DB_HOST = "127.0.0.1";
    $DB_USER = "root";
    $DB_PASS = "";
    $DB_NAME = "porx9725_myppi";
    $DB_PORT = 3306;
} else {
    // ===== SERVER / HOSTING =====
    $DB_HOST = "localhost";
    $DB_USER = "porx9725_ppi_user";
    $DB_PASS = "Ppi@2025!";
    $DB_NAME = "porx9725_myppi";
    $DB_PORT = 3306;
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
