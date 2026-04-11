<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include_once '../cek_akses.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo "<script>alert('ID tidak valid');history.back();</script>";
    exit;
}

/* ===== HEADER DATA ===== */
$qHeader = mysqli_query($koneksi, "SELECT * FROM tb_audit_limbah WHERE id=$id");
$data = mysqli_fetch_assoc($qHeader);
if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); history.back();</script>";
    exit;
}

/* ===== DETAIL ===== */
$detail = mysqli_query($koneksi, "
    SELECT item_no, jawaban, keterangan
    FROM tb_audit_limbah_detail
    WHERE audit_id=$id
    ORDER BY item_no
");


$monitoring = [
    1 => "Personal – Bersih, rapih, dan menggunakan pakaian yang sesuai",
    2 => "Personal – Kuku pendek dan bersih",
    3 => "Personal – Tidak menggunakan perhiasan tangan",
    4 => "Personal – Rambut rapih, dan menggunakan APD dengan tepat ketika menangani limbah infeksius",
    5 => "Personal – Staf mendapat vaksinasi penyakit menular",
    6 => "Personal – Pemeriksaan kesehatan berkala untuk staf",
    7 => "Pengangkutan – Penunjukan personil yang bertanggung jawab untuk setiap zona atau area",
    8 => "Pengangkutan – Kantong limbah medis padat sebelum dimasukkan ke kendaraan pengangkut harus diletakkan dalam kontainer yang kuat dan tertutup",
    9 => "Pengangkutan – Alat angkut tidak memiliki sudut tajam yang dapat merusak kantong, tertutup dan aman dari tumpahan cairan",
    10 => "Pengangkutan – Kantong limbah medis padat harus aman dari jangkauan manusia maupun binatang",
    11 => "Pengangkutan – Peralatan diberi label dan berwarna sesuai dengan kategori limbah",
    12 => "Pengangkutan – Pelekatan simbol limbah B3 pada badan kendaraan pengangkut sebagai bentuk komunikasi bahaya atas limbah B3 yang diangkut",
    13 => "Pengangkutan – Penerapan aturan segregasi dalam pemuatan limbah B3 ke dalam alat angkut",
    14 => "Pengangkutan – Penerapan inspeksi kondisi limbah B3 yang diangkut oleh pengemudi",
    15 => "Pengangkutan – Pastikan hanya melakukan bongkar-muat di lokasi yang sudah ditentukan",
    16 => "Pengangkutan – Usahakan lokasi bongkar-muat dibuat tertutup (indoor), atau minimal memiliki atap",
    17 => "Pengangkutan – Buat saluran penampungan tumpahan yang kedap air dan bak penampungan tumpahan yang buntu di lokasi bongkar-muat",
    18 => "Pengangkutan – Tutup saluran penampungan limpasan air hujan saat kegiatan bongkar-muat berlangsung untuk menghindari masuknya tumpahan limbah B3 ke dalam saluran tersebut",
    19 => "Pengangkutan – Hindari melakukan kegiatan bongkar-muat saat hujan untuk menghindari potensi tumpahan yang akan larut dan terbawa oleh limpasan air hujan",
    20 => "Pengangkutan – Seluruh muatan harus diikat kuat selama dan posisinya diatur dengan baik sehingga bebannya terdistribusi secara merata di sumbu-sumbu kendaraan",
    21 => "Pengangkutan – Pastikan pemuatan kemasan ke dalam kendaraan juga memperhitungkan kemudahan dan keamanan saat pembongkaran",
    22 => "Penyimpanan sementara sebelum dimusnahkan – Tempat penampungan harus memiliki lantai yang kokoh dilengkapi dengan drainase yang baik dan mudah dibersihkan serta didesinfeksi",
    23 => "Penyimpanan sementara sebelum dimusnahkan – Tidak boleh berada dekat dengan lokasi penyimpanan bahan makanan atau dapur",
    24 => "Penyimpanan sementara sebelum dimusnahkan – Harus ada pencahayaan yang baik serta kemudahan akses untuk kendaraan pengumpul limbah",
    25 => "Penyimpanan sementara sebelum dimusnahkan – Lokasi untuk tempat penyimpanan limbah yang berbahaya dan beracun minimum berjarak 50 meter dari lokasi fasilitas umum dan daerah bebas banjir sehingga aman dari kemungkinan terkena banjir",
    26 => "Penyimpanan sementara sebelum dimusnahkan – Area penyimpanan harus diamankan untuk mencegah binatang, anak-anak, dll memasuki dan mengakses daerah tersebut",
    27 => "Penyimpanan sementara sebelum dimusnahkan – Selain itu, harus kedap air (sebaiknya beton), terlindung dari air hujan, harus aman, dipagari dengan penanda yang tepat",
    28 => "Penyimpanan sementara sebelum dimusnahkan – Penyimpanan limbah medis padat harus sesuai iklim tropis yaitu pada musim hujan paling lama 48 jam dan musim kemarau paling lama 24 jam",
    29 => "Pengolahan Insinerator – Menggunakan pembakaran suhu tinggi (misalnya: pirolisis, gasifikasi, plasma arc). Pembakaran dilakukan dengan suhu 800°C sampai 1200°C",
    30 => "Pengolahan Insinerator – Suatu sistem yang terkontrol dan terisolir dari lingkungannya agar sifat bahayanya hilang atau berkurang",
    31 => "Pembuangan Akhir Limbah Medis – Hasil dari pengolahan limbah medis berupa abu",
    32 => "Pembuangan Akhir Limbah Medis – Penimbunan (landfill)",
    33 => "Pembuangan Akhir Limbah Medis – Lokasi bekas pengolahan dan penimbunan limbah medis B3 pun harus ditangani dengan baik untuk mencegah hal-hal yang tidak diinginkan",
    34 => "Pembuangan Akhir Limbah Medis – Tempat atau lokasi yang diperuntukkan khusus sebagai tempat penimbunan (secure landfill) limbah medis didesain sesuai dengan persyaratan penimbunan limbah B3",
    35 => "Pembuangan Akhir Limbah Medis – Tempat penimbunan mempunyai sistem pengumpulan dan pengolahan lindi",
];

?>


<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "LIHAT DATA AUDIT LIMBAH";
?>
<!--end-->


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        <?php echo $pageTitle; ?> | PPI PHBW
    </title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        .main-content {
            padding: 16px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .08);
            padding: 20px 32px 28px 32px;
            border: 1px solid #e6ecf5;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-download {
            background: #16a34a;
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 6px 16px rgba(22, 163, 74, .35);
        }

        .btn-download:hover {
            background: #15803d;
        }

        @media print {

            /* ===== KERTAS ===== */
            @page {
                size: A4 portrait;
                margin: 14mm 16mm 16mm 16mm;
                /* 🔽 margin atas diperkecil */
            }

            /* ===== PAKSA WARNA ===== */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* ===== HILANGKAN JARAK ATAS GLOBAL ===== */
            body,
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                background: #f6f8fc !important;
            }

            /* ===== SEMBUNYIKAN NAV ===== */
            .sidebar,
            .btn-back,
            .btn-download,
            .breadcrumb {
                display: none !important;
            }

            /* ===== CONTAINER (NAIK KE ATAS) ===== */
            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 14px 20px 24px 20px !important;
                /* 🔽 padding atas kecil */
                box-shadow: 0 12px 32px rgba(0, 0, 0, .08) !important;
                border-radius: 14px !important;
                background: #fff !important;
            }

            /* ===== HEADER LOGO ===== */
            .print-header {
                margin-bottom: 6px !important;
                /* 🔽 JARAK DIKECILKAN */
                padding-bottom: 6px !important;
                border-bottom: 2px solid #e6ecf5;
            }

            .print-logo {
                height: 48px !important;
                /* 🔽 sedikit lebih kecil */
            }

            .print-title {
                font-size: 13px !important;
            }

            /* ===== JUDUL ===== */
            .header {
                margin-top: 0 !important;
            }

            /* ===== TABEL ===== */
            table {
                width: 100% !important;
                table-layout: fixed;
                font-size: 12px;
            }

            th,
            td {
                white-space: normal;
                word-wrap: break-word;
            }
        }




        .header h2 {
            margin: 0;
            color: #2b60d3;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .meta div {
            background: #f7f9fd;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #e6ecf5;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
        }

        .badge.BAIK {
            background: #2ecc71
        }

        .badge.INTERMEDIATE {
            background: #f1c40f
        }

        .badge.MINIMAL {
            background: #e74c3c
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #dbe5f6;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            border: 1.5px solid #b6c6e3;
            /* LEBIH TEGAS */
            background: #fff;
        }

        th,
        td {
            padding: 10px 12px;
            border: 1px solid #c5d3ec;
            /* GRID JELAS */
            vertical-align: top;
        }

        th {
            background: #e9f1ff;
            /* LEBIH KONTRAS */
            text-align: center;
            font-weight: 700;
            color: #1f3f8b;
        }



        td.center {
            text-align: center;
        }

        .back {
            margin-top: 16px;
            display: inline-block;
            text-decoration: none;
            background: #2b60d3;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
        }

        .back:hover {
            opacity: .9
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .btn-back {
            background: #2b60d3;
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 6px 16px rgba(43, 96, 211, .35);
            white-space: nowrap;
        }

        .btn-back:hover {
            background: #1f4fb5;
        }

        .print-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }

        .print-logo {
            height: 56px;
            width: auto;
        }

        .print-title {
            font-size: 14px;
            line-height: 1.4;
            color: #1f3f8b;
        }

        .print-title strong {
            font-size: 15px;
        }

        .print-header {
            padding-bottom: 10px;
            border-bottom: 2px solid #e6ecf5;
        }

        .note-box {
            background: #f7f9fd;
            border: 1px solid #e6ecf5;
            border-left: 4px solid #2b60d3;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-line;
        }

        .signature-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .signature-card {
            flex: 1;
            min-width: 220px;
        }

        .signature-frame {
            border: 1px dashed #cbd9f8;
            border-radius: 8px;
            padding: 10px;
            background: #fafcff;
        }

        .signature-name {
            margin-top: 6px;
            font-size: 13px;
        }

        @media(max-width: 768px) {
            .main-content {
                padding: 10px;
            }

            .container {
                padding: 16px;
                border-radius: 16px;
            }

            .header-flex {
                flex-direction: column;
                align-items: stretch;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn-download,
            .header-actions .btn-back {
                flex: 1 1 100%;
                text-align: center;
            }

            .meta {
                grid-template-columns: 1fr;
            }
        }

        body.dark-mode .main-content {
            background: #0b1220;
        }

        body.dark-mode .container {
            background: #111827;
            border-color: rgba(56, 189, 248, .14);
            box-shadow: 0 0 0 1px rgba(56, 189, 248, .08), 0 20px 60px rgba(0, 0, 0, .55);
            color: #e2e8f0;
        }

        body.dark-mode .header h2,
        body.dark-mode h4 {
            color: #7dd3fc !important;
        }

        body.dark-mode .print-title,
        body.dark-mode .print-title strong,
        body.dark-mode .header small,
        body.dark-mode .signature-name {
            color: #cbd5e1;
        }

        body.dark-mode .print-header {
            border-bottom-color: rgba(56, 189, 248, .14);
        }

        body.dark-mode .meta div {
            background: #0f172a;
            border-color: rgba(56, 189, 248, .12);
            color: #e2e8f0;
        }

        body.dark-mode .table-wrap {
            border-color: rgba(56, 189, 248, .14);
            background: #0f172a;
        }

        body.dark-mode table {
            background: transparent;
            border-color: rgba(56, 189, 248, .14);
        }

        body.dark-mode th {
            background: linear-gradient(180deg, #0f2744 0%, #0b1e35 100%);
            color: #7dd3fc;
            border-color: rgba(56, 189, 248, .16);
        }

        body.dark-mode td {
            color: #dbe6f5;
            border-color: rgba(56, 189, 248, .09);
        }

        body.dark-mode .btn-back {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            box-shadow: 0 10px 24px rgba(37, 99, 235, .28);
        }

        body.dark-mode .btn-download {
            box-shadow: 0 10px 24px rgba(22, 163, 74, .28);
        }

        body.dark-mode .note-box {
            background: #0f172a;
            border-color: rgba(56, 189, 248, .12);
            color: #dbe6f5;
        }

        body.dark-mode .signature-frame {
            background: #0b1735;
            border-color: rgba(56, 189, 248, .22);
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
                <?php include '../breadcrumb.php'; ?>


                <div class="container">

                    <div class="print-header">
                        <img src="<?= base_url('assets/images/logo phbw123.png') ?>" alt="Logo PHBW" class="print-logo">
                        <div class="print-title">
                            <strong>Primaya Hospital Bhakti Wara</strong><br>
                            <span>Monitoring Audit PPI</span>
                        </div>
                    </div>


                    <div class="header header-flex">
                        <div>
                            <h2>Detail Audit Limbah</h2>
                            <small>Monitoring Audit PPI</small>
                        </div>

                        <div class="header-actions">
                            <button onclick="window.print()" class="btn-download">⬇ Unduh PDF</button>
                            <a href="audit_limbah.php?tab=rekap" class="btn-back">← Kembali</a>
                        </div>
                    </div>



                    <div class="meta">
                        <div><b>Tanggal</b><br>
                            <?= $data['tanggal'] ?>
                        </div>
                        <div><b>Petugas</b><br>
                            <?= htmlspecialchars($data['nama_petugas']) ?>
                        </div>
                        <div><b>Auditor</b><br>
                            <?= htmlspecialchars($data['nama_auditor']) ?>
                        </div>
                        <div><b>Persentase</b><br>
                            <?= $data['persentase'] ?>%
                        </div>
                        <div>
                            <b>Kategori</b><br>
                            <span class="badge <?= $data['kategori'] ?>">
                                <?= $data['kategori'] ?>
                            </span>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:8%">No</th>
                                    <th style="width:54%">Monitoring</th>
                                    <th style="width:13%">Jawaban</th>
                                    <th style="width:25%">Keterangan</th>
                                </tr>

                            </thead>
                            <tbody>
                                <?php while ($d = mysqli_fetch_assoc($detail)): ?>
                                <tr>
                                    <td class="center">
                                        <?= $d['item_no'] ?>
                                    </td>
                                    <td>
                                        <?= $monitoring[$d['item_no']] ?? 'Monitoring tidak ditemukan' ?>
                                    </td>
                                    <td class="center">
                                        <?= $d['jawaban'] ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($d['keterangan']) ?: '-' ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>

                            </tbody>
                        </table>
                    </div>


                    <?php if (!empty($data['keterangan'])): ?>
                    <div style="margin-top:24px">
                        <h4 style="margin-bottom:8px;color:#2b60d3">Keterangan Tambahan</h4>

                        <div class="note-box">
                            <?= htmlspecialchars($data['keterangan']) ?>
                        </div>
                    </div>
                    <?php endif; ?>



                    <?php if (!empty($data['ttd_petugas']) || !empty($data['ttd_auditor'])): ?>
                    <div style="margin-top:24px">

                        <h4 style="margin-bottom:10px;color:#2b60d3">Tanda Tangan</h4>

                        <div class="signature-grid">

                            <?php if (!empty($data['ttd_petugas'])): ?>
                            <div class="signature-card">
                                <b>Petugas</b>
                                <div class="signature-frame">
                                    <img src="<?= $data['ttd_petugas'] ?>"
                                        style="max-width:100%;height:120px;object-fit:contain">
                                </div>
                                <div class="signature-name">
                                    <?= htmlspecialchars($data['nama_petugas']) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($data['ttd_auditor'])): ?>
                            <div class="signature-card">
                                <b>Auditor</b>
                                <div class="signature-frame">
                                    <img src="<?= $data['ttd_auditor'] ?>"
                                        style="max-width:100%;height:120px;object-fit:contain">
                                </div>
                                <div class="signature-name">
                                    <?= htmlspecialchars($data['nama_auditor']) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endif; ?>

                </div>

            </div>


        </main>

    </div>



    <script src="<?= asset('assets/js/utama.js') ?>"></script>



</body>

</html>
