<?php
session_start();
if (!isset($_SESSION['username'])) { http_response_code(403); exit; }

include '../koneksi.php';
$id = intval($_POST['id'] ?? 0);

if ($id <= 0) { echo 'error'; exit; }

$stmt = mysqli_prepare($conn, "DELETE FROM surveilans_data WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
if (mysqli_stmt_execute($stmt)) {
    echo "deleted";
} else {
    echo "error";
}
mysqli_stmt_close($stmt);
?>
