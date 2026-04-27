<style>
  /* Khusus form audit: mode HP tetap pakai tabel seperti desktop */
  #tab-form .mobile-card {
    display: none !important;
  }

  #tab-form .opportunity-card {
    display: block !important;
  }

  #tab-form .opportunity-card .audit-table {
    display: table !important;
  }

  #tab-form .table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }

  #tab-form .audit-table {
    min-width: 860px;
  }

  #tab-form .audit-table th,
  #tab-form .audit-table td {
    white-space: normal;
  }

  #tab-form .audit-table td:first-child {
    min-width: 260px;
    width: 260px;
  }

/* Baris tabel form lebih tegas */
#tab-form .audit-table tbody tr {
  border-bottom: 2px solid #dbeafe;
}

#tab-form .audit-table tbody td {
  border-bottom: 2px solid #dbeafe !important;
  border-right: 1px solid #e5e7eb;
}

#tab-form .audit-table tbody td:last-child {
  border-right: none;
}

#tab-form .audit-table tbody tr:nth-child(odd) td {
  background: #f8fafc;
}

#tab-form .audit-table tbody tr:nth-child(even) td {
  background: #eef6ff;
}

#tab-form .audit-table tbody tr:hover td {
  background: #dbeafe !important;
}

/* Radio button lebih jelas */
#tab-form .audit-table input[type="radio"] {
  appearance: none;
  -webkit-appearance: none;
  width: 22px;
  height: 22px;
  border: 2px solid #64748b;
  border-radius: 50%;
  background: #ffffff;
  cursor: pointer;
  box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.08);
}

#tab-form .audit-table input[type="radio"]:checked {
  border: 6px solid #1e40af;
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.16);
}

#tab-form .audit-table input[type="radio"]:hover {
  border-color: #1e40af;
}

/* Khusus HP: radio tetap jelas walaupun compact */
@media (max-width: 768px) {
  #tab-form .audit-table input[type="radio"] {
    width: 20px;
    height: 20px;
  }

  #tab-form .audit-table input[type="radio"]:checked {
    border-width: 5px;
  }

  #tab-form .audit-table tbody td {
    border-bottom: 2px solid #dbeafe !important;
  }
}

</style>

<style>
/* bikin tabel lebih compact di HP */
@media (max-width: 768px) {

  #tab-form .audit-table {
    min-width: 600px; /* sebelumnya 860px -> diperkecil */
  }

  #tab-form .audit-table th,
  #tab-form .audit-table td {
    padding: 8px 6px; /* lebih kecil */
    font-size: 12px;
  }

  /* header diperkecil */
  #tab-form .audit-table thead th {
    font-size: 11px;
    padding: 8px 6px;
  }

  /* kolom moment dipersempit */
  #tab-form .audit-table td:first-child {
    min-width: 140px;
    width: 140px;
    font-size: 12px;
  }

  /* radio lebih kecil */
  #tab-form .audit-table input[type="radio"] {
    width: 16px;
    height: 16px;
  }
}

@media (max-width: 768px) {

  /* Bikin kolom lebih proporsional */
  #tab-form .audit-table th:not(:first-child),
  #tab-form .audit-table td:not(:first-child) {
    width: 70px;
    text-align: center;
  }

  /* Header jadi 2 baris biar tidak sempit */
  #tab-form .audit-table thead th {
    white-space: normal;
    line-height: 1.2;
  }

  /* Moment biar lebih enak dibaca */
  #tab-form .audit-table td:first-child {
    line-height: 1.3;
    font-weight: 600;
  }

  /* Tambahin jarak antar radio */
  #tab-form .audit-table td {
    text-align: center;
  }

  /* Smooth scroll biar enak */
  #tab-form .table-responsive {
    scroll-snap-type: x mandatory;
  }

  #tab-form .audit-table th,
  #tab-form .audit-table td {
    scroll-snap-align: start;
  }
}

</style>

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
          <select name="profesi" class="form-control" required>
            <option value="">Pilih Profesi</option>
            <?php foreach ($profesiList as $profesi): ?>
              <option
                value="<?= htmlspecialchars($profesi) ?>"
                <?= (($_POST['profesi'] ?? '') === $profesi) ? 'selected' : '' ?>>
                <?= htmlspecialchars($profesi) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group full">
          <label class="form-label">Ruangan yang diaudit <span class="required">*</span></label>
          <select name="ruangan" class="form-control" required>
            <option value="">Pilih Unit/Ruangan</option>
            <?php foreach ($ruanganList as $ruangan): ?>
              <option
                value="<?= htmlspecialchars($ruangan) ?>"
                <?= (($_POST['ruangan'] ?? '') === $ruangan) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ruangan) ?>
              </option>
            <?php endforeach; ?>
          </select>
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