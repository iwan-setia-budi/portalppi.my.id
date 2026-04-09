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


<?php $pageTitle = "LAPORAN"; ?>



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <!-- === Link CSS eksternal === -->
    <style>

/* =========================================
   BACKGROUND AREA
========================================= */
        .container-lap {
            padding: 30px 40px;
        }

/* =========================================
   HEADER SECTION
========================================= */
        .lap-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .06);
            transition: .3s ease;
        }

        .lap-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, .1);
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
        .lap-wrapper {
            
            padding: 20px;
            border-radius: 20px;
            background: linear-gradient(145deg, #ffffff, #f1f5f9);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(6px);
        }

/* =========================================
   TITLES
========================================= */
        .lap-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, #1e40af, #2563eb);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .lap-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 10px;
        }

/* =========================================
   GRID SYSTEM
========================================= */
        .lap-grid {
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

        .lap-item {
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

        .lap-item:hover {
            background: white;
            border-color: #2563eb;
            box-shadow: 0 8px 20px rgba(37, 99, 235, .15);
            transform: translateX(6px);
        }

        .lap-item .arrow {
            font-size: 16px;
            color: #94a3b8;
            transition: .2s;
        }

        .lap-item:hover .arrow {
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

        .lap-wrapper {
            margin-top: 10px;
        }

        .lap-grid {
            margin-top: 30px;
        }


        .internal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 18px;
            margin-top: 20px;
        }

    .internal-card{
        background: linear-gradient(145deg,#f0f9ff,#e0f2fe);
        border: 5px solid #dbeafe;
        border-radius:18px;
    
        padding:22px 16px;
        text-align:center;
        text-decoration:none;
        color:#1e293b;
    
        transition:.3s cubic-bezier(.4,0,.2,1);
    }
    
    .internal-card:hover{
            background: linear-gradient(145deg,#ffffff,#f1f5f9);
        border-color:#2563eb;
        box-shadow:0 15px 35px rgba(37,99,235,.18);
        transform:translateY(-6px);
    }
    
    
    .internal-card:active {
        transform: scale(0.97);
    }

        /* Coming soon - file belum tersedia */
        .internal-card.coming-soon {
            opacity: 0.55;
            position: relative;
            cursor: not-allowed;
            pointer-events: none;
            filter: grayscale(40%);
        }

        .internal-card.coming-soon::after {
            content: 'Segera';
            position: absolute;
            top: 8px;
            right: 8px;
            background: #f59e0b;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            letter-spacing: 0.04em;
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
        }

        .internal-card h4 {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }


/* =========================================
   LIST STYLE
========================================= */
        .lap-list {
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
            .container-lap {
                padding: 20px;
            }

            .lap-wrapper {
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
    /* ===== DARK MODE: PREMIUM ===== */
    body.dark-mode .container-lap {
        background: radial-gradient(circle at 8% -10%, rgba(59, 130, 246, .14), transparent 35%);
    }

    body.dark-mode .lap-wrapper,
    body.dark-mode .lap-card {
        background: linear-gradient(170deg, #16263b, #1b2d45);
        border: 1.5px solid rgba(59, 130, 246, .32);
        box-shadow: 0 14px 34px rgba(2, 6, 23, .36), inset 0 0 18px rgba(59, 130, 246, .08);
        color: #e2e8f0;
    }

    body.dark-mode .page-hero {
        box-shadow: 0 20px 48px rgba(2, 6, 23, .45);
    }

    body.dark-mode .hero-btn {
        background: linear-gradient(135deg, #ffffff, #eef2f7);
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, .45);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .2);
    }
    body.dark-mode .hero-btn:hover {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        color: #020617;
    }

    body.dark-mode .card-label {
        color: #9fb2c9;
    }

    body.dark-mode .main-title,
    body.dark-mode .lap-title,
    body.dark-mode .lap-subtitle {
        color: #e2e8f0;
    }

    body.dark-mode .lap-title {
        background: none !important;
        background-clip: initial !important;
        -webkit-background-clip: initial !important;
        -webkit-text-fill-color: #e2e8f0 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .internal-grid {
        background: transparent;
    }

    body.dark-mode .internal-card,
    body.dark-mode .internal-card:nth-child(3n+1),
    body.dark-mode .internal-card:nth-child(3n+2),
    body.dark-mode .internal-card:nth-child(3n) {
        background: linear-gradient(155deg, #17293f 0%, #1d314c 100%);
        border: 1.5px solid rgba(96, 165, 250, .34);
        color: #e2e8f0;
        box-shadow: 0 10px 22px rgba(2, 6, 23, .34), inset 0 0 12px rgba(59, 130, 246, .07);
    }

    body.dark-mode .internal-card:hover {
        background: linear-gradient(155deg, #1b3653 0%, #244060 100%);
        border-color: rgba(125, 211, 252, .74);
        box-shadow: 0 16px 30px rgba(59, 130, 246, .26), inset 0 0 16px rgba(96, 165, 250, .12);
    }

    body.dark-mode .internal-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, .2), rgba(96, 165, 250, .12));
        border: 1px solid rgba(96, 165, 250, .34);
        box-shadow: 0 10px 22px rgba(2, 6, 23, .3);
    }

    body.dark-mode .internal-card h4 {
        color: #e2e8f0;
    }

    body.dark-mode .internal-card.coming-soon {
        opacity: .72;
        filter: grayscale(20%);
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

            <div class="container-lap">

                <div class="page-hero">
                    <div>
                        <h1>Laporan PPI</h1>
                        <small>Manajemen Laporan Komite PPI</small>
                    </div>
                    <button class="hero-btn" onclick="kembaliDashboard()">🏠 Dashboard</button>
                </div>


                <div class="lap-wrapper">

                    <h1 class="lap-title">📋 Laporan Komite PPI Rumah Sakit</h1>
                    <p class="lap-subtitle">Pilih kategori Laporan yang ingin Anda buka.</p>
                </div>
                
                
                <div class="lap-grid">

                    <!-- CARD 1 -->
                    <div class="lap-card">
                        <div class="card-label">Kategori Utama</div>
                        <h2 class="main-title">Laporan Keluar</h2>



                        <div class="internal-grid">

                        <a href="https://drive.google.com/drive/folders/1dAep1UpeAFt0j9aVMUIzhFHbyQ52qona?usp=sharing" target="_blank" rel="noopener noreferrer" class="internal-card">
                            <div class="internal-icon">📋</div>
                            <h4>Laporan IPCN Ke Komite</h4>
                        </a>
                        
                        <a href="https://mutufasyankes.kemkes.go.id/simar/admin" target="_blank" rel="noopener noreferrer" class="internal-card">
                            <div class="internal-icon">🏛️</div>
                            <h4>KEMENKES</h4>
                        </a>
                        
                        <a href="https://drive.google.com/drive/folders/1rk9bKIN1RXljEqdX0K5Nko1FY6ApvLu0?usp=sharing" target="_blank" rel="noopener noreferrer" class="internal-card">
                            <div class="internal-icon">📊</div>
                            <h4>KPI</h4>
                        </a>
                        
                        <a href="https://drive.google.com/drive/folders/1rgvp0Qgjy2lQeeWi1l0I3GPieGZh7nrZ?usp=sharing" target="_blank" rel="noopener noreferrer" class="internal-card">
                            <div class="internal-icon">📈</div>
                            <h4>PPI Ke MUTU</h4>
                        </a>
                        
                        <a href="https://docs.google.com/spreadsheets/d/1r5U58fy0WQSZpKHd8A5TlJomMWrrjyojA97Kp4_IdYE/edit?usp=sharing" target="_blank" rel="noopener noreferrer" class="internal-card">
                            <div class="internal-icon">🦠</div>
                            <h4>HAIs Corporate</h4>
                        </a>
                        
                            <a href="lap_ipcn.php" target="_blank" rel="noopener noreferrer" class="internal-card">
                                <div class="internal-icon">🧼</div>
                                <h4>Kebersihan Tangan</h4>
                            </a>

                            <a href="https://linktr.ee/fasilitas1234" target="_blank" rel="noopener noreferrer" class="internal-card">
                                <div class="internal-icon">🧤</div>
                                <h4>Alat Pelindung Diri (APD)</h4>
                            </a>
                        
                        </div>

                    </div>

                    <!-- CARD 2 -->
                    <div class="lap-card">
                        <div class="card-label">Kategori Khusus</div>
                        <h2 class="main-title">Laporan Masuk</h2>

                        <div class="internal-grid">

                            <a href="https://myppi.primaya.id/" target="_blank" rel="noopener noreferrer" class="internal-card">
                                <div class="internal-icon">🏥</div>
                                <h4>Unit</h4>
                            </a>
                            
                            <a href="https://drive.google.com/drive/folders/1JASUsiGQ9qcLHUidrpNjoES0t_MNcnB8" target="_blank" rel="noopener noreferrer" class="internal-card">
                                <div class="internal-icon">🔬</div>
                                <h4>Surveilans HAIs</h4>
                            </a>
                            
                            <a href="https://drive.google.com/drive/folders/1gB2Q2PFZY1R_s0ZDZ6jHA7VtHADeoXwF" target="_blank" rel="noopener noreferrer" class="internal-card">
                                <div class="internal-icon">🦠</div>
                                <h4>Surveilans IDO UKB</h4>
                            </a>
                                                        
                            <a href="#" class="internal-card coming-soon">
                                <div class="internal-icon">👨‍⚕️</div>
                                <h4>Laporan Kesehatan Karyawan</h4>
                            </a>
                            
                            <a href="#" class="internal-card coming-soon">
                                <div class="internal-icon">📊</div>
                                <h4>Penyakit Potensi KLB / Surv. Epidemiologi</h4>
                            </a>
                            
                            <a href="#" class="internal-card coming-soon">
                                <div class="internal-icon">💊</div>
                                <h4>Antibiotika</h4>
                            </a>

                        </div>



                    </div>
                    
                    

                </div>


            </div>

        </main>
    </div>

    <script>
        function kembaliDashboard() { window.location.href = "/dashboard.php"; }
    </script>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>


</body>

</html>