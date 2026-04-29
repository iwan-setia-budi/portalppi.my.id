<?php
require_once __DIR__ . '/../../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';
$conn = $koneksi;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
  die('ID audit tidak valid.');
}

$qAudit = mysqli_query($conn, "SELECT * FROM audit_gizi WHERE id = $id");
$audit = mysqli_fetch_assoc($qAudit);
if (!$audit) {
  die('Data audit tidak ditemukan.');
}

$message = '';
if (isset($_POST['update'])) {
  $tanggal = $_POST['tanggal_audit'] ?? '';
  $catatan = trim($_POST['catatan_audit'] ?? '');
  $petugas = trim($_POST['nama_petugas_unit'] ?? '');
  $ttd = trim($_POST['tanda_tangan_petugas'] ?? '');
  $signatureData = $_POST['signature_data'] ?? '';

  if ($tanggal && $petugas) {
    if (preg_match('/^data:image\/png;base64,/', $signatureData)) {
      $uploadDir = __DIR__ . '/../../uploads/audit_gizi/';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }
      $signatureBase64 = substr($signatureData, strpos($signatureData, ',') + 1);
      $signatureBinary = base64_decode(str_replace(' ', '+', $signatureBase64), true);
      if ($signatureBinary !== false && strlen($signatureBinary) > 0) {
        $signatureFileName = 'ttd_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        if (file_put_contents($uploadDir . $signatureFileName, $signatureBinary) !== false) {
          $ttd = 'uploads/audit_gizi/' . $signatureFileName;
        }
      }
    }

    if ($ttd === '') {
      $ttd = $audit['tanda_tangan_petugas'] ?? '';
    }

    if ($ttd === '') {
      $message = 'Tanda tangan petugas wajib diisi.';
    } else {
    $stmt = mysqli_prepare($conn, "UPDATE audit_gizi SET tanggal_audit=?, catatan_audit=?, nama_petugas_unit=?, tanda_tangan_petugas=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $tanggal, $catatan, $petugas, $ttd, $id);
    if (mysqli_stmt_execute($stmt)) {
      header("Location: detail_audit.php?id=$id&status=updated");
      exit;
    }
    $message = 'Gagal update data.';
    }
  } else {
    $message = 'Lengkapi data wajib (tanggal dan nama petugas unit).';
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Audit Gizi</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    .audit-page {
      background: radial-gradient(900px 420px at 18% -10%, rgba(37, 99, 235, 0.12), transparent 62%), #eef3f7;
      min-height: 100vh;
      color: #0f172a;
    }
    .page-wrap { padding: 16px; }
    .hero-card, .section-card {
      background: #fff;
      border: 1px solid rgba(148, 163, 184, .35);
      border-radius: 18px;
      padding: 18px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, .07);
      margin-bottom: 14px;
    }
    .hero-card h1 { margin: 0; font-size: 28px; }
    .subtitle { color: #64748b; margin: 8px 0 0; font-weight: 600; }
    .form-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .full { grid-column: 1 / -1; }
    .field-label { display:block; margin-bottom: 8px; font-size: 14px; font-weight: 800; }
    .required { color:#e11d48; }
    .form-control {
      width: 100%; border: 1.5px solid rgba(148,163,184,.62); border-radius: 12px; padding: 12px 14px;
      font-size: 15px; color:#0f172a; outline:none; transition: .2s ease; box-sizing: border-box;
    }
    .form-control:focus { border-color:#1e40af; box-shadow: 0 0 0 4px rgba(30, 64, 175, .15); }
    .btn-row { display:flex; gap:10px; flex-wrap: wrap; margin-top: 16px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 14px; border-radius:12px; text-decoration:none; font-weight:700; border:1px solid transparent; cursor:pointer; }
    .btn-primary { background: linear-gradient(135deg,#1e40af,#1e3a8a); color:#fff; }
    .btn-secondary { background:#fff; color:#0f172a; border-color:#cbd5e1; }
    .alert { border-radius:12px; padding:10px 12px; margin-bottom:10px; border:1px solid #fecaca; color:#991b1b; background:#fef2f2; font-weight:700; }
    .help { margin-top: 10px; padding: 12px; border-radius: 12px; border:1px solid #dbe3ee; background:#f8fafc; color:#475569; font-size: 13px; line-height: 1.6; }
    .signature-pad-wrap { border: 1.5px dashed #94a3b8; border-radius: 14px; padding: 10px; background: #f8fafc; }
    .signature-canvas { width:100%; height:170px; background:#fff; border:1px solid #dbe3ee; border-radius:10px; touch-action:none; cursor: crosshair; display:block; }
    .signature-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
    .signature-preview { margin-top: 10px; }
    .signature-preview img { max-width: 100%; height: 70px; object-fit: contain; border:1px solid #dbe3ee; border-radius:8px; background:#fff; padding:4px; }
    @media (max-width: 768px) {
      .page-wrap { padding: 8px; }
      .hero-card h1 { font-size: 22px; }
      .section-card, .hero-card { padding: 14px; border-radius: 12px; }
      .form-grid { grid-template-columns: 1fr; }
      .btn { width: 100%; }
      .signature-canvas { height: 140px; }
    }
  </style>
</head>
<body class="audit-page">
  <div class="layout">
    <?php include_once __DIR__ . '/../../sidebar.php'; ?>
    <main>
      <?php include_once __DIR__ . '/../../topbar.php'; ?>
      <div class="page-wrap">
        <div class="hero-card">
          <h1>Edit Audit Gizi #<?= (int) $audit['id'] ?></h1>
          <p class="subtitle">Perbarui data utama audit dengan tampilan yang lebih rapi dan responsif.</p>
        </div>

        <div class="section-card">
          <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
          <form method="post">
            <div class="form-grid">
              <div>
                <label class="field-label">Tanggal Audit <span class="required">*</span></label>
                <input type="date" class="form-control" name="tanggal_audit" value="<?= htmlspecialchars($audit['tanggal_audit']) ?>" required>
              </div>
              <div>
                <label class="field-label">Nama Petugas Unit <span class="required">*</span></label>
                <input type="text" class="form-control" name="nama_petugas_unit" value="<?= htmlspecialchars($audit['nama_petugas_unit']) ?>" required>
              </div>
              <div class="full">
                <label class="field-label">Tanda Tangan Petugas <span class="required">*</span></label>
                <input type="hidden" name="tanda_tangan_petugas" value="<?= htmlspecialchars($audit['tanda_tangan_petugas']) ?>">
                <div class="signature-pad-wrap">
                  <canvas id="signatureCanvas" class="signature-canvas"></canvas>
                  <input type="hidden" name="signature_data" id="signatureData">
                  <div class="signature-actions">
                    <button type="button" class="btn btn-secondary" id="btnClearSignature">Hapus Tanda Tangan Baru</button>
                  </div>
                </div>
                <?php if (!empty($audit['tanda_tangan_petugas'])): ?>
                  <div class="signature-preview">
                    <small>Tanda tangan saat ini:</small><br>
                    <img src="../../<?= htmlspecialchars($audit['tanda_tangan_petugas']) ?>" alt="Tanda tangan saat ini">
                  </div>
                <?php endif; ?>
              </div>
              <div class="full">
                <label class="field-label">Catatan Audit</label>
                <textarea class="form-control" name="catatan_audit" rows="4"><?= htmlspecialchars($audit['catatan_audit']) ?></textarea>
              </div>
            </div>

            <div class="btn-row">
              <button class="btn btn-primary" name="update" type="submit">Simpan</button>
              <a class="btn btn-secondary" href="detail_audit.php?id=<?= $id ?>">Kembali</a>
              <a class="btn btn-secondary" href="../audit_gizi.php?tab=tab-data">Data Audit</a>
            </div>
            <div class="help">Halaman ini mengubah data utama audit. Untuk checklist detail, tetap lihat pada halaman detail audit.</div>
          </form>
        </div>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
  <script>
    (function () {
      const canvas = document.getElementById('signatureCanvas');
      const hidden = document.getElementById('signatureData');
      const clearBtn = document.getElementById('btnClearSignature');
      if (!canvas || !hidden || !clearBtn) return;

      const ctx = canvas.getContext('2d');
      let drawing = false;
      let hasStroke = false;

      function initCtx() {
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.2;
      }

      function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();
        const prev = hasStroke ? canvas.toDataURL('image/png') : '';
        canvas.width = Math.floor(rect.width * ratio);
        canvas.height = Math.floor(rect.height * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        initCtx();
        ctx.clearRect(0, 0, rect.width, rect.height);
        if (prev) {
          const img = new Image();
          img.onload = function () {
            ctx.drawImage(img, 0, 0, rect.width, rect.height);
            hidden.value = canvas.toDataURL('image/png');
          };
          img.src = prev;
        }
      }

      function getPoint(event) {
        const rect = canvas.getBoundingClientRect();
        if (event.touches && event.touches[0]) {
          return { x: event.touches[0].clientX - rect.left, y: event.touches[0].clientY - rect.top };
        }
        return { x: event.clientX - rect.left, y: event.clientY - rect.top };
      }

      function startDraw(event) {
        event.preventDefault();
        drawing = true;
        const p = getPoint(event);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
      }

      function moveDraw(event) {
        if (!drawing) return;
        event.preventDefault();
        const p = getPoint(event);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        hasStroke = true;
      }

      function endDraw(event) {
        if (!drawing) return;
        event.preventDefault();
        drawing = false;
        ctx.closePath();
        hidden.value = hasStroke ? canvas.toDataURL('image/png') : '';
      }

      function clearSignature() {
        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, rect.height);
        hasStroke = false;
        hidden.value = '';
      }

      canvas.addEventListener('mousedown', startDraw);
      canvas.addEventListener('mousemove', moveDraw);
      window.addEventListener('mouseup', endDraw);
      canvas.addEventListener('touchstart', startDraw, { passive: false });
      canvas.addEventListener('touchmove', moveDraw, { passive: false });
      window.addEventListener('touchend', endDraw, { passive: false });
      clearBtn.addEventListener('click', clearSignature);
      window.addEventListener('resize', resizeCanvas);
      resizeCanvas();
    })();
  </script>
</body>
</html>
