<div id="tab-grafik" class="tab-pane active">
  <div class="section-card">
    <div class="section-title">Grafik Kepatuhan Per Profesi</div>
    <div class="chart-box">
      <canvas id="chartProfesi"></canvas>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Grafik Kepatuhan Per Unit</div>
    <div class="chart-box">
      <canvas id="chartUnit"></canvas>
    </div>
  </div>

  <div class="section-card">
    <div class="section-title">Grafik Kepatuhan Per Moment</div>
    <div class="chart-box">
      <canvas id="chartMoment"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const chartProfesi = document.getElementById('chartProfesi');
    if (chartProfesi) {
      new Chart(chartProfesi, {
        type: 'bar',
        data: {
          labels: <?= json_encode($grafikProfesiLabel) ?>,
          datasets: [{
            label: 'Kepatuhan (%)',
            data: <?= json_encode($grafikProfesiValue) ?>,
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
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
            data: <?= json_encode($grafikUnitValue) ?>,
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
      });
    }

    const chartMoment = document.getElementById('chartMoment');
    if (chartMoment) {
      new Chart(chartMoment, {
        type: 'bar',
        data: {
          labels: <?= json_encode($grafikMomentLabel) ?>,
          datasets: [{
            label: 'Kepatuhan (%)',
            data: <?= json_encode($grafikMomentValue) ?>,
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
      });
    }
  </script>
</div>