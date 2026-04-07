<?php
session_start();
include "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // ✅ Gunakan password_verify untuk cek hash
            if (password_verify($password, $user['password'])) {

            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "⚠️ Password salah!";
        }
    } else {
        $error = "⚠️ Username tidak ditemukan!";
    }
}
?>




<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | MyPPI PORTAL</title>
  <style>
    :root {
      --primary: #007bff;
      --background: #f2f7ff;
      --card-bg: #ffffff;
      --input-bg: #f9f9f9;
      --border: #ddd;
      --text: #333;
      --muted: #888;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0; padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--background);
      display: flex; justify-content: center; align-items: center;
      min-height: 100vh; flex-direction: column; padding: 20px;
    }
    .logo {
      font-size: 1.8rem; font-weight: bold;
      color: var(--primary); margin-bottom: 10px;
      display: flex; align-items: center; gap: 8px;
    }
    .login-box {
      background-color: var(--card-bg);
      width: 100%; max-width: 400px;
      padding: 25px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
    }
    .login-box h2 {
      margin-top: 0; font-size: 1.4rem;
      text-align: center; color: var(--text);
    }
    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block; font-size: 0.95rem;
      margin-bottom: 6px; color: var(--text);
    }
    .input-wrapper {
      display: flex; align-items: center;
      background-color: var(--input-bg);
      border: 1px solid var(--border);
      border-radius: 6px; overflow: hidden;
    }
    .input-wrapper span {
      padding: 10px; background-color: #eee;
      font-size: 1rem; border-right: 1px solid var(--border);
      color: #555;
    }
    .input-wrapper input {
      flex: 1; border: none; padding: 10px;
      font-size: 1rem; background-color: transparent; outline: none;
    }
    .login-btn {
      width: 100%; padding: 12px; font-size: 1rem;
      background-color: var(--primary);
      color: #fff; border: none; border-radius: 6px;
      cursor: pointer; transition: background-color 0.2s ease;
    }
    .login-btn:hover { background-color: #005dc5; }
    .error {
      color: red; text-align: center;
      margin-bottom: 15px; font-size: 0.95rem;
    }
  </style>
</head>
<body>
  <div class="logo">🔐 <span>MyPPI PORTAL</span></div>

  <div class="login-box">
    <h2>Silakan Login</h2>
    <p style="text-align:center; color:#777;">Masukkan username dan password</p>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="username">Username</label>
        <div class="input-wrapper">
          <span>@</span>
          <input type="text" name="username" id="username" placeholder="Masukkan username" required />
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <span>🔒</span>
          <input type="password" name="password" id="password" placeholder="Masukkan password" required />
        </div>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>
  </div>

  <footer style="margin-top: 25px; font-size: 0.85rem; color: #888; text-align:center;">
    Developed by <a href="#" style="color:#007bff; text-decoration:none;">Primaya Hospital</a>
  </footer>
</body>
</html>
