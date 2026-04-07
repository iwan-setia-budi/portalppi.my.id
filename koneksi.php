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
    $DB_PORT = 3307;
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
