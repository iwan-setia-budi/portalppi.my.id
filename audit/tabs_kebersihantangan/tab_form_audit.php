<div id="tab-form" class="tab-pane active">
  <form action="" method="post">
    <div class="section-card">
      <div class="section-title">Data Audit</div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Tanggal Audit <span class="required">*</span></label>
          <input
            type="date"
            name="tanggal_audit"
            class="form-control"
            value="<?= htmlspecialchars($_POST['tanggal_audit'] ?? '') ?>"
            required>
        </div>

        <div class="form-group">
          <label class="form-label">Nama Petugas yang diaudit <span class="required">*</span></label>
          <input
            type="text"
            name="nama_petugas"
            class="form-control"
            placeholder="Masukkan nama petugas"
            value="<?= htmlspecialchars($_POST['nama_petugas'] ?? '') ?>"
            required>
        </div>

        <div class="form-group full">
          <label class="form-label">Profesi <span class="required">*</span></label>
          <div class="radio-list">
            <?php foreach ($profesiList as $profesi): ?>
              <label class="radio-item">
                <input
                  type="radio"
                  name="profesi"
                  value="<?= htmlspecialchars($profesi) ?>"
                  <?= (($_POST['profesi'] ?? '') === $profesi) ? 'checked' : '' ?>
                  required>
                <span><?= htmlspecialchars($profesi) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group full">
          <label class="form-label">Ruangan yang diaudit <span class="required">*</span></label>
          <div class="radio-list">
            <?php foreach ($ruanganList as $ruangan): ?>
              <label class="radio-item">
                <input
                  type="radio"
                  name="ruangan"
                  value="<?= htmlspecialchars($ruangan) ?>"
                  <?= (($_POST['ruangan'] ?? '') === $ruangan) ? 'checked' : '' ?>
                  required>
                <span><?= htmlspecialchars($ruangan) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="section-card">
      <div class="intro-bar">Audit Hand Hygiene Primaya Hospital Bhakti Wara</div>
      <div class="intro-text">
        Menentukan angka kepatuhan petugas di rumah sakit dengan 5 moment cuci tangan
        dan 6 langkah cuci tangan.
      </div>
    </div>

    <div class="subheading-box">
      5 MOMENT DAN 6 LANGKAH CUCI TANGAN<br>
      <small>Pilih hasil observasi pada setiap opportunity</small>
    </div>

    <?php for ($i = 1; $i <= 5; $i++): ?>
      <div class="opportunity-card">
        <div class="opportunity-title"><?= $i ?>. Opportunities</div>

        <div class="table-responsive">
          <table class="audit-table">
            <thead>
              <tr>
                <th>Moment</th>
                <?php foreach ($opsiCuciTangan as $key => $label): ?>
                  <th><?= htmlspecialchars($label) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($moments as $momentKey => $momentLabel): ?>
                <tr>
                  <td><?= htmlspecialchars($momentLabel) ?></td>
                  <?php foreach ($opsiCuciTangan as $opsiKey => $opsiLabel): ?>
                    <td>
                      <input
                        type="radio"
                        name="observasi[<?= $i ?>][<?= $momentKey ?>]"
                        value="<?= htmlspecialchars($opsiKey) ?>"
                        <?= (($_POST['observasi'][$i][$momentKey] ?? '') === $opsiKey) ? 'checked' : '' ?>>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mobile-card">
        <div class="mobile-opportunity">
          <div class="mobile-opportunity-title"><?= $i ?>. Opportunities</div>

          <?php foreach ($moments as $momentKey => $momentLabel): ?>
            <div class="mobile-moment-card">
              <div class="mobile-moment-title"><?= htmlspecialchars($momentLabel) ?></div>

              <div class="mobile-option-list">
                <?php foreach ($opsiCuciTangan as $opsiKey => $opsiLabel): ?>
                  <label class="mobile-option">
                    <input
                      type="radio"
                      name="observasi[<?= $i ?>][<?= $momentKey ?>]"
                      value="<?= htmlspecialchars($opsiKey) ?>"
                      <?= (($_POST['observasi'][$i][$momentKey] ?? '') === $opsiKey) ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($opsiLabel) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endfor; ?>

    <div class="section-card">
      <div class="form-group">
        <label class="form-label">Keterangan</label>
        <textarea
          name="keterangan"
          class="form-textarea"
          rows="4"
          placeholder="Tambahkan catatan jika diperlukan"><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
      </div>

      <div class="button-row">
        <button type="submit" name="simpan" class="btn btn-primary">Simpan Audit</button>
        <button type="reset" class="btn btn-secondary">Kosongkan Formulir</button>
      </div>

      <div class="small-note">
        Pastikan data audit utama terisi dan setiap observasi dipilih sesuai hasil audit.
      </div>
    </div>
  </form>
</div>