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
$dataGrafik = [];
$qGrafik = mysqli_query($conn, "
  SELECT
    d.kode_bagian,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum
  FROM audit_gizi a
  JOIN audit_gizi_detail d ON a.id = d.audit_id
  $grafikWhereSql
  GROUP BY d.kode_bagian
  ORDER BY d.kode_bagian ASC
");
while ($row = mysqli_fetch_assoc($qGrafik)) {
  $num = (int) ($row['num'] ?? 0);
  $den = (int) ($row['denum'] ?? 0);
  $labelGrafik[] = $row['kode_bagian'] ?? '-';
  $dataGrafik[] = $den > 0 ? round(($num / $den) * 100, 2) : 0;
}
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
$judulGrafik = 'Grafik Kepatuhan per Bagian - ' . $periodeLabel;
$judulTren = 'Grafik Tren Kepatuhan (%) - ' . $periodeLabel;

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
  FROM audit_gizi a
  JOIN audit_gizi_detail d ON a.id = d.audit_id
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
  #tab-grafik .card-title { margin: 0 0 12px; font-size: 22px; font-weight: 900; }
  #tab-grafik .filter-grid { display:grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto auto; gap: 12px; align-items: center; }
  #tab-grafik .chart-head {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    margin-bottom: 12px;
    gap: 10px;
  }
  #tab-grafik .chart-title { margin: 0; text-align: center; font-size: 22px; font-weight: 900; }
  #tab-grafik .chart-head-left { justify-self: start; }
  #tab-grafik .chart-head-right { justify-self: end; }
  #tab-grafik .btn-download { box-shadow: 0 6px 14px rgba(30, 64, 175, 0.24); }
  #tab-grafik .chart-wrap { border: 1px solid var(--line); border-radius: 14px; padding: 14px; background: var(--card); }
  #tab-grafik .target-input { text-align: center; font-weight: 800; }
  @media (max-width: 900px) {
    #tab-grafik .filter-grid { grid-template-columns: 1fr; }
    #tab-grafik .chart-head { grid-template-columns: 1fr; }
    #tab-grafik .chart-title { text-align: left; font-size: 18px; }
    #tab-grafik .chart-head-right { justify-self: start; }
  }
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
      <div class="chart-head-left"></div>
      <h3 class="chart-title"><?= htmlspecialchars($judulGrafik) ?></h3>
      <div class="chart-head-right">
        <button type="button" class="btn btn-primary btn-download" id="btnDownloadGrafik">Download Gambar</button>
      </div>
    </div>
    <div class="chart-wrap">
      <canvas id="chartGizi" height="120"></canvas>
    </div>
  </div>
  <div class="section-card">
    <div class="chart-head">
      <div class="chart-head-left"></div>
      <h3 class="chart-title"><?= htmlspecialchars($judulTren) ?></h3>
      <div class="chart-head-right">
        <button type="button" class="btn btn-primary btn-download" id="btnDownloadTren">Download Grafik Tren</button>
      </div>
    </div>
    <div class="chart-wrap">
      <canvas id="chartTrenGizi" height="110"></canvas>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
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

    const el = document.getElementById('chartGizi');
    if (!el) return;
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
        plugins: {
          title: {
            display: true,
            text: <?= json_encode($judulGrafik) ?>,
            color: '#0f172a',
            font: {
              size: 16,
              weight: '700'
            },
            padding: {
              top: 4,
              bottom: 10
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
            font: {
              weight: '700',
              size: 11
            },
            formatter: function (value) {
              return Number(value).toFixed(1).replace('.0', '') + '%';
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function (v) { return v + '%'; }
            }
          }
        }
      }
    });

    const btnDownload = document.getElementById('btnDownloadGrafik');
    if (btnDownload) {
      btnDownload.addEventListener('click', function () {
        const link = document.createElement('a');
        link.href = chart.toBase64Image('image/png', 1);
        link.download = 'grafik-kepatuhan-gizi.png';
        link.click();
      });
    }

    const elTrend = document.getElementById('chartTrenGizi');
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
        plugins: {
          title: {
            display: true,
            text: <?= json_encode($judulTren) ?>,
            color: '#0f172a',
            font: {
              size: 16,
              weight: '700'
            },
            padding: {
              top: 4,
              bottom: 10
            }
          },
          datalabels: {
            display: function (ctx) { return ctx.datasetIndex === 0; },
            color: '#111827',
            anchor: 'end',
            align: 'top',
            font: { weight: '700', size: 10 },
            formatter: function (v) { return Number(v).toFixed(1).replace('.0', '') + '%'; }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: { callback: function (v) { return v + '%'; } }
          }
        }
      }
    });

    const btnDownloadTren = document.getElementById('btnDownloadTren');
    if (btnDownloadTren) {
      btnDownloadTren.addEventListener('click', function () {
        const link = document.createElement('a');
        link.href = trendChart.toBase64Image('image/png', 1);
        link.download = 'grafik-tren-kepatuhan-gizi.png';
        link.click();
      });
    }
  })();
</script>
