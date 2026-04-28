<?php
require_once __DIR__ . '/../../config/assets.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';

$conn = $koneksi;
$pageTitle = "EDIT AUDIT APD";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$profesiList = [
  "Dokter Spesialis",
  "Dokter Jaga",
  "Perawat/Bidan",
  "Analis",
  "Radiografer",
  "Fisioterapis",
  "P. Kebersihan",
  "P. Gizi",
  "P. Farmasi"
];

$ruanganList = [
  "UGD",
  "HD",
  "Poli",
  "OK",
  "VK",
  "ICU",
  "Perina",
  "St. Yosef",
  "St. Teresia",
  "St. Lukas",
  "St. Anna",
  "Radiologi",
  "Laboratorium",
  "Rehabilitasi Medik",
  "Farmasi",
  "Gizi/Dapur",
  "Cleaning Service"
];

$indikatorPenilaian = [
  "kesesuaian_apd_1" => "Kesesuaian APD",
  "kesegeraan_melepas_apd_1" => "Kesegeraan melepas APD",
  "urutan_pelepasan_apd_1" => "Urutan pelepasan APD",
  "fasilitas_apd_1" => "Terdapat fasilitas APD",
  "kesesuaian_apd_2" => "Kesesuaian APD",
  "kesegeraan_melepas_apd_2" => "Kesegeraan melepas APD",
  "urutan_pelepasan_apd_2" => "Urutan pelepasan APD",
  "fasilitas_apd_2" => "Terdapat fasilitas APD"
];

$apdDigunakan = [
  "topi_nurse_cap_1" => "Topi (Nurse Cap)",
  "masker_bedah_1" => "Masker Bedah",
  "masker_n95_1" => "Masker N95 (Setara)",
  "goggles_1" => "Goggles",
  "face_shield_1" => "Face Shield",
  "sarung_tangan_1" => "Sarung Tangan",
  "sarung_tangan_steril_1" => "Sarung Tangan Steril",
  "sarung_tangan_rumah_tangga_1" => "Sarung Tangan Rumah tangga",
  "apron_1" => "Apron",
  "gown_1" => "Gown",
  "sepatu_boot_1" => "Sepatu tertutup/boot",
  "topi_nurse_cap_2" => "Topi (Nurse Cap)",
  "masker_bedah_2" => "Masker Bedah",
  "masker_n95_2" => "Masker N95 (Setara)",
  "goggles_2" => "Goggles",
  "face_shield_2" => "Face Shield",
  "sarung_tangan_2" => "Sarung Tangan",
  "sarung_tangan_steril_2" => "Sarung Tangan Steril",
  "sarung_tangan_rumah_tangga_2" => "Sarung Tangan Rumah tangga",
  "apron_2" => "Apron",
  "gown_2" => "Gown",
  "sepatu_boot_2" => "Sepatu tertutup/boot"
];

$opsiJawaban = [
  "ya" => "Ya",
  "tidak" => "Tidak",
  "na" => "NA"
];

if ($id <= 0) {
  die("ID audit tidak valid.");
}

$stmt = mysqli_prepare($conn, "SELECT * FROM audit_apd WHERE id = ?");
if (!$stmt) {
  die("Gagal menyiapkan query data audit.");
}
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
  die("Data audit tidak ditemukan.");
}

$selectedPenilaian = [];
$selectedApd = [];
$qDetail = mysqli_query($conn, "SELECT kategori, indikator_key, jawaban FROM audit_apd_detail WHERE audit_id = " . (int) $id);
while ($row = mysqli_fetch_assoc($qDetail)) {
  if (($row['kategori'] ?? '') === 'indikator_penilaian') {
    $selectedPenilaian[$row['indikator_key']] = strtolower($row['jawaban'] ?? '');
  } elseif (($row['kategori'] ?? '') === 'apd_digunakan') {
    $selectedApd[$row['indikator_key']] = strtolower($row['jawaban'] ?? '');
  }
}

$message = '';

if (isset($_POST['update'])) {
  $tanggal_audit = trim($_POST['tanggal_audit'] ?? '');
  $nama_petugas = trim($_POST['nama_petugas'] ?? '');
  $profesi = trim($_POST['profesi'] ?? '');
  $ruangan = trim($_POST['ruangan'] ?? '');
  $tindakan = trim($_POST['tindakan'] ?? '');
  $keterangan = trim($_POST['keterangan'] ?? '');
  $penilaian = $_POST['penilaian'] ?? [];
  $apd = $_POST['apd'] ?? [];

  $selectedPenilaian = $penilaian;
  $selectedApd = $apd;

  $fotoLama = $data['foto'] ?? '';
  $fotoBaru = $fotoLama;
  $uploadDir = __DIR__ . '/../uploads_apd/';
  $newUploadedPath = '';

  if (!empty($_FILES['foto']['name'])) {
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed, true)) {
      $newName = 'apd_' . date('YmdHis') . '_' . rand(1000, 9999) . '.' . $ext;
      if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $newName)) {
        $fotoBaru = $newName;
        $newUploadedPath = $uploadDir . $newName;
      }
    }
  }

  if ($tanggal_audit === '' || $nama_petugas === '' || $profesi === '' || $ruangan === '' || $tindakan === '') {
    $message = '<div class="alert alert-danger">Semua field wajib harus diisi.</div>';
  } elseif (!in_array($profesi, $profesiList, true)) {
    $message = '<div class="alert alert-danger">Profesi tidak valid.</div>';
  } elseif (!in_array($ruangan, $ruanganList, true)) {
    $message = '<div class="alert alert-danger">Ruangan tidak valid.</div>';
  } elseif (empty($penilaian) && empty($apd)) {
    $message = '<div class="alert alert-danger">Minimal isi satu pilihan checklist.</div>';
  } else {
    mysqli_begin_transaction($conn);
    try {
      $stmtUpdate = mysqli_prepare($conn, "
        UPDATE audit_apd
        SET tanggal_audit = ?, nama_petugas = ?, profesi = ?, ruangan = ?, tindakan = ?, keterangan = ?, foto = ?
        WHERE id = ?
      ");
      mysqli_stmt_bind_param(
        $stmtUpdate,
        "sssssssi",
        $tanggal_audit,
        $nama_petugas,
        $profesi,
        $ruangan,
        $tindakan,
        $keterangan,
        $fotoBaru,
        $id
      );
      mysqli_stmt_execute($stmtUpdate);

      $stmtDeleteDetail = mysqli_prepare($conn, "DELETE FROM audit_apd_detail WHERE audit_id = ?");
      mysqli_stmt_bind_param($stmtDeleteDetail, "i", $id);
      mysqli_stmt_execute($stmtDeleteDetail);

      $stmtInsertDetail = mysqli_prepare($conn, "
        INSERT INTO audit_apd_detail (audit_id, kategori, indikator_key, indikator_label, jawaban)
        VALUES (?, ?, ?, ?, ?)
      ");

      foreach ($penilaian as $key => $jawaban) {
        if (!isset($opsiJawaban[$jawaban])) {
          continue;
        }
        $kategori = 'indikator_penilaian';
        $label = $indikatorPenilaian[$key] ?? $key;
        mysqli_stmt_bind_param($stmtInsertDetail, "issss", $id, $kategori, $key, $label, $jawaban);
        mysqli_stmt_execute($stmtInsertDetail);
      }

      foreach ($apd as $key => $jawaban) {
        if (!isset($opsiJawaban[$jawaban])) {
          continue;
        }
        $kategori = 'apd_digunakan';
        $label = $apdDigunakan[$key] ?? $key;
        mysqli_stmt_bind_param($stmtInsertDetail, "issss", $id, $kategori, $key, $label, $jawaban);
        mysqli_stmt_execute($stmtInsertDetail);
      }

      mysqli_commit($conn);

      if ($newUploadedPath !== '' && $fotoLama !== '' && $fotoLama !== $fotoBaru && is_file($uploadDir . $fotoLama)) {
        @unlink($uploadDir . $fotoLama);
      }

      header("Location: detail_audit.php?id=" . $id . "&status=updated");
      exit;
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      if ($newUploadedPath !== '' && is_file($newUploadedPath)) {
        @unlink($newUploadedPath);
      }
      $message = '<div class="alert alert-danger">Gagal memperbarui data audit.</div>';
    }
  }

  $data['tanggal_audit'] = $tanggal_audit;
  $data['nama_petugas'] = $nama_petugas;
  $data['profesi'] = $profesi;
  $data['ruangan'] = $ruangan;
  $data['tindakan'] = $tindakan;
  $data['keterangan'] = $keterangan;
  $data['foto'] = $fotoBaru;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Audit APD</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    .audit-page { background: #eef3f7; min-height: 100vh; }
    .page { width: 100%; padding: 20px; }
    .container { width: 100%; max-width: none; margin: 0; }
    .card {
      background: #fff; border: 1px solid #d9e5f4; border-radius: 20px;
      box-shadow: 0 10px 24px rgba(30, 64, 128, .08); margin-bottom: 16px; padding: 18px;
    }
    .title { font-size: 24px; font-weight: 800; color: #173f79; margin-bottom: 8px; }
    .subtitle { color: #5b7499; margin-bottom: 14px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .group { display: flex; flex-direction: column; gap: 8px; }
    .group.full { grid-column: 1 / -1; }
    .label { font-size: 13px; font-weight: 800; color: #23406d; }
    .required { color: #e54848; }
    .control {
      width: 100%; border: 1.5px solid #cbdcf0; border-radius: 12px; padding: 10px 12px;
      font-size: 14px; color: #173f79; background: #fff; outline: none;
    }
    .control:focus { border-color: #4d8dff; box-shadow: 0 0 0 3px rgba(77,141,255,.12); }
    .textarea { min-height: 110px; resize: vertical; }
    .table-wrap { overflow-x: auto; border: 1px solid #dbe7f5; border-radius: 14px; }
    .table { width: 100%; border-collapse: collapse; min-width: 640px; }
    .table th { background: #2b55c6; color: #fff; padding: 10px; font-size: 13px; text-align: center; }
    .table th:first-child { text-align: left; }
    .table td { border-bottom: 1px solid #e4ebf5; padding: 8px 10px; font-size: 13px; color: #173f79; text-align: center; }
    .table td:first-child { text-align: left; font-weight: 700; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
    .btn {
      display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
      border-radius: 999px; padding: 10px 14px; font-weight: 800; border: 1px solid #c7d7ed;
      color: #24436c; background: #fff; cursor: pointer;
    }
    .btn-primary { color: #fff; border: none; background: linear-gradient(135deg, #2459cc, #4d8dff); }
    .thumb { margin-top: 8px; max-width: 220px; border-radius: 12px; border: 1px solid #dbe7f5; display: block; }
    .alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 12px; font-weight: 700; }
    .alert-danger { background: #fff1f1; color: #c93535; border: 1px solid #f2b8b8; }
    @media (max-width: 768px) {
      .page { padding: 12px; }
      .card { padding: 12px; border-radius: 14px; }
      .title { font-size: 19px; }
      .grid { grid-template-columns: 1fr; gap: 10px; }
      .table { min-width: 560px; }
      .actions { flex-direction: column; }
      .btn { width: 100%; }
    }
  </style>
</head>
<body class="audit-page">
  <div class="layout">
    <?php include_once __DIR__ . '/../../sidebar.php'; ?>
    <main>
      <?php include_once __DIR__ . '/../../topbar.php'; ?>
      <div class="page">
        <div class="container">
          <div class="card">
            <div class="title">Edit Audit APD</div>
            <div class="subtitle">Anda bisa edit data utama sekaligus checklist indikator/APD.</div>
            <?= $message ?>
            <form method="post" enctype="multipart/form-data">
              <div class="grid">
                <div class="group">
                  <label class="label">Tanggal Audit <span class="required">*</span></label>
                  <input type="date" name="tanggal_audit" class="control" value="<?= htmlspecialchars($data['tanggal_audit'] ?? '') ?>" required>
                </div>
                <div class="group">
                  <label class="label">Nama Petugas <span class="required">*</span></label>
                  <input type="text" name="nama_petugas" class="control" value="<?= htmlspecialchars($data['nama_petugas'] ?? '') ?>" required>
                </div>
                <div class="group">
                  <label class="label">Profesi <span class="required">*</span></label>
                  <select name="profesi" class="control" required>
                    <option value="">Pilih profesi</option>
                    <?php foreach ($profesiList as $item): ?>
                      <option value="<?= htmlspecialchars($item) ?>" <?= ($data['profesi'] ?? '') === $item ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="group">
                  <label class="label">Ruangan <span class="required">*</span></label>
                  <select name="ruangan" class="control" required>
                    <option value="">Pilih ruangan</option>
                    <?php foreach ($ruanganList as $item): ?>
                      <option value="<?= htmlspecialchars($item) ?>" <?= ($data['ruangan'] ?? '') === $item ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="group full">
                  <label class="label">Tindakan <span class="required">*</span></label>
                  <input type="text" name="tindakan" class="control" value="<?= htmlspecialchars($data['tindakan'] ?? '') ?>" required>
                </div>
                <div class="group full">
                  <label class="label">Keterangan</label>
                  <textarea name="keterangan" class="control textarea"><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                </div>
                <div class="group full">
                  <label class="label">Ganti Foto (opsional)</label>
                  <input type="file" name="foto" class="control" accept="image/png,image/jpeg,image/jpg,image/webp">
                  <?php if (!empty($data['foto'])): ?>
                    <img src="../uploads_apd/<?= htmlspecialchars($data['foto']) ?>" alt="Foto Audit APD" class="thumb">
                  <?php endif; ?>
                </div>
              </div>

              <div class="card" style="margin-top:16px;">
                <div class="title" style="font-size:20px;">Indikator Penilaian APD</div>
                <div class="table-wrap">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Indikator</th>
                        <?php foreach ($opsiJawaban as $label): ?>
                          <th><?= htmlspecialchars($label) ?></th>
                        <?php endforeach; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($indikatorPenilaian as $key => $label): ?>
                        <tr>
                          <td><?= htmlspecialchars($label) ?></td>
                          <?php foreach ($opsiJawaban as $opsiKey => $opsiLabel): ?>
                            <td>
                              <input
                                type="radio"
                                name="penilaian[<?= htmlspecialchars($key) ?>]"
                                value="<?= htmlspecialchars($opsiKey) ?>"
                                <?= (($selectedPenilaian[$key] ?? '') === $opsiKey) ? 'checked' : '' ?>>
                            </td>
                          <?php endforeach; ?>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="card" style="margin-top:16px;">
                <div class="title" style="font-size:20px;">APD yang Digunakan</div>
                <div class="table-wrap">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>APD</th>
                        <?php foreach ($opsiJawaban as $label): ?>
                          <th><?= htmlspecialchars($label) ?></th>
                        <?php endforeach; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($apdDigunakan as $key => $label): ?>
                        <tr>
                          <td><?= htmlspecialchars($label) ?></td>
                          <?php foreach ($opsiJawaban as $opsiKey => $opsiLabel): ?>
                            <td>
                              <input
                                type="radio"
                                name="apd[<?= htmlspecialchars($key) ?>]"
                                value="<?= htmlspecialchars($opsiKey) ?>"
                                <?= (($selectedApd[$key] ?? '') === $opsiKey) ? 'checked' : '' ?>>
                            </td>
                          <?php endforeach; ?>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="actions">
                <button type="submit" name="update" class="btn btn-primary">💾 Simpan Perubahan Lengkap</button>
                <a href="detail_audit.php?id=<?= (int) $data['id'] ?>" class="btn">← Kembali ke Detail</a>
                <a href="../apd.php?tab=tab-data" class="btn">📋 Kembali ke Data</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
