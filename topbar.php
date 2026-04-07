<div class="topbar">
  <button class="hamb" id="toggleSidebar">☰</button>

  <div style="font-weight:700;color:var(--brand)">
    <?= isset($pageTitle) ? $pageTitle : 'Dashboard'; ?>
  </div>

  <!-- USER DROPDOWN -->
  <div class="user-menu">
    <div class="user-toggle" id="userToggle">
      👤 <?= $_SESSION['username']; ?> ▼
    </div>

    <div class="user-dropdown" id="userDropdown">
      <a href="/profile.php">Profile</a>
      <a href="/ganti_password.php">Ganti Password</a>
      <hr>
      <a href="/logout.php" class="logout">Logout</a>
    </div>
  </div>

</div>
