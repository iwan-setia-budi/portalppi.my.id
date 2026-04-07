<?php
include "koneksi.php";

if ($conn) {
    echo "✅ Koneksi berhasil ke database!";
} else {
    echo "❌ Gagal konek: " . mysqli_connect_error();
}
?>
