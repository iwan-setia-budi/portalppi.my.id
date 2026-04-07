<?php
include_once '../koneksi.php';

$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

$where = [];

if($bulan){
    $where[] = "MONTH(tanggal) = '$bulan'";
}

if($tahun){
    $where[] = "YEAR(tanggal) = '$tahun'";
}

$whereSql = '';
if(count($where) > 0){
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$query = $conn->query("
    SELECT unit, COUNT(*) as total 
    FROM tb_supervise 
    $whereSql
    GROUP BY unit
");

$labels = [];
$values = [];

while($row = $query->fetch_assoc()){
    $labels[] = $row['unit'];
    $values[] = $row['total'];
}

echo json_encode([
    "labels"=>$labels,
    "values"=>$values
]);
