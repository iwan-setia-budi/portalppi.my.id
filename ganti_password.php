<?php
require_once __DIR__ . '/config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . base_url('login.php'));
    exit();
}

$username = $_SESSION['username'];
$pageTitle = "Password";
$message = "";
$error = "";

/* ================== PROSES ================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Ambil password lama dari database
    $stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hashedPassword)) {
        $error = "Password lama salah!";
    } elseif ($new !== $confirm) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($new) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {

        $newHash = password_hash($new, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password=? WHERE username=?");
        $update->bind_param("ss", $newHash, $username);
        $update->execute();
        $update->close();

        $message = "Password berhasil diubah!";
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ganti Password</title>

<link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

<style>

.container{
    padding:30px;
}

/* HEADER */
.page-header{
    margin-bottom:25px;
}

.page-header h2{
    margin:0;
}

/* CARD */
.card-form{
    max-width:480px;
    margin:auto;
    background:white;
    padding:30px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

/* INPUT */
.form-group{
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-size:14px;
}

.form-group input{
    width:100%;
    padding:12px 15px;
    border-radius:12px;
    border:1px solid #d1d5db;
}

.form-group input:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.2);
}

/* BUTTON */
.btn-primary{
    width:100%;
    padding:12px;
    border-radius:12px;
    border:none;
    background:linear-gradient(135deg,#2563eb,#1e3a8a);
    color:white;
    font-weight:600;
    cursor:pointer;
}

/* ALERT */
.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:10px 15px;
    border-radius:10px;
    margin-bottom:15px;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px 15px;
    border-radius:10px;
    margin-bottom:15px;
}



.password-wrapper{
  position:relative;
}

.password-wrapper input{
  width:100%;
  padding:12px 40px 12px 12px; /* kanan extra space */
  border-radius:12px;
  border:1px solid #d1d5db;
}

.toggle-pass{
  position:absolute;
  right:12px;
  top:50%;
  transform:translateY(-50%);
  cursor:pointer;
  font-size:16px;
  user-select:none;
}

/* DARK MODE KHUSUS HALAMAN PASSWORD */
body.dark-mode.password-page .container {
    background: transparent;
}

body.dark-mode.password-page .page-header h2 {
    color: #f8fafc;
}

body.dark-mode.password-page .card-form {
    background: linear-gradient(160deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.92));
    border: 1px solid rgba(51, 65, 85, 0.9);
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.4);
}

body.dark-mode.password-page .form-group label {
    color: #cbd5e1 !important;
}

body.dark-mode.password-page .password-wrapper input,
body.dark-mode.password-page .form-group input {
    background: #1e293b !important;
    border: 1px solid #334155 !important;
    color: #e2e8f0 !important;
}

body.dark-mode.password-page .password-wrapper input:focus,
body.dark-mode.password-page .form-group input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,.25) !important;
}

body.dark-mode.password-page .toggle-pass {
    color: #bfdbfe;
}

body.dark-mode.password-page .btn-primary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border: 1px solid rgba(147, 197, 253, 0.3);
}

body.dark-mode.password-page .alert-success {
    background: rgba(22, 101, 52, 0.22);
    color: #86efac;
    border: 1px solid rgba(134, 239, 172, 0.3);
}

body.dark-mode.password-page .alert-error {
    background: rgba(127, 29, 29, 0.24);
    color: #fecaca;
    border: 1px solid rgba(252, 165, 165, 0.3);
}



</style>
</head>

<body class="password-page">
<div class="layout">

<?php include_once 'sidebar.php'; ?>

<main>

<?php include_once 'topbar.php'; ?>

<div class="container">

<div class="page-header">
    <h2>🔒 Ganti Password</h2>
</div>

<div class="card-form">

<?php if($message): ?>
<div class="alert-success"><?= $message ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<label>Password Lama</label>
<div class="password-wrapper">
  <input type="password" name="current_password" required>
  <span class="toggle-pass" onclick="togglePassword(this)">👁</span>
</div>
</div>


<div class="form-group">
<label>Password Baru</label>
<div class="password-wrapper">
  <input type="password" name="new_password" required>
  <span class="toggle-pass" onclick="togglePassword(this)">👁</span>
</div>
</div>

<div class="form-group">
<label>Konfirmasi Password Baru</label>
<div class="password-wrapper">
  <input type="password" name="confirm_password" required>
  <span class="toggle-pass" onclick="togglePassword(this)">👁</span>
</div>
</div>


<button type="submit" class="btn-primary">
Update Password
</button>

</form>

</div>

</div>

</main>
</div>


<script>
function togglePassword(el){
  let input = el.parentElement.querySelector("input");
  input.type = input.type === "password" ? "text" : "password";
}
</script>


 <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
