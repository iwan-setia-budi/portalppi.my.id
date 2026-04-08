<?php
require_once __DIR__ . '/../config/assets.php'; include_once '../header.php'; ?>
<?php include_once '../sidebar.php'; ?>

<link rel="stylesheet" href="<?= asset('assets/css/dashboard.css') ?>">


<main>
  <!-- === Header Topbar === -->
  <div class="topbar">
    <button class="hamb" id="toggleSidebar">☰</button>
    <div class="topbar-title">
      <span class="emoji">📈</span> Dashboard
    </div>
    <div class="periode-badge">Periode: Agustus 2025</div>
  </div>

  <!-- === Main Content === -->
  <div class="container fade-in">
    <h2 class="greeting">
      Halo, <span class="highlight">Administrator</span> 👋
    </h2>
    <p class="subtext">Selamat datang di panel monitoring PPI Rumah Sakit</p>

    <!-- === KPI Section === -->
    <section class="grid kpi-grid">
      <div class="card kpi-card">
        <div class="kpi">
          <div class="icon blue">🖐️</div>
          <div class="meta">
            <div class="label">Kepatuhan Cuci Tangan</div>
            <div class="val">92.5%</div>
          </div>
        </div>
      </div>
      <div class="card kpi-card">
        <div class="kpi">
          <div class="icon yellow">🧤</div>
          <div class="meta">
            <div class="label">Kepatuhan APD</div>
            <div class="val">88%</div>
          </div>
        </div>
      </div>
      <div class="card kpi-card">
        <div class="kpi">
          <div class="icon red">⚕️</div>
          <div class="meta">
            <div class="label">Rate HAI</div>
            <div class="val">1.7</div>
          </div>
        </div>
      </div>
    </section>

    <!-- === Table & Regulation Section === -->
    <section class="grid content-grid">
      <div class="card">
        <h3>📊 Audit Terakhir</h3>
        <table class="modern-table">
          <thead>
            <tr>
              <th>Tanggal</th><th>Unit</th><th>Jenis</th><th>%</th><th>Auditor</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>2025-08-12</td><td>ICU</td><td>Cuci Tangan</td><td>92%</td><td>IPCN A</td></tr>
            <tr><td>2025-08-10</td><td>OK</td><td>APD</td><td>88%</td><td>IPCN B</td></tr>
          </tbody>
        </table>
      </div>

      <div class="card">
        <h3>📘 Regulasi Terbaru</h3>
        <ul class="regulasi-list">
          <li><a href="#">📄 SPO Cuci Tangan v3</a></li>
          <li><a href="#">📄 Panduan APD 2025</a></li>
          <li><a href="#">📄 Pedoman PPI 2025</a></li>
        </ul>
      </div>
    </section>
  </div>
</main>


<?php include_once '../footer.php'; ?>
