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
