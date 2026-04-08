<?php
require_once __DIR__ . '/../config/assets.php';
session_start();
include_once '../koneksi.php';
include_once '../cek_akses.php';
?>



<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "MASTER DATA";
?>
<!--end-->



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Internal</title>
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <!-- === Link CSS eksternal === -->
    <style>
        /* =========================================
   BACKGROUND AREA
========================================= */
        .container-supervise {
            padding: 30px 40px;
        }

        /* =========================================
   HEADER SECTION
========================================= */
        .audit-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .06);
            transition: .3s ease;
        }

        .audit-card:hover {
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
        .audit-wrapper {
            
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
        .audit-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, #1e40af, #2563eb);
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
                        <h1>Master Data PPI</h1>
                        <small>Pengelolaan Master Data Aplikasi</small>
                    </div>
                    <button class="hero-btn" onclick="kembaliDashboard()">🏠 Dashboard</button>
                </div>


                <div class="audit-wrapper">

                    <h1 class="audit-title">📋 Manajemen Master Data Rumah Sakit</h1>
                    <p class="audit-subtitle">Pilih kategori yang ingin Anda buka.</p>
                </div>
                
                
                <div class="audit-grid">

                    <!-- CARD 1 -->
                    <div class="audit-card">
                        <div class="card-label">Kategori Utama</div>
                        <h2 class="main-title">Master Data Umum</h2>
                    <div class="internal-grid">
                        
                    <!-- UNIT -->
                    <a href="/master/master.php?type=unit" class="internal-card">
                        <div class="internal-icon">🏢</div>
                        <h4>Daftar Unit</h4>
                    </a>
                    
                    <!-- PROFESI -->
                    <a href="/master/master.php?type=profesi" class="internal-card">
                        <div class="internal-icon">👩‍⚕️</div>
                        <h4>Daftar Profesi</h4>
                    </a>
                    
                    <!-- TINDAKAN -->
                    <a href="/master/master.php?type=tindakan" class="internal-card">
                        <div class="internal-icon">📋</div>
                        <h4>Jenis Tindakan</h4>
                    </a>
                
                    <!-- LAPORAN -->
                    <a href="/master/master.php?type=laporan" class="internal-card">
                        <div class="internal-icon">📊</div>
                        <h4>Jenis Laporan</h4>
                    </a>
                
                    <!-- MATERI -->
                    <a href="/master/master.php?type=materi" class="internal-card">
                        <div class="internal-icon">📚</div>
                        <h4>Jenis Materi</h4>
                    </a>
                
                    <!-- PELATIHAN -->
                    <a href="/master/master.php?type=pelatihan" class="internal-card">
                        <div class="internal-icon">🎓</div>
                        <h4>Jenis Pelatihan</h4>
                    </a>
                

                    <!-- RAPAT -->
                    <a href="/master/master.php?type=rapat" class="internal-card">
                        <div class="internal-icon">🗓️</div>
                        <h4>Jenis Rapat</h4>
                    </a>
                    

                    
                        <!-- FORM & BROSUR -->
                    <a href="/master/master.php?type=form_brosur" class="internal-card">
                        <div class="internal-icon">📝</div>
                        <h4>Kategori Form & Brosur</h4>
                    </a>
                
                    <!-- KEGIATAN -->
                    <a href="/master/master.php?type=kegiatan" class="internal-card">
                        <div class="internal-icon">🎯</div>
                        <h4>Kategori Kegiatan</h4>
                    </a>
                    
                    </div>
                </div>
                
                
                        <!-- CARD 2 -->
                <div class="audit-grid">
                    <div class="audit-card">
                        <div class="card-label">Regulasi</div>
                        <h2 class="main-title">Master Regulasi</h2>
                    <div class="internal-grid">
                
                    <!-- DOKUMEN -->
                    <a href="/master/master.php?type=dokumen" class="internal-card">
                        <div class="internal-icon">📁</div>
                        <h4>Jenis Dokumen</h4>
                    </a>
                    

                    <!-- REFERENSI -->
                    <a href="/master/master.php?type=referensi" class="internal-card">
                        <div class="internal-icon">📖</div>
                        <h4>Jenis Referensi</h4>
                    </a>
                
                    <!-- ELEMEN -->
                    <a href="/master/master.php?type=elemen" class="internal-card">
                        <div class="internal-icon">📌</div>
                        <h4>Jenis Elemen</h4>
                    </a>
                    

                    <!-- REGULASI -->
                    <a href="/master/master.php?type=jenis_regulasi" class="internal-card">
                        <div class="internal-icon">📜</div>
                        <h4>Klasifikasi Regulasi</h4>
                    </a>
                
                    <!-- SUMBER -->
                    <a href="/master/master.php?type=sumber_referensi" class="internal-card">
                        <div class="internal-icon">🔗</div>
                        <h4>Sumber Referensi</h4>
                    </a>
                    </div>
                </div>
                
                
                

                    <!-- CARD 3 -->
                    <div class="audit-card">
                        <div class="card-label">Bundle HAIs</div>
                        <h2 class="main-title">Master Bundle HAIs</h2>

                        <div class="internal-grid">
                            
                            <!-- AUDIT BUNDLE HAIs -->
                            <a href="/master/master_bundle_item.php" class="internal-card">
                                <div class="internal-icon">📋</div>
                                <h4>Master Bundle Item</h4>
                            </a>
                            
                            <a href="master_bundle_mapping.php" class="internal-card">
                                <div class="internal-icon">🧩</div>
                                <h4>Master Bundle Mapping</h4>
                            </a>
                            

                             <a href="/master/bundle_view.php?jenis=vap" class="internal-card">
                                <div class="internal-icon">🫁</div>
                                <h4>View Bundle VAP</h4>
                            </a>
                            
                            <a href="/master/bundle_view.php?jenis=iadp" class="internal-card">
                                <div class="internal-icon">🩸</div>
                                <h4>View Bundle IADP</h4>
                            </a>
                            
                            <a href="/master/bundle_view.php?jenis=isk" class="internal-card">
                                <div class="internal-icon">🚻</div>
                                <h4>View Bundle ISK</h4>
                            </a>
                            
                            <a href="/master/bundle_view.php?jenis=ido" class="internal-card">
                                <div class="internal-icon">🦠</div>
                                <h4>View Bundle IDO</h4>
                            </a>

                        </div>


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