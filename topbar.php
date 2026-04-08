<?php
  $username = isset($_SESSION['username']) ? (string) $_SESSION['username'] : 'User';
  $cleanUsername = trim($username);
  $initialSource = preg_replace('/[^a-zA-Z0-9 ]/', '', $cleanUsername);
  $initialParts = preg_split('/\s+/', $initialSource, -1, PREG_SPLIT_NO_EMPTY);
  $userInitials = '';

  if (!empty($initialParts)) {
    foreach (array_slice($initialParts, 0, 2) as $part) {
      $userInitials .= strtoupper(substr($part, 0, 1));
    }
  }

  if ($userInitials === '') {
    $userInitials = strtoupper(substr($cleanUsername !== '' ? $cleanUsername : 'U', 0, 1));
  }
?>

<div class="topbar">
  <button class="hamb" id="toggleSidebar">☰</button>

  <div style="font-weight:700;color:var(--brand)">
    <?= isset($pageTitle) ? $pageTitle : 'Dashboard'; ?>
  </div>

  <div class="topbar-right">
    <button type="button" class="theme-switch" id="toggleThemeGlobal" aria-label="Ubah tema"><span class="theme-text">🌙 Mode Gelap</span></button>

    <!-- USER DROPDOWN -->
    <div class="user-menu">
      <button type="button" class="user-toggle" id="userToggle" aria-expanded="false" aria-haspopup="true" aria-controls="userDropdown">
        <span class="user-toggle-glow" aria-hidden="true"></span>
        <span class="user-avatar" aria-hidden="true"><?= htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="user-meta">
          <span class="user-text"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
        </span>
        <span class="user-caret" aria-hidden="true"></span>
      </button>

      <div class="user-dropdown" id="userDropdown">
        <div class="user-dropdown-header">
          <span class="user-dropdown-avatar"><?= htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8'); ?></span>
          <div class="user-dropdown-meta">
            <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong>
            <span>Administrator aktif</span>
          </div>
        </div>
        <div class="user-dropdown-divider"></div>
        <a href="/profile.php">Profile</a>
        <a href="/ganti_password.php">Ganti Password</a>
        <div class="user-dropdown-divider"></div>
        <a href="/logout.php" class="logout">Logout</a>
      </div>
    </div>
  </div>

</div>
