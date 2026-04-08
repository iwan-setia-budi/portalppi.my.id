<?php
session_start();
if (!isset($_SESSION['username'])) { http_response_code(403); exit; }

include '../koneksi.php';
header('Content-Type: application/json');

$jenis = $_GET['jenis'] ?? '';
$data  = [];

$stmt = mysqli_prepare($conn, "SELECT * FROM surveilans_data WHERE jenis=? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt, "s", $jenis);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = $r;
}
mysqli_stmt_close($stmt);

echo json_encode($data);
?>
