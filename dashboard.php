<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['username'])) {
  header("Location: " . base_url('login.php'));
  exit();
}
?>

<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "Dashboard";
?>
<!--end-->

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3.0" />
  <title>Dashboard PPI PHBW</title>
<link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon.png">

  <!-- === Link CSS eksternal === -->
  <link rel="stylesheet" href="/assets/css/utama.css?v=18">
    <link rel="stylesheet" href="/assets/css/dashboard.css?v=12">


</head>

<body>
  <div class="layout">

    <!-- Link ke Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <main>

      <!-- Link Ke topbar -->
      <?php include_once 'topbar.php'; ?>

      <!-- Isi Konten -->
      <div class="container">
        <h2>Halo, <span style="color:var(--brand)">Administrator</span> 👋</h2>

        <section class="grid">
          <div class="card">
            <div class="kpi">
              <div class="icon" style="background:#eaf4ff;color:var(--brand)">🖐️</div>
              <div class="meta">
                <div class="label">Kepatuhan Cuci Tangan</div>
                <div class="val">92.5%</div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="kpi">
              <div class="icon" style="background:#fff7ed;color:#f59e0b">🧤</div>
              <div class="meta">
                <div class="label">Kepatuhan APD</div>
                <div class="val">98%</div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="kpi">
              <div class="icon" style="background:#fef2f2;color:#dc2626">⚕️</div>
              <div class="meta">
                <div class="label">Rate HAI</div>
                <div class="val">1.7</div>
              </div>
            </div>
          </div>
        </section>

        <section class="grid">
          <div class="card">
            <h3>📊 Audit Terakhir</h3>
            <table>
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Unit</th>
                  <th>Jenis</th>
                  <th>%</th>
                  <th>Auditor</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>2025-08-12</td>
                  <td>ICU</td>
                  <td>Cuci Tangan</td>
                  <td>92%</td>
                  <td>IPCN A</td>
                </tr>
                <tr>
                  <td>2025-08-10</td>
                  <td>OK</td>
                  <td>APD</td>
                  <td>98%</td>
                  <td>IPCN B</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="card">
            <h3>📘 Regulasi Terbaru</h3>
            <a href="#">📄 SPO Cuci Tangan v3</a>
            <a href="#">📄 Panduan APD 2025</a>
            <a href="#">📄 Pedoman PPI 2025</a>
          </div>
        </section>
      </div>

    </main>

</div>
    <script src="/assets/js/utama.js?v=6"></script>

</body>

</html>