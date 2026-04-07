<?php
include '../koneksi.php';
$id = $_POST['id'];

$sql = "DELETE FROM surveilans_data WHERE id = $id";
if (mysqli_query($conn, $sql)) {
  echo "deleted";
} else {
  echo "error";
}
?>
