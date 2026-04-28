<?php
/* =========================
   FILTER KHUSUS TAB GRAFIK APD
========================= */

$filter_periode = $_GET['periode'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';
$filter_triwulan = $_GET['triwulan'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';
$filter_profesi = $_GET['f_profesi'] ?? '';
$filter_ruangan = $_GET['f_ruangan'] ?? '';
$filter_kategori = $_GET['f_kategori'] ?? '';

$whereGrafik = [];

if ($filter_profesi !== '') {
  $whereGrafik[] = "a.profesi = '" . mysqli_real_escape_string($conn, $filter_profesi) . "'";
}

if ($filter_ruangan !== '') {
  $whereGrafik[] = "a.ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'";
}

if ($filter_kategori !== '') {
  $whereGrafik[] = "d.kategori = '" . mysqli_real_escape_string($conn, $filter_kategori) . "'";
}

if ($filter_periode === 'bulan' && $filter_bulan !== '' && $filter_tahun !== '') {
  $whereGrafik[] = "MONTH(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_bulan) . "'";
  $whereGrafik[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
}

if ($filter_periode === 'triwulan' && $filter_triwulan !== '' && $filter_tahun !== '') {
  $tw = (int) $filter_triwulan;

  if ($tw === 1) {
    $whereGrafik[] = "MONTH(a.tanggal_audit) BETWEEN 1 AND 3";
  } elseif ($tw === 2) {
    $whereGrafik[] = "MONTH(a.tanggal_audit) BETWEEN 4 AND 6";
  } elseif ($tw === 3) {
    $whereGrafik[] = "MONTH(a.tanggal_audit) BETWEEN 7 AND 9";
  } elseif ($tw === 4) {
    $whereGrafik[] = "MONTH(a.tanggal_audit) BETWEEN 10 AND 12";
  }

  $whereGrafik[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
}

if ($filter_periode === 'tahun' && $filter_tahun !== '') {
  $whereGrafik[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
}

$whereGrafikSql = count($whereGrafik) ? 'WHERE ' . implode(' AND ', $whereGrafik) : '';

/* =========================
   GRAFIK INDIKATOR PENILAIAN
   % = Ya / (Ya + Tidak), NA tidak dihitung
========================= */

$grafikIndikatorLabel = [];
$grafikIndikatorValue = [];

$whereIndikatorSql = $whereGrafikSql
  ? $whereGrafikSql . " AND d.kategori = 'indikator_penilaian'"
  : "WHERE d.kategori = 'indikator_penilaian'";

$qGrafikIndikator = mysqli_query($conn, "
  SELECT
    d.indikator_label,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS ya,
    SUM(CASE WHEN d.jawaban = 'tidak' THEN 1 ELSE 0 END) AS tidak,
    ROUND(
      (SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) /
      NULLIF(SUM(CASE WHEN d.jawaban IN ('ya','tidak') THEN 1 ELSE 0 END), 0)) * 100,
      2
    ) AS persen
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereIndikatorSql
  GROUP BY d.indikator_label
  ORDER BY d.indikator_label ASC
");

while ($row = mysqli_fetch_assoc($qGrafikIndikator)) {
  $grafikIndikatorLabel[] = $row['indikator_label'];
  $grafikIndikatorValue[] = (float) ($row['persen'] ?? 0);
}

/* =========================
   GRAFIK APD DIGUNAKAN
========================= */

$grafikApdLabel = [];
$grafikApdValue = [];

$whereApdSql = $whereGrafikSql
  ? $whereGrafikSql . " AND d.kategori = 'apd_digunakan'"
  : "WHERE d.kategori = 'apd_digunakan'";

$qGrafikAPD = mysqli_query($conn, "
  SELECT
    d.indikator_label,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS ya,
    SUM(CASE WHEN d.jawaban = 'tidak' THEN 1 ELSE 0 END) AS tidak,
    ROUND(
      (SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) /
      NULLIF(SUM(CASE WHEN d.jawaban IN ('ya','tidak') THEN 1 ELSE 0 END), 0)) * 100,
      2
    ) AS persen
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereApdSql
  GROUP BY d.indikator_label
  ORDER BY d.indikator_label ASC
");

while ($row = mysqli_fetch_assoc($qGrafikAPD)) {
  $grafikApdLabel[] = $row['indikator_label'];
  $grafikApdValue[] = (float) ($row['persen'] ?? 0);
}

/* =========================
   GRAFIK PROFESI
========================= */

$grafikProfesiLabel = [];
$grafikProfesiValue = [];

$qGrafikProfesi = mysqli_query($conn, "
  SELECT
    a.profesi,
    ROUND(
      (SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) /
      NULLIF(SUM(CASE WHEN d.jawaban IN ('ya','tidak') THEN 1 ELSE 0 END), 0)) * 100,
      2
    ) AS persen
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereGrafikSql
  GROUP BY a.profesi
  ORDER BY a.profesi ASC
");

while ($row = mysqli_fetch_assoc($qGrafikProfesi)) {
  $grafikProfesiLabel[] = $row['profesi'];
  $grafikProfesiValue[] = (float) ($row['persen'] ?? 0);
}

/* =========================
   GRAFIK UNIT
========================= */

$grafikUnitLabel = [];
$grafikUnitValue = [];

$qGrafikUnit = mysqli_query($conn, "
  SELECT
    a.ruangan,
    ROUND(
      (SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) /
      NULLIF(SUM(CASE WHEN d.jawaban IN ('ya','tidak') THEN 1 ELSE 0 END), 0)) * 100,
      2
    ) AS persen
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereGrafikSql
  GROUP BY a.ruangan
  ORDER BY a.ruangan ASC
");

while ($row = mysqli_fetch_assoc($qGrafikUnit)) {
  $grafikUnitLabel[] = $row['ruangan'];
  $grafikUnitValue[] = (float) ($row['persen'] ?? 0);
}

/* =========================
   GRAFIK TREN BULANAN
========================= */

$namaBulan = [
  1 => 'Jan',
  2 => 'Feb',
  3 => 'Mar',
  4 => 'Apr',
  5 => 'Mei',
  6 => 'Jun',
  7 => 'Jul',
  8 => 'Agu',
  9 => 'Sep',
  10 => 'Okt',
  11 => 'Nov',
  12 => 'Des'
];

$bulanTampil = range(1, 12);

if ($filter_periode === 'triwulan' && $filter_triwulan !== '') {
  $tw = (int) $filter_triwulan;

  if ($tw === 1) {
    $bulanTampil = [1, 2, 3];
  } elseif ($tw === 2) {
    $bulanTampil = [4, 5, 6];
  } elseif ($tw === 3) {
    $bulanTampil = [7, 8, 9];
  } elseif ($tw === 4) {
    $bulanTampil = [10, 11, 12];
  }
}

if ($filter_periode === 'bulan' && $filter_bulan !== '') {
  $bulanTampil = [(int) $filter_bulan];
}

$grafikTrenLabel = [];
$grafikTrenValue = [];

foreach ($bulanTampil as $bulan) {
  $grafikTrenLabel[] = $namaBulan[$bulan];
  $grafikTrenValue[$bulan] = 0;
}

$whereTren = [];

if ($filter_profesi !== '') {
  $whereTren[] = "a.profesi = '" . mysqli_real_escape_string($conn, $filter_profesi) . "'";
}

if ($filter_ruangan !== '') {
  $whereTren[] = "a.ruangan = '" . mysqli_real_escape_string($conn, $filter_ruangan) . "'";
}

if ($filter_kategori !== '') {
  $whereTren[] = "d.kategori = '" . mysqli_real_escape_string($conn, $filter_kategori) . "'";
}

if ($filter_tahun !== '') {
  $whereTren[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filter_tahun) . "'";
} else {
  $whereTren[] = "YEAR(a.tanggal_audit) = YEAR(CURDATE())";
}

$bulanIn = implode(',', array_map('intval', $bulanTampil));
$whereTren[] = "MONTH(a.tanggal_audit) IN ($bulanIn)";

$whereTrenSql = count($whereTren) ? 'WHERE ' . implode(' AND ', $whereTren) : '';

$qGrafikTren = mysqli_query($conn, "
  SELECT
    MONTH(a.tanggal_audit) AS bulan,
    ROUND(
      (SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) /
      NULLIF(SUM(CASE WHEN d.jawaban IN ('ya','tidak') THEN 1 ELSE 0 END), 0)) * 100,
      2
    ) AS persen
  FROM audit_apd a
  JOIN audit_apd_detail d ON a.id = d.audit_id
  $whereTrenSql
  GROUP BY MONTH(a.tanggal_audit)
  ORDER BY MONTH(a.tanggal_audit) ASC
");

while ($row = mysqli_fetch_assoc($qGrafikTren)) {
  $bulan = (int) $row['bulan'];
  $grafikTrenValue[$bulan] = (float) ($row['persen'] ?? 0);
}

$grafikTrenValue = array_values($grafikTrenValue);

$namaBulanLengkap = [
  1 => 'Januari',
  2 => 'Februari',
  3 => 'Maret',
  4 => 'April',
  5 => 'Mei',
  6 => 'Juni',
  7 => 'Juli',
  8 => 'Agustus',
  9 => 'September',
  10 => 'Oktober',
  11 => 'November',
  12 => 'Desember'
];

$periodeJudul = 'Semua Periode';

if ($filter_periode === 'bulan' && $filter_bulan !== '' && $filter_tahun !== '') {
  $periodeJudul = ($namaBulanLengkap[(int) $filter_bulan] ?? '') . ' ' . $filter_tahun;
} elseif ($filter_periode === 'triwulan' && $filter_triwulan !== '' && $filter_tahun !== '') {
  $periodeJudul = 'Triwulan ' . $filter_triwulan . ' ' . $filter_tahun;
} elseif ($filter_periode === 'tahun' && $filter_tahun !== '') {
  $periodeJudul = 'Tahun ' . $filter_tahun;
} elseif ($filter_tahun !== '') {
  $periodeJudul = 'Tahun ' . $filter_tahun;
}

$judulGrafikIndikator = 'Grafik Kepatuhan APD Berdasarkan Indikator Penilaian ' . $periodeJudul . ' di Rumah Sakit Primaya Bhakti Wara';
$judulGrafikAPD = 'Grafik APD yang Digunakan ' . $periodeJudul . ' di Rumah Sakit Primaya Bhakti Wara';
$judulGrafikProfesi = 'Grafik Kepatuhan APD Berdasarkan Profesi ' . $periodeJudul . ' di Rumah Sakit Primaya Bhakti Wara';
$judulGrafikUnit = 'Grafik Kepatuhan APD Berdasarkan Unit ' . $periodeJudul . ' di Rumah Sakit Primaya Bhakti Wara';
$judulGrafikTren = 'Grafik Tren Kepatuhan APD ' . $periodeJudul . ' di Rumah Sakit Primaya Bhakti Wara';
?>

<style>
  .chart-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-bottom: 12px;
    flex-wrap: wrap;
  }

  .chart-download-btn {
    border: 0;
    border-radius: 12px;
    padding: 10px 14px;
    cursor: pointer;
    font-weight: 800;
    color: #ffffff;
    background: linear-gradient(135deg, #1e40af, #075985);
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.16);
  }

  .chart-download-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.22);
  }

  #tab-grafik-apd .chart-box {
    position: relative;
    height: 560px !important;
    min-height: 560px !important;
    width: 100%;
    background:
      radial-gradient(circle at top left, rgba(59, 130, 246, 0.20), transparent 34%),
      linear-gradient(135deg, #f8fafc 0%, #e0f2fe 48%, #f1f5f9 100%) !important;
    border: 1px solid rgba(30, 64, 175, 0.18) !important;
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12) !important;
  }
</style>

<div id="tab-grafik-apd" class="tab-pane active">
  <div class="section-card">
    <div class="section-title">Filter Grafik Audit APD</div>

    <form method="get">
      <input type="hidden" name="tab" value="tab-grafik">

      <div class="filter-row">
        <select name="periode" class="form-control">
          <option value="">Semua Periode</option>
          <option value="bulan" <?= $filter_periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
          <option value="triwulan" <?= $filter_periode === 'triwulan' ? 'selected' : '' ?>>Per Triwulan</option>
          <option value="tahun" <?= $filter_periode === 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
        </select>

        <select name="bulan" class="form-control">
          <option value="">Semua Bulan</option>
          <?php for ($b = 1; $b <= 12; $b++): ?>
            <option value="<?= $b ?>" <?= (string) $filter_bulan === (string) $b ? 'selected' : '' ?>>
              <?= $b ?>
            </option>
          <?php endfor; ?>
        </select>

        <select name="triwulan" class="form-control">
          <option value="">Semua Triwulan</option>
          <option value="1" <?= (string) $filter_triwulan === '1' ? 'selected' : '' ?>>Triwulan 1</option>
          <option value="2" <?= (string) $filter_triwulan === '2' ? 'selected' : '' ?>>Triwulan 2</option>
          <option value="3" <?= (string) $filter_triwulan === '3' ? 'selected' : '' ?>>Triwulan 3</option>
          <option value="4" <?= (string) $filter_triwulan === '4' ? 'selected' : '' ?>>Triwulan 4</option>
        </select>

        <select name="tahun" class="form-control">
          <option value="">Semua Tahun</option>
          <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
            <option value="<?= $t ?>" <?= (string) $filter_tahun === (string) $t ? 'selected' : '' ?>>
              <?= $t ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="filter-row">
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

        <select name="f_kategori" class="form-control">
          <option value="">Semua Kategori</option>
          <option value="indikator_penilaian" <?= $filter_kategori === 'indikator_penilaian' ? 'selected' : '' ?>>Indikator Penilaian</option>
          <option value="apd_digunakan" <?= $filter_kategori === 'apd_digunakan' ? 'selected' : '' ?>>APD yang Digunakan</option>
        </select>

        <div class="button-row" style="margin-top:0;">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="?tab=tab-grafik" class="btn btn-secondary">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <div class="section-card">
    <div class="section-title">Grafik Kepatuhan Per Indikator Penilaian</div>
    <div class="chart-toolbar">
      <button type="button" class="chart-download-btn" onclick="downloadChart('chartIndikator', 'grafik-apd-indikator')">
        Download Gambar
      </button>
    </div>
    <div class="chart-box">
      <canvas id="chartIndikator"></canvas>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Grafik APD yang Digunakan</div>
    <div class="chart-toolbar">
      <button type="button" class="chart-download-btn" onclick="downloadChart('chartAPD', 'grafik-apd-digunakan')">
        Download Gambar
      </button>
    </div>
    <div class="chart-box">
      <canvas id="chartAPD"></canvas>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Grafik Kepatuhan Per Profesi</div>
    <div class="chart-toolbar">
      <button type="button" class="chart-download-btn" onclick="downloadChart('chartProfesi', 'grafik-apd-profesi')">
        Download Gambar
      </button>
    </div>
    <div class="chart-box">
      <canvas id="chartProfesi"></canvas>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Grafik Kepatuhan Per Unit</div>
    <div class="chart-toolbar">
      <button type="button" class="chart-download-btn" onclick="downloadChart('chartUnit', 'grafik-apd-unit')">
        Download Gambar
      </button>
    </div>
    <div class="chart-box">
      <canvas id="chartUnit"></canvas>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Grafik Tren Kepatuhan Januari - Desember</div>
    <div class="chart-toolbar">
      <button type="button" class="chart-download-btn" onclick="downloadChart('chartTren', 'grafik-apd-tren')">
        Download Gambar
      </button>
    </div>
    <div class="chart-box">
      <canvas id="chartTren"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    const chartBackgroundPlugin = {
      id: 'chartBackgroundPlugin',
      beforeDraw: function (chart) {
        const ctx = chart.ctx;
        const width = chart.width;
        const height = chart.height;

        ctx.save();

        const gradient = ctx.createLinearGradient(0, 0, width, height);
        gradient.addColorStop(0, '#f8fafc');
        gradient.addColorStop(0.45, '#e0f2fe');
        gradient.addColorStop(1, '#f1f5f9');

        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, width, height);

        ctx.fillStyle = 'rgba(30, 64, 175, 0.05)';
        ctx.beginPath();
        ctx.arc(60, 45, 120, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = 'rgba(14, 165, 233, 0.06)';
        ctx.beginPath();
        ctx.arc(width - 80, height - 50, 150, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
      }
    };

    const targetLinePlugin = {
      id: 'targetLinePlugin',
      afterDatasetsDraw: function (chart) {
        const yScale = chart.scales.y;
        const ctx = chart.ctx;

        if (!yScale) return;

        const y = yScale.getPixelForValue(100);
        const leftX = chart.chartArea.left;
        const rightX = chart.chartArea.right;

        ctx.save();

        ctx.beginPath();
        ctx.setLineDash([8, 6]);
        ctx.moveTo(leftX, y);
        ctx.lineTo(rightX, y);
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#dc2626';
        ctx.stroke();

        ctx.setLineDash([]);
        ctx.fillStyle = '#dc2626';
        ctx.font = 'bold 12px Arial';
        ctx.textAlign = 'right';
        ctx.fillText('Target 100%', rightX - 6, y - 8);

        ctx.restore();
      }
    };

    const percentLabelPlugin = {
      id: 'percentLabelPlugin',
      afterDatasetsDraw: function (chart) {
        const ctx = chart.ctx;

        chart.data.datasets.forEach(function (dataset, datasetIndex) {
          const meta = chart.getDatasetMeta(datasetIndex);

          meta.data.forEach(function (element, index) {
            const value = dataset.data[index];

            if (value === null || value === undefined) return;

            const position = element.tooltipPosition();
            let yPos = position.y - 10;

            if (value >= 95) {
              yPos = position.y - 18;
            }

            if (yPos < chart.chartArea.top + 18) {
              yPos = chart.chartArea.top + 18;
            }

            ctx.save();
            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.fillText(value + '%', position.x, yPos);
            ctx.restore();
          });
        });
      }
    };

    Chart.register(chartBackgroundPlugin, targetLinePlugin, percentLabelPlugin);

    function getChartOptions(chartTitle) {
      return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 800
        },
        layout: {
          padding: {
            top: 58,
            right: 24,
            bottom: 12,
            left: 8
          }
        },
        scales: {
          x: {
            ticks: {
              color: '#0f172a',
              font: { weight: 'bold' }
            },
            grid: { color: 'rgba(148, 163, 184, 0.22)' }
          },
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              color: '#0f172a',
              font: { weight: 'bold' },
              callback: function (value) {
                return value + '%';
              }
            },
            grid: { color: 'rgba(100, 116, 139, 0.24)' }
          }
        },
        plugins: {
          title: {
            display: true,
            text: chartTitle,
            color: '#0f172a',
            font: { size: 16, weight: 'bold' },
            padding: { top: 6, bottom: 18 }
          },
          legend: {
            position: 'top',
            align: 'center',
            labels: {
              color: '#0f172a',
              boxWidth: 36,
              padding: 22,
              font: { weight: 'bold' }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.92)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            callbacks: {
              label: function (context) {
                return context.dataset.label + ': ' + context.parsed.y + '%';
              }
            }
          }
        }
      };
    }

    function warnaBar(values) {
      return values.map(function (value) {
        return value >= 100 ? 'rgba(22, 163, 74, 0.82)' : 'rgba(220, 38, 38, 0.82)';
      });
    }

    function warnaBorder(values) {
      return values.map(function (value) {
        return value >= 100 ? 'rgba(21, 128, 61, 1)' : 'rgba(185, 28, 28, 1)';
      });
    }

    const dataIndikator = <?= json_encode($grafikIndikatorValue) ?>;
    const dataAPD = <?= json_encode($grafikApdValue) ?>;
    const dataProfesi = <?= json_encode($grafikProfesiValue) ?>;
    const dataUnit = <?= json_encode($grafikUnitValue) ?>;
    const dataTren = <?= json_encode($grafikTrenValue) ?>;

    const chartIndikator = document.getElementById('chartIndikator');
    if (chartIndikator) {
      new Chart(chartIndikator, {
        type: 'bar',
        data: {
          labels: <?= json_encode($grafikIndikatorLabel) ?>,
          datasets: [{
            label: 'Kepatuhan (%)',
            data: dataIndikator,
            backgroundColor: warnaBar(dataIndikator),
            borderColor: warnaBorder(dataIndikator),
            borderWidth: 1.5,
            borderRadius: 8
          }]
        },
        options: getChartOptions(<?= json_encode($judulGrafikIndikator) ?>)
      });
    }

    const chartAPD = document.getElementById('chartAPD');
    if (chartAPD) {
      new Chart(chartAPD, {
        type: 'bar',
        data: {
          labels: <?= json_encode($grafikApdLabel) ?>,
          datasets: [{
            label: 'Ya (%)',
            data: dataAPD,
            backgroundColor: warnaBar(dataAPD),
            borderColor: warnaBorder(dataAPD),
            borderWidth: 1.5,
            borderRadius: 8
          }]
        },
        options: getChartOptions(<?= json_encode($judulGrafikAPD) ?>)
      });
    }

    const chartProfesi = document.getElementById('chartProfesi');
    if (chartProfesi) {
      new Chart(chartProfesi, {
        type: 'bar',
        data: {
          labels: <?= json_encode($grafikProfesiLabel) ?>,
          datasets: [{
            label: 'Kepatuhan (%)',
            data: dataProfesi,
            backgroundColor: warnaBar(dataProfesi),
            borderColor: warnaBorder(dataProfesi),
            borderWidth: 1.5,
            borderRadius: 8
          }]
        },
        options: getChartOptions(<?= json_encode($judulGrafikProfesi) ?>)
      });
    }

    const chartUnit = document.getElementById('chartUnit');
    if (chartUnit) {
      new Chart(chartUnit, {
        type: 'bar',
        data: {
          labels: <?= json_encode($grafikUnitLabel) ?>,
          datasets: [{
            label: 'Kepatuhan (%)',
            data: dataUnit,
            backgroundColor: warnaBar(dataUnit),
            borderColor: warnaBorder(dataUnit),
            borderWidth: 1.5,
            borderRadius: 8
          }]
        },
        options: getChartOptions(<?= json_encode($judulGrafikUnit) ?>)
      });
    }

    const chartTren = document.getElementById('chartTren');
    if (chartTren) {
      new Chart(chartTren, {
        type: 'line',
        data: {
          labels: <?= json_encode($grafikTrenLabel) ?>,
          datasets: [{
            label: 'Tren Kepatuhan (%)',
            data: dataTren,
            borderColor: '#1e40af',
            backgroundColor: 'rgba(30, 64, 175, 0.16)',
            pointBackgroundColor: warnaBar(dataTren),
            pointBorderColor: warnaBorder(dataTren),
            pointRadius: 5,
            pointHoverRadius: 7,
            borderWidth: 3,
            tension: 0.35,
            fill: true
          }]
        },
        options: getChartOptions(<?= json_encode($judulGrafikTren) ?>)
      });
    }

    function downloadChart(canvasId, fileName) {
      const canvas = document.getElementById(canvasId);
      if (!canvas) return;

      const imageUrl = canvas.toDataURL('image/png', 1.0);
      const link = document.createElement('a');
      link.href = imageUrl;
      link.download = fileName + '.png';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>
</div>
