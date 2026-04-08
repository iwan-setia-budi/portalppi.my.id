<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data) || !csrf_validate($data['csrf_token'] ?? '')) {
  http_response_code(419);
  echo json_encode(['status' => 'csrf']);
  exit;
}

$p = trim($data['pimpinan'] ?? '');
$k = trim($data['ketua'] ?? '');
$s = trim($data['sekretaris'] ?? '');
$d = trim($data['ipcd'] ?? '');
$n = trim($data['ipcn'] ?? '');
$ipcln = trim($data['ipcln'] ?? '[]');
$pj = trim($data['pj'] ?? '[]');

$latestResult = mysqli_query($conn, "SELECT id FROM tb_struktur_ppi ORDER BY id DESC LIMIT 1");
$latestRow = $latestResult ? mysqli_fetch_assoc($latestResult) : null;
$latestId = (int) ($latestRow['id'] ?? 0);

if ($latestId <= 0) {
  http_response_code(404);
  echo json_encode(['status' => 'not-found']);
  exit;
}

$stmt = mysqli_prepare($conn, "UPDATE tb_struktur_ppi SET pimpinan = ?, ketua = ?, sekretaris = ?, ipcd = ?, ipcn = ?, ipcln = ?, pj = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "sssssssi", $p, $k, $s, $d, $n, $ipcln, $pj, $latestId);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode(['status' => $ok ? 'ok' : 'error']);
