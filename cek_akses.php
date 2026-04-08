<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "koneksi.php";

// 1️⃣ Cek login
if (!isset($_SESSION['username'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

// 2️⃣ Ambil data session
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

// 3️⃣ Deteksi folder saat ini
$current_folder = basename(dirname($_SERVER['PHP_SELF']));
if ($current_folder == '' || $current_folder == '.') {
    $current_folder = 'dashboard';
}


// 4️⃣ Jika admin → bebas akses
if ($role === 'admin') {
    return;
}

// 5️⃣ Cek izin user — prepared statement mencegah SQL Injection
$stmt = mysqli_prepare($koneksi, "SELECT id FROM user_access WHERE user_id=? AND halaman=? AND diizinkan=1 LIMIT 1");
mysqli_stmt_bind_param($stmt, "is", $user_id, $current_folder);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 6️⃣ Jika tidak punya izin
if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>
        alert('🚫 Anda tidak memiliki izin untuk mengakses halaman ini!');
        window.location.href = '../dashboard.php';
    </script>";
    exit;
}
?>
