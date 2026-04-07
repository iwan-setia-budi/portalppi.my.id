<?php
// tampilkan error sementara untuk memastikan include berfungsi
ini_set('display_errors', 1);
error_reporting(E_ALL);

// mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) session_start();

// ====== lokasi file header, sidebar, footer ======
$includes_path = __DIR__ . '/../includes'; // karena file ini di folder "audit"
$header  = $includes_path . '/header.php';
$sidebar = $includes_path . '/sidebar.php';
$footer  = $includes_path . '/footer.php';

// ====== cek file ======
if (!file_exists($header)) {
    die("⚠️ File header.php tidak ditemukan di: $header");
}
if (!file_exists($sidebar)) {
    die("⚠️ File sidebar.php tidak ditemukan di: $sidebar");
}
if (!file_exists($footer)) {
    die("⚠️ File footer.php tidak ditemukan di: $footer");
}

// ====== tampilkan layout ======
require_once $header;
require_once $sidebar;
?>

<main>
  <div class="container">
    <h2>🖐️ Halaman Audit Kebersihan Tangan</h2>
    <p>Selamat datang, <b>Administrator</b>! Ini adalah halaman khusus audit kebersihan tangan.</p>
  </div>
</main>

<?php
require_once $footer;
