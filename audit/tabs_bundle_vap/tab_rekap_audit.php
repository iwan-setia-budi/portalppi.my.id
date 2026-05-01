<style>
  #tab-rekap .card-title { margin: 0 0 12px; font-size: 20px; font-weight: 900; letter-spacing: -0.2px; }
  #tab-rekap .filter-grid { display:grid; grid-template-columns: 1fr 1fr 1fr 1fr auto auto; gap: 12px; align-items: center; }
  #tab-rekap .stats-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 12px; }
  #tab-rekap .stat-box {
    background: var(--card); border:1px solid var(--line); border-radius: 14px; padding: 12px;
    box-shadow: 0 6px 15px rgba(15,23,42,.05);
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
  #tab-rekap .mobile-item h4 { margin:0 0 8px; font-size:19px; font-weight: 800; }
  #tab-rekap .subheading-level-2 { font-size: 19px; font-weight: 800; }
  #tab-rekap .subheading-level-3 { font-size: 18px; font-weight: 700; }
  #tab-rekap .subheading-level-4 { font-size: 17px; font-weight: 700; }
  #tab-rekap .subheading-note { margin:0 0 12px; color:#64748b; font-size:18px; font-weight:600; line-height:1.45; }
  #tab-rekap table { width:100%; border-collapse:separate; border-spacing:0 2px; padding: 0 6px 4px; }
  #tab-rekap thead th {
    font-size: 12px; text-transform: uppercase; letter-spacing: .35px; color: #64748b;
    text-align: left; padding: 5px 6px 4px; font-weight: 900;
    background: linear-gradient(180deg, #eaf2ff, #dbeafe); border-bottom: 1px solid #bfdbfe;
  }
  #tab-rekap td {
    background: var(--card); border-top:1px solid var(--line); border-bottom:1px solid var(--line);
    padding: 6px 6px; vertical-align: middle;
  }
  #tab-rekap td:first-child { border-left:1px solid var(--line); border-top-left-radius: 12px; border-bottom-left-radius: 12px; font-weight: 800; }
  #tab-rekap td:last-child { border-right:1px solid var(--line); border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
  #tab-rekap tbody tr:nth-child(even) td { background: color-mix(in srgb, var(--card) 94%, #e2e8f0 6%); }
  #tab-rekap tbody tr { transition: all .2s ease; }
  #tab-rekap tbody tr:hover { transform: translateY(-2px); }
  #tab-rekap tbody tr:hover td { background: #f8fafc; box-shadow: 0 8px 15px rgba(15,23,42,.08); }
  #tab-rekap .center { text-align: center; }
  #tab-rekap .score-pill {
    display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:4px 9px;
    font-size:12px; font-weight:900; border:1px solid transparent;
  }
  #tab-rekap .score-pill.good { background:#dcfce7; color:#166534; border-color:#86efac; }
  #tab-rekap .score-pill.warn { background:#fef9c3; color:#854d0e; border-color:#fde047; }
  #tab-rekap .score-pill.bad { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
  #tab-rekap .bar-wrap { margin-top: 4px; }
  #tab-rekap .bar { width:100%; height:6px; border-radius:999px; background:#e2e8f0; border:1px solid #cbd5e1; overflow:hidden; }
  #tab-rekap .bar-fill { height:100%; border-radius:999px; background: linear-gradient(135deg, #22c55e, #16a34a); }
  #tab-rekap .bar-fill.warn { background: linear-gradient(135deg, #facc15, #eab308); }
  #tab-rekap .bar-fill.bad { background: linear-gradient(135deg, #f87171, #ef4444); }
  @media (max-width: 900px) {
    #tab-rekap .filter-grid { grid-template-columns: 1fr; }
    #tab-rekap .stats-grid { grid-template-columns: 1fr; }
    #tab-rekap .desktop-table { display:none; }
    #tab-rekap .mobile-list { display:block; }
    #tab-rekap table { min-width: 600px; }
    #tab-rekap td { padding: 6px 5px; }
    #tab-rekap thead th { padding: 5px 5px 4px; font-size: 11px; }
    #tab-rekap .score-pill { font-size: 12px; padding: 4px 8px; }
  }
  body.dark-mode #tab-rekap thead th { color:#cbd5e1; background: linear-gradient(180deg, #1e293b, #0f172a); border-bottom-color:#475569; }
  body.dark-mode #tab-rekap tbody tr:nth-child(even) td { background: color-mix(in srgb, var(--card) 94%, #0f172a 6%); }
  body.dark-mode #tab-rekap tbody tr:hover td { background:#0f172a; box-shadow: 0 8px 15px rgba(2,6,23,.35); }
  #tab-rekap .rekap-matrix-wrap { margin-top: 14px; }
  #tab-rekap .rekap-matrix { min-width: 460px; }
  #tab-rekap .rekap-matrix th.month-col,
  #tab-rekap .rekap-matrix td.month-col {
    text-align: center; white-space: nowrap; font-size: 11px; padding: 5px 5px;
  }
  #tab-rekap .rekap-matrix th.month-col { max-width: 56px; }
  #tab-rekap .rekap-matrix .avg-col { font-weight: 900; background: #f1f5f9 !important; }
  body.dark-mode #tab-rekap .rekap-matrix .avg-col { background: #1e293b !important; }
  #tab-rekap .rekap-matrix .score-pill.matrix-pill { font-size: 11px; padding: 3px 6px; white-space: normal; max-width: 104px; line-height: 1.2; text-align: center; }
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
  #tab-rekap .matrix-mobile-totals h4 { margin: 0 0 10px; font-size: 18px; font-weight: 800; color: var(--ink); }
  #tab-rekap .download-toolbar { margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 10px; }
  #tab-rekap .btn-download-image {
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: #fff;
    border: 1px solid rgba(99, 102, 241, 0.6);
    border-radius: 12px;
    padding: 10px 14px;
    font-weight: 800;
    text-decoration: none;
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.28);
    transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
  }
  #tab-rekap .btn-download-image:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 22px rgba(76, 29, 149, 0.28);
    filter: brightness(1.04);
  }
  #tab-rekap .btn-download-image:active {
    transform: translateY(0);
    box-shadow: 0 5px 12px rgba(30, 64, 175, 0.25);
  }
  body.dark-mode #tab-rekap .btn-download-image {
    border-color: rgba(129, 140, 248, 0.7);
    box-shadow: 0 10px 20px rgba(30, 58, 138, 0.45);
  }
</style>

<?php
$bagianLabels = [
  'W' => 'Bundle VAP',
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
  FROM audit_bundle_vap a
  JOIN detail_audit_bundle_vap d ON a.id = d.audit_id
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

  <div class="section-card" id="rekap-bagian-card">
    <h3 class="card-title">Rekap Kepatuhan per Bagian</h3>
    <div class="download-toolbar">
      <a class="btn-download-image js-download-image" href="#" data-target-id="rekap-bagian-card" data-file-prefix="rekap_bagian_bundle_vap">Download Gambar Rekap Bagian</a>
    </div>
    <?php if (count($rekapRows) > 0): ?>
      <div class="table-shell">
        <div class="table-scroll desktop-table">
          <table>
            <thead>
            <tr>
              <th>Kode</th>
              <th>Bagian</th>
              <th class="center">Skor</th>
              <th class="center">Progress</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rekapRows as $row): ?>
              <tr>
                <td><strong><?= htmlspecialchars($row['kode_bagian']) ?></strong></td>
                <td>
                  <?= htmlspecialchars($bagianLabels[$row['kode_bagian']] ?? 'Bagian Audit') ?>
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
              <h4 class="subheading-level-2"><?= htmlspecialchars($row['kode_bagian']) ?></h4>
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
      <div style="padding:15px; border:1px dashed var(--line); border-radius:12px; color:#64748b;">
        Tidak ada data rekap untuk periode yang dipilih.
      </div>
    <?php endif; ?>
  </div>

  <div class="section-card" id="rekap-periode-card">
    <h3 class="card-title">Rekap per Periode (Skor per Bulan)</h3>
    <div class="download-toolbar">
      <a class="btn-download-image js-download-image" href="#" data-target-id="rekap-periode-card" data-file-prefix="rekap_periode_bundle_vap">Download Gambar Rekap Periode</a>
    </div>
    <p class="subheading-note">
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
                  <th>Kode</th>
                  <th>Bagian</th>
                  <th class="center">Skor akhir</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rekapRows as $row): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($row['kode_bagian']) ?></strong></td>
                    <td>
                      <?= htmlspecialchars($bagianLabels[$row['kode_bagian']] ?? '') ?>
                    </td>
                    <td class="center">
                      <span class="score-pill <?= $row['score_class'] ?>"><?= $row['persen'] ?>% (<?= $row['num'] ?>/<?= $row['denum'] ?>)</span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2">Total / basis periode</td>
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
                <h4 class="subheading-level-2"><?= htmlspecialchars($row['kode_bagian']) ?></h4>
                <div class="subheading-level-4" style="color:#64748b;margin-bottom:8px;"><?= htmlspecialchars($bagianLabels[$row['kode_bagian']] ?? '') ?></div>
                <div class="center">
                  <span class="score-pill <?= $row['score_class'] ?>"><?= $row['persen'] ?>% (<?= $row['num'] ?>/<?= $row['denum'] ?>)</span>
                </div>
              </div>
            <?php endforeach; ?>
            <div class="matrix-mobile-totals">
              <h4 class="subheading-level-3">Total / basis periode</h4>
              <div class="center">
                <?php $bfClsM = $matrixPillClassFn($overallPct, $totalNum, $totalDenum); ?>
                <span class="score-pill matrix-pill <?= $bfClsM ?>"><?= $overallPct ?>% (<?= $totalNum ?>/<?= $totalDenum ?>)</span>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div style="padding:15px; border:1px dashed var(--line); border-radius:12px; color:#64748b;">Tidak ada data.</div>
      <?php endif; ?>
    <?php else: ?>
      <div class="table-shell rekap-matrix-wrap">
        <div class="table-scroll desktop-table">
          <table class="rekap-matrix">
            <thead>
              <tr>
                <th>Kode</th>
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
                  <td><strong><?= htmlspecialchars($kode) ?></strong></td>
                  <td>
                    <?= htmlspecialchars($bagianLabels[$kode] ?? '') ?>
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
                <td colspan="2">Total / basis periode</td>
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
              <h4 class="subheading-level-2"><?= htmlspecialchars($kode) ?></h4>
              <div class="subheading-level-4" style="color:#64748b;margin-bottom:8px;"><?= htmlspecialchars($bagianLabels[$kode] ?? '') ?></div>
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
                    <div class="subheading-level-4" style="color:#64748b;font-weight:800;margin-bottom:4px;"><?= htmlspecialchars($namaBulanPendek[$m] ?? '') ?></div>
                    <?php if ($pct === null): ?>
                      <span style="color:#94a3b8;">—</span>
                    <?php else: ?>
                      <span class="score-pill matrix-pill <?= $cls ?>"><?= $pct ?>% (<?= $cn ?>/<?= $cd ?>)</span>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <div style="margin-top:10px; text-align:center;">
                <span class="subheading-level-4" style="color:#64748b;font-weight:800;">Rata-rata</span><br>
                <?php if ($avgPct === null): ?>
                  <span style="color:#94a3b8;">—</span>
                <?php else: ?>
                  <span class="score-pill matrix-pill <?= $avgClass ?>"><?= $avgPct ?>% (<?= $sumN ?>/<?= $sumD ?>)</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="matrix-mobile-totals">
            <h4 class="subheading-level-3">Total / basis periode</h4>
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
                  <div class="subheading-level-4" style="color:#64748b;font-weight:800;margin-bottom:4px;"><?= htmlspecialchars($namaBulanPendek[$m] ?? '') ?></div>
                  <?php if ($fp === null || $fd <= 0): ?>
                    <span style="color:#94a3b8;">—</span>
                  <?php else: ?>
                    <span class="score-pill matrix-pill <?= $fcls ?>"><?= $fp ?>% (<?= $fn ?>/<?= $fd ?>)</span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <div style="margin-top:10px; text-align:center;">
              <span class="subheading-level-4" style="color:#64748b;font-weight:800;">Gabungan kolom periode</span><br>
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

    function buildPeriodSuffix() {
      const tahun = "<?= (int) $rekapTahun ?>";
      const p = "<?= htmlspecialchars($rekapPeriode, ENT_QUOTES, 'UTF-8') ?>";
      if (p === 'bulanan') {
        const b = String(<?= (int) $rekapBulan ?>).padStart(2, '0');
        return p + '_' + tahun + '_b' + b;
      }
      if (p === 'triwulan') {
        return p + '_' + tahun + '_tw' + <?= (int) $rekapTriwulan ?>;
      }
      return p + '_' + tahun;
    }

    function inlineComputedStyles(root) {
      const all = [root].concat(Array.from(root.querySelectorAll('*')));
      all.forEach(function (el) {
        const computed = window.getComputedStyle(el);
        let styleText = '';
        for (let i = 0; i < computed.length; i++) {
          const prop = computed[i];
          styleText += prop + ':' + computed.getPropertyValue(prop) + ';';
        }
        el.setAttribute('style', styleText);
      });
    }

    async function renderElementToCanvasOffline(target) {
      const clone = target.cloneNode(true);
      inlineComputedStyles(clone);

      const rect = target.getBoundingClientRect();
      const width = Math.max(1, Math.ceil(rect.width));
      const height = Math.max(1, Math.ceil(rect.height));
      const scale = 2;

      const svgMarkup =
        '<svg xmlns="http://www.w3.org/2000/svg" width="' + (width * scale) + '" height="' + (height * scale) + '">' +
          '<foreignObject x="0" y="0" width="100%" height="100%">' +
            '<div xmlns="http://www.w3.org/1999/xhtml" style="transform:scale(' + scale + ');transform-origin:top left;width:' + width + 'px;height:' + height + 'px;background:#ffffff;">' +
              clone.outerHTML +
            '</div>' +
          '</foreignObject>' +
        '</svg>';

      const blob = new Blob([svgMarkup], { type: 'image/svg+xml;charset=utf-8' });
      const url = URL.createObjectURL(blob);
      try {
        const img = await new Promise(function (resolve, reject) {
          const image = new Image();
          image.onload = function () { resolve(image); };
          image.onerror = function () { reject(new Error('svg-render-failed')); };
          image.src = url;
        });

        const canvas = document.createElement('canvas');
        canvas.width = width * scale;
        canvas.height = height * scale;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        return canvas;
      } finally {
        URL.revokeObjectURL(url);
      }
    }

    function renderTableDataToCanvas(target) {
      const titleEl = target.querySelector('.card-title');
      const tableEl = target.querySelector('.desktop-table table') || target.querySelector('table');
      const title = titleEl ? titleEl.textContent.trim() : 'Rekap Audit Bundle VAP';
      if (!tableEl) {
        throw new Error('table-not-found');
      }

      const headers = Array.from(tableEl.querySelectorAll('thead th')).map(function (th) {
        return (th.textContent || '').trim();
      });
      const bodyRows = Array.from(tableEl.querySelectorAll('tbody tr')).map(function (tr) {
        return Array.from(tr.querySelectorAll('td')).map(function (td) {
          return (td.textContent || '').replace(/\s+/g, ' ').trim();
        });
      }).filter(function (row) { return row.length > 0; });

      const cols = Math.max(headers.length, bodyRows[0] ? bodyRows[0].length : 0, 1);
      const measureCanvas = document.createElement('canvas');
      const measureCtx = measureCanvas.getContext('2d');
      if (!measureCtx) {
        throw new Error('canvas-context-not-available');
      }
      measureCtx.font = '12px Arial, sans-serif';

      const colWidths = [];
      for (let i = 0; i < cols; i++) {
        let longest = (headers[i] || '').length;
        for (let r = 0; r < bodyRows.length; r++) {
          const val = (bodyRows[r][i] || '');
          if (val.length > longest) longest = val.length;
        }
        const sample = (headers[i] || 'Kolom ' + (i + 1)).slice(0, 28).padEnd(Math.min(longest, 28), 'W');
        const measured = Math.ceil(measureCtx.measureText(sample).width) + 20;
        if (i === 0) {
          colWidths.push(Math.max(240, Math.min(340, measured)));
        } else {
          colWidths.push(Math.max(90, Math.min(150, measured)));
        }
      }

      const paddingX = 14;
      const titleH = 42;
      const rowH = 28;
      const headerH = 30;
      const width = colWidths.reduce(function (a, b) { return a + b; }, 0) + (paddingX * 2);
      const height = titleH + headerH + (bodyRows.length * rowH) + 24;

      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext('2d');

      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, width, height);

      ctx.fillStyle = '#1e293b';
      ctx.font = 'bold 20px Arial, sans-serif';
      ctx.fillText(title, paddingX, 29);

      let y = titleH;
      let x = paddingX;
      ctx.fillStyle = '#e2e8f0';
      ctx.fillRect(paddingX, y, width - (paddingX * 2), headerH);
      ctx.strokeStyle = '#cbd5e1';
      ctx.lineWidth = 1;
      ctx.strokeRect(paddingX, y, width - (paddingX * 2), headerH);

      ctx.fillStyle = '#334155';
      ctx.font = 'bold 12px Arial, sans-serif';
      for (let i = 0; i < cols; i++) {
        const headerText = headers[i] || ('Kolom ' + (i + 1));
        ctx.fillText(headerText.slice(0, 22), x + 6, y + 20);
        x += colWidths[i];
      }

      y += headerH;
      ctx.font = '11px Arial, sans-serif';
      for (let r = 0; r < bodyRows.length; r++) {
        const row = bodyRows[r];
        x = paddingX;
        ctx.fillStyle = r % 2 === 0 ? '#ffffff' : '#f8fafc';
        ctx.fillRect(paddingX, y, width - (paddingX * 2), rowH);
        ctx.strokeStyle = '#e2e8f0';
        ctx.strokeRect(paddingX, y, width - (paddingX * 2), rowH);

        ctx.fillStyle = '#0f172a';
        for (let c = 0; c < cols; c++) {
          const text = row[c] || '';
          const maxChars = c === 0 ? 40 : 20;
          ctx.fillText(text.slice(0, maxChars), x + 6, y + 19);
          x += colWidths[c];
        }
        y += rowH;
      }

      return canvas;
    }

    function renderBagianListToCanvas(target) {
      const titleEl = target.querySelector('.card-title');
      const title = titleEl ? titleEl.textContent.trim() : 'Rekap Kepatuhan per Bagian';

      let rows = Array.from(target.querySelectorAll('.desktop-table tbody tr td:first-child')).map(function (td) {
        return (td.textContent || '').replace(/\s+/g, ' ').trim();
      }).filter(Boolean);

      if (!rows.length) {
        rows = Array.from(target.querySelectorAll('.mobile-list .mobile-item h4')).map(function (el) {
          return (el.textContent || '').replace(/\s+/g, ' ').trim();
        }).filter(Boolean);
      }
      if (!rows.length) {
        throw new Error('bagian-rows-not-found');
      }

      const paddingX = 16;
      const titleH = 42;
      const rowH = 30;
      const headerH = 28;
      const width = 760;
      const height = titleH + headerH + (rows.length * rowH) + 20;

      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext('2d');
      if (!ctx) {
        throw new Error('canvas-context-not-available');
      }

      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, width, height);

      ctx.fillStyle = '#0f172a';
      ctx.font = 'bold 24px Arial, sans-serif';
      ctx.fillText(title, paddingX, 30);

      let y = titleH;
      ctx.fillStyle = '#e2e8f0';
      ctx.fillRect(paddingX, y, width - (paddingX * 2), headerH);
      ctx.strokeStyle = '#cbd5e1';
      ctx.strokeRect(paddingX, y, width - (paddingX * 2), headerH);
      ctx.fillStyle = '#334155';
      ctx.font = 'bold 13px Arial, sans-serif';
      ctx.fillText('BAGIAN', paddingX + 8, y + 19);

      y += headerH;
      ctx.font = 'bold 11px Arial, sans-serif';
      rows.forEach(function (row, idx) {
        ctx.fillStyle = idx % 2 === 0 ? '#ffffff' : '#f8fafc';
        ctx.fillRect(paddingX, y, width - (paddingX * 2), rowH);
        ctx.strokeStyle = '#e2e8f0';
        ctx.strokeRect(paddingX, y, width - (paddingX * 2), rowH);
        ctx.fillStyle = '#1f2937';
        ctx.fillText(row.slice(0, 80), paddingX + 8, y + 20);
        y += rowH;
      });

      return canvas;
    }

    async function downloadSectionAsImage(targetId, filePrefix) {
      const target = document.getElementById(targetId);
      if (!target) {
        alert('Bagian rekap tidak ditemukan.');
        return;
      }
      try {
        let canvas;
        // Khusus rekap bagian: langsung pakai renderer tabel/canvas (paling stabil).
        if (targetId === 'rekap-bagian-card') {
          try {
            canvas = renderTableDataToCanvas(target);
          } catch (_errRenderTable) {
            canvas = renderBagianListToCanvas(target);
          }
        } else {
          try {
            if (typeof window.html2canvas === 'function') {
              canvas = await window.html2canvas(target, {
                scale: 2,
                useCORS: true,
                backgroundColor: getComputedStyle(document.body).backgroundColor || '#ffffff'
              });
            } else {
              canvas = await renderElementToCanvasOffline(target);
            }
          } catch (_errRenderDom) {
            // Fallback paling stabil: gambar ulang dari data tabel ke canvas murni.
            canvas = renderTableDataToCanvas(target);
          }
        }
        if (!canvas) {
          canvas = await renderElementToCanvasOffline(target);
        }
        let dataUrl;
        try {
          dataUrl = canvas.toDataURL('image/png');
        } catch (_errToDataUrl) {
          // Jika canvas hasil render DOM gagal diexport, fallback ke render tabel murni.
          canvas = targetId === 'rekap-bagian-card' ? renderBagianListToCanvas(target) : renderTableDataToCanvas(target);
          dataUrl = canvas.toDataURL('image/png');
        }
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = filePrefix + '_' + buildPeriodSuffix() + '.png';
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (err) {
        alert('Download gambar gagal. Silakan coba refresh halaman lalu klik ulang.');
      }
    }

    document.querySelectorAll('.js-download-image').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = btn.getAttribute('data-target-id') || '';
        const filePrefix = btn.getAttribute('data-file-prefix') || 'rekap_bundle_vap';
        downloadSectionAsImage(targetId, filePrefix);
      });
    });
  })();
</script>
