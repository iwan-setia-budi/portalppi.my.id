<?php
include '../koneksi.php';

$tahun = $_POST['tahun'];
$bulan = $_POST['bulan'];
$jenis = $_POST['jenis'];
$num = $_POST['numerator'];
$denum = $_POST['denominator'];
$hasil = $_POST['hasil'];
$satuan = $_POST['satuan'];

$sql = "INSERT INTO surveilans_data (tahun, bulan, jenis, numerator, denominator, hasil, satuan)
        VALUES ('$tahun', '$bulan', '$jenis', '$num', '$denum', '$hasil', '$satuan')";

if (mysqli_query($conn, $sql)) {
  echo "success";
} else {
  echo "error";
}
?>
