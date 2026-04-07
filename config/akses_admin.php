<?php
session_start();

// Cegah akses tanpa login
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Cegah akses jika bukan admin
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini hanya untuk Admin.');window.location='../dashboard.php';</script>";
    exit;
}
?>
