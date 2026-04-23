<div id="tab-rekap" class="tab-pane active">
  <div class="section-card">
    <div class="section-title">Filter Rekap</div>

    <form method="get">
      <input type="hidden" name="tab" value="tab-rekap">

      <div class="filter-row">
        <input type="date" name="tgl_awal" class="form-control" value="<?= htmlspecialchars($filter_tgl_awal) ?>">
        <input type="date" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($filter_tgl_akhir) ?>">

        <select name="bulan" class="form-control">
          <option value="">Semua Bulan</option>
          <?php for ($b = 1; $b <= 12; $b++): ?>
            <option value="<?= $b ?>" <?= (string) $filter_bulan === (string) $b ? 'selected' : '' ?>>
              <?= $b ?>
            </option>
          <?php endfor; ?>
        </select>

        <select name="tahun" class="form-control">
          <option value="">Semua Tahun</option>
          <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
            <option value="<?= $t ?>" <?= (string) $filter_tahun === (string) $t ? 'selected' : '' ?>>
              <?= $t ?>
            </option>
          <?php endfor; ?>
        </select>

        <select name="f_profesi" class="form-control">
          <option value="">Semua Profesi</option>
          <?php foreach ($profesiList as $item): ?>
            <option value="<?= htmlspecialchars($item) ?>" <?= $filter_profesi === $item ? 'selected' : '' ?>>
              <?= htmlspecialchars($item) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="f_ruangan" class="form-control">
          <option value="">Semua Unit</option>
          <?php foreach ($ruanganList as $item): ?>
            <option value="<?= htmlspecialchars($item) ?>" <?= $filter_ruangan === $item ? 'selected' : '' ?>>
              <?= htmlspecialchars($item) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-row">
        <select name="f_moment" class="form-control">
          <option value="">Semua Moment</option>
          <?php foreach ($moments as $key => $label): ?>
            <option value="<?= htmlspecialchars($key) ?>" <?= $filter_moment === $key ? 'selected' : '' ?>>
              <?= htmlspecialchars($label) ?>
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
    <div class="section-title">Angka Kepatuhan Rumah Sakit</div>
    <div class="table-responsive">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Indikator</th>
            <th>Num</th>
            <th>Denum</th>
            <th>%</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Kepatuhan Rumah Sakit</td>
            <td><?= (int) ($kepatuhanRS['num'] ?? 0) ?></td>
            <td><?= (int) ($kepatuhanRS['denum'] ?? 0) ?></td>
            <td><?= (float) ($kepatuhanRS['persen'] ?? 0) ?>%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Per Moment</div>
    <div class="table-responsive">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Moment</th>
            <th>Num</th>
            <th>Denum</th>
            <th>%</th>
          </tr>
        </thead>
        <?php
          $total_num_moment = 0;
          $total_denum_moment = 0;
        ?>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($qRekapMoment)): ?>
            <?php
              $num = (int) $row['num'];
              $denum = (int) $row['denum'];
              $total_num_moment += $num;
              $total_denum_moment += $denum;
            ?>
            <tr>
              <td><?= htmlspecialchars($moments[$row['label_rekap']] ?? strtoupper($row['label_rekap'])) ?></td>
              <td><?= $num ?></td>
              <td><?= $denum ?></td>
              <td><?= (float) $row['persen'] ?>%</td>
            </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr style="font-weight:bold; background:#f1f5f9;">
            <td>Total</td>
            <td><?= $total_num_moment ?></td>
            <td><?= $total_denum_moment ?></td>
            <td><?= $total_denum_moment > 0 ? round(($total_num_moment / $total_denum_moment) * 100, 2) : 0 ?>%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Per Profesi</div>
    <div class="table-responsive">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Profesi</th>
            <th>Num</th>
            <th>Denum</th>
            <th>%</th>
          </tr>
        </thead>
        <?php
          $total_num_profesi = 0;
          $total_denum_profesi = 0;
        ?>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($qRekapProfesi)): ?>
            <?php
              $num = (int) $row['num'];
              $denum = (int) $row['denum'];
              $total_num_profesi += $num;
              $total_denum_profesi += $denum;
            ?>
            <tr>
              <td><?= htmlspecialchars($row['label_rekap']) ?></td>
              <td><?= $num ?></td>
              <td><?= $denum ?></td>
              <td><?= (float) $row['persen'] ?>%</td>
            </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr style="font-weight:bold; background:#f1f5f9;">
            <td>Total</td>
            <td><?= $total_num_profesi ?></td>
            <td><?= $total_denum_profesi ?></td>
            <td><?= $total_denum_profesi > 0 ? round(($total_num_profesi / $total_denum_profesi) * 100, 2) : 0 ?>%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Per Unit</div>
    <div class="table-responsive">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Unit</th>
            <th>Num</th>
            <th>Denum</th>
            <th>%</th>
          </tr>
        </thead>
        <?php
          $total_num_unit = 0;
          $total_denum_unit = 0;
        ?>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($qRekapUnit)): ?>
            <?php
              $num = (int) $row['num'];
              $denum = (int) $row['denum'];
              $total_num_unit += $num;
              $total_denum_unit += $denum;
            ?>
            <tr>
              <td><?= htmlspecialchars($row['label_rekap']) ?></td>
              <td><?= $num ?></td>
              <td><?= $denum ?></td>
              <td><?= (float) $row['persen'] ?>%</td>
            </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr style="font-weight:bold; background:#f1f5f9;">
            <td>Total</td>
            <td><?= $total_num_unit ?></td>
            <td><?= $total_denum_unit ?></td>
            <td><?= $total_denum_unit > 0 ? round(($total_num_unit / $total_denum_unit) * 100, 2) : 0 ?>%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Berdasarkan Penggunaan Antiseptik</div>
    <div class="table-responsive">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Antiseptik</th>
            <th>Num</th>
            <th>Denum</th>
            <th>%</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rekapAntiseptikRows as $row): ?>
            <?php
              $persen = $denumAntiseptik > 0
                ? round(($row['num'] / $denumAntiseptik) * 100, 2)
                : 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($row['label_rekap']) ?></td>
              <td><?= $row['num'] ?></td>
              <td><?= $denumAntiseptik ?></td>
              <td><?= $persen ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Rekap Berdasarkan Cara Melakukan HH</div>
    <div class="table-responsive">
      <table class="summary-table">
        <thead>
          <tr>
            <th>Cara HH</th>
            <th>Num</th>
            <th>Denum</th>
            <th>%</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rekapCaraHHRows as $row): ?>
            <?php
              $persen = $denumCaraHH > 0
                ? round(($row['num'] / $denumCaraHH) * 100, 2)
                : 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($row['label_rekap']) ?></td>
              <td><?= $row['num'] ?></td>
              <td><?= $denumCaraHH ?></td>
              <td><?= $persen ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>