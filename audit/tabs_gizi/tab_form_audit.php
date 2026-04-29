<style>
  #tab-form .section-title {
    margin: 0 0 12px;
    font-size: 24px;
    font-weight: 900;
    letter-spacing: -0.2px;
  }

  #tab-form .field-label {
    display: block;
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 8px;
    color: #0f172a;
  }

  #tab-form .required {
    color: #e11d48;
  }

  #tab-form .opportunity-card {
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 14px;
    background: #fff;
    padding: 14px;
    margin-bottom: 14px;
  }

  #tab-form .opportunity-title {
    margin: 0 0 10px;
    font-size: 20px;
    font-weight: 800;
    color: #111827;
  }

  #tab-form .table-responsive {
    width: 100%;
    overflow-x: auto;
    border-radius: 10px;
  }

  #tab-form .audit-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 760px;
    border-radius: 10px;
    overflow: hidden;
  }

  #tab-form .audit-table thead th {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
    color: #fff;
    font-weight: 800;
    font-size: 13px;
    padding: 10px 8px;
    border: none;
    text-align: center;
  }

  #tab-form .audit-table thead th:first-child {
    text-align: left;
    padding-left: 12px;
  }

  #tab-form .audit-table tbody td {
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 8px;
    font-size: 13px;
  }

  #tab-form .audit-table tbody tr:nth-child(odd) td {
    background: #f9fbfc;
  }

  #tab-form .audit-table td:first-child {
    font-weight: 600;
    color: #1f2937;
    width: 70%;
    min-width: 420px;
    text-align: left;
  }

  #tab-form .audit-table td:not(:first-child) {
    width: 10%;
    text-align: center;
  }

  #tab-form .audit-table input[type="radio"] {
    width: 18px;
    height: 18px;
    accent-color: #1e40af;
    cursor: pointer;
  }

  #tab-form .mobile-card {
    display: none;
  }

  #tab-form .mobile-item {
    border-bottom: 1px solid #edf2f7;
    padding: 10px 0;
  }

  #tab-form .mobile-item:last-child {
    border-bottom: none;
  }

  #tab-form .mobile-item-title {
    font-size: 13px;
    line-height: 1.45;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
  }

  #tab-form .mobile-option-list {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  #tab-form .mobile-option {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #dbe3ee;
    border-radius: 10px;
    padding: 7px 10px;
    background: #fff;
    font-size: 12px;
    cursor: pointer;
  }

  #tab-form .mobile-option input[type="radio"] {
    width: 15px;
    height: 15px;
    accent-color: #1e40af;
  }

  #tab-form .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  #tab-form .mt-16 {
    margin-top: 16px;
  }

  #tab-form .small-note {
    margin-top: 8px;
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
  }

  #tab-form .signature-pad-wrap {
    border: 1.5px dashed #94a3b8;
    border-radius: 14px;
    padding: 10px;
    background: #f8fafc;
  }

  #tab-form .signature-canvas {
    width: 100%;
    height: 190px;
    background: #fff;
    border: 1px solid #dbe3ee;
    border-radius: 10px;
    touch-action: none;
    cursor: crosshair;
    display: block;
  }

  #tab-form .signature-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 10px;
  }

  @media (max-width: 768px) {
    #tab-form .section-title {
      font-size: 18px;
    }

    #tab-form .opportunity-title {
      font-size: 16px;
      margin-bottom: 8px;
    }

    #tab-form .opportunity-card {
      padding: 12px;
    }

    #tab-form .table-responsive {
      display: none;
    }

    #tab-form .mobile-card {
      display: block;
    }

    #tab-form .grid-2 {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    #tab-form .signature-canvas {
      height: 150px;
    }
  }
</style>

<div id="tab-form" class="tab-pane active">
  <form method="post" enctype="multipart/form-data">
    <div class="section-card">
      <h2 class="section-title">Form Audit Gizi</h2>
      <label class="field-label">Tanggal Audit <span class="required">*</span></label>
      <input type="date" name="tanggal_audit" class="form-control" value="<?= htmlspecialchars($_POST['tanggal_audit'] ?? '') ?>" required>
    </div>

    <?php foreach ($checklistSections as $kode => $section): ?>
      <div class="section-card opportunity-card">
        <h3 class="opportunity-title"><?= htmlspecialchars($kode) ?> - <?= htmlspecialchars($section['title']) ?></h3>

        <div class="table-responsive">
          <table class="audit-table">
            <thead>
              <tr>
                <th>Daftar Tilik</th>
                <?php foreach ($opsiJawaban as $opsiLabel): ?>
                  <th><?= htmlspecialchars($opsiLabel) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($section['items'] as $idx => $item): ?>
                <?php $urutan = $idx + 1; ?>
                <tr>
                  <td><strong><?= $urutan ?>.</strong> <?= htmlspecialchars($item) ?></td>
                  <?php foreach ($opsiJawaban as $opsiKey => $opsiLabel): ?>
                    <td>
                      <input
                        type="radio"
                        name="jawaban[<?= htmlspecialchars($kode) ?>][<?= $urutan ?>]"
                        value="<?= htmlspecialchars($opsiKey) ?>"
                        <?= (($_POST['jawaban'][$kode][$urutan] ?? '') === $opsiKey) ? 'checked' : '' ?>
                        required>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="mobile-card">
          <?php foreach ($section['items'] as $idx => $item): ?>
            <?php $urutan = $idx + 1; ?>
            <div class="mobile-item">
              <div class="mobile-item-title"><strong><?= $urutan ?>.</strong> <?= htmlspecialchars($item) ?></div>
              <div class="mobile-option-list">
                <?php foreach ($opsiJawaban as $opsiKey => $opsiLabel): ?>
                  <label class="mobile-option">
                    <input
                      type="radio"
                      name="jawaban[<?= htmlspecialchars($kode) ?>][<?= $urutan ?>]"
                      value="<?= htmlspecialchars($opsiKey) ?>"
                      <?= (($_POST['jawaban'][$kode][$urutan] ?? '') === $opsiKey) ? 'checked' : '' ?>
                      required>
                    <span><?= htmlspecialchars($opsiLabel) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="section-card">
      <label class="field-label">Catatan Audit</label>
      <textarea name="catatan_audit" class="form-control" rows="4" placeholder="Tambahkan catatan audit jika diperlukan"><?= htmlspecialchars($_POST['catatan_audit'] ?? '') ?></textarea>

      <div class="grid-2 mt-16">
        <div>
          <label class="field-label">Nama Petugas Unit <span class="required">*</span></label>
          <input type="text" name="nama_petugas_unit" class="form-control" value="<?= htmlspecialchars($_POST['nama_petugas_unit'] ?? '') ?>" required>
        </div>
        <div>
          <label class="field-label">Tanda Tangan Petugas <span class="required">*</span></label>
          <div class="signature-pad-wrap">
            <canvas id="signatureCanvas" class="signature-canvas"></canvas>
            <input type="hidden" name="signature_data" id="signatureData" required>
            <div class="signature-actions">
              <button type="button" class="btn btn-secondary" id="btnClearSignature">Hapus Tanda Tangan</button>
            </div>
          </div>
          <div class="small-note">Tanda tangan langsung di area atas (presisi desktop & HP).</div>
        </div>
      </div>

      <div class="mt-16">
        <label class="field-label">Dokumentasi Foto</label>
        <input type="file" name="dokumentasi_foto[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple>
        <div class="small-note">Upload maksimum 5 file yang didukung. Maks 10 MB per file.</div>
      </div>

      <div class="mt-16">
        <button type="submit" name="simpan" class="btn btn-primary">Simpan Audit</button>
      </div>
    </div>
  </form>
</div>
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
