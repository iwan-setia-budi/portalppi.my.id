<?php
$grafikPeriode = $_GET['grafik_periode'] ?? 'tahunan';
$grafikBulan = isset($_GET['grafik_bulan']) ? (int) $_GET['grafik_bulan'] : (int) date('n');
$grafikTriwulan = isset($_GET['grafik_triwulan']) ? (int) $_GET['grafik_triwulan'] : (int) ceil(((int) date('n')) / 3);
$grafikTahun = isset($_GET['grafik_tahun']) ? (int) $_GET['grafik_tahun'] : (int) date('Y');
$grafikTarget = isset($_GET['grafik_target']) ? (float) $_GET['grafik_target'] : 85.0;

$grafikPeriode = in_array($grafikPeriode, ['bulanan', 'triwulan', 'tahunan'], true) ? $grafikPeriode : 'tahunan';
$grafikBulan = max(1, min(12, $grafikBulan));
$grafikTriwulan = max(1, min(4, $grafikTriwulan));
$grafikTahun = max(2020, min(2100, $grafikTahun));
$grafikTarget = max(0, min(100, $grafikTarget));

$grafikWhere = [];
if ($grafikPeriode === 'bulanan') {
  $grafikWhere[] = "MONTH(a.tanggal_audit) = $grafikBulan";
  $grafikWhere[] = "YEAR(a.tanggal_audit) = $grafikTahun";
} elseif ($grafikPeriode === 'triwulan') {
  $startMonth = (($grafikTriwulan - 1) * 3) + 1;
  $endMonth = $startMonth + 2;
  $grafikWhere[] = "MONTH(a.tanggal_audit) BETWEEN $startMonth AND $endMonth";
  $grafikWhere[] = "YEAR(a.tanggal_audit) = $grafikTahun";
} else {
  $grafikWhere[] = "YEAR(a.tanggal_audit) = $grafikTahun";
}
$grafikWhereSql = count($grafikWhere) ? 'WHERE ' . implode(' AND ', $grafikWhere) : '';

$labelGrafik = [];
$labelGrafikNama = [];
$kodeKeterangan = [];
$dataGrafik = [];
$itemLabelMap = [];
if (isset($checklistSections) && is_array($checklistSections)) {
  foreach ($checklistSections as $kode => $section) {
    $items = $section['items'] ?? [];
    foreach ($items as $idx => $itemText) {
      $indikatorKode = sprintf('%s%04d', (string) $kode, $idx + 1);
      $itemLabelMap[$indikatorKode] = (string) $itemText;
    }
  }
}
$qGrafik = mysqli_query($conn, "
  SELECT
    CONCAT(d.kode_bagian, LPAD(d.urutan_item, 4, '0')) AS kode_bagian,
    MAX(d.item_text) AS item_text,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum
  FROM audit_bundle_vap a
  JOIN detail_audit_bundle_vap d ON a.id = d.audit_id
  $grafikWhereSql
  GROUP BY d.kode_bagian, d.urutan_item
  ORDER BY d.kode_bagian ASC, d.urutan_item ASC
");
while ($row = mysqli_fetch_assoc($qGrafik)) {
  $num = (int) ($row['num'] ?? 0);
  $den = (int) ($row['denum'] ?? 0);
  $kodeItem = (string) ($row['kode_bagian'] ?? '-');
  $namaItem = $itemLabelMap[$kodeItem] ?? (string) ($row['item_text'] ?? '');
  $labelGrafik[] = $kodeItem;
  $labelGrafikNama[] = $namaItem;
  $kodeKeterangan[] = [
    'kode' => $kodeItem,
    'nama' => $namaItem,
    'color_key' => count($kodeKeterangan) % 6,
  ];
  $dataGrafik[] = $den > 0 ? round(($num / $den) * 100, 2) : 0;
}
$ikonKodeMap = [
  'W0001' => '🛏',
  'W0002' => '🪥',
  'W0003' => '🫁',
  'W0004' => '🧼',
  'W0005' => '💤',
];
$targetGrafik = array_fill(0, count($labelGrafik), $grafikTarget);
$namaBulan = [
  1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
  5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
  9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$periodeLabel = '';
if ($grafikPeriode === 'bulanan') {
  $periodeLabel = ($namaBulan[$grafikBulan] ?? $grafikBulan) . ' ' . $grafikTahun;
} elseif ($grafikPeriode === 'triwulan') {
  $periodeLabel = 'Triwulan ' . $grafikTriwulan . ' ' . $grafikTahun;
} else {
  $periodeLabel = 'Tahun ' . $grafikTahun;
}
$judulGrafik = 'Grafik Kepatuhan Bundle VAP di Rumah Sakit Primaya Bhakti Wara - ' . $periodeLabel;
$judulTren = 'Grafik Tren Kepatuhan Bundle VAP di Rumah Sakit Primaya Bhakti Wara - ' . $periodeLabel;
$subJudulGrafik = 'Kepatuhan per Item (' . $periodeLabel . ')';
$subJudulTren = 'Tren Kepatuhan (' . $periodeLabel . ')';

$trendMonthList = [];
if ($grafikPeriode === 'bulanan') {
  $trendMonthList = [$grafikBulan];
} elseif ($grafikPeriode === 'triwulan') {
  $startMonth = (($grafikTriwulan - 1) * 3) + 1;
  $trendMonthList = [$startMonth, $startMonth + 1, $startMonth + 2];
} else {
  $trendMonthList = range(1, 12);
}

$trendWhere = ["YEAR(a.tanggal_audit) = $grafikTahun"];
if ($grafikPeriode === 'bulanan') {
  $trendWhere[] = "MONTH(a.tanggal_audit) = $grafikBulan";
} elseif ($grafikPeriode === 'triwulan') {
  $startMonth = (($grafikTriwulan - 1) * 3) + 1;
  $endMonth = $startMonth + 2;
  $trendWhere[] = "MONTH(a.tanggal_audit) BETWEEN $startMonth AND $endMonth";
}
$trendWhereSql = 'WHERE ' . implode(' AND ', $trendWhere);

$trendMap = [];
$qTrend = mysqli_query($conn, "
  SELECT
    MONTH(a.tanggal_audit) AS bln,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum
  FROM audit_bundle_vap a
  JOIN detail_audit_bundle_vap d ON a.id = d.audit_id
  $trendWhereSql
  GROUP BY MONTH(a.tanggal_audit)
  ORDER BY MONTH(a.tanggal_audit) ASC
");
while ($r = mysqli_fetch_assoc($qTrend)) {
  $bln = (int) ($r['bln'] ?? 0);
  $num = (int) ($r['num'] ?? 0);
  $den = (int) ($r['denum'] ?? 0);
  $trendMap[$bln] = $den > 0 ? round(($num / $den) * 100, 2) : 0;
}

$trendLabels = [];
$trendData = [];
foreach ($trendMonthList as $m) {
  $trendLabels[] = substr($namaBulan[$m] ?? (string) $m, 0, 3);
  $trendData[] = $trendMap[$m] ?? 0;
}

$barColors = [];
$barBorders = [];
$labelColors = [];
foreach ($dataGrafik as $val) {
  if ($val >= $grafikTarget) {
    $barColors[] = 'rgba(34, 197, 94, 0.75)';
    $barBorders[] = 'rgba(22, 163, 74, 1)';
    $labelColors[] = '#166534';
  } else {
    $barColors[] = 'rgba(239, 68, 68, 0.75)';
    $barBorders[] = 'rgba(220, 38, 38, 1)';
    $labelColors[] = '#991b1b';
  }
}
?>
<style>
  #tab-grafik .card-title { margin: 0 0 12px; font-size: 20px; font-weight: 900; letter-spacing: -0.2px; }
  #tab-grafik .filter-grid { display:grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto auto; gap: 12px; align-items: center; }
  #tab-grafik .chart-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    row-gap: 8px;
    align-items: center;
    margin-bottom: 18px;
    gap: 10px;
  }
  #tab-grafik .chart-title {
    margin: 0;
    text-align: left;
    font-size: 19px;
    font-weight: 900;
    letter-spacing: -0.15px;
    line-height: 1.25;
    max-width: min(100%, 900px);
  }
  #tab-grafik .chart-head-right { margin-left: auto; }
  #tab-grafik .btn-download { box-shadow: 0 6px 14px rgba(30, 64, 175, 0.24); }
  #tab-grafik .chart-wrap { border: 1px solid var(--line); border-radius: 14px; padding: 10px 14px 14px; background: var(--card); }
  #tab-grafik .chart-canvas-wrap { position: relative; width: 100%; height: 520px; }
  #tab-grafik .chart-canvas-wrap.is-trend { height: 480px; }
  #tab-grafik .chart-canvas-wrap canvas { width: 100% !important; height: 100% !important; display: block; }
  #tab-grafik .kode-keterangan-wrap {
    margin-top: -6px;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 12px;
    background: color-mix(in srgb, var(--card) 95%, #e2e8f0 5%);
  }
  #tab-grafik .kode-keterangan-title {
    margin: 0 0 8px;
    font-size: 14px;
    font-weight: 900;
    color: #334155;
    letter-spacing: .2px;
  }
  #tab-grafik .kode-keterangan-subtitle {
    margin: 0 0 10px;
    color: #64748b;
    font-size: 13px;
  }
  #tab-grafik .kode-keterangan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 10px;
  }
  #tab-grafik .kode-keterangan-item {
    border: 1px solid transparent;
    border-radius: 10px;
    padding: 10px 10px 9px;
    font-size: 13px;
    line-height: 1.4;
    color: #1f2937;
    background: #f8fafc;
    display: grid;
    grid-template-columns: 1fr auto;
    column-gap: 8px;
    align-items: center;
  }
  #tab-grafik .kode-keterangan-item strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 8px;
    border-radius: 999px;
    border: 1px solid transparent;
    margin-right: 6px;
    color: #0f172a;
    font-size: 12px;
    min-width: 62px;
  }
  #tab-grafik .kode-keterangan-item.k0 { background: #eff6ff; border-color: #bfdbfe; }
  #tab-grafik .kode-keterangan-item.k0 strong { background: #dbeafe; border-color: #93c5fd; color: #1e40af; }
  #tab-grafik .kode-keterangan-item.k1 { background: #f0fdf4; border-color: #bbf7d0; }
  #tab-grafik .kode-keterangan-item.k1 strong { background: #dcfce7; border-color: #86efac; color: #166534; }
  #tab-grafik .kode-keterangan-item.k2 { background: #fffbeb; border-color: #fde68a; }
  #tab-grafik .kode-keterangan-item.k2 strong { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
  #tab-grafik .kode-keterangan-item.k3 { background: #fef2f2; border-color: #fecaca; }
  #tab-grafik .kode-keterangan-item.k3 strong { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
  #tab-grafik .kode-keterangan-item.k4 { background: #f5f3ff; border-color: #ddd6fe; }
  #tab-grafik .kode-keterangan-item.k4 strong { background: #ede9fe; border-color: #c4b5fd; color: #5b21b6; }
  #tab-grafik .kode-keterangan-item.k5 { background: #f0fdfa; border-color: #99f6e4; }
  #tab-grafik .kode-keterangan-item.k5 strong { background: #ccfbf1; border-color: #5eead4; color: #115e59; }
  #tab-grafik .kode-keterangan-item .kode-desc {
    color: #334155;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.35;
    min-height: calc(1.35em * 2);
  }
  #tab-grafik .kode-keterangan-item-main { min-width: 0; }
  #tab-grafik .kode-keterangan-icon {
    font-size: 26px;
    line-height: 1;
    opacity: .75;
    align-self: center;
    filter: saturate(.9);
  }
  #tab-grafik .target-input { text-align: center; font-weight: 800; }
  @media (max-width: 900px) {
    #tab-grafik .filter-grid { grid-template-columns: 1fr; }
    #tab-grafik .chart-head { flex-direction: column; align-items: flex-start; }
    #tab-grafik .chart-title { text-align: left; font-size: 18px; }
    #tab-grafik .chart-head-right { margin-left: 0; }
    #tab-grafik .chart-wrap { padding: 16px 10px 14px; overflow: hidden; }
    #tab-grafik .chart-canvas-wrap { height: clamp(310px, 62vw, 390px); min-width: 0; }
    #tab-grafik .chart-canvas-wrap.is-trend { height: clamp(260px, 54vw, 320px); min-width: 0; }
    #tab-grafik .kode-keterangan-grid { grid-template-columns: 1fr; }
  }
  body.dark-mode #tab-grafik .kode-keterangan-wrap { background: color-mix(in srgb, var(--card) 90%, #0f172a 10%); }
  body.dark-mode #tab-grafik .kode-keterangan-title { color: #cbd5e1; }
  body.dark-mode #tab-grafik .kode-keterangan-subtitle { color: #94a3b8; }
  body.dark-mode #tab-grafik .kode-keterangan-item { color: #e2e8f0; background: #0f172a; }
  body.dark-mode #tab-grafik .kode-keterangan-item .kode-desc { color: #cbd5e1; }
  body.dark-mode #tab-grafik .kode-keterangan-item strong { color: #f8fafc; }
  body.dark-mode #tab-grafik .kode-keterangan-icon { opacity: .9; }
</style>
<div id="tab-grafik" class="tab-pane active">
  <div class="section-card">
    <h3 class="card-title">Filter Periode Grafik</h3>
    <form method="get">
      <input type="hidden" name="tab" value="tab-grafik">
      <div class="filter-grid">
        <select name="grafik_periode" class="form-control" id="grafikPeriode">
          <option value="bulanan" <?= $grafikPeriode === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
          <option value="triwulan" <?= $grafikPeriode === 'triwulan' ? 'selected' : '' ?>>Triwulan</option>
          <option value="tahunan" <?= $grafikPeriode === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
        </select>
        <select name="grafik_bulan" class="form-control" id="grafikBulan">
          <?php for ($b = 1; $b <= 12; $b++): ?>
            <option value="<?= $b ?>" <?= (int) $grafikBulan === $b ? 'selected' : '' ?>>Bulan <?= $b ?></option>
          <?php endfor; ?>
        </select>
        <select name="grafik_triwulan" class="form-control" id="grafikTriwulan">
          <?php for ($q = 1; $q <= 4; $q++): ?>
            <option value="<?= $q ?>" <?= (int) $grafikTriwulan === $q ? 'selected' : '' ?>>Triwulan <?= $q ?></option>
          <?php endfor; ?>
        </select>
        <select name="grafik_tahun" class="form-control">
          <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
            <option value="<?= $t ?>" <?= (int) $grafikTahun === (int) $t ? 'selected' : '' ?>>Tahun <?= $t ?></option>
          <?php endfor; ?>
        </select>
        <input type="number" name="grafik_target" class="form-control target-input" min="0" max="100" step="0.1" value="<?= htmlspecialchars((string) $grafikTarget) ?>" placeholder="Target %">
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a class="btn btn-secondary" href="?tab=tab-grafik">Reset</a>
      </div>
    </form>
  </div>
  <div class="section-card">
    <div class="chart-head">
      <h3 class="chart-title"><?= htmlspecialchars($subJudulGrafik) ?></h3>
      <div class="chart-head-right">
        <button type="button" class="btn btn-primary btn-download" id="btnDownloadGrafik">Download Gambar</button>
      </div>
    </div>
    <div class="chart-wrap" id="grafikBundleVapExportWrap">
      <div class="chart-canvas-wrap">
        <canvas id="chartCssd"></canvas>
      </div>
      <?php if (count($kodeKeterangan) > 0): ?>
        <div class="kode-keterangan-wrap">
          <p class="kode-keterangan-title">Keterangan kode indikator</p>
          <p class="kode-keterangan-subtitle">Penjelasan untuk setiap kode indikator yang digunakan pada grafik.</p>
          <div class="kode-keterangan-grid">
            <?php foreach ($kodeKeterangan as $ket): ?>
              <div class="kode-keterangan-item k<?= (int) $ket['color_key'] ?>">
                <div class="kode-keterangan-item-main">
                  <strong><?= htmlspecialchars($ket['kode']) ?></strong>
                  <span class="kode-desc"><?= htmlspecialchars($ket['nama'] !== '' ? $ket['nama'] : '-') ?></span>
                </div>
                <span class="kode-keterangan-icon"><?= htmlspecialchars($ikonKodeMap[$ket['kode']] ?? '📌') ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="section-card">
    <div class="chart-head">
      <h3 class="chart-title"><?= htmlspecialchars($subJudulTren) ?></h3>
      <div class="chart-head-right">
        <button type="button" class="btn btn-primary btn-download" id="btnDownloadTren">Download Grafik Tren</button>
      </div>
    </div>
    <div class="chart-wrap">
      <div class="chart-canvas-wrap is-trend">
        <canvas id="chartTrenCssd"></canvas>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
<script>
  (function () {
    const whiteBackgroundPlugin = {
      id: 'whiteBackgroundPlugin',
      beforeDraw(chart) {
        const { ctx, width, height } = chart;
        ctx.save();
        ctx.globalCompositeOperation = 'destination-over';
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.restore();
      }
    };

    const periode = document.getElementById('grafikPeriode');
    const bulan = document.getElementById('grafikBulan');
    const triwulan = document.getElementById('grafikTriwulan');
    if (periode && bulan && triwulan) {
      const syncFields = () => {
        const v = periode.value;
        bulan.disabled = v !== 'bulanan';
        triwulan.disabled = v !== 'triwulan';
        bulan.style.opacity = bulan.disabled ? '0.6' : '1';
        triwulan.style.opacity = triwulan.disabled ? '0.6' : '1';
      };
      periode.addEventListener('change', syncFields);
      syncFields();
    }

    const el = document.getElementById('chartCssd');
    if (!el) return;
    const getAdaptiveTitleSize = (chart) => {
      const w = chart && chart.width ? chart.width : window.innerWidth;
      if (w <= 640) return 14;
      if (w <= 900) return 16;
      return 20;
    };
    const chart = new Chart(el, {
      type: 'bar',
      plugins: [ChartDataLabels, whiteBackgroundPlugin],
      data: {
        labels: <?= json_encode($labelGrafik) ?>,
        datasets: [{
          label: 'Kepatuhan (%)',
          data: <?= json_encode($dataGrafik) ?>,
          backgroundColor: <?= json_encode($barColors) ?>,
          borderColor: <?= json_encode($barBorders) ?>,
          borderWidth: 1
        }, {
          type: 'line',
          label: 'Target (<?= $grafikTarget ?>%)',
          data: <?= json_encode($targetGrafik) ?>,
          borderColor: 'rgba(239, 68, 68, 0.95)',
          borderWidth: 2,
          borderDash: [6, 4],
          pointRadius: 0,
          pointHoverRadius: 0,
          fill: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
          padding: {
            top: 10,
            right: 20,
            left: 14,
            bottom: 24
          }
        },
        plugins: {
          legend: {
            position: 'bottom',
            align: 'center',
            labels: {
              padding: 18,
              boxWidth: 36,
              boxHeight: 12
            }
          },
          title: {
            display: true,
            text: <?= json_encode($judulGrafik) ?>,
            color: '#0f172a',
            font: {
              size: function (ctx) {
                return getAdaptiveTitleSize(ctx.chart);
              },
              weight: '700'
            },
            padding: {
              top: 4,
              bottom: 60
            }
          },
          datalabels: {
            display: function (ctx) {
              return ctx.datasetIndex === 0;
            },
            color: function (ctx) {
              const colors = <?= json_encode($labelColors) ?>;
              return colors[ctx.dataIndex] || '#0f172a';
            },
            anchor: 'end',
            align: 'top',
            offset: 2,
            clamp: true,
            clip: false,
            font: {
              weight: '700',
              size: 11
            },
            formatter: function (value) {
              return Number(value).toFixed(1).replace('.0', '') + '%';
            }
          },
          tooltip: {
            callbacks: {
              title: function (items) {
                if (!items || !items.length) return '';
                return 'Kode ' + items[0].label;
              },
              afterBody: function (items) {
                if (!items || !items.length) return '';
                const namaItems = <?= json_encode($labelGrafikNama) ?>;
                const idx = items[0].dataIndex;
                return namaItems[idx] ? ['Indikator: ' + namaItems[idx]] : [];
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              padding: 6,
              autoSkip: false,
              maxTicksLimit: 12,
              maxRotation: 0,
              minRotation: 0,
              font: {
                size: 10
              }
            }
          },
          y: {
            min: 0,
            beginAtZero: true,
            max: 100,
            ticks: {
              stepSize: 10,
              autoSkip: false,
              precision: 0,
              callback: function (v) { return v + '%'; }
            }
          }
        }
      }
    });

    const btnDownload = document.getElementById('btnDownloadGrafik');
    if (btnDownload) {
      btnDownload.addEventListener('click', async function () {
        const wrap = document.getElementById('grafikBundleVapExportWrap');
        const tryCanvasOnly = function () {
          const link = document.createElement('a');
          link.href = chart.toBase64Image('image/png', 1);
          link.download = 'grafik-kepatuhan-bundle-vap.png';
          link.click();
        };
        if (!wrap || typeof html2canvas !== 'function') {
          tryCanvasOnly();
          return;
        }
        const prevText = btnDownload.textContent;
        btnDownload.disabled = true;
        btnDownload.textContent = 'Menyiapkan…';
        try {
          chart.resize();
          await new Promise(function (r) { requestAnimationFrame(function () { requestAnimationFrame(r); }); });
          const out = await html2canvas(wrap, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
            onclone: function (doc) {
              const w = doc.getElementById('grafikBundleVapExportWrap');
              if (w) {
                w.style.borderRadius = '14px';
                w.style.background = '#ffffff';
              }
              const kk = doc.querySelector('#grafikBundleVapExportWrap .kode-keterangan-wrap');
              if (kk) {
                kk.style.background = '#f1f5f9';
                kk.style.borderColor = '#e2e8f0';
              }
            }
          });
          const link = document.createElement('a');
          link.href = out.toDataURL('image/png');
          link.download = 'grafik-kepatuhan-bundle-vap.png';
          link.click();
        } catch (_e) {
          tryCanvasOnly();
        } finally {
          btnDownload.disabled = false;
          btnDownload.textContent = prevText;
        }
      });
    }

    const elTrend = document.getElementById('chartTrenCssd');
    if (!elTrend) return;
    const trendChart = new Chart(elTrend, {
      type: 'line',
      plugins: [ChartDataLabels, whiteBackgroundPlugin],
      data: {
        labels: <?= json_encode($trendLabels) ?>,
        datasets: [{
          label: 'Tren Kepatuhan (%)',
          data: <?= json_encode($trendData) ?>,
          borderColor: 'rgba(37, 99, 235, 0.95)',
          backgroundColor: 'rgba(59, 130, 246, 0.18)',
          fill: true,
          tension: 0.35,
          pointRadius: 4,
          pointBackgroundColor: '#ef4444',
          pointBorderColor: '#991b1b',
          pointBorderWidth: 1.2
        }, {
          label: 'Target <?= $grafikTarget ?>%',
          data: Array(<?= count($trendLabels) ?>).fill(<?= $grafikTarget ?>),
          borderColor: 'rgba(239, 68, 68, 0.95)',
          borderWidth: 1.6,
          borderDash: [6, 4],
          pointRadius: 0,
          fill: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
          padding: {
            top: 10,
            right: 20,
            left: 14,
            bottom: 24
          }
        },
        plugins: {
          legend: {
            position: 'bottom',
            align: 'center',
            labels: {
              padding: 18,
              boxWidth: 36,
              boxHeight: 12
            }
          },
          title: {
            display: true,
            text: <?= json_encode($judulTren) ?>,
            color: '#0f172a',
            font: {
              size: function (ctx) {
                return getAdaptiveTitleSize(ctx.chart);
              },
              weight: '700'
            },
            padding: {
              top: 4,
              bottom: 60
            }
          },
          datalabels: {
            display: function (ctx) { return ctx.datasetIndex === 0; },
            color: '#111827',
            anchor: 'end',
            align: 'top',
            offset: 2,
            clamp: true,
            clip: false,
            font: { weight: '700', size: 10 },
            formatter: function (v) { return Number(v).toFixed(1).replace('.0', '') + '%'; }
          }
        },
        scales: {
          x: {
            ticks: {
              padding: 6,
              autoSkip: true,
              maxTicksLimit: 6,
              maxRotation: 0,
              minRotation: 0,
              font: {
                size: 10
              }
            }
          },
          y: {
            min: 0,
            beginAtZero: true,
            max: 100,
            ticks: {
              stepSize: 10,
              autoSkip: false,
              precision: 0,
              callback: function (v) { return v + '%'; }
            }
          }
        }
      }
    });

    const btnDownloadTren = document.getElementById('btnDownloadTren');
    if (btnDownloadTren) {
      btnDownloadTren.addEventListener('click', function () {
        const link = document.createElement('a');
        link.href = trendChart.toBase64Image('image/png', 1);
        link.download = 'grafik-tren-kepatuhan-bundle-vap.png';
        link.click();
      });
    }
  })();
</script>
