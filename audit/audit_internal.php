<?php
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
$pageTitle = "AUDIT DAN SUPERVISI";
?>
<!--end-->



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Internal</title>
    <link rel="stylesheet" href="/assets/css/utama.css?v=15">

    <!-- === Link CSS eksternal === -->
    <style>
        /* =========================================
   BACKGROUND AREA
========================================= */
        .container-supervise {
            padding: 30px 40px;
            border-radius: 24px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .06);
        }

        /* =========================================
   HEADER SECTION
========================================= */
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


        /* Tombol elegan */
        .dashboard-btn {
            background: white;
            color: var(--blue-2);
            border: none;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .dashboard-btn:hover {
            transform: translateY(-2px);
        }

        /* =========================================
   WRAPPER MAIN BOX
========================================= */
        .audit-wrapper {

            padding: 20px;
            border-radius: 20px;
            background: linear-gradient(145deg, #ffffff, #f1f5f9);
            box-shadow:
                0 24px 46px rgba(15, 23, 42, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(6px);
        }

        /* =========================================
   TITLES
========================================= */
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

        /* =========================================
   GRID SYSTEM
========================================= */
        .audit-grid {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }



        /* =========================================
   CARD TITLE
========================================= */
        .main-title {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 18px;
            color: #0f172a;
        }

        .audit-item {
            display: flex;
            justify-content: space-between;
            align-items: center;

            padding: 14px 18px;
            border-radius: 14px;

            background: #f8fafc;
            border: 1px solid #e2e8f0;

            font-size: 14px;
            font-weight: 500;
            color: #1e293b;

            transition: .25s ease;
        }

        .audit-item:hover {
            background: white;
            border-color: #2563eb;
            box-shadow: 0 8px 20px rgba(37, 99, 235, .15);
            transform: translateX(6px);
        }

        .audit-item .arrow {
            font-size: 16px;
            color: #94a3b8;
            transition: .2s;
        }

        .audit-item:hover .arrow {
            transform: translateX(4px);
            color: #2563eb;
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

        .audit-wrapper {
            margin-top: 10px;
        }

        .audit-grid {
            margin-top: 30px;
        }


        .internal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
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


        /* =========================================
   LIST STYLE
========================================= */
        .audit-list {
            list-style: none;
            padding: 0;
            margin: 0;

            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
        }







        /* =========================================
   RESPONSIVE
========================================= */
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

            <div class="container-supervise">

                <div class="page-hero">
                    <div>
                        <h1>Audit Internal PPI</h1>
                        <small>Manajemen audit mutu dan keselamatan pasien</small>
                    </div>
                    <button class="hero-btn" onclick="kembaliDashboard()">🏠 Dashboard</button>
                </div>


                <div class="audit-wrapper">

                    <h1 class="audit-title">📋 Audit Internal Rumah Sakit</h1>
                    <p class="audit-subtitle">Pilih kategori audit internal yang ingin Anda buka.</p>
                </div>


                <div class="audit-grid">

                    <!-- CARD 1 -->
                    <div class="audit-card">
                        <div class="card-label">Kategori Utama</div>
                        <h2 class="main-title">Audit Kewaspadaan Isolasi</h2>



                        <div class="internal-grid">

                            <a href="https://linktr.ee/fasilitas1234" class="internal-card">
                                <div class="internal-icon">🧼</div>
                                <h4>Kebersihan Tangan</h4>
                            </a>

                            <a href="https://linktr.ee/fasilitas1234" class="internal-card">
                                <div class="internal-icon">🧤</div>
                                <h4>Audit APD</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🏥</div>
                                <h4>Audit Unit</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">😷</div>
                                <h4>Etika Batuk</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🩺</div>
                                <h4>Praktek Lumbal Fungsi</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🧴</div>
                                <h4>Audit CSSD</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🍽️</div>
                                <h4>Audit Gizi</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">⚰️</div>
                                <h4>Kamar Jenazah</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">♻️</div>
                                <h4>Audit TPS</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🚑</div>
                                <h4>Audit Ambulance</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🦠</div>
                                <h4>Kewaspadaan Transmisi</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🏨</div>
                                <h4>Ruang Isolasi</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">😷</div>
                                <h4>Universal Masking</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🎯</div>
                                <h4>Targeted Mask Use</h4>
                            </a>
                        </div>

                    </div>

                    <!-- CARD 2 -->
                    <div class="audit-card">
                        <div class="card-label">Bundle HAIs</div>
                        <h2 class="main-title">Audit Bundle HAIs</h2>

                        <div class="internal-grid">

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🫁</div>
                                <h4>Bundle VAP</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🩸</div>
                                <h4>Bundle IADP</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🚻</div>
                                <h4>Bundle ISK</h4>
                            </a>

                            <a href="https://myppi.primaya.id/" class="internal-card">
                                <div class="internal-icon">🦠</div>
                                <h4>Bundle IDO</h4>
                            </a>

                        </div>



                    </div>


                    <!-- CARD 3 -->
                    <div class="audit-card">
                        <div class="card-label">Hasil Audit</div>
                        <h2 class="main-title">Audit Cuci Tangan dan APD</h2>

                        <div class="internal-grid">

                            <a href="https://linktr.ee/hasilau" target="_blank" rel="noopener noreferrer"
                                class="internal-card">
                                <div class="internal-icon">🧼</div>
                                <h4>Kebersihan Tangan</h4>
                            </a>

                            <a href="https://linktr.ee/hasilau" target="_blank" rel="noopener noreferrer"
                                class="internal-card">
                                <div class="internal-icon">😷</div>
                                <h4>APD</h4>
                            </a>


                        </div>



                    </div>



                </div>


            </div>
        </main>
    </div>

    <script>
        function kembaliDashboard() {
            window.location.href = "/dashboard.php";
        }
    </script>

    <script src="/assets/js/utama.js?v=5"></script>


</body>

</html>