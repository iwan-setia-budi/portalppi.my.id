<div id="tab-data-apd" class="tab-pane active">
  <div class="section-card">
    <div class="section-title">Filter Data Audit APD</div>

    <form method="get">
      <input type="hidden" name="tab" value="tab-data">

      <div class="filter-row">
        <input
          type="text"
          name="keyword_data"
          class="form-control"
          placeholder="Cari nama petugas / profesi / ruangan / tindakan / keterangan"
          value="<?= htmlspecialchars($keyword_data ?? '') ?>">

        <select name="bulan" class="form-control">
          <option value="">Semua Bulan</option>
          <?php for ($b = 1; $b <= 12; $b++): ?>
            <option value="<?= $b ?>" <?= (string) ($filter_bulan ?? '') === (string) $b ? 'selected' : '' ?>>
              <?= $b ?>
            </option>
          <?php endfor; ?>
        </select>

        <select name="tahun" class="form-control">
          <option value="">Semua Tahun</option>
          <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
            <option value="<?= $t ?>" <?= (string) ($filter_tahun ?? '') === (string) $t ? 'selected' : '' ?>>
              <?= $t ?>
            </option>
          <?php endfor; ?>
        </select>

        <select name="f_profesi" class="form-control">
          <option value="">Semua Profesi</option>
          <?php foreach ($profesiList as $item): ?>
            <option value="<?= htmlspecialchars($item) ?>" <?= ($filter_profesi ?? '') === $item ? 'selected' : '' ?>>
              <?= htmlspecialchars($item) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-row">
        <select name="f_ruangan" class="form-control">
          <option value="">Semua Unit</option>
          <?php foreach ($ruanganList as $item): ?>
            <option value="<?= htmlspecialchars($item) ?>" <?= ($filter_ruangan ?? '') === $item ? 'selected' : '' ?>>
              <?= htmlspecialchars($item) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <div class="button-row">
          <button type="submit" class="btn btn-primary">Cari</button>
          <a href="?tab=tab-data" class="btn btn-secondary">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <div class="section-card">
    <div class="section-title">Data Audit APD</div>

    <div class="data-table-wrap">
      <table class="summary-table data-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Nama Petugas</th>
            <th>Profesi</th>
            <th>Ruangan</th>
            <th>Tindakan</th>
            <th>Num</th>
            <th>Denum</th>
            <th>%</th>
            <th>Foto</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($qData) && mysqli_num_rows($qData) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($qData)): ?>
              <?php
              $num = (int) ($row['num'] ?? 0);
              $denum = (int) ($row['denum'] ?? 0);
              $persen = $denum > 0 ? round(($num / $denum) * 100, 2) : 0;
              $foto = $row['foto'] ?? '';
              ?>
              <tr>
                <td><?= htmlspecialchars($row['tanggal_audit'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nama_petugas'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['profesi'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['ruangan'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['tindakan'] ?? '') ?></td>
                <td><?= $num ?></td>
                <td><?= $denum ?></td>
                <td><?= $persen ?>%</td>
                <td>
                  <?php if ($foto !== ''): ?>
                    <a
                      href="uploads_apd/<?= htmlspecialchars($foto) ?>"
                      target="_blank"
                      class="btn btn-secondary"
                      style="min-height:34px;padding:8px 12px;font-size:12px;border-radius:10px;">
                      Lihat Foto
                    </a>
                  <?php else: ?>
                    <span style="color:#64748b;font-weight:700;">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="aksi-group">
                    <a
                      href="crud_apd/detail_audit.php?id=<?= (int) $row['id'] ?>"
                      class="btn btn-primary">
                      Lihat
                    </a>

                    <a
                      href="crud_apd/edit_audit.php?id=<?= (int) $row['id'] ?>"
                      class="btn btn-warning">
                      Edit
                    </a>

                    <a
                      href="crud_apd/hapus_audit.php?id=<?= (int) $row['id'] ?>"
                      class="btn btn-danger"
                      onclick="return confirm('Yakin hapus data audit APD ini?')">
                      Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" style="text-align:center; padding:20px; color:#64748b; font-weight:600;">
                Belum ada data audit APD.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
      <div class="button-row pagination-row">
        <div class="small-note">
          Halaman <?= (int) ($page ?? 1) ?> dari <?= (int) ($totalPages ?? 1) ?>
        </div>

        <div class="button-row" style="margin-top:0;">
          <?php
          $baseQuery = $_GET;
          $baseQuery['tab'] = 'tab-data';
          ?>

          <?php if (($page ?? 1) > 1): ?>
            <?php
            $prevQuery = $baseQuery;
            $prevQuery['page'] = ((int) $page) - 1;
            ?>
            <a href="?<?= htmlspecialchars(http_build_query($prevQuery)) ?>" class="btn btn-secondary">
              ← Sebelumnya
            </a>
          <?php endif; ?>

          <?php if (($page ?? 1) < ($totalPages ?? 1)): ?>
            <?php
            $nextQuery = $baseQuery;
            $nextQuery['page'] = ((int) $page) + 1;
            ?>
            <a href="?<?= htmlspecialchars(http_build_query($nextQuery)) ?>" class="btn btn-primary">
              Berikutnya →
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
