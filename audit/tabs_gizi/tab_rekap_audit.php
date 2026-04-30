<style>
  #tab-rekap .card-title { margin: 0 0 12px; font-size: 22px; font-weight: 900; }
  #tab-rekap .filter-grid { display:grid; grid-template-columns: 1fr 1fr 1fr 1fr auto auto; gap: 12px; align-items: center; }
  #tab-rekap .stats-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 12px; }
  #tab-rekap .stat-box {
    background: var(--card); border:1px solid var(--line); border-radius: 14px; padding: 12px;
    box-shadow: 0 6px 16px rgba(15,23,42,.05);
  }
  #tab-rekap .stat-label { color:#64748b; font-size:12px; text-transform: uppercase; letter-spacing: .5px; font-weight: 800; }
  #tab-rekap .stat-value { margin-top: 4px; font-size: 24px; font-weight: 900; color: var(--ink); }
  #tab-rekap .table-shell { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; background: var(--card); margin-top: 14px; }
  #tab-rekap .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  #tab-rekap .mobile-list { display:none; }
  #tab-rekap .mobile-item {
    border:1px solid var(--line); border-radius:12px; background:var(--card); padding:12px; margin-bottom:10px;
    box-shadow: 0 6px 12px rgba(15,23,42,.06);
  }
  #tab-rekap .mobile-item h4 { margin:0 0 8px; font-size:16px; }
  #tab-rekap table { width:100%; border-collapse:separate; border-spacing:0 6px; padding: 0 10px 8px; }
  #tab-rekap thead th {
    font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #64748b;
    text-align: left; padding: 8px 10px 6px; font-weight: 900;
    background: linear-gradient(180deg, #eaf2ff, #dbeafe); border-bottom: 1px solid #bfdbfe;
  }
  #tab-rekap td {
    background: var(--card); border-top:1px solid var(--line); border-bottom:1px solid var(--line);
    padding: 12px 10px; vertical-align: middle;
  }
  #tab-rekap td:first-child { border-left:1px solid var(--line); border-top-left-radius: 12px; border-bottom-left-radius: 12px; font-weight: 800; }
  #tab-rekap td:last-child { border-right:1px solid var(--line); border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
  #tab-rekap tbody tr:nth-child(even) td { background: color-mix(in srgb, var(--card) 94%, #e2e8f0 6%); }
  #tab-rekap tbody tr { transition: all .2s ease; }
  #tab-rekap tbody tr:hover { transform: translateY(-2px); }
  #tab-rekap tbody tr:hover td { background: #f8fafc; box-shadow: 0 8px 16px rgba(15,23,42,.08); }
  #tab-rekap .center { text-align: center; }
  #tab-rekap .score-pill {
    display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:5px 10px;
    font-size:12px; font-weight:900; border:1px solid transparent;
  }
  #tab-rekap .score-pill.good { background:#dcfce7; color:#166534; border-color:#86efac; }
  #tab-rekap .score-pill.warn { background:#fef9c3; color:#854d0e; border-color:#fde047; }
  #tab-rekap .score-pill.bad { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
  #tab-rekap .bar-wrap { margin-top: 6px; }
  #tab-rekap .bar { width:100%; height:8px; border-radius:999px; background:#e2e8f0; border:1px solid #cbd5e1; overflow:hidden; }
  #tab-rekap .bar-fill { height:100%; border-radius:999px; background: linear-gradient(135deg, #22c55e, #16a34a); }
  #tab-rekap .bar-fill.warn { background: linear-gradient(135deg, #facc15, #eab308); }
  #tab-rekap .bar-fill.bad { background: linear-gradient(135deg, #f87171, #ef4444); }
  @media (max-width: 900px) {
    #tab-rekap .filter-grid { grid-template-columns: 1fr; }
    #tab-rekap .stats-grid { grid-template-columns: 1fr; }
    #tab-rekap .desktop-table { display:none; }
    #tab-rekap .mobile-list { display:block; }
    #tab-rekap table { min-width: 680px; }
    #tab-rekap td { padding: 10px 8px; }
    #tab-rekap thead th { padding: 7px 8px 6px; font-size: 11px; }
    #tab-rekap .score-pill { font-size: 11px; padding: 4px 8px; }
  }
  body.dark-mode #tab-rekap thead th { color:#cbd5e1; background: linear-gradient(180deg, #1e293b, #0f172a); border-bottom-color:#475569; }
  body.dark-mode #tab-rekap tbody tr:nth-child(even) td { background: color-mix(in srgb, var(--card) 94%, #0f172a 6%); }
  body.dark-mode #tab-rekap tbody tr:hover td { background:#0f172a; box-shadow: 0 8px 16px rgba(2,6,23,.35); }
  #tab-rekap .rekap-matrix-wrap { margin-top: 14px; }
  #tab-rekap .rekap-matrix { min-width: 520px; }
  #tab-rekap .rekap-matrix th.month-col,
  #tab-rekap .rekap-matrix td.month-col {
    text-align: center; white-space: nowrap; font-size: 11px; padding: 8px 6px;
  }
  #tab-rekap .rekap-matrix th.month-col { max-width: 72px; }
  #tab-rekap .rekap-matrix .avg-col { font-weight: 900; background: #f1f5f9 !important; }
  body.dark-mode #tab-rekap .rekap-matrix .avg-col { background: #1e293b !important; }
  #tab-rekap .rekap-matrix .score-pill.matrix-pill { font-size: 10px; padding: 4px 7px; white-space: normal; max-width: 118px; line-height: 1.25; text-align: center; }
  #tab-rekap .rekap-matrix tfoot td {
    background: #e8eef4 !important; border-top: 2px solid #94a3b8; font-weight: 800; vertical-align: middle;
  }
  #tab-rekap .rekap-matrix tfoot td:first-child {
    border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 1px solid var(--line);
  }
  #tab-rekap .rekap-matrix tfoot td:last-child {
    border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-right: 1px solid var(--line);
  }
  body.dark-mode #tab-rekap .rekap-matrix tfoot td { background: #1e293b !important; border-top-color: #64748b; }
  #tab-rekap .matrix-mobile-totals {
    margin-top: 12px; padding: 12px; border-radius: 12px; border: 1px solid var(--line); background: #e8eef4;
  }
  body.dark-mode #tab-rekap .matrix-mobile-totals { background: #1e293b; }
  #tab-rekap .matrix-mobile-totals h4 { margin: 0 0 10px; font-size: 13px; font-weight: 900; color: var(--ink); }
</style>

<?php
$bagianLabels = [
  'D01' => 'Penerimaan Bahan Makanan Mentah',
  'D02' => 'Higiene dan Sanitasi Gudang',
  'D03' => 'Kebersihan Dapur',
  'D04' => 'Tenaga Pengolah',
  'D05' => 'Proses Pengolahan',
  'D06' => 'Cara Pengangkutan Makanan',
  'D07' => 'Penyimpanan Dingin',
  'D08' => 'Cara Penyajian Makanan',
  'D09' => 'Alat Makan'
];
$rekapRows = [];
$totalNum = 0;
$totalDenum = 0;
while ($row = mysqli_fetch_assoc($qRekapBagian)) {
  $num = (int) ($row['num'] ?? 0);
  $den = (int) ($row['denum'] ?? 0);
  $persen = $den > 0 ? round(($num / $den) * 100, 1) : 0;
  $scoreClass = $persen >= 95 ? 'good' : ($persen >= 80 ? 'warn' : 'bad');
  $rekapRows[] = [
    'kode_bagian' => $row['kode_bagian'],
    'num' => $num,
    'denum' => $den,
    'persen' => $persen,
    'score_class' => $scoreClass
  ];
  $totalNum += $num;
  $totalDenum += $den;
}
$overallPct = $totalDenum > 0 ? round(($totalNum / $totalDenum) * 100, 1) : 0;

$namaBulanPendek = [
  1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
  7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];
$kodeUrutan = array_keys($bagianLabels);
sort($kodeUrutan);

$matrixWhere = ["YEAR(a.tanggal_audit) = $rekapTahun"];
if ($rekapPeriode === 'bulanan') {
  $matrixWhere[] = "MONTH(a.tanggal_audit) = $rekapBulan";
} elseif ($rekapPeriode === 'triwulan') {
  $smMatrix = (($rekapTriwulan - 1) * 3) + 1;
  $emMatrix = $smMatrix + 2;
  $matrixWhere[] = "MONTH(a.tanggal_audit) BETWEEN $smMatrix AND $emMatrix";
}
$matrixWhereSql = 'WHERE ' . implode(' AND ', $matrixWhere);

$qMatrix = mysqli_query($conn, "
  SELECT
    d.kode_bagian,
    MONTH(a.tanggal_audit) AS bln,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum
  FROM audit_gizi a
  JOIN audit_gizi_detail d ON a.id = d.audit_id
  $matrixWhereSql
  GROUP BY d.kode_bagian, MONTH(a.tanggal_audit)
");

$matrixData = [];
while ($mr = mysqli_fetch_assoc($qMatrix)) {
  $kb = $mr['kode_bagian'] ?? '';
  $bln = (int) ($mr['bln'] ?? 0);
  if ($kb === '' || $bln < 1 || $bln > 12) {
    continue;
  }
  if (!isset($matrixData[$kb])) {
    $matrixData[$kb] = [];
  }
  $n = (int) ($mr['num'] ?? 0);
  $d = (int) ($mr['denum'] ?? 0);
  $matrixData[$kb][$bln] = [
    'num' => $n,
    'denum' => $d,
    'pct' => $d > 0 ? round(($n / $d) * 100, 1) : null
  ];
}

if ($rekapPeriode === 'tahunan') {
  $colMonths = range(1, 12);
} elseif ($rekapPeriode === 'triwulan') {
  $smCol = (($rekapTriwulan - 1) * 3) + 1;
  $colMonths = [$smCol, $smCol + 1, $smCol + 2];
} else {
  $colMonths = [$rekapBulan];
}

/** Hijau = 100% kepatuhan (num === denum); kuning = selain itu — seperti badge di referensi. */
$matrixPillClassFn = static function (?float $pct, int $num, int $den) {
  if ($den <= 0 || $pct === null) {
    return '';
  }
  return $num === $den ? 'good' : 'warn';
};

$matrixFooterByMonth = [];
foreach ($colMonths as $m) {
  $fn = 0;
  $fd = 0;
  foreach ($kodeUrutan as $kode) {
    $c = $matrixData[$kode][$m] ?? null;
    if ($c) {
      $fn += (int) $c['num'];
      $fd += (int) $c['denum'];
    }
  }
  $matrixFooterByMonth[$m] = [
    'num' => $fn,
    'denum' => $fd,
    'pct' => $fd > 0 ? round(($fn / $fd) * 100, 1) : null
  ];
}
$matrixFooterGrandN = 0;
$matrixFooterGrandD = 0;
foreach ($colMonths as $m) {
  $matrixFooterGrandN += $matrixFooterByMonth[$m]['num'];
  $matrixFooterGrandD += $matrixFooterByMonth[$m]['denum'];
}
$matrixFooterGrandPct = $matrixFooterGrandD > 0 ? round(($matrixFooterGrandN / $matrixFooterGrandD) * 100, 1) : null;
?>

<div id="tab-rekap" class="tab-pane active">
  <div class="section-card">
    <h3 class="card-title">Filter Periode Rekap</h3>
    <form method="get">
      <input type="hidden" name="tab" value="tab-rekap">
      <div class="filter-grid">
        <select name="rekap_periode" class="form-control" id="rekapPeriode">
          <option value="bulanan" <?= $rekapPeriode === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
          <option value="triwulan" <?= $rekapPeriode === 'triwulan' ? 'selected' : '' ?>>Triwulan</option>
          <option value="tahunan" <?= $rekapPeriode === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
        </select>
        <select name="rekap_bulan" class="form-control" id="rekapBulan">
          <?php for ($b = 1; $b <= 12; $b++): ?>
            <option value="<?= $b ?>" <?= (int) $rekapBulan === $b ? 'selected' : '' ?>>Bulan <?= $b ?></option>
          <?php endfor; ?>
        </select>
        <select name="rekap_triwulan" class="form-control" id="rekapTriwulan">
          <?php for ($q = 1; $q <= 4; $q++): ?>
            <option value="<?= $q ?>" <?= (int) $rekapTriwulan === $q ? 'selected' : '' ?>>Triwulan <?= $q ?></option>
          <?php endfor; ?>
        </select>
        <select name="rekap_tahun" class="form-control">
          <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
            <option value="<?= $t ?>" <?= (int) $rekapTahun === (int) $t ? 'selected' : '' ?>>Tahun <?= $t ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a class="btn btn-secondary" href="?tab=tab-rekap">Reset</a>
      </div>
    </form>

    <div class="stats-grid">
      <div class="stat-box">
        <div class="stat-label">Periode</div>
        <div class="stat-value"><?= ucfirst($rekapPeriode) ?></div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Skor Total</div>
        <div class="stat-value"><?= $overallPct ?>%</div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Num / Denum</div>
        <div class="stat-value"><?= $totalNum ?> / <?= $totalDenum ?></div>
      </div>
    </div>
  </div>

  <div class="section-card">
    <h3 class="card-title">Rekap Kepatuhan per Bagian</h3>
    <?php if (count($rekapRows) > 0): ?>
      <div class="table-shell">
        <div class="table-scroll desktop-table">
          <table>
            <thead>
            <tr>
              <th>Bagian</th>
              <th class="center">Skor</th>
              <th class="center">Progress</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rekapRows as $row): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($row['kode_bagian']) ?></strong>
                  <span style="color:#64748b; font-weight:600;"> - <?= htmlspecialchars($bagianLabels[$row['kode_bagian']] ?? 'Bagian Audit') ?></span>
                </td>
                <td class="center">
                  <span class="score-pill <?= $row['score_class'] ?>"><?= $row['persen'] ?>% (<?= $row['num'] ?>/<?= $row['denum'] ?>)</span>
                </td>
                <td class="center">
                  <div class="bar-wrap">
                    <div class="bar">
                      <div class="bar-fill <?= $row['score_class'] === 'good' ? '' : ($row['score_class'] === 'warn' ? 'warn' : 'bad') ?>" style="width: <?= $row['persen'] ?>%;"></div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mobile-list">
          <?php foreach ($rekapRows as $row): ?>
            <div class="mobile-item">
              <h4><?= htmlspecialchars($row['kode_bagian']) ?></h4>
              <div class="center" style="margin-bottom:8px;">
                <span class="score-pill <?= $row['score_class'] ?>"><?= $row['persen'] ?>% (<?= $row['num'] ?>/<?= $row['denum'] ?>)</span>
              </div>
              <div class="bar-wrap">
                <div class="bar">
                  <div class="bar-fill <?= $row['score_class'] === 'good' ? '' : ($row['score_class'] === 'warn' ? 'warn' : 'bad') ?>" style="width: <?= $row['persen'] ?>%;"></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <div style="padding:16px; border:1px dashed var(--line); border-radius:12px; color:#64748b;">
        Tidak ada data rekap untuk periode yang dipilih.
      </div>
    <?php endif; ?>
  </div>

  <div class="section-card">
    <h3 class="card-title">Rekap per Periode (Skor per Bulan)</h3>
    <p style="margin:0 0 12px; color:#64748b; font-size:14px; font-weight:600;">
      <?php if ($rekapPeriode === 'tahunan'): ?>
        Kolom Jan–Des <?= (int) $rekapTahun ?>; kolom terakhir <strong>Rata-rata</strong> = agregat num/denum seluruh bulan pada tahun tersebut per bagian.
      <?php elseif ($rekapPeriode === 'triwulan'): ?>
        Tiga bulan triwulan <?= (int) $rekapTriwulan ?> tahun <?= (int) $rekapTahun ?>; <strong>Rata-rata</strong> = agregat num/denum seluruh bulan triwulan per bagian.
      <?php else: ?>
        Sama seperti rekap bagian di atas untuk bulan terpilih; tanpa progress bar.
      <?php endif; ?>
    </p>

    <?php if ($rekapPeriode === 'bulanan'): ?>
      <?php if (count($rekapRows) > 0): ?>
        <div class="table-shell rekap-matrix-wrap">
          <div class="table-scroll desktop-table">
            <table class="rekap-matrix">
              <thead>
                <tr>
                  <th>Bagian</th>
                  <th class="center">Skor akhir</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rekapRows as $row): ?>
                  <tr>
                    <td>
                      <strong><?= htmlspecialchars($row['kode_bagian']) ?></strong>
                      <span style="color:#64748b; font-weight:600;"> - <?= htmlspecialchars($bagianLabels[$row['kode_bagian']] ?? '') ?></span>
                    </td>
                    <td class="center">
                      <span class="score-pill <?= $row['score_class'] ?>"><?= $row['persen'] ?>% (<?= $row['num'] ?>/<?= $row['denum'] ?>)</span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td>Total / basis periode</td>
                  <td class="center">
                    <?php
                    $bfCls = $matrixPillClassFn($overallPct, $totalNum, $totalDenum);
                    ?>
                    <span class="score-pill matrix-pill <?= $bfCls ?>"><?= $overallPct ?>% (<?= $totalNum ?>/<?= $totalDenum ?>)</span>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="mobile-list">
            <?php foreach ($rekapRows as $row): ?>
              <div class="mobile-item">
                <h4><?= htmlspecialchars($row['kode_bagian']) ?></h4>
                <div style="color:#64748b;font-size:13px;margin-bottom:8px;"><?= htmlspecialchars($bagianLabels[$row['kode_bagian']] ?? '') ?></div>
                <div class="center">
                  <span class="score-pill <?= $row['score_class'] ?>"><?= $row['persen'] ?>% (<?= $row['num'] ?>/<?= $row['denum'] ?>)</span>
                </div>
              </div>
            <?php endforeach; ?>
            <div class="matrix-mobile-totals">
              <h4>Total / basis periode</h4>
              <div class="center">
                <?php $bfClsM = $matrixPillClassFn($overallPct, $totalNum, $totalDenum); ?>
                <span class="score-pill matrix-pill <?= $bfClsM ?>"><?= $overallPct ?>% (<?= $totalNum ?>/<?= $totalDenum ?>)</span>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div style="padding:16px; border:1px dashed var(--line); border-radius:12px; color:#64748b;">Tidak ada data.</div>
      <?php endif; ?>
    <?php else: ?>
      <div class="table-shell rekap-matrix-wrap">
        <div class="table-scroll desktop-table">
          <table class="rekap-matrix">
            <thead>
              <tr>
                <th>Bagian</th>
                <?php foreach ($colMonths as $m): ?>
                  <th class="month-col"><?= htmlspecialchars($namaBulanPendek[$m] ?? (string) $m) ?></th>
                <?php endforeach; ?>
                <th class="center month-col avg-col">Rata-rata</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($kodeUrutan as $kode): ?>
                <?php
                $sumN = 0;
                $sumD = 0;
                foreach ($colMonths as $m) {
                  $cell = $matrixData[$kode][$m] ?? null;
                  if ($cell && (int) $cell['denum'] > 0) {
                    $sumN += (int) $cell['num'];
                    $sumD += (int) $cell['denum'];
                  }
                }
                $avgPct = $sumD > 0 ? round(($sumN / $sumD) * 100, 1) : null;
                $avgClass = $matrixPillClassFn($avgPct, $sumN, $sumD);
                ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($kode) ?></strong>
                    <span style="color:#64748b; font-weight:600;"> - <?= htmlspecialchars($bagianLabels[$kode] ?? '') ?></span>
                  </td>
                  <?php foreach ($colMonths as $m): ?>
                    <?php
                    $cell = $matrixData[$kode][$m] ?? null;
                    $pct = $cell['pct'] ?? null;
                    $cn = (int) ($cell['num'] ?? 0);
                    $cd = (int) ($cell['denum'] ?? 0);
                    $cls = $matrixPillClassFn($pct, $cn, $cd);
                    ?>
                    <td class="month-col">
                      <?php if ($pct === null): ?>
                        <span style="color:#94a3b8;">—</span>
                      <?php else: ?>
                        <span class="score-pill matrix-pill <?= $cls ?>"><?= $pct ?>% (<?= $cn ?>/<?= $cd ?>)</span>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="center month-col avg-col">
                    <?php if ($avgPct === null): ?>
                      <span style="color:#94a3b8;">—</span>
                    <?php else: ?>
                      <span class="score-pill matrix-pill <?= $avgClass ?>"><?= $avgPct ?>% (<?= $sumN ?>/<?= $sumD ?>)</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td>Total / basis periode</td>
                <?php foreach ($colMonths as $m): ?>
                  <?php
                  $ft = $matrixFooterByMonth[$m] ?? ['num' => 0, 'denum' => 0, 'pct' => null];
                  $fp = $ft['pct'] ?? null;
                  $fn = (int) ($ft['num'] ?? 0);
                  $fd = (int) ($ft['denum'] ?? 0);
                  $fcls = $matrixPillClassFn($fp, $fn, $fd);
                  ?>
                  <td class="month-col">
                    <?php if ($fp === null || $fd <= 0): ?>
                      <span style="color:#94a3b8;">—</span>
                    <?php else: ?>
                      <span class="score-pill matrix-pill <?= $fcls ?>"><?= $fp ?>% (<?= $fn ?>/<?= $fd ?>)</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="center month-col avg-col">
                  <?php if ($matrixFooterGrandPct === null || $matrixFooterGrandD <= 0): ?>
                    <span style="color:#94a3b8;">—</span>
                  <?php else: ?>
                    <?php $gcls = $matrixPillClassFn($matrixFooterGrandPct, $matrixFooterGrandN, $matrixFooterGrandD); ?>
                    <span class="score-pill matrix-pill <?= $gcls ?>"><?= $matrixFooterGrandPct ?>% (<?= $matrixFooterGrandN ?>/<?= $matrixFooterGrandD ?>)</span>
                  <?php endif; ?>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="mobile-list">
          <?php foreach ($kodeUrutan as $kode): ?>
            <?php
            $sumN = 0;
            $sumD = 0;
            foreach ($colMonths as $m) {
              $cell = $matrixData[$kode][$m] ?? null;
              if ($cell && (int) $cell['denum'] > 0) {
                $sumN += (int) $cell['num'];
                $sumD += (int) $cell['denum'];
              }
            }
            $avgPct = $sumD > 0 ? round(($sumN / $sumD) * 100, 1) : null;
            $avgClass = $matrixPillClassFn($avgPct, $sumN, $sumD);
            ?>
            <div class="mobile-item">
              <h4><?= htmlspecialchars($kode) ?></h4>
              <div style="color:#64748b;font-size:13px;margin-bottom:8px;"><?= htmlspecialchars($bagianLabels[$kode] ?? '') ?></div>
              <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(76px, 1fr)); gap:8px;">
                <?php foreach ($colMonths as $m): ?>
                  <?php
                  $cell = $matrixData[$kode][$m] ?? null;
                  $pct = $cell['pct'] ?? null;
                  $cn = (int) ($cell['num'] ?? 0);
                  $cd = (int) ($cell['denum'] ?? 0);
                  $cls = $matrixPillClassFn($pct, $cn, $cd);
                  ?>
                  <div>
                    <div style="font-size:11px;color:#64748b;font-weight:800;margin-bottom:4px;"><?= htmlspecialchars($namaBulanPendek[$m] ?? '') ?></div>
                    <?php if ($pct === null): ?>
                      <span style="color:#94a3b8;">—</span>
                    <?php else: ?>
                      <span class="score-pill matrix-pill <?= $cls ?>"><?= $pct ?>% (<?= $cn ?>/<?= $cd ?>)</span>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <div style="margin-top:10px; text-align:center;">
                <span style="font-size:11px;color:#64748b;font-weight:800;">Rata-rata</span><br>
                <?php if ($avgPct === null): ?>
                  <span style="color:#94a3b8;">—</span>
                <?php else: ?>
                  <span class="score-pill matrix-pill <?= $avgClass ?>"><?= $avgPct ?>% (<?= $sumN ?>/<?= $sumD ?>)</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="matrix-mobile-totals">
            <h4>Total / basis periode</h4>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(76px, 1fr)); gap:8px;">
              <?php foreach ($colMonths as $m): ?>
                <?php
                $ft = $matrixFooterByMonth[$m] ?? ['num' => 0, 'denum' => 0, 'pct' => null];
                $fp = $ft['pct'] ?? null;
                $fn = (int) ($ft['num'] ?? 0);
                $fd = (int) ($ft['denum'] ?? 0);
                $fcls = $matrixPillClassFn($fp, $fn, $fd);
                ?>
                <div>
                  <div style="font-size:11px;color:#64748b;font-weight:800;margin-bottom:4px;"><?= htmlspecialchars($namaBulanPendek[$m] ?? '') ?></div>
                  <?php if ($fp === null || $fd <= 0): ?>
                    <span style="color:#94a3b8;">—</span>
                  <?php else: ?>
                    <span class="score-pill matrix-pill <?= $fcls ?>"><?= $fp ?>% (<?= $fn ?>/<?= $fd ?>)</span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <div style="margin-top:10px; text-align:center;">
              <span style="font-size:11px;color:#64748b;font-weight:800;">Gabungan kolom periode</span><br>
              <?php if ($matrixFooterGrandPct === null || $matrixFooterGrandD <= 0): ?>
                <span style="color:#94a3b8;">—</span>
              <?php else: ?>
                <?php $mgCls = $matrixPillClassFn($matrixFooterGrandPct, $matrixFooterGrandN, $matrixFooterGrandD); ?>
                <span class="score-pill matrix-pill <?= $mgCls ?>"><?= $matrixFooterGrandPct ?>% (<?= $matrixFooterGrandN ?>/<?= $matrixFooterGrandD ?>)</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  (function () {
    const periode = document.getElementById('rekapPeriode');
    const bulan = document.getElementById('rekapBulan');
    const triwulan = document.getElementById('rekapTriwulan');
    if (!periode || !bulan || !triwulan) return;

    function syncFields() {
      const v = periode.value;
      bulan.disabled = v !== 'bulanan';
      triwulan.disabled = v !== 'triwulan';
      bulan.style.opacity = bulan.disabled ? '0.6' : '1';
      triwulan.style.opacity = triwulan.disabled ? '0.6' : '1';
    }

    periode.addEventListener('change', syncFields);
    syncFields();
  })();
</script>
