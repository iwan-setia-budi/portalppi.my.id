<style>
  /* Khusus form audit APD */
  #tab-form-apd .table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: #fff;
  }

  #tab-form-apd .apd-table {
    width: 100%;
    min-width: 640px;
    border-collapse: separate;
    border-spacing: 0;
  }

  #tab-form-apd .apd-table thead th {
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: #fff;
    font-weight: 800;
    padding: 14px;
    font-size: 14px;
    text-align: center;
    white-space: nowrap;
  }

  #tab-form-apd .apd-table thead th:first-child {
    text-align: left;
  }

  #tab-form-apd .apd-table tbody td {
    padding: 13px 14px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.30);
    border-right: 1px solid rgba(226, 232, 240, 0.9);
    font-size: 14px;
    background: #fff;
    text-align: center;
    vertical-align: middle;
  }

  #tab-form-apd .apd-table tbody td:last-child {
    border-right: none;
  }

  #tab-form-apd .apd-table tbody tr:nth-child(odd) td {
    background: #f8fafc;
  }

  #tab-form-apd .apd-table tbody tr:nth-child(even) td {
    background: #eef6ff;
  }

  #tab-form-apd .apd-table tbody tr:hover td {
    background: #dbeafe !important;
  }

  #tab-form-apd .apd-table td:first-child {
    text-align: left;
    font-weight: 700;
    color: #1f2937;
    min-width: 260px;
  }

  #tab-form-apd .apd-table input[type="radio"] {
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

  #tab-form-apd .apd-table input[type="radio"]:checked {
    border: 6px solid #1e40af;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.16);
  }

  #tab-form-apd .apd-table input[type="radio"]:hover {
    border-color: #1e40af;
  }

  #tab-form-apd .table-responsive::after {
    content: "Geser tabel ke samping";
    display: none;
    text-align: right;
    font-size: 12px;
    color: #64748b;
    padding: 8px 10px 10px;
    font-weight: 700;
  }

  @media (max-width: 768px) {
    #tab-form-apd .apd-table {
      min-width: 0;
      width: 100%;
      table-layout: fixed;
    }

    #tab-form-apd .apd-table th,
    #tab-form-apd .apd-table td {
      padding: 7px 4px;
      font-size: 11px;
    }

    #tab-form-apd .apd-table td:first-child {
      min-width: 0;
      line-height: 1.3;
      width: 52%;
      white-space: normal;
      word-break: break-word;
    }

    #tab-form-apd .apd-table input[type="radio"] {
      width: 16px;
      height: 16px;
    }

    #tab-form-apd .apd-table input[type="radio"]:checked {
      border-width: 4px;
    }

    #tab-form-apd .apd-table th:not(:first-child),
    #tab-form-apd .apd-table td:not(:first-child) {
      width: 16%;
      text-align: center;
      white-space: nowrap;
    }

    #tab-form-apd .table-responsive::after {
      display: none;
    }
  }
</style>

<div id="tab-form-apd" class="tab-pane active">
  <form action="" method="post" enctype="multipart/form-data">
    <div class="section-card">
      <div class="section-title">Data Audit APD</div>

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

        <div class="form-group">
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

        <div class="form-group">
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

        <div class="form-group full">
          <label class="form-label">Tindakan yang dilakukan <span class="required">*</span></label>
          <input
            type="text"
            name="tindakan"
            class="form-control"
            placeholder="Masukkan tindakan yang dilakukan"
            value="<?= htmlspecialchars($_POST['tindakan'] ?? '') ?>"
            required>
        </div>
      </div>
    </div>

    <div class="section-card">
      <div class="section-title">Indikator Penilaian APD <span class="required">*</span></div>
      <div class="small-note" style="margin-bottom:12px;">
        Pilih Ya, Tidak, atau NA sesuai hasil observasi. Item yang tidak terobservasi boleh dikosongkan bila diperlukan.
      </div>

      <div class="table-responsive">
        <table class="apd-table">
          <thead>
            <tr>
              <th>Indikator Penilaian</th>
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
                      <?= (($_POST['penilaian'][$key] ?? '') === $opsiKey) ? 'checked' : '' ?>>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="section-card">
      <div class="section-title">APD yang digunakan</div>
      <div class="small-note" style="margin-bottom:12px;">
        Pilih APD yang digunakan atau tandai NA bila tidak relevan.
      </div>

      <div class="table-responsive">
        <table class="apd-table">
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
                      <?= (($_POST['apd'][$key] ?? '') === $opsiKey) ? 'checked' : '' ?>>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="section-card">
      <div class="form-grid">
        <div class="form-group full">
          <label class="form-label">Keterangan</label>
          <textarea
            name="keterangan"
            class="form-textarea"
            rows="4"
            placeholder="Tambahkan catatan jika diperlukan"><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
        </div>

        <div class="form-group full">
          <label class="form-label">Foto Bila Ada</label>
          <input
            type="file"
            name="foto"
            class="form-control"
            accept="image/png,image/jpeg,image/jpg,image/webp">
          <div class="small-note">Format yang disarankan: JPG, PNG, atau WEBP.</div>
        </div>
      </div>

      <div class="button-row">
        <button type="submit" name="simpan" class="btn btn-primary">Simpan Audit APD</button>
        <button type="reset" class="btn btn-secondary">Kosongkan Formulir</button>
      </div>
    </div>
  </form>
</div>
