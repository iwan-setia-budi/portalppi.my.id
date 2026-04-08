<?php
session_start();
if (!isset($_SESSION['username'])) { http_response_code(403); exit; }

include '../koneksi.php';

$tahun  = intval($_POST['tahun']   ?? 0);
$bulan  = trim($_POST['bulan']     ?? '');
$jenis  = trim($_POST['jenis']     ?? '');
$num    = floatval($_POST['numerator']   ?? 0);
$denum  = floatval($_POST['denominator'] ?? 0);
$hasil  = floatval($_POST['hasil'] ?? 0);
$satuan = trim($_POST['satuan']    ?? '');

$stmt = mysqli_prepare($conn,
    "INSERT INTO surveilans_data (tahun, bulan, jenis, numerator, denominator, hasil, satuan)
     VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "issddds", $tahun, $bulan, $jenis, $num, $denum, $hasil, $satuan);

if (mysqli_stmt_execute($stmt)) {
    echo "success";
} else {
    echo "error";
}
mysqli_stmt_close($stmt);
?>
