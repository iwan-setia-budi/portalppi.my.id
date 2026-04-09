<?php
require_once __DIR__ . '/../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: " . base_url('login.php'));
    exit();
}
?>

<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "Audit External";
?>
<!--end-->


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3.0" />
    <title>Audit External</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        /* ================================
   ADAPTASI LAYOUT PORTAL
================================ */
        main {
            background: #f6f8fc;
            margin: 0;
            padding: calc(var(--topbar-height) + 20px) 0 0 0;
        }

        .main-content {
            padding: 0px 25px 15px 25px;
        }

        /* WRAPPER PUSAT */
        .audit-container {
            padding: 25px 20px;
            width: 100%;
        }

        /* ================================
   TITLE
================================ */
        .title {
            font-size: 32px;
            font-weight: 800;
            color: #004c94;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 15px;
            color: #607a92;
            text-align: center;
            margin-bottom: 35px;
        }

        .breadcrumb {
            margin: 0 10px 10px 0;
            display: block;
        }

        .audit-card a,
        .audit-card:link,
        .audit-card:visited {
            text-decoration: none;
            color: inherit;
        }

        /* ================================
   GRID CARD
================================ */
        .audit-grid {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }




        /* ================================
   CARD
================================ */
        .audit-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 6px 18px rgba(40, 80, 140, .1);
            border: 1px solid #e5e9f0;
            text-align: center;
            cursor: pointer;
            transition: .25s ease;
        }

        .audit-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 28px rgba(40, 80, 160, .18);
        }

        .audit-card {
            font-size: 46px;
            margin-bottom: 12px;
        }

        .icon-audit {
            width: 100px;
            height: 100px;
            border-radius: 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 60px;

            background: linear-gradient(135deg, #e0f2ff, #c7e7ff);
            box-shadow: 0 8px 18px rgba(0, 0, 0, .08);

            margin: 0 auto 16px auto;
            /* center dan beri jarak ke bawah */
        }

        .audit-card h3 {
            font-size: 21px;
            margin-bottom: 6px;
            font-weight: 700;
            color: #1a355c;
        }

        .audit-card p {
            font-size: 14px;
            color: #607a92;
        }

        /* ================================
   RESPONSIVE
================================ */
        @media (max-width: 600px) {
            .audit-container {
                padding: 15px;
            }
        }

        /* ================================
   DARK MODE - PREMIUM
================================ */
        body.dark-mode main {
            background: #0f1927;
        }

        body.dark-mode .main-content {
            background: #0f1927;
        }

        body.dark-mode .audit-container {
            background: #0f1927;
        }

        body.dark-mode .title {
            color: #60a5fa;
            text-shadow: 0 0 20px rgba(96, 165, 250, 0.3);
        }

        body.dark-mode .subtitle {
            color: #9fb2c9;
        }

        body.dark-mode .breadcrumb {
            color: #cbd5e1;
            background: #16253a !important;
            border-color: rgba(59, 130, 246, 0.25) !important;
        }

        body.dark-mode .audit-grid {
            gap: 24px;
        }

        body.dark-mode .audit-card {
            background: linear-gradient(145deg, #1a2f48, #1e3a54);
            border: 1.5px solid rgba(59, 130, 246, 0.4);
            box-shadow: 0 6px 18px rgba(59, 130, 246, 0.15), inset 0 0 15px rgba(59, 130, 246, 0.08);
            color: #e2e8f0;
            transition: 0.25s ease;
        }

        body.dark-mode .audit-card:hover {
            background: linear-gradient(145deg, #1f3d54, #2a4a6a);
            border-color: rgba(59, 130, 246, 0.6);
            transform: translateY(-6px);
            box-shadow: 0 10px 28px rgba(59, 130, 246, 0.3), inset 0 0 20px rgba(59, 130, 246, 0.12);
        }

        body.dark-mode .icon-audit {
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.2), rgba(37, 99, 235, 0.15));
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 8px 18px rgba(59, 130, 246, 0.2), inset 0 0 15px rgba(59, 130, 246, 0.1);
            color: #93c5fd;
        }

        body.dark-mode .audit-card h3 {
            color: #e2e8f0;
        }

        body.dark-mode .audit-card p {
            color: #9fb2c9;
        }
    </style>

</head>

<body>

    <div class="layout">

        <!-- Link ke Sidebar -->
        <?php include_once '../sidebar.php'; ?>


        <main>

            <!-- Link Ke topbar -->
            <?php include_once '../topbar.php'; ?>

            <div class="main-content">

                <div class="audit-container">

                    <h1 class="title">📋 Audit Eksternal Rumah Sakit</h1>
                    <p class="subtitle">Silakan pilih jenis audit eksternal yang ingin dilakukan.</p>

                    <div class="audit-grid">

                        <a href="audit_laundry.php" class="audit-card">
                            <div class="icon-audit">🧺</div>
                            <h3>Audit Laundry</h3>
                            <p>Pemeriksaan proses pengelolaan linen & kebersihan.</p>
                        </a>

                        <a href="audit_limbah.php" class="audit-card">
                            <div class="icon-audit">♻️️</div>
                            <h3>Audit Limbah</h3>
                            <p>Pemeriksaan pengelolaan limbah medis & non-medis.</p>
                        </a>

                        <a href="audit_gizi.php" class="audit-card">
                            <div class="icon-audit">🍽️️</div>
                            <h3>Audit Pelayanan Gizi</h3>
                            <p>Pemeriksaan mutu pelayanan gizi & dapur RS.</p>
                        </a>

                        <a href="audit_lainnya.php" class="audit-card">
                            <div class="icon-audit">📋</div>
                            <h3>Audit Lainnya</h3>
                            <p>Pemeriksaan tambahan terkait layanan laundry eksternal.</p>
                        </a>

                    </div>
                </div>

            </div>
        </main>

    </div>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>

</body>

</html>