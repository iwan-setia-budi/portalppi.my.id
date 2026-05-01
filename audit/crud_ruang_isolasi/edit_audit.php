<?php
require_once __DIR__ . '/../../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';
$conn = $koneksi;

$ruanganDiauditOptions = require __DIR__ . '/../inc_ruangan_kewaspadaan_transmisi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
  die('ID audit tidak valid.');
}

$opsiJawaban = [
  'ya' => 'Ya',
  'tidak' => 'Tidak',
  'na' => 'NA'
];

$checklistSections = [
  'K00' => [
    'title' => 'Checklist Ruang Isolasi',
    'items' => [
      'Tersedia poster penandaan di depan pintu',
      'Terdapat edukasi Pasien dan keluarga',
      'Tersedia APD diruang isolasi',
      'Tersedia Fasilitas kebersihan tangan',
      'Suhu ruangan sesuai',
      'Ada bukti dokumentasi untuk suhu dan tekanan',
      'Pembersihan ruangan dilakukan sesuai standar',
      'Tekanan ruangan sesuai',
      'Pintu kamar selalu tertutup (transmisi airborne)',
    ]
  ],
];

$qAudit = mysqli_query($conn, "SELECT * FROM audit_ruang_isolasi WHERE id = $id");
$audit = mysqli_fetch_assoc($qAudit);
if (!$audit) {
  die('Data audit tidak ditemukan.');
}

$existingJawaban = [];
$qDetail = mysqli_query($conn, "SELECT kode_bagian, urutan_item, jawaban FROM detail_audit_ruang_isolasi WHERE audit_id = $id");
while ($d = mysqli_fetch_assoc($qDetail)) {
  $existingJawaban[$d['kode_bagian']][(int) $d['urutan_item']] = $d['jawaban'];
}

$formTanggal = $_POST['tanggal_audit'] ?? $audit['tanggal_audit'];
$formRuangan = $_POST['ruangan_diaudit'] ?? ($audit['ruangan_diaudit'] ?? '');
$formCatatan = $_POST['catatan_audit'] ?? $audit['catatan_audit'];
$formPetugas = $_POST['nama_petugas_unit'] ?? $audit['nama_petugas_unit'];
$formJawaban = $_POST['jawaban'] ?? $existingJawaban;

$message = '';
if (isset($_POST['update'])) {
  $tanggal = $_POST['tanggal_audit'] ?? '';
  $ruangan = $_POST['ruangan_diaudit'] ?? '';
  $catatan = trim($_POST['catatan_audit'] ?? '');
  $petugas = trim($_POST['nama_petugas_unit'] ?? '');
  $ttd = trim($_POST['tanda_tangan_petugas'] ?? '');
  $signatureData = $_POST['signature_data'] ?? '';
  $jawaban = $_POST['jawaban'] ?? [];

  if (!$tanggal || !$petugas || $ruangan === '' || !in_array($ruangan, $ruanganDiauditOptions, true)) {
    $message = 'Lengkapi data wajib (tanggal, ruangan yang diaudit, dan nama petugas unit).';
  } else {
    $invalid = false;
    foreach ($checklistSections as $kode => $section) {
      foreach ($section['items'] as $idx => $item) {
        $urutan = $idx + 1;
        $jawab = $jawaban[$kode][$urutan] ?? '';
        if (!isset($opsiJawaban[$jawab])) {
          $invalid = true;
          break 2;
        }
      }
    }

    if ($invalid) {
      $message = 'Semua item checklist harus dipilih (Ya/Tidak/NA).';
    } else {
      if (preg_match('/^data:image\/png;base64,/', $signatureData)) {
        $uploadDir = __DIR__ . '/../../uploads/audit_ruang_isolasi/';
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        $signatureBase64 = substr($signatureData, strpos($signatureData, ',') + 1);
        $signatureBinary = base64_decode(str_replace(' ', '+', $signatureBase64), true);
        if ($signatureBinary !== false && strlen($signatureBinary) > 0) {
          $signatureFileName = 'ttd_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
          if (file_put_contents($uploadDir . $signatureFileName, $signatureBinary) !== false) {
            $ttd = 'uploads/audit_ruang_isolasi/' . $signatureFileName;
          }
        }
      }

      if ($ttd === '') {
        $ttd = $audit['tanda_tangan_petugas'] ?? '';
      }

      if ($ttd === '') {
        $message = 'Tanda tangan petugas wajib diisi.';
      } else {
        mysqli_begin_transaction($conn);
        try {
          $stmt = mysqli_prepare($conn, "UPDATE audit_ruang_isolasi SET tanggal_audit=?, ruangan_diaudit=?, catatan_audit=?, nama_petugas_unit=?, tanda_tangan_petugas=? WHERE id=?");
          mysqli_stmt_bind_param($stmt, "sssssi", $tanggal, $ruangan, $catatan, $petugas, $ttd, $id);
          mysqli_stmt_execute($stmt);

          mysqli_query($conn, "DELETE FROM detail_audit_ruang_isolasi WHERE audit_id = $id");

          $stmtDetail = mysqli_prepare($conn, "INSERT INTO detail_audit_ruang_isolasi (audit_id, kode_bagian, urutan_item, item_text, jawaban) VALUES (?, ?, ?, ?, ?)");
          foreach ($checklistSections as $kode => $section) {
            foreach ($section['items'] as $idx => $item) {
              $urutan = $idx + 1;
              $jawab = $jawaban[$kode][$urutan] ?? 'na';
              mysqli_stmt_bind_param($stmtDetail, "isiss", $id, $kode, $urutan, $item, $jawab);
              mysqli_stmt_execute($stmtDetail);
            }
          }

          mysqli_commit($conn);
          header("Location: detail_audit.php?id=$id&status=updated");
          exit;
        } catch (Throwable $e) {
          mysqli_rollback($conn);
          $message = 'Gagal update data audit.';
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Audit Ruang Isolasi</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    :root { --bg:#eef3f7; --card:#ffffff; --card-2:#f8fafc; --ink:#0f172a; --muted:#64748b; --line:#dbe3ee; --line-strong:#94a3b8; }
    body.dark-mode { --bg:#0b1220; --card:#111827; --card-2:#0f172a; --ink:#e5e7eb; --muted:#94a3b8; --line:#334155; --line-strong:#475569; }
    .audit-page { background: radial-gradient(900px 420px at 18% -10%, rgba(37,99,235,.12), transparent 62%), var(--bg); min-height:100vh; color:var(--ink); }
    .page-wrap { padding:16px; }
    .hero-card,.section-card { background:var(--card); border:1px solid var(--line); border-radius:18px; padding:18px; box-shadow:0 10px 24px rgba(15,23,42,.07); margin-bottom:14px; }
    .hero-card h1 { margin:0; font-size:28px; }
    .subtitle { color:var(--muted); margin:8px 0 0; font-weight:600; }
    .alert { border-radius:12px; padding:10px 12px; margin-bottom:10px; border:1px solid #fecaca; color:#991b1b; background:#fef2f2; font-weight:700; }
    .field-label { display:block; margin-bottom:8px; font-size:14px; font-weight:800; }
    /* Jarak antar field bertumpuk di kartu atas (tanggal → ruangan, dll.) */
    .section-card > .form-control + .field-label,
    .section-card > textarea.form-control + .field-label {
      margin-top: 20px;
    }
    .required { color:#e11d48; }
    .form-control { width:100%; border:1.5px solid var(--line-strong); border-radius:12px; padding:11px 13px; font-size:14px; color:var(--ink); outline:none; background:var(--card); box-sizing:border-box; }
    .form-control:focus { border-color:#1e40af; box-shadow:0 0 0 4px rgba(30,64,175,.14); }
    .section-toggle { width:100%; border:none; background:transparent; padding:0; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left; }
    .section-toggle:focus-visible { outline: 3px solid rgba(30,64,175,.35); outline-offset: 4px; border-radius: 10px; }
    .section-chevron { display:inline-flex; font-size:14px; transition: transform .2s ease; }
    .section-toggle[aria-expanded="true"] .section-chevron { transform: rotate(180deg); }
    .opportunity-title { margin:0; font-size:18px; font-weight:800; color:var(--ink); }
    .section-code { display:inline-flex; width:fit-content; padding:2px 8px; border-radius:999px; border:1px solid var(--line); color:var(--muted); font-size:11px; font-weight:800; background:var(--card-2); }
    .section-body { margin-top:10px; }
    .table-responsive { width:100%; overflow-x:auto; border-radius:10px; border:1px solid var(--line); max-height:420px; }
    .audit-table { width:100%; border-collapse:separate; border-spacing:0 6px; min-width:860px; padding:0 8px 8px; }
    .audit-table thead th { background:linear-gradient(135deg,#1e40af,#1e3a8a); color:#fff; font-weight:800; font-size:13px; padding:9px 8px; text-align:center; position:sticky; top:0; z-index:2; }
    .audit-table tbody td { border-top:1px solid var(--line); border-bottom:1px solid var(--line); padding:9px 8px; font-size:13px; background:var(--card); }
    .audit-table tbody tr:nth-child(odd) td { background:var(--card-2); }
    .audit-table tbody td:first-child { border-left:1px solid var(--line); border-top-left-radius:10px; border-bottom-left-radius:10px; text-align:center; font-weight:700; white-space:nowrap; }
    .audit-table tbody td:last-child { border-right:1px solid var(--line); border-top-right-radius:10px; border-bottom-right-radius:10px; }
    .audit-table td:nth-child(2) { width:62%; min-width:420px; font-weight:600; }
    .choice-pill { display:inline-flex; align-items:center; justify-content:center; width:100%; min-width:74px; padding:6px 8px; border-radius:999px; border:1px solid var(--line); background:var(--card); color:var(--muted); font-size:12px; font-weight:800; cursor:pointer; }
    .choice-input { position:absolute; opacity:0; pointer-events:none; }
    .choice-pill.is-selected { transform:translateY(-1px); box-shadow:0 6px 14px rgba(15,23,42,.14); }
    .choice-pill.choice-ya.is-selected { background:linear-gradient(135deg,#bbf7d0,#86efac); border-color:#16a34a; color:#166534; }
    .choice-pill.choice-tidak.is-selected { background:linear-gradient(135deg,#fecaca,#fca5a5); border-color:#f87171; color:#991b1b; }
    .choice-pill.choice-na.is-selected { background:linear-gradient(135deg,#e2e8f0,#cbd5e1); border-color:#94a3b8; color:#334155; }
    .audit-row.state-missing td { border-color:#fbbf24 !important; background:#fffbeb !important; }
    .progress-wrap { margin-top:12px; }
    .progress-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:700; color:var(--muted); }
    .progress-bar { width:100%; height:10px; border-radius:999px; background:var(--card-2); border:1px solid var(--line); overflow:hidden; }
    .progress-fill { width:0%; height:100%; background:linear-gradient(135deg,#22c55e,#16a34a); transition:width .2s ease; }
    .progress-warning { margin-top:8px; color:#b45309; font-weight:700; font-size:12px; }
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px; }
    .signature-pad-wrap { border:1.7px dashed var(--line-strong); border-radius:14px; padding:10px; background:var(--card-2); }
    .signature-canvas { width:100%; height:180px; background:var(--card); border:1.7px solid var(--line-strong); border-radius:10px; touch-action:none; cursor:crosshair; display:block; }
    .signature-actions { display:flex; gap:8px; margin-top:10px; }
    .signature-preview img { max-width:100%; height:70px; object-fit:contain; border:1px solid var(--line); border-radius:8px; background:var(--card); padding:4px; }
    .btn-row { display:flex; gap:10px; flex-wrap:wrap; margin-top:16px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 14px; border-radius:12px; text-decoration:none; font-weight:700; border:1px solid transparent; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#1e40af,#1e3a8a); color:#fff; }
    .btn-danger { background:linear-gradient(135deg,#dc2626,#b91c1c); color:#fff; }
    .btn-secondary { background:var(--card); color:var(--ink); border-color:var(--line-strong); }
    @media (max-width: 768px) {
      .page-wrap { padding:8px; }
      .hero-card h1 { font-size:22px; }
      .hero-card,.section-card { padding:14px; border-radius:12px; }
      .grid-2 { grid-template-columns:1fr; gap:12px; }
      .btn { width:100%; }
      .signature-canvas { height:145px; }
      .table-responsive { max-height:none; border:none; overflow:visible; }
      .audit-table { min-width: 0; width: 100%; border-spacing: 0; padding: 0; }
      .audit-table thead { display: none; }
      .audit-table tbody { display: grid; gap: 10px; }
      .audit-table tbody tr {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: var(--card);
        padding: 10px;
      }
      .audit-table tbody td {
        border: none;
        border-radius: 0 !important;
        padding: 0;
        background: transparent !important;
      }
      .audit-table tbody td:nth-child(1) {
        grid-column: 1 / -1;
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
      }
      .audit-table tbody td:nth-child(2) {
        grid-column: 1 / -1;
        min-width: 0;
        width: auto;
        font-size: 13px;
        line-height: 1.45;
      }
      .choice-pill {
        min-width: 0;
        padding: 8px 6px;
        font-size: 11px;
      }
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
          <h1>Edit Audit Ruang Isolasi #<?= (int) $audit['id'] ?></h1>
          <p class="subtitle">Tampilan edit disamakan dengan form audit awal agar lebih konsisten.</p>
        </div>

        <form method="post">
          <input type="hidden" name="tanda_tangan_petugas" value="<?= htmlspecialchars($audit['tanda_tangan_petugas']) ?>">
          <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

          <div class="section-card">
            <label class="field-label">Tanggal Audit <span class="required">*</span></label>
            <input type="date" name="tanggal_audit" class="form-control" value="<?= htmlspecialchars($formTanggal) ?>" required>

            <label class="field-label">Ruangan yang diaudit <span class="required">*</span></label>
            <select name="ruangan_diaudit" class="form-control" required>
              <option value="">— Pilih ruangan —</option>
              <?php foreach ($ruanganDiauditOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>" <?= ($formRuangan === $opt) ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
              <?php endforeach; ?>
            </select>

            <div class="progress-wrap">
              <div class="progress-head">
                <span>Progress Pengisian</span>
                <strong id="auditProgressText">0/0 item</strong>
              </div>
              <div class="progress-bar"><div class="progress-fill" id="auditProgressFill"></div></div>
              <div class="progress-warning" id="auditProgressWarning"></div>
            </div>
          </div>

          <?php $sectionIdx = 0; ?>
          <?php foreach ($checklistSections as $kode => $section): ?>
            <?php $sectionId = 'audit-section-' . $sectionIdx; ?>
            <div class="section-card">
              <button
                type="button"
                class="section-toggle"
                data-section-toggle
                aria-expanded="<?= $sectionIdx === 0 ? 'true' : 'false' ?>"
                aria-controls="<?= htmlspecialchars($sectionId) ?>">
                <span>
                  <h3 class="opportunity-title"><?= htmlspecialchars($section['title']) ?></h3>
                  <span class="section-code"><?= htmlspecialchars($kode) ?></span>
                </span>
                <span class="section-chevron" aria-hidden="true">▼</span>
              </button>
              <div class="section-body" id="<?= htmlspecialchars($sectionId) ?>">
                <div class="table-responsive">
                  <table class="audit-table">
                    <thead>
                      <tr>
                        <th>Kode</th>
                        <th>Item Indikator</th>
                        <?php foreach ($opsiJawaban as $opsiLabel): ?>
                          <th><?= htmlspecialchars($opsiLabel) ?></th>
                        <?php endforeach; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($section['items'] as $idx => $item): ?>
                        <?php $urutan = $idx + 1; ?>
                        <?php $kodeItem = $kode . str_pad((string) $urutan, 2, '0', STR_PAD_LEFT); ?>
                        <?php $selected = $formJawaban[$kode][$urutan] ?? ''; ?>
                        <tr class="audit-row" data-audit-row>
                          <td><?= htmlspecialchars($kodeItem) ?></td>
                          <td><?= htmlspecialchars($item) ?></td>
                          <?php foreach ($opsiJawaban as $opsiKey => $opsiLabel): ?>
                            <td>
                              <label class="choice-pill choice-<?= htmlspecialchars($opsiKey) ?>">
                                <input
                                  class="choice-input"
                                  type="radio"
                                  name="jawaban[<?= htmlspecialchars($kode) ?>][<?= $urutan ?>]"
                                  value="<?= htmlspecialchars($opsiKey) ?>"
                                  <?= $selected === $opsiKey ? 'checked' : '' ?>
                                  required>
                                <span><?= htmlspecialchars($opsiLabel) ?></span>
                              </label>
                            </td>
                          <?php endforeach; ?>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <?php $sectionIdx++; ?>
          <?php endforeach; ?>

          <div class="section-card">
            <label class="field-label">Catatan Audit</label>
            <textarea class="form-control" name="catatan_audit" rows="4"><?= htmlspecialchars($formCatatan) ?></textarea>
            <div class="grid-2">
              <div>
                <label class="field-label">Nama Petugas Unit <span class="required">*</span></label>
                <input type="text" name="nama_petugas_unit" class="form-control" value="<?= htmlspecialchars($formPetugas) ?>" required>
              </div>
              <div>
                <label class="field-label">Tanda Tangan Petugas <span class="required">*</span></label>
                <div class="signature-pad-wrap">
                  <canvas id="signatureCanvas" class="signature-canvas"></canvas>
                  <input type="hidden" name="signature_data" id="signatureData">
                  <div class="signature-actions">
                    <button type="button" class="btn btn-danger" id="btnClearSignature">Hapus Tanda Tangan Baru</button>
                  </div>
                </div>
                <?php if (!empty($audit['tanda_tangan_petugas'])): ?>
                  <div class="signature-preview" style="margin-top:8px;">
                    <small>Tanda tangan saat ini:</small><br>
                    <img src="../../<?= htmlspecialchars($audit['tanda_tangan_petugas']) ?>" alt="Tanda tangan saat ini">
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="btn-row">
              <button class="btn btn-primary" name="update" type="submit">Simpan Perubahan</button>
              <a class="btn btn-secondary" href="detail_audit.php?id=<?= $id ?>">Kembali ke Detail</a>
              <a class="btn btn-secondary" href="../audit_ruang_isolasi.php?tab=tab-data">Data Audit</a>
            </div>
          </div>
        </form>
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

    (function () {
      const rows = document.querySelectorAll('[data-audit-row]');
      const progressText = document.getElementById('auditProgressText');
      const progressFill = document.getElementById('auditProgressFill');
      const progressWarning = document.getElementById('auditProgressWarning');

      function updateSelectedPills() {
        document.querySelectorAll('.choice-pill').forEach(function (pill) {
          pill.classList.remove('is-selected');
        });
        document.querySelectorAll('.choice-input:checked').forEach(function (input) {
          const holder = input.closest('.choice-pill');
          if (holder) holder.classList.add('is-selected');
        });
      }

      function updateRowStates() {
        rows.forEach(function (row) {
          row.classList.remove('state-missing');
          const checked = row.querySelector('.choice-input:checked');
          if (!checked) {
            row.classList.add('state-missing');
          }
        });
      }

      function updateProgress() {
        const radios = document.querySelectorAll('.choice-input[name^="jawaban["]');
        const names = new Set();
        radios.forEach(function (radio) { names.add(radio.name); });
        let completed = 0;
        names.forEach(function (name) {
          if (document.querySelector('.choice-input[name="' + name + '"]:checked')) {
            completed++;
          }
        });
        const total = names.size;
        const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
        const remaining = Math.max(0, total - completed);
        if (progressText) progressText.textContent = completed + ' / ' + total + ' item sudah diisi';
        if (progressFill) progressFill.style.width = percent + '%';
        if (progressWarning) {
          progressWarning.textContent = remaining > 0
            ? 'Masih ada ' + remaining + ' item yang belum dipilih.'
            : 'Semua item sudah terisi, siap simpan perubahan.';
        }
      }

      document.addEventListener('change', function (event) {
        if (!event.target.classList.contains('choice-input')) return;
        updateSelectedPills();
        updateRowStates();
        updateProgress();
      });

      function setSectionState(btn, body, expanded) {
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        body.style.display = expanded ? 'block' : 'none';
      }

      const sectionPairs = [];
      document.querySelectorAll('[data-section-toggle]').forEach(function (btn, idx) {
        const section = btn.closest('.section-card');
        const body = section ? section.querySelector('.section-body') : null;
        if (!section || !body) return;

        const defaultExpanded = idx === 0;
        setSectionState(btn, body, defaultExpanded);
        sectionPairs.push({ btn: btn, body: body });

        btn.addEventListener('click', function () {
          const expanded = btn.getAttribute('aria-expanded') === 'true';
          setSectionState(btn, body, !expanded);
        });
      });

      const invalidRows = document.querySelectorAll('.audit-row.state-missing');
      if (invalidRows.length > 0) {
        sectionPairs.forEach(function (pair) {
          const hasInvalidInSection = pair.body.querySelector('.audit-row.state-missing');
          if (hasInvalidInSection) {
            setSectionState(pair.btn, pair.body, true);
          }
        });
      }

      updateSelectedPills();
      updateRowStates();
      updateProgress();
    })();
  </script>
</body>
</html>
