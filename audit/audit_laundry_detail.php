<?php
include_once '../koneksi.php';
include_once '../cek_akses.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo "<script>alert('ID tidak valid');history.back();</script>";
    exit;
}

/* ===== HEADER DATA ===== */
$qHeader = mysqli_query($koneksi, "SELECT * FROM tb_audit_laundry WHERE id=$id");
$data = mysqli_fetch_assoc($qHeader);
if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); history.back();</script>";
    exit;
}

/* ===== DETAIL ===== */
$detail = mysqli_query($koneksi, "
    SELECT item_no, jawaban, keterangan
    FROM tb_audit_laundry_detail
    WHERE audit_id=$id
    ORDER BY item_no
");


$monitoring = [

    // ======================
    // HYGIENE PERSONAL
    // ======================
    1 => "Hygiene Personal – Bersih, rapih, dan menggunakan pakaian yang sesuai",
    2 => "Hygiene Personal – Kuku pendek dan bersih",
    3 => "Hygiene Personal – Tidak menggunakan perhiasan tangan",
    4 => "Hygiene Personal – Rambut rapih, dan menggunakan APD dengan tepat ketika menangani linen kotor dan bersih",
    5 => "Hygiene Personal – Staf mendapat vaksinasi penyakit menular",
    6 => "Hygiene Personal – Pemeriksaan kesehatan berkala untuk staf",
    7 => "Hygiene Personal – Petugas Laundry tidak menggunakan pakaian kerja dari rumah",

    // ======================
    // TEMPAT & PROSES PENCUCIAN
    // ======================
    8 => "Tempat dan Proses Pencucian – Tersedia sarana cuci tangan",
    9 => "Tempat dan Proses Pencucian – Tersedia fasilitas APD",
    10 => "Tempat dan Proses Pencucian – Temperatur suhu pencucian sesuai standar 70°C (25 menit) / 95°C (10 menit)",
    11 => "Tempat dan Proses Pencucian – Linen kotor terpisah dari linen bersih",
    12 => "Tempat dan Proses Pencucian – Penyortiran linen kotor tidak diletakkan di lantai",
    13 => "Tempat dan Proses Pencucian – Ada ruangan & mesin cuci terpisah untuk linen infeksius & non-infeksius",
    14 => "Tempat dan Proses Pencucian – Penggunaan chemical & detergen sesuai IFU",
    15 => "Tempat dan Proses Pencucian – Linen infeksius tidak dilakukan penghitungan",
    16 => "Tempat dan Proses Pencucian – Pembersihan & pemeliharaan mesin rutin",
    17 => "Tempat dan Proses Pencucian – Petugas menggunakan APD lengkap",
    18 => "Tempat dan Proses Pencucian – Area pencucian bersih dan kering",
    19 => "Tempat dan Proses Pencucian – Menggunakan troli infeksius & non infeksius",
    20 => "Tempat dan Proses Pencucian – Troli dibersihkan setiap habis digunakan",
    21 => "Tempat dan Proses Pencucian – Pemeriksaan air bersih & IPAL berkala",
    22 => "Tempat dan Proses Pencucian – Ada bukti sertifikasi swab linen",
    23 => "Tempat dan Proses Pencucian – Tersedia eyewash berfungsi",
    24 => "Tempat dan Proses Pencucian – Memiliki saluran pembuangan tertutup & pre-treatment",

    // ======================
    // TEMPAT PENGERINGAN
    // ======================
    25 => "Tempat Pengeringan – Tersedia sarana cuci tangan",
    26 => "Tempat Pengeringan – Petugas menggunakan APD (penutup kepala, masker)",
    27 => "Tempat Pengeringan – Area pengeringan terpisah dari area pencucian",
    28 => "Tempat Pengeringan – Temperatur pengeringan 70–80°C (40–60 menit)",
    29 => "Tempat Pengeringan – Proses pengeringan 15–30 menit",
    30 => "Tempat Pengeringan – Petugas khusus menangani linen bersih",
    31 => "Tempat Pengeringan – Tidak melakukan penjemuran linen",
    32 => "Tempat Pengeringan – Menggunakan troli bersih & dibersihkan rutin",

    // ======================
    // PENYETRIKAAN & PELIPATAN
    // ======================
    33 => "Penyetrikaan & Pelipatan – Tersedia sarana cuci tangan",
    34 => "Penyetrikaan & Pelipatan – Petugas menggunakan APD lengkap",
    35 => "Penyetrikaan & Pelipatan – Linen kering langsung disetrika",
    36 => "Penyetrikaan & Pelipatan – Linen dipisahkan sesuai jenis",
    37 => "Penyetrikaan & Pelipatan – Menggunakan mesin press/roll press suhu 160°C",
    38 => "Penyetrikaan & Pelipatan – Area setrika bersih dan kering",
    39 => "Penyetrikaan & Pelipatan – Mesin setrika dibersihkan rutin",
    40 => "Penyetrikaan & Pelipatan – Linen bersih tidak diletakkan di lantai",

    // ======================
    // TEMPAT PENYIMPANAN
    // ======================
    41 => "Tempat Penyimpanan – Linen disimpan di rak atau lemari tertutup",
    42 => "Tempat Penyimpanan – Penyimpanan linen sesuai jenis & FIFO",
    43 => "Tempat Penyimpanan – Tidak ada debu, sarang laba-laba, jamur",
    44 => "Tempat Penyimpanan – Ruangan dibersihkan rutin",
    45 => "Tempat Penyimpanan – Suhu ruang 22–26°C, RH 40–60%",
    46 => "Tempat Penyimpanan – Penyimpanan linen 30–50 cm dari lantai & 5 cm dari dinding",

    // ======================
    // PENDISTRIBUSIAN
    // ======================
    47 => "Pendistribusian – Pintu distribusi bersih berbeda dengan kotor",
    48 => "Pendistribusian – Linen bersih dibungkus plastik",
    49 => "Pendistribusian – Kendaraan transportasi bersih & didisinfeksi rutin",
    50 => "Pendistribusian – Ada bukti ceklist pembersihan kendaraan",
];

?>


<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "LIHAT DATA AUDIT LAUNDRY";
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
    <link rel="stylesheet" href="/assets/css/utama.css?v=10">

    <style>
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .08);
            padding: 20px 32px 28px 32px;
            ;
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
                        <img src="/assets/images/logo phbw123.png" alt="Logo PHBW" class="print-logo">
                        <div class="print-title">
                            <strong>Primaya Hospital Bhakti Wara</strong><br>
                            <span>Monitoring Audit PPI</span>
                        </div>
                    </div>


                    <div class="header header-flex">
                        <div>
                            <h2>Detail Audit Laundry</h2>
                            <small>Monitoring Audit PPI</small>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button onclick="window.print()" class="btn-download">⬇ Unduh PDF</button>
                            <a href="audit_laundry.php?tab=rekap" class="btn-back">← Kembali</a>
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

                        <div style="
                            background:#f7f9fd;
                            border:1px solid #e6ecf5;
                            border-left:4px solid #2b60d3;
                            border-radius:8px;
                            padding:12px 14px;
                            font-size:14px;
                            line-height:1.6;
                            white-space:pre-line;
    ">
                            <?= htmlspecialchars($data['keterangan']) ?>
                        </div>
                    </div>
                    <?php endif; ?>



                    <?php if (!empty($data['ttd_petugas']) || !empty($data['ttd_auditor'])): ?>
                    <div style="margin-top:24px">

                        <h4 style="margin-bottom:10px;color:#2b60d3">Tanda Tangan</h4>

                        <div style="display:flex;gap:20px;flex-wrap:wrap">

                            <?php if (!empty($data['ttd_petugas'])): ?>
                            <div style="flex:1;min-width:220px">
                                <b>Petugas</b>
                                <div
                                    style="border:1px dashed #cbd9f8;border-radius:8px;padding:10px;background:#fafcff">
                                    <img src="<?= $data['ttd_petugas'] ?>"
                                        style="max-width:100%;height:120px;object-fit:contain">
                                </div>
                                <div style="margin-top:6px;font-size:13px">
                                    <?= htmlspecialchars($data['nama_petugas']) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($data['ttd_auditor'])): ?>
                            <div style="flex:1;min-width:220px">
                                <b>Auditor</b>
                                <div
                                    style="border:1px dashed #cbd9f8;border-radius:8px;padding:10px;background:#fafcff">
                                    <img src="<?= $data['ttd_auditor'] ?>"
                                        style="max-width:100%;height:120px;object-fit:contain">
                                </div>
                                <div style="margin-top:6px;font-size:13px">
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



    <script src="/assets/js/utama.js?v=5"></script>



</body>

</html>