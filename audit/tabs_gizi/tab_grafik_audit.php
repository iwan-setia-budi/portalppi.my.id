<?php
$labelGrafik = [];
$dataGrafik = [];
$qGrafik = mysqli_query($conn, "
  SELECT
    d.kode_bagian,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum
  FROM audit_gizi_detail d
  GROUP BY d.kode_bagian
  ORDER BY d.kode_bagian ASC
");
while ($row = mysqli_fetch_assoc($qGrafik)) {
  $num = (int) ($row['num'] ?? 0);
  $den = (int) ($row['denum'] ?? 0);
  $labelGrafik[] = $row['kode_bagian'] ?? '-';
  $dataGrafik[] = $den > 0 ? round(($num / $den) * 100, 2) : 0;
}
?>
<div id="tab-grafik" class="tab-pane active">
  <div class="section-card">
    <h3>Grafik Kepatuhan per Bagian</h3>
    <canvas id="chartGizi" height="120"></canvas>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  (function () {
    const el = document.getElementById('chartGizi');
    if (!el) return;
    new Chart(el, {
      type: 'bar',
      data: {
        labels: <?= json_encode($labelGrafik) ?>,
        datasets: [{
          label: 'Kepatuhan (%)',
          data: <?= json_encode($dataGrafik) ?>,
          backgroundColor: 'rgba(37, 99, 235, 0.75)',
          borderColor: 'rgba(30, 64, 175, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
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
  })();
</script>
