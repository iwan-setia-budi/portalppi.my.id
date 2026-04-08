<?php
require_once __DIR__ . '/config/assets.php';
session_start();
include "koneksi.php";

// 🔒 Cek login
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$csrfToken = csrf_token();

$available_pages = [
  'dashboard' => ['icon' => '🏠', 'label' => 'Dashboard'],
  'regulasi' => ['icon' => '📜', 'label' => 'Regulasi'],
  'komite' => ['icon' => '🏛️', 'label' => 'Komite PPI'],
  'surveilance' => ['icon' => '🔬', 'label' => 'Surveilance'],
  'audit' => ['icon' => '📊', 'label' => 'Audit & Supervisi'],
  'diklat' => ['icon' => '🎓', 'label' => 'Diklat & Pelatihan'],
  'dokumen' => ['icon' => '📁', 'label' => 'Dokumen & Formulir'],
  'laporan' => ['icon' => '📈', 'label' => 'Laporan PPI'],
  'users' => ['icon' => '👥', 'label' => 'Manajemen User']
];

// ======================
// HAPUS USER
// ======================
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);
  if (!csrf_validate($_GET['csrf'] ?? '') || $id <= 0) {
    ppi_abort_csrf();
  }

  if ($id === (int) $_SESSION['user_id']) {
    echo "<script>alert('⚠️ Anda tidak bisa menghapus akun yang sedang dipakai.'); window.location='users.php';</script>";
    exit;
  }

  mysqli_begin_transaction($koneksi);
  $deleteAccess = mysqli_prepare($koneksi, "DELETE FROM user_access WHERE user_id = ?");
  $deleteUser = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
  mysqli_stmt_bind_param($deleteAccess, "i", $id);
  mysqli_stmt_bind_param($deleteUser, "i", $id);

  $ok = mysqli_stmt_execute($deleteAccess) && mysqli_stmt_execute($deleteUser);
  if ($ok) {
    mysqli_commit($koneksi);
  } else {
    mysqli_rollback($koneksi);
  }

  mysqli_stmt_close($deleteAccess);
  mysqli_stmt_close($deleteUser);

  echo "<script>alert('🗑️ User berhasil dihapus.'); window.location='users.php';</script>";
  exit;
}

// ======================
// UPDATE USER
// ======================
if (isset($_POST['update'])) {
  if (!csrf_validate($_POST['csrf_token'] ?? '')) {
    ppi_abort_csrf();
  }

  $id = intval($_POST['user_id']);
  $username_input = trim($_POST['username']);
  $password_input = trim($_POST['password']);
  $role_input = trim($_POST['role']);

  $duplicateStmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");
  mysqli_stmt_bind_param($duplicateStmt, "si", $username_input, $id);
  mysqli_stmt_execute($duplicateStmt);
  $cek = mysqli_stmt_get_result($duplicateStmt);
  if ($cek && mysqli_num_rows($cek) > 0) {
    mysqli_stmt_close($duplicateStmt);
    echo "<script>alert('⚠️ Username sudah digunakan!'); window.location='users.php?edit=$id';</script>";
    exit;
  }
  mysqli_stmt_close($duplicateStmt);

  if ($password_input !== '' && strlen($password_input) < 6) {
    echo "<script>alert('⚠️ Password minimal 6 karakter.'); window.location='users.php?edit=$id';</script>";
    exit;
  }

  mysqli_begin_transaction($koneksi);
  if ($password_input !== '') {
    $password = password_hash($password_input, PASSWORD_DEFAULT);
    $updateStmt = mysqli_prepare($koneksi, "UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
    mysqli_stmt_bind_param($updateStmt, "sssi", $username_input, $password, $role_input, $id);
  } else {
    $updateStmt = mysqli_prepare($koneksi, "UPDATE users SET username = ?, role = ? WHERE id = ?");
    mysqli_stmt_bind_param($updateStmt, "ssi", $username_input, $role_input, $id);
  }
  $ok = mysqli_stmt_execute($updateStmt);
  mysqli_stmt_close($updateStmt);

  $deleteAccessStmt = mysqli_prepare($koneksi, "DELETE FROM user_access WHERE user_id = ?");
  mysqli_stmt_bind_param($deleteAccessStmt, "i", $id);
  $ok = $ok && mysqli_stmt_execute($deleteAccessStmt);
  mysqli_stmt_close($deleteAccessStmt);

  $insertAccessStmt = mysqli_prepare($koneksi, "INSERT INTO user_access (user_id, halaman, diizinkan) VALUES (?, ?, 1)");
  if (!empty($_POST['akses'])) {
    foreach ($_POST['akses'] as $halaman) {
      $halaman = basename(trim(strtolower($halaman)));
      if (isset($available_pages[$halaman])) {
        mysqli_stmt_bind_param($insertAccessStmt, "is", $id, $halaman);
        $ok = $ok && mysqli_stmt_execute($insertAccessStmt);
      }
    }
  }
  mysqli_stmt_close($insertAccessStmt);

  if ($ok) {
    mysqli_commit($koneksi);
  } else {
    mysqli_rollback($koneksi);
  }

  echo "<script>alert('✏️ Data user berhasil diperbarui.'); window.location='users.php?tab=daftar';</script>";
  exit;
}

// ======================
// TAMBAH USER BARU
// ======================
if (isset($_POST['tambah'])) {
  if (!csrf_validate($_POST['csrf_token'] ?? '')) {
    ppi_abort_csrf();
  }

  $username_input = trim($_POST['username']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = trim($_POST['role']);

  if (strlen($_POST['password']) < 6) {
    echo "<script>alert('⚠️ Password minimal 6 karakter.'); window.location='users.php';</script>";
    exit;
  }

  // 🚫 Cegah username duplikat
  $cekStmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? LIMIT 1");
  mysqli_stmt_bind_param($cekStmt, "s", $username_input);
  mysqli_stmt_execute($cekStmt);
  $cek = mysqli_stmt_get_result($cekStmt);
  if (mysqli_num_rows($cek) > 0) {
    mysqli_stmt_close($cekStmt);
    echo "<script>alert('⚠️ Username sudah digunakan!'); window.location='users.php';</script>";
    exit;
  }
  mysqli_stmt_close($cekStmt);

  // Simpan user baru
  mysqli_begin_transaction($koneksi);
  $insertUserStmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
  mysqli_stmt_bind_param($insertUserStmt, "sss", $username_input, $password, $role);
  $ok = mysqli_stmt_execute($insertUserStmt);
  mysqli_stmt_close($insertUserStmt);
  $user_id = mysqli_insert_id($koneksi); // Ambil ID user baru

  // Simpan hak akses
  $insertAccessStmt = mysqli_prepare($koneksi, "INSERT INTO user_access (user_id, halaman, diizinkan) VALUES (?, ?, 1)");
  if (!empty($_POST['akses'])) {
    foreach ($_POST['akses'] as $halaman) {
      $halaman = basename(trim(strtolower($halaman))); // Bersihkan dari ../ atau .php
      if (isset($available_pages[$halaman])) {
        mysqli_stmt_bind_param($insertAccessStmt, "is", $user_id, $halaman);
        $ok = $ok && mysqli_stmt_execute($insertAccessStmt);
      }
    }
  }
  mysqli_stmt_close($insertAccessStmt);

  if ($ok) {
    mysqli_commit($koneksi);
  } else {
    mysqli_rollback($koneksi);
  }

  echo "<script>alert('✅ User baru berhasil ditambahkan!'); window.location='users.php';</script>";
  exit;
}

$is_edit_mode = false;
$edit_user = null;
$edit_access = [];

if (isset($_GET['edit'])) {
  $edit_id = intval($_GET['edit']);
  $editStmt = mysqli_prepare($koneksi, "SELECT id, username, role, protected FROM users WHERE id = ? LIMIT 1");
  mysqli_stmt_bind_param($editStmt, "i", $edit_id);
  mysqli_stmt_execute($editStmt);
  $edit_q = mysqli_stmt_get_result($editStmt);
  if ($edit_q && mysqli_num_rows($edit_q) > 0) {
    $is_edit_mode = true;
    $edit_user = mysqli_fetch_assoc($edit_q);

    $aksesEditStmt = mysqli_prepare($koneksi, "SELECT halaman FROM user_access WHERE user_id = ? AND diizinkan = 1");
    mysqli_stmt_bind_param($aksesEditStmt, "i", $edit_id);
    mysqli_stmt_execute($aksesEditStmt);
    $akses_edit_q = mysqli_stmt_get_result($aksesEditStmt);
    while ($a = mysqli_fetch_assoc($akses_edit_q)) {
      $edit_access[] = $a['halaman'];
    }
    mysqli_stmt_close($aksesEditStmt);
  }
  mysqli_stmt_close($editStmt);
}

$countResult = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users");
$countRow = $countResult ? mysqli_fetch_assoc($countResult) : ['total' => 0];
$userCount = (int) ($countRow['total'] ?? 0);

$usersResult = mysqli_query($koneksi, "SELECT id, username, role, protected FROM users ORDER BY id DESC");
$usersList = [];
while ($usersResult && ($row = mysqli_fetch_assoc($usersResult))) {
  $usersList[] = $row;
}

$accessMap = [];
$allAccessResult = mysqli_query($koneksi, "SELECT user_id, halaman FROM user_access WHERE diizinkan = 1 ORDER BY halaman ASC");
while ($allAccessResult && ($row = mysqli_fetch_assoc($allAccessResult))) {
  $accessMap[(int) $row['user_id']][] = ucfirst($row['halaman']);
}
?>

<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "Manajemen User";
?>
<!--end-->

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manajemen User | MyPPI</title>

  <!-- === Link CSS eksternal === -->
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* ========== PAGE WRAPPER ========== */
.um-page {
  width: 100%;
  max-width: none;
  margin: 32px 0 60px;
  padding: 0 24px;
  box-sizing: border-box;
}

/* ========== TABS ========== */
.um-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.um-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1.5px solid rgba(11, 95, 166, .28);
  background: linear-gradient(135deg, #f4f9ff 0%, #e9f4ff 100%);
  color: var(--blue-1);
  padding: 10px 16px;
  border-radius: 14px;
  font-size: .88rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .22s ease;
  box-shadow: 0 8px 18px rgba(13, 83, 145, .12), inset 0 1px 0 rgba(255,255,255,.65);
}

.um-tab-btn:hover {
  background: var(--blue-soft);
  border-color: rgba(11, 95, 166, .45);
}

.um-tab-btn.active {
  background: linear-gradient(135deg, #0f5fa6 0%, #1e88e5 55%, #31a3ff 100%);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 12px 24px rgba(11,95,166,.34), inset 0 1px 0 rgba(255,255,255,.22);
}

.um-tab-btn i {
  font-size: 16px;
  line-height: 1;
}

.um-tab-btn.active i {
  color: #fff;
}

.um-tab-panel {
  display: none;
}

.um-tab-panel.active {
  display: block;
}

/* ========== PAGE HEADER HERO ========== */
.um-hero {
  background: linear-gradient(135deg, var(--blue-1) 0%, var(--blue-2) 52%, var(--blue-3) 100%);
  border-radius: 20px;
  padding: 32px 36px;
  margin-bottom: 28px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: var(--shadow-md);
  position: relative;
  overflow: hidden;
}
.um-hero::before {
  content: '';
  position: absolute;
  top: -60px; right: -60px;
  width: 260px; height: 260px;
  background: rgba(255,255,255,.04);
  border-radius: 50%;
}
.um-hero::after {
  content: '';
  position: absolute;
  bottom: -80px; left: 30%;
  width: 340px; height: 340px;
  background: rgba(255,255,255,.03);
  border-radius: 50%;
}
.um-hero-icon {
  width: 58px; height: 58px;
  background: rgba(255,255,255,.18);
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  backdrop-filter: blur(6px);
  border: 1px solid rgba(255,255,255,.3);
}

.um-hero-icon i {
  font-size: 28px;
  color: #ffffff;
  line-height: 1;
}
.um-hero-text h1 {
  font-size: 1.6rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 5px;
  letter-spacing: -.3px;
}
.um-hero-text p {
  color: rgba(200,225,255,.75);
  font-size: .9rem;
  margin: 0;
}

/* ========== CARDS ========== */
.um-card {
  width: 100%;
  background: var(--card);
  border-radius: 18px;
  box-shadow: var(--shadow-sm);
  border: 1px solid rgba(11, 60, 93, .12);
  overflow: hidden;
  margin-bottom: 28px;
}
.um-card-header {
  background: linear-gradient(90deg, #f6fbff 0%, var(--blue-soft) 100%);
  border-bottom: 1px solid rgba(11, 60, 93, .12);
  padding: 18px 24px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.um-card-header .um-card-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--blue-1), var(--blue-3));
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: white;
  flex-shrink: 0;
}

.um-card-header .um-card-icon i {
  font-size: 18px;
  color: #ffffff;
  line-height: 1;
}
.um-card-header h2 {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--blue-1);
  margin: 0;
}
.um-card-header span.um-badge-count {
  margin-left: auto;
  background: linear-gradient(135deg, #0d5da3 0%, #1d85df 100%);
  color: white;
  font-size: .94rem;
  font-weight: 700;
  padding: 8px 16px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.2);
  box-shadow: 0 10px 20px rgba(14, 103, 177, .35);
}
.um-card-body { padding: 0 24px 24px; }

/* ========== TABLE ========== */
.um-table-wrap { width: 100%; overflow-x: auto; margin-top: 6px; }
.um-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  font-size: .9rem;
  border: 1px solid rgba(11, 60, 93, .16);
  border-radius: 12px;
  overflow: hidden;
}
.um-table thead tr {
  background: linear-gradient(180deg, #ebf5ff 0%, #dceeff 100%);
}
.um-table thead th {
  padding: 13px 16px;
  text-align: left;
  font-size: .78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: var(--blue-2);
  border-bottom: 2px solid rgba(11, 95, 166, .35);
  white-space: normal;
  border-right: 1px solid rgba(11, 60, 93, .12);
}
.um-table thead th:last-child { border-right: none; }
.um-table thead th:first-child { border-radius: 10px 0 0 0; }
.um-table thead th:last-child  { border-radius: 0 10px 0 0; }
.um-table tbody tr {
  border-bottom: 1px solid rgba(11, 60, 93, .09);
  transition: background .15s;
}
.um-table tbody tr:nth-child(even) { background: #fbfdff; }
.um-table tbody tr:hover { background: #f7fbff; }
.um-table tbody td {
  padding: 14px 16px;
  color: var(--text);
  vertical-align: middle;
  border-right: 1px solid rgba(11, 60, 93, .08);
  overflow-wrap: anywhere;
  word-break: break-word;
}
.um-table tbody td:last-child { border-right: none; }

.um-table thead th:nth-child(1),
.um-table tbody td:nth-child(1) { width: 64px; }

.um-table thead th:nth-child(2),
.um-table tbody td:nth-child(2) { width: 22%; }

.um-table thead th:nth-child(3),
.um-table tbody td:nth-child(3) { width: 16%; }

.um-table thead th:nth-child(4),
.um-table tbody td:nth-child(4) { width: 40%; }

.um-table thead th:nth-child(5),
.um-table tbody td:nth-child(5) { width: 140px; }

/* avatar cell */
.um-user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}
.um-avatar {
  width: 38px; height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--blue-1), var(--blue-3));
  color: white;
  font-weight: 700;
  font-size: .88rem;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  text-transform: uppercase;
}
.um-username { font-weight: 600; color: var(--text); }

/* role badge */
.um-role {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: .2px;
}
.um-role.admin {
  background: rgba(11, 95, 166, .12);
  color: var(--blue-2);
  border: 1px solid rgba(11, 95, 166, .3);
}
.um-role.petugas {
  background: rgba(30, 136, 229, .1);
  color: var(--blue-1);
  border: 1px solid rgba(30, 136, 229, .3);
}

/* access tags */
.um-access-wrap { display: flex; flex-wrap: wrap; gap: 5px; }
.um-access-tag {
  background: #f6fbff;
  color: var(--blue-1);
  border: 1px solid rgba(11, 95, 166, .2);
  border-radius: 6px;
  font-size: .73rem;
  font-weight: 600;
  padding: 3px 8px;
}
.um-no-access {
  color: var(--muted);
  font-style: italic;
  font-size: .85rem;
}

/* action buttons */
.um-btn-del {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #d62839 0%, #ef4444 100%);
  color: #ffffff;
  border: 1px solid rgba(167, 22, 22, .26);
  padding: 7px 14px;
  border-radius: 11px;
  font-size: .8rem;
  font-weight: 700;
  text-decoration: none;
  transition: all .2s;
  white-space: nowrap;
  box-shadow: 0 10px 18px rgba(214, 40, 57, .25);
}
.um-btn-del:hover {
  background: linear-gradient(135deg, #cf2234 0%, #e03a3a 100%);
  color: #ffffff;
  border-color: rgba(167, 22, 22, .44);
  box-shadow: 0 12px 22px rgba(214,40,57,.34);
  transform: translateY(-1px);
  text-decoration: none;
}

.um-btn-edit {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #0f5fa6 0%, #1f86e3 55%, #36a5ff 100%);
  color: #ffffff;
  border: 1px solid rgba(12, 92, 159, .32);
  padding: 7px 14px;
  border-radius: 11px;
  font-size: .8rem;
  font-weight: 700;
  text-decoration: none;
  transition: all .2s;
  white-space: nowrap;
  box-shadow: 0 10px 20px rgba(15,95,166,.28);
}

.um-btn-edit:hover {
  color: #ffffff;
  text-decoration: none;
  transform: translateY(-1px);
  box-shadow: 0 12px 24px rgba(11,95,166,.36);
}

.um-action-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.um-protected {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  background: linear-gradient(135deg, #fff5c8 0%, #ffd76a 100%);
  color: #7a4a00;
  border: 1px solid #e0ab34;
  padding: 7px 15px;
  border-radius: 14px;
  font-size: .82rem;
  font-weight: 700;
  text-shadow: 0 1px 0 rgba(255,255,255,.55);
  box-shadow: 0 10px 20px rgba(224,171,52,.34), inset 0 1px 0 rgba(255,255,255,.65);
}

/* ========== FORM SECTION ========== */
.um-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 6px;
}
.um-form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.um-form-group.full { grid-column: 1 / -1; }
.um-form-label {
  font-size: .8rem;
  font-weight: 700;
  color: var(--blue-2);
  text-transform: uppercase;
  letter-spacing: .5px;
}
.um-form-input,
.um-form-select {
  padding: 11px 14px;
  border: 1.5px solid rgba(11, 95, 166, .2);
  border-radius: 10px;
  font-size: .92rem;
  color: var(--text);
  background: #f9fcff;
  font-family: 'Inter', sans-serif;
  transition: border-color .2s, box-shadow .2s;
  outline: none;
  width: 100%;
}
.um-form-input:focus,
.um-form-select:focus {
  border-color: var(--blue-3);
  background: #fff;
  box-shadow: 0 0 0 4px rgba(30,136,229,.16);
}
.um-form-input::placeholder { color: var(--muted); }

.um-password-wrap {
  position: relative;
}

.um-form-input-password {
  padding-left: 44px;
}

.um-password-toggle {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--blue-2);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all .18s;
}

.um-password-toggle:hover {
  background: rgba(11, 95, 166, .1);
  color: var(--blue-1);
}

.um-password-toggle i {
  font-size: 1rem;
  line-height: 1;
}

/* ========== ACCESS CHECKBOX GRID ========== */
.um-access-label {
  font-size: .8rem;
  font-weight: 700;
  color: var(--blue-2);
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-top: 4px;
  margin-bottom: 8px;
  display: block;
}
.um-access-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(178px, 1fr));
  gap: 8px;
}
.um-check-label {
  display: flex;
  align-items: center;
  gap: 9px;
  background: linear-gradient(135deg, #f3f9ff 0%, #ffffff 100%);
  border: 1.5px solid rgba(73, 141, 202, .38);
  border-radius: 14px;
  padding: 10px 13px;
  cursor: pointer;
  font-size: .85rem;
  font-weight: 600;
  color: var(--text);
  transition: all .2s ease;
  user-select: none;
  box-shadow: 0 4px 12px rgba(15,95,166,.08);
}

.um-check-label:nth-child(3n+1) {
  background: linear-gradient(135deg, #edf6ff 0%, #ffffff 100%);
  border-color: rgba(30, 136, 229, .38);
}

.um-check-label:nth-child(3n+2) {
  background: linear-gradient(135deg, #eefcf7 0%, #ffffff 100%);
  border-color: rgba(16, 185, 129, .34);
}

.um-check-label:nth-child(3n) {
  background: linear-gradient(135deg, #fff8ee 0%, #ffffff 100%);
  border-color: rgba(245, 158, 11, .34);
}
.um-check-label:hover {
  border-color: var(--blue-3);
  color: var(--blue-1);
  transform: translateY(-1px) scale(1.01);
  box-shadow: 0 10px 22px rgba(15,95,166,.2);
}

.um-check-label:has(input:checked) {
  background: linear-gradient(135deg, #dff0ff 0%, #edf8ff 100%);
  border-color: rgba(30,136,229,.68);
  box-shadow: 0 12px 22px rgba(30,136,229,.24);
}

.um-check-label:has(input:checked) .um-check-icon {
  transform: scale(1.05);
}
.um-check-label input[type=checkbox] {
  width: 16px; height: 16px;
  accent-color: var(--blue-2);
  cursor: pointer;
  flex-shrink: 0;
}
.um-check-icon {
  font-size: 1rem;
  filter: saturate(1.15);
}

/* ========== FORM FOOTER ========== */
.um-form-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 22px;
  flex-wrap: wrap;
  gap: 12px;
}
.um-btn-submit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #0f5fa6 0%, #1d85df 55%, #34a1ff 100%);
  color: white;
  border: none;
  padding: 13px 28px;
  border-radius: 14px;
  font-size: .95rem;
  font-weight: 700;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  transition: all .2s;
  box-shadow: 0 12px 26px rgba(11,95,166,.35), inset 0 1px 0 rgba(255,255,255,.22);
}
.um-btn-submit:hover {
  background: linear-gradient(135deg, #0d4f89 0%, #146ab5 100%);
  box-shadow: 0 14px 30px rgba(11,95,166,.46);
  transform: translateY(-1px);
}
.um-back-link {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  color: var(--blue-1);
  text-decoration: none;
  font-size: .92rem;
  font-weight: 700;
  padding: 10px 18px;
  border: 1.5px solid rgba(11, 95, 166, .28);
  border-radius: 14px;
  background: linear-gradient(135deg, #f4f9ff 0%, #ebf5ff 100%);
  box-shadow: 0 8px 18px rgba(15,95,166,.1);
  transition: all .18s;
}
.um-back-link:hover {
  background: linear-gradient(135deg, #e3f1ff 0%, #d9ecff 100%);
  color: var(--blue-1);
  border-color: rgba(11,95,166,.52);
  box-shadow: 0 10px 20px rgba(15,95,166,.16);
  transform: translateY(-1px);
  text-decoration: none;
}

/* ========== EMPTY STATE ========== */
.um-empty {
  text-align: center;
  padding: 40px 20px;
  color: var(--muted);
}
.um-empty .um-empty-icon { font-size: 2.5rem; margin-bottom: 10px; }
.um-empty p { font-size: .9rem; }

/* ========== MOBILE ========== */
@media (max-width: 768px) {
  .um-page {
    padding: 0 12px;
    margin: 20px 0 40px;
  }

  .um-hero { padding: 22px 20px; gap: 14px; }
  .um-hero-text h1 { font-size: 1.3rem; }

  .um-card-body { padding: 0 16px 20px; }
  .um-card-header { padding: 16px; }

  .um-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }

  .um-tab-btn {
    width: 100%;
    padding: 9px 10px;
    font-size: .8rem;
  }

  .um-table {
    font-size: .82rem;
  }

  .um-table thead th,
  .um-table tbody td {
    padding: 10px 10px;
  }

  .um-user-cell {
    gap: 8px;
  }

  .um-avatar {
    width: 32px;
    height: 32px;
    font-size: .76rem;
  }

  .um-access-tag {
    font-size: .68rem;
    padding: 2px 6px;
  }

  .um-role {
    font-size: .7rem;
    padding: 4px 8px;
  }

  .um-btn-del {
    padding: 5px 8px;
    font-size: .72rem;
  }

  .um-table-wrap { display: none; }
  .um-mobile-list { display: block !important; }

  .um-mobile-card {
    padding: 14px;
    border-radius: 12px;
  }

  .um-mobile-card-top {
    margin-bottom: 10px;
  }

  .um-mobile-card-body .um-row {
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
  }

  .um-mobile-card-body .um-row > div {
    text-align: left !important;
    width: 100%;
  }

  .um-mobile-card .um-access-wrap {
    justify-content: flex-start !important;
  }

  .um-mobile-card .um-btn-del,
  .um-mobile-card .um-btn-edit,
  .um-mobile-card .um-protected {
    width: 100%;
    justify-content: center;
  }

  .um-form-grid { grid-template-columns: 1fr; }
  .um-form-group.full { grid-column: 1; }

  .um-form-footer { flex-direction: column; }
  .um-btn-submit, .um-back-link { width: 100%; justify-content: center; }
}

/* mobile card list (hidden on desktop) */
.um-mobile-list { display: none; }
.um-mobile-card {
  background: #f9fcff;
  border: 1.5px solid rgba(11, 95, 166, .18);
  border-radius: 14px;
  padding: 16px;
  margin-top: 12px;
}
.um-mobile-card-top {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}
.um-mobile-card-body .um-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
  padding: 7px 0;
  border-bottom: 1px solid rgba(11, 60, 93, .1);
  font-size: .85rem;
}
.um-mobile-card-body .um-row:last-child { border-bottom: none; }
.um-row-label {
  font-weight: 600;
  color: var(--muted);
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .4px;
  white-space: nowrap;
  flex-shrink: 0;
}
</style>


</head>

<body>

  <div class="layout">

    <!-- Link ke Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <main>

      <!-- Link Ke topbar -->
      <?php include_once 'topbar.php'; ?>
    
    
<div class="um-page">

  <!-- ===== HERO HEADER ===== -->
  <div class="um-hero">
    <div class="um-hero-icon"><i class="bi bi-people-fill"></i></div>
    <div class="um-hero-text">
      <h1>Manajemen Pengguna</h1>
      <p>Kelola akun dan hak akses pengguna sistem MyPPI</p>
    </div>
  </div>

  <div class="um-tabs" role="tablist" aria-label="Navigasi manajemen pengguna">
    <button type="button" class="um-tab-btn active" data-tab-target="tab-input" role="tab" aria-selected="true">
      <i class="bi bi-plus-circle-fill"></i>
      <span>Tambah Pengguna</span>
    </button>
    <button type="button" class="um-tab-btn" data-tab-target="tab-daftar" role="tab" aria-selected="false">
      <i class="bi bi-card-list"></i>
      <span>Daftar Pengguna</span>
    </button>
  </div>

  <div id="tab-input" class="um-tab-panel active" role="tabpanel">

    <!-- ===== FORM TAMBAH USER ===== -->
    <div class="um-card">
      <div class="um-card-header">
        <div class="um-card-icon"><i class="bi bi-plus-lg"></i></div>
        <h2>Tambah Pengguna Baru</h2>
      </div>
      <div class="um-card-body" style="padding-top:20px;">
        <form method="POST">
          <?= csrf_input() ?>
          <div class="um-form-grid">

            <div class="um-form-group">
              <label class="um-form-label">Username</label>
              <input type="text" name="username" class="um-form-input" placeholder="Contoh: budi_admin" value="<?= $is_edit_mode ? htmlspecialchars($edit_user['username']) : '' ?>" required>
            </div>

            <div class="um-form-group">
              <label class="um-form-label">Password</label>
              <div class="um-password-wrap">
                <button type="button" class="um-password-toggle" data-target="um-password-input" aria-label="Tampilkan password" aria-pressed="false">
                  <i class="bi bi-eye"></i>
                </button>
                <input id="um-password-input" type="password" name="password" class="um-form-input um-form-input-password" placeholder="<?= $is_edit_mode ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>" <?= $is_edit_mode ? '' : 'required' ?>>
              </div>
            </div>

            <div class="um-form-group">
              <label class="um-form-label">Role</label>
              <select name="role" class="um-form-select" required>
                <option value="admin" <?= $is_edit_mode && $edit_user['role'] === 'admin' ? 'selected' : '' ?>>🛡️ Admin</option>
                <option value="petugas" <?= $is_edit_mode && $edit_user['role'] === 'petugas' ? 'selected' : '' ?>>👤 Petugas</option>
              </select>
            </div>

            <div class="um-form-group full">
              <span class="um-access-label">Pilih Halaman yang Bisa Diakses</span>
              <div class="um-access-grid">
                <?php foreach ($available_pages as $key => $meta): ?>
                  <label class="um-check-label">
                    <input type="checkbox" name="akses[]" value="<?= $key ?>" <?= in_array($key, $edit_access, true) ? 'checked' : '' ?>>
                    <span class="um-check-icon"><?= $meta['icon'] ?></span>
                    <?= $meta['label'] ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

          </div>

          <div class="um-form-footer">
            <a href="dashboard.php" class="um-back-link">← Kembali ke Dashboard</a>
            <?php if ($is_edit_mode): ?>
              <input type="hidden" name="user_id" value="<?= intval($edit_user['id']) ?>">
              <button type="submit" name="update" class="um-btn-submit">💾 Update Pengguna</button>
            <?php else: ?>
              <button type="submit" name="tambah" class="um-btn-submit">💾 Simpan Pengguna</button>
            <?php endif; ?>
          </div>

          <?php if ($is_edit_mode): ?>
            <div style="margin-top:12px;">
              <a href="users.php?tab=daftar" class="um-back-link" style="padding:8px 14px; font-size:.82rem;">Batal Edit</a>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </div>

  </div>

  <div id="tab-daftar" class="um-tab-panel" role="tabpanel">

  <!-- ===== TABEL USER ===== -->
  <div class="um-card">
    <div class="um-card-header">
      <div class="um-card-icon"><i class="bi bi-card-list"></i></div>
      <h2>Daftar Pengguna</h2>
      <?php
        echo "<span class='um-badge-count'>{$userCount} User</span>";
      ?>
    </div>
    <div class="um-card-body">

      <!-- Desktop Table -->
      <div class="um-table-wrap">
        <table class="um-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Pengguna</th>
              <th>Role</th>
              <th>Hak Akses</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($usersList)):
              foreach ($usersList as $row):
                $halaman = $accessMap[(int) $row['id']] ?? [];
                $initials = strtoupper(substr($row['username'], 0, 2));
                $role_class = ($row['role'] === 'admin') ? 'admin' : 'petugas';
                $role_icon  = ($row['role'] === 'admin') ? '🛡️' : '👤';
            ?>
            <tr>
              <td style="color:#94a3b8; font-size:.82rem; font-weight:600;"><?= $row['id'] ?></td>
              <td>
                <div class="um-user-cell">
                  <div class="um-avatar"><?= $initials ?></div>
                  <span class="um-username"><?= htmlspecialchars($row['username']) ?></span>
                </div>
              </td>
              <td>
                <span class="um-role <?= $role_class ?>"><?= $role_icon ?> <?= ucfirst($row['role']) ?></span>
              </td>
              <td>
                <?php if (empty($halaman)): ?>
                  <span class="um-no-access">Tidak ada izin</span>
                <?php else: ?>
                  <div class="um-access-wrap">
                    <?php foreach ($halaman as $h): ?>
                      <span class="um-access-tag"><?= $h ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div class="um-action-group">
                  <a href="users.php?edit=<?= $row['id'] ?>" class="um-btn-edit">
                    <i class="bi bi-pencil-square"></i> Edit
                  </a>
                  <?php if (isset($row['protected']) && $row['protected'] == 1): ?>
                    <span class="um-protected">🔒 Terlindungi</span>
                  <?php else: ?>
                      <a href="users.php?hapus=<?= $row['id'] ?>&csrf=<?= urlencode($csrfToken) ?>" class="um-btn-del"
                       onclick="return confirm('Yakin ingin menghapus user ini?')">
                      <i class="bi bi-trash-fill"></i> Hapus
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="5">
                <div class="um-empty">
                  <div class="um-empty-icon">🙅</div>
                  <p>Belum ada pengguna yang terdaftar.</p>
                </div>
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card List -->
      <div class="um-mobile-list">
        <?php
        if (!empty($usersList)):
          foreach ($usersList as $row):
            $halaman2 = $accessMap[(int) $row['id']] ?? [];
            $initials2 = strtoupper(substr($row['username'], 0, 2));
            $role_class2 = ($row['role'] === 'admin') ? 'admin' : 'petugas';
        ?>
        <div class="um-mobile-card">
          <div class="um-mobile-card-top">
            <div class="um-avatar"><?= $initials2 ?></div>
            <div>
              <div class="um-username"><?= htmlspecialchars($row['username']) ?></div>
              <span class="um-role <?= $role_class2 ?>" style="margin-top:4px; display:inline-flex;"><?= ucfirst($row['role']) ?></span>
            </div>
          </div>
          <div class="um-mobile-card-body">
            <div class="um-row">
              <span class="um-row-label">Hak Akses</span>
              <div style="text-align:right;">
                <?php if (empty($halaman2)): ?>
                  <span class="um-no-access">Tidak ada</span>
                <?php else: ?>
                  <div class="um-access-wrap" style="justify-content:flex-end;">
                    <?php foreach ($halaman2 as $h): ?><span class="um-access-tag"><?= $h ?></span><?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="um-row" style="padding-top:10px;">
              <div class="um-action-group" style="width:100%;">
                <a href="users.php?edit=<?= $row['id'] ?>" class="um-btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                <?php if (isset($row['protected']) && $row['protected'] == 1): ?>
                  <span class="um-protected">🔒 Terlindungi</span>
                <?php else: ?>
                  <a href="users.php?hapus=<?= $row['id'] ?>&csrf=<?= urlencode($csrfToken) ?>" class="um-btn-del"
                     onclick="return confirm('Yakin ingin menghapus user ini?')"><i class="bi bi-trash-fill"></i> Hapus</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; else: ?>
          <div class="um-empty"><div class="um-empty-icon">🙅</div><p>Belum ada pengguna.</p></div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  </div>

</div>

    </main>

</div>
    <script src="<?= asset('assets/js/utama.js') ?>"></script>
    <script>
      (function () {
        var tabButtons = document.querySelectorAll('.um-tab-btn');
        var tabPanels = document.querySelectorAll('.um-tab-panel');

        function activateTab(targetId) {
          tabButtons.forEach(function (btn) {
            var active = btn.getAttribute('data-tab-target') === targetId;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
          });

          tabPanels.forEach(function (panel) {
            panel.classList.toggle('active', panel.id === targetId);
          });
        }

        tabButtons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            activateTab(btn.getAttribute('data-tab-target'));
          });
        });

        var initialTab = new URLSearchParams(window.location.search).get('tab');
        if (initialTab === 'daftar') {
          activateTab('tab-daftar');
        } else {
          activateTab('tab-input');
        }

        var passwordToggles = document.querySelectorAll('.um-password-toggle');
        passwordToggles.forEach(function (toggle) {
          toggle.addEventListener('click', function () {
            var targetId = toggle.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (!input) return;

            var showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
            toggle.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
            toggle.innerHTML = showPassword
              ? '<i class="bi bi-eye-slash"></i>'
              : '<i class="bi bi-eye"></i>';
          });
        });
      })();
    </script>
</body>
</html>
