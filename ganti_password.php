<?php
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

<link rel="stylesheet" href="/assets/css/utama.css?v=10">

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



</style>
</head>

<body>
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


 <script src="/assets/js/utama.js?v=6"></script>
</body>
</html>
