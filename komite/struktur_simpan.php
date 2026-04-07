<?php
include_once '../koneksi.php';
$conn = $koneksi;

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
  $p = mysqli_real_escape_string($conn, $data['pimpinan']);
  $k = mysqli_real_escape_string($conn, $data['ketua']);
  $s = mysqli_real_escape_string($conn, $data['sekretaris']);
  $d = mysqli_real_escape_string($conn, $data['ipcd']);
  $n = mysqli_real_escape_string($conn, $data['ipcn']);
  $ipcln = mysqli_real_escape_string($conn, $data['ipcln']);
  $pj = mysqli_real_escape_string($conn, $data['pj']);

  mysqli_query($conn, "UPDATE tb_struktur_ppi 
                       SET pimpinan='$p', ketua='$k', sekretaris='$s', ipcd='$d', ipcn='$n', ipcln='$ipcln', pj='$pj'
                       ORDER BY id DESC LIMIT 1");
}
