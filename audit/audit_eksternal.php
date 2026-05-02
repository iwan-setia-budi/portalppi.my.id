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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit External</title>
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        .container-supervise {
            padding: 30px 40px;
            border-radius: 24px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .06);
        }

        .audit-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .09);
            transition: .3s ease;
        }

        .audit-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 54px rgba(15, 23, 42, .15);
        }

        .card-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            margin-bottom: 6px;
        }

        .audit-wrapper {
            padding: 20px;
            border-radius: 20px;
            background: linear-gradient(145deg, #ffffff, #f1f5f9);
            box-shadow:
                0 24px 46px rgba(15, 23, 42, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(6px);
            margin-top: 10px;
        }

        .audit-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, #1e40af, #2563eb);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .audit-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .audit-grid {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-top: 30px;
        }

        .main-title {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 18px;
            color: #0f172a;
        }

        .page-hero {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            padding: 28px 32px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 50px rgba(37, 99, 235, .25);
        }

        .page-hero h1 {
            font-size: 22px;
            font-weight: 600;
            color: white;
            margin: 0;
        }

        .page-hero small {
            display: block;
            opacity: .8;
            font-size: 13px;
            margin-top: 4px;
            color: white;
        }

        .hero-btn {
            background: white;
            color: #1e3a8a;
            border: none;
            padding: 10px 18px;
            font-weight: 600;
            border-radius: 999px;
            cursor: pointer;
            transition: .2s;
        }

        .hero-btn:hover {
            transform: translateY(-3px);
        }

        .internal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-top: 20px;
        }

        .internal-card {
            background: linear-gradient(145deg, #f0f9ff, #e0f2fe);
            border: 2px solid #9fc1ea;
            border-radius: 18px;
            padding: 22px 16px;
            text-align: center;
            text-decoration: none;
            color: #1e293b;
            transition: .3s cubic-bezier(.4, 0, .2, 1);
            box-shadow:
                0 12px 24px rgba(30, 64, 175, .12),
                inset 0 0 0 1px rgba(255, 255, 255, .6);
        }

        .internal-card:hover {
            background: linear-gradient(145deg, #ffffff, #f1f5f9);
            border-color: #1d4ed8;
            box-shadow: 0 18px 40px rgba(37, 99, 235, .24);
            transform: translateY(-6px);
        }

        .internal-card:active {
            transform: scale(0.97);
        }

        .internal-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 12px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            box-shadow: 0 10px 22px rgba(37, 99, 235, .18);
        }

        .internal-card h4 {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }

        .internal-desc {
            margin: 8px 0 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.45;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container-supervise {
                padding: 20px;
            }

            .audit-wrapper {
                padding: 10px;
            }

            .internal-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .internal-icon {
                width: 70px;
                height: 70px;
                font-size: 36px;
            }

            .internal-card {
                padding: 16px 10px;
            }

            .page-hero {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .hero-btn {
                align-self: stretch;
                text-align: center;
            }
        }

        body.dark-mode .container-supervise {
            background: #0f1927;
            box-shadow: 0 16px 36px rgba(2, 6, 23, 0.4);
        }

        body.dark-mode .audit-card {
            background: #1a2a40;
            border: 1.5px solid rgba(59, 130, 246, 0.4);
            box-shadow: 0 18px 38px rgba(2, 6, 23, 0.35), inset 0 0 20px rgba(59, 130, 246, 0.08);
            color: #e2e8f0;
        }

        body.dark-mode .audit-card:hover {
            border-color: rgba(59, 130, 246, 0.6);
            box-shadow: 0 28px 54px rgba(59, 130, 246, 0.2), inset 0 0 20px rgba(59, 130, 246, 0.12);
        }

        body.dark-mode .card-label {
            color: #9fb2c9;
        }

        body.dark-mode .audit-wrapper {
            background: linear-gradient(145deg, rgba(20, 35, 58, 0.6), rgba(17, 31, 51, 0.6));
            border: 1px solid rgba(59, 130, 246, 0.25);
            box-shadow: 0 24px 46px rgba(2, 6, 23, 0.4), inset 0 0 20px rgba(59, 130, 246, 0.1);
        }

        body.dark-mode .audit-title {
            background: linear-gradient(to right, #60a5fa, #93c5fd);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        body.dark-mode .audit-subtitle {
            color: #9fb2c9;
        }

        body.dark-mode .main-title {
            color: #e2e8f0;
        }

        body.dark-mode .page-hero {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.35);
        }

        body.dark-mode .hero-btn {
            background: linear-gradient(135deg, #ffffff, #e5ebf3);
            color: #0b1220;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        body.dark-mode .hero-btn:hover {
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            color: #020617;
        }

        body.dark-mode .internal-card {
            background: linear-gradient(145deg, #1a2f48, #1e3a54);
            border: 1.5px solid rgba(59, 130, 246, 0.4);
            color: #e2e8f0;
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.15), inset 0 0 15px rgba(59, 130, 246, 0.08);
        }

        body.dark-mode .internal-card:hover {
            background: linear-gradient(145deg, #1f3d54, #2a4a6a);
            border-color: rgba(59, 130, 246, 0.6);
            box-shadow: 0 18px 40px rgba(59, 130, 246, 0.3), inset 0 0 20px rgba(59, 130, 246, 0.12);
        }

        body.dark-mode .internal-icon {
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.2), rgba(37, 99, 235, 0.15));
            box-shadow: 0 10px 22px rgba(59, 130, 246, 0.25), inset 0 0 15px rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        body.dark-mode .internal-card h4 {
            color: #e2e8f0;
        }

        body.dark-mode .internal-desc {
            color: #9fb2c9;
        }

        body.dark-mode .breadcrumb {
            background: #16253a !important;
            border-color: rgba(59, 130, 246, 0.25) !important;
            color: #cbd5e1 !important;
        }
    </style>

</head>

<body>

    <div class="layout">

        <?php include_once '../sidebar.php'; ?>

        <main>

            <?php include_once '../topbar.php'; ?>

            <div class="container-supervise">

                <div class="page-hero">
                    <div>
                        <h1>Audit Eksternal PPI</h1>
                        <small>Evaluasi mutu layanan & kepatuhan dari perspektif audit eksternal</small>
                    </div>
                    <button type="button" class="hero-btn" onclick="kembaliDashboard()">🏠 Dashboard</button>
                </div>

                <div class="audit-wrapper">
                    <h1 class="audit-title">Audit eksternal rumah sakit</h1>
                    <p class="audit-subtitle">Pilih jenis audit eksternal yang ingin Anda buka.</p>
                </div>

                <div class="audit-grid">

                    <div class="audit-card">
                        <div class="card-label">Kategori audit</div>
                        <h2 class="main-title">Jenis pemeriksaan</h2>

                        <div class="internal-grid">

                            <a href="audit_laundry.php" class="internal-card">
                                <div class="internal-icon">🧺</div>
                                <h4>Audit laundry</h4>
                                <p class="internal-desc">Pemeriksaan pengelolaan linen & kebersihan.</p>
                            </a>

                            <a href="audit_limbah.php" class="internal-card">
                                <div class="internal-icon">♻️</div>
                                <h4>Audit limbah</h4>
                                <p class="internal-desc">Pengelolaan limbah medis & non-medis.</p>
                            </a>

                            <a href="audit_gizi.php" class="internal-card">
                                <div class="internal-icon">🍽️</div>
                                <h4>Audit pelayanan gizi</h4>
                                <p class="internal-desc">Mutu pelayanan gizi & dapur rumah sakit.</p>
                            </a>

                            <a href="audit_lainnya.php" class="internal-card">
                                <div class="internal-icon">📋</div>
                                <h4>Audit lainnya</h4>
                                <p class="internal-desc">Pemeriksaan tambahan terkait layanan eksternal.</p>
                            </a>

                        </div>
                    </div>

                </div>

            </div>

        </main>

    </div>

    <script>
        function kembaliDashboard() {
            window.location.href = "<?= base_url('dashboard.php') ?>";
        }
    </script>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>

</body>

</html>
