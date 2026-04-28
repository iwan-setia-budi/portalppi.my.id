<style>
  #tab-rekap-apd .summary-table th:first-child,
  #tab-rekap-apd .summary-table td:first-child {
    text-align: left;
  }

  #tab-rekap-apd .summary-table th:not(:first-child),
  #tab-rekap-apd .summary-table td:not(:first-child) {
    text-align: center;
  }

  #tab-rekap-apd .summary-table td {
    vertical-align: middle;
  }

  #tab-rekap-apd .summary-table td:nth-child(2),
  #tab-rekap-apd .summary-table td:nth-child(3),
  #tab-rekap-apd .summary-table td:nth-child(4),
  #tab-rekap-apd .summary-table td:nth-child(5),
  #tab-rekap-apd .summary-table td:nth-child(6),
  #tab-rekap-apd .summary-table td:nth-child(7) {
    font-variant-numeric: tabular-nums;
    font-weight: 600;
  }

  #tab-rekap-apd .summary-table tfoot td {
    background: #e0f2fe !important;
    font-weight: 900;
    border-top: 2px solid #1e40af;
    font-size: 15px;
  }

  #tab-rekap-apd .summary-table tbody tr:hover td {
    background: #eff6ff !important;
  }

  #tab-rekap-apd .badge-ya,
  #tab-rekap-apd .badge-tidak,
  #tab-rekap-apd .badge-na {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 54px;
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 900;
    font-size: 12px;
  }

  #tab-rekap-apd .badge-ya {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
  }

  #tab-rekap-apd .badge-tidak {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }

  #tab-rekap-apd .badge-na {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
  }
</style>

<div id="tab-rekap-apd" class="tab-pane active">
  <div class="section-card">
    <div class="section-title">Filter Rekap Audit APD</div>

    <form method="get">
      <input type="hidden" name="tab" value="tab-rekap">

      <div class="filter-row">
        <input
          type="date"
          name="tgl_awal"
          class="form-control"
          value="<?= htmlspecialchars($filter_tgl_awal ?? '') ?>">

        <input
          type="date"
          name="tgl_akhir"
          class="form-control"
          value="<?= htmlspecialchars($filter_tgl_akhir ?? '') ?>">

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

        <select name="f_ruangan" class="form-control">
          <option value="">Semua Unit</option>
          <?php foreach ($ruanganList as $item): ?>
            <option value="<?= htmlspecialchars($item) ?>" <?= ($filter_ruangan ?? '') === $item ? 'selected' : '' ?>>
              <?= htmlspecialchars($item) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <div class="button-row">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="?tab=tab-rekap" class="btn btn-secondary">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <div class="section-card">
    <div class="section-title">Kepatuhan APD Keseluruhan</div>
    <div class="table-scroll-x">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Indikator</th>
            <th>Ya</th>
            <th>Tidak</th>
            <th>NA</th>
            <th>Denum</th>
            <th>% Ya</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $totalYa = (int) ($kepatuhanAPD['ya'] ?? 0);
          $totalTidak = (int) ($kepatuhanAPD['tidak'] ?? 0);
          $totalNa = (int) ($kepatuhanAPD['na'] ?? 0);
          $totalDenum = $totalYa + $totalTidak;
          $totalPersen = $totalDenum > 0 ? round(($totalYa / $totalDenum) * 100, 2) : 0;
          ?>
          <tr>
            <td>Kepatuhan APD Keseluruhan</td>
            <td><span class="badge-ya"><?= $totalYa ?></span></td>
            <td><span class="badge-tidak"><?= $totalTidak ?></span></td>
            <td><span class="badge-na"><?= $totalNa ?></span></td>
            <td><?= $totalDenum ?></td>
            <td><?= $totalPersen ?>%</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="small-note">
      Persentase dihitung dari Ya / (Ya + Tidak). Jawaban NA tidak masuk denumerator.
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Indikator Penilaian APD</div>
    <div class="table-scroll-x">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Indikator</th>
            <th>Ya</th>
            <th>Tidak</th>
            <th>NA</th>
            <th>Denum</th>
            <th>% Ya</th>
          </tr>
        </thead>
        <?php
        $sumIndYa = 0;
        $sumIndTidak = 0;
        $sumIndNa = 0;
        ?>
        <tbody>
          <?php if (isset($qRekapIndikator) && mysqli_num_rows($qRekapIndikator) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($qRekapIndikator)): ?>
              <?php
              $ya = (int) ($row['ya'] ?? 0);
              $tidak = (int) ($row['tidak'] ?? 0);
              $na = (int) ($row['na'] ?? 0);
              $denum = $ya + $tidak;
              $persen = $denum > 0 ? round(($ya / $denum) * 100, 2) : 0;
              $sumIndYa += $ya;
              $sumIndTidak += $tidak;
              $sumIndNa += $na;
              ?>
              <tr>
                <td><?= htmlspecialchars($row['indikator_label'] ?? $row['indikator_key'] ?? '-') ?></td>
                <td><span class="badge-ya"><?= $ya ?></span></td>
                <td><span class="badge-tidak"><?= $tidak ?></span></td>
                <td><span class="badge-na"><?= $na ?></span></td>
                <td><?= $denum ?></td>
                <td><?= $persen ?>%</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:20px; color:#64748b; font-weight:600;">
                Belum ada data rekap indikator.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <?php
          $sumIndDenum = $sumIndYa + $sumIndTidak;
          $sumIndPersen = $sumIndDenum > 0 ? round(($sumIndYa / $sumIndDenum) * 100, 2) : 0;
          ?>
          <tr>
            <td>Total</td>
            <td><?= $sumIndYa ?></td>
            <td><?= $sumIndTidak ?></td>
            <td><?= $sumIndNa ?></td>
            <td><?= $sumIndDenum ?></td>
            <td><?= $sumIndPersen ?>%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap APD yang Digunakan</div>
    <div class="table-scroll-x">
      <table class="summary-table">
        <thead>
          <tr>
            <th>APD</th>
            <th>Ya</th>
            <th>Tidak</th>
            <th>NA</th>
            <th>Denum</th>
            <th>% Ya</th>
          </tr>
        </thead>
        <?php
        $sumApdYa = 0;
        $sumApdTidak = 0;
        $sumApdNa = 0;
        ?>
        <tbody>
          <?php if (isset($qRekapAPD) && mysqli_num_rows($qRekapAPD) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($qRekapAPD)): ?>
              <?php
              $ya = (int) ($row['ya'] ?? 0);
              $tidak = (int) ($row['tidak'] ?? 0);
              $na = (int) ($row['na'] ?? 0);
              $denum = $ya + $tidak;
              $persen = $denum > 0 ? round(($ya / $denum) * 100, 2) : 0;
              $sumApdYa += $ya;
              $sumApdTidak += $tidak;
              $sumApdNa += $na;
              ?>
              <tr>
                <td><?= htmlspecialchars($row['indikator_label'] ?? $row['indikator_key'] ?? '-') ?></td>
                <td><span class="badge-ya"><?= $ya ?></span></td>
                <td><span class="badge-tidak"><?= $tidak ?></span></td>
                <td><span class="badge-na"><?= $na ?></span></td>
                <td><?= $denum ?></td>
                <td><?= $persen ?>%</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:20px; color:#64748b; font-weight:600;">
                Belum ada data rekap APD.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <?php
          $sumApdDenum = $sumApdYa + $sumApdTidak;
          $sumApdPersen = $sumApdDenum > 0 ? round(($sumApdYa / $sumApdDenum) * 100, 2) : 0;
          ?>
          <tr>
            <td>Total</td>
            <td><?= $sumApdYa ?></td>
            <td><?= $sumApdTidak ?></td>
            <td><?= $sumApdNa ?></td>
            <td><?= $sumApdDenum ?></td>
            <td><?= $sumApdPersen ?>%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Per Profesi</div>
    <div class="table-scroll-x">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Profesi</th>
            <th>Ya</th>
            <th>Tidak</th>
            <th>NA</th>
            <th>Denum</th>
            <th>% Ya</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($qRekapProfesi) && mysqli_num_rows($qRekapProfesi) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($qRekapProfesi)): ?>
              <?php
              $ya = (int) ($row['ya'] ?? 0);
              $tidak = (int) ($row['tidak'] ?? 0);
              $na = (int) ($row['na'] ?? 0);
              $denum = $ya + $tidak;
              $persen = $denum > 0 ? round(($ya / $denum) * 100, 2) : 0;
              ?>
              <tr>
                <td><?= htmlspecialchars($row['label_rekap'] ?? '-') ?></td>
                <td><?= $ya ?></td>
                <td><?= $tidak ?></td>
                <td><?= $na ?></td>
                <td><?= $denum ?></td>
                <td><?= $persen ?>%</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:20px; color:#64748b; font-weight:600;">
                Belum ada data rekap profesi.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Per Unit</div>
    <div class="table-scroll-x">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Unit</th>
            <th>Ya</th>
            <th>Tidak</th>
            <th>NA</th>
            <th>Denum</th>
            <th>% Ya</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($qRekapUnit) && mysqli_num_rows($qRekapUnit) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($qRekapUnit)): ?>
              <?php
              $ya = (int) ($row['ya'] ?? 0);
              $tidak = (int) ($row['tidak'] ?? 0);
              $na = (int) ($row['na'] ?? 0);
              $denum = $ya + $tidak;
              $persen = $denum > 0 ? round(($ya / $denum) * 100, 2) : 0;
              ?>
              <tr>
                <td><?= htmlspecialchars($row['label_rekap'] ?? '-') ?></td>
                <td><?= $ya ?></td>
                <td><?= $tidak ?></td>
                <td><?= $na ?></td>
                <td><?= $denum ?></td>
                <td><?= $persen ?>%</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:20px; color:#64748b; font-weight:600;">
                Belum ada data rekap unit.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
