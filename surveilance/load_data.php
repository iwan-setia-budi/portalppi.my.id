<?php
include '../koneksi.php';
$jenis = $_GET['jenis'];
$data = [];

$q = mysqli_query($conn, "SELECT * FROM surveilans_data WHERE jenis='$jenis' ORDER BY id ASC");
while ($r = mysqli_fetch_assoc($q)) {
  $data[] = $r;
}
echo json_encode($data);
?>
