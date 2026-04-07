<?php
include_once '../koneksi.php';

$result = $conn->query("SELECT DISTINCT YEAR(tanggal) as tahun FROM tb_supervise ORDER BY tahun DESC");

$tahun = [];

while($row = $result->fetch_assoc()){
    $tahun[] = $row['tahun'];
}

echo json_encode($tahun);
