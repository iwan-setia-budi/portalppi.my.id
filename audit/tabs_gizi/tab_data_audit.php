<div id="tab-data" class="tab-pane active">
  <div class="section-card">
    <h3>Filter Data Audit</h3>
    <form method="get">
      <input type="hidden" name="tab" value="tab-data">
      <div style="display:grid; grid-template-columns:2fr 1fr 1fr auto; gap:10px;">
        <input type="text" name="keyword_data" class="form-control" placeholder="Cari nama petugas/catatan" value="<?= htmlspecialchars($keywordData) ?>">
        <select name="bulan" class="form-control">
          <option value="">Semua Bulan</option>
          <?php for ($b = 1; $b <= 12; $b++): ?>
            <option value="<?= $b ?>" <?= (string) $filterBulan === (string) $b ? 'selected' : '' ?>><?= $b ?></option>
          <?php endfor; ?>
        </select>
        <select name="tahun" class="form-control">
          <option value="">Semua Tahun</option>
          <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
            <option value="<?= $t ?>" <?= (string) $filterTahun === (string) $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn btn-primary" type="submit">Cari</button>
      </div>
    </form>
  </div>

  <div class="section-card">
    <h3>Data Audit Gizi</h3>
    <div style="overflow-x:auto;">
      <table style="width:100%; min-width:780px; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="padding:10px; border-bottom:1px solid #dbe3ee;">Tanggal</th>
            <th style="padding:10px; border-bottom:1px solid #dbe3ee;">Nama Petugas Unit</th>
            <th style="padding:10px; border-bottom:1px solid #dbe3ee; text-align:center;">Num</th>
            <th style="padding:10px; border-bottom:1px solid #dbe3ee; text-align:center;">Denum</th>
            <th style="padding:10px; border-bottom:1px solid #dbe3ee; text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($qData) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($qData)): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #eef2f7;"><?= htmlspecialchars($row['tanggal_audit']) ?></td>
                <td style="padding:10px; border-bottom:1px solid #eef2f7;"><?= htmlspecialchars($row['nama_petugas_unit']) ?></td>
                <td style="padding:10px; border-bottom:1px solid #eef2f7; text-align:center;"><?= (int) $row['num'] ?></td>
                <td style="padding:10px; border-bottom:1px solid #eef2f7; text-align:center;"><?= (int) $row['denum'] ?></td>
                <td style="padding:10px; border-bottom:1px solid #eef2f7; text-align:center;">
                  <a class="btn btn-primary" href="crud_gizi/detail_audit.php?id=<?= (int) $row['id'] ?>">Lihat</a>
                  <a class="btn btn-warning" href="crud_gizi/edit_audit.php?id=<?= (int) $row['id'] ?>">Edit</a>
                  <a class="btn btn-danger" href="crud_gizi/hapus_audit.php?id=<?= (int) $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" style="padding:16px; text-align:center;">Belum ada data audit gizi.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
