<?php
include_once '../koneksi.php';
include "../cek_akses.php";

// ================== UPDATE PROGRESS (AJAX) ==================
if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_progress') {

    $id = (int)$_POST['id'];
    $progress = (int)$_POST['progress'];
    $tanggal = $_POST['tanggal'];

    if ($progress >= 0 && $progress <= 5) {

        $stmt = $koneksi->prepare("
            INSERT INTO tb_kalender_progress (kalender_id, tanggal, progress)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE progress=VALUES(progress)
        ");

        $stmt->bind_param("isi", $id, $tanggal, $progress);
        $stmt->execute();
        $stmt->close();
    }

    echo "success";
    exit;
}



$conn = $koneksi;

// ======================================
// AMBIL MASTER KATEGORI
// ======================================
$kategori_list = [];

$kat = $conn->query("
    SELECT id, nama_kegiatan
    FROM tb_kegiatan
    WHERE status='aktif'
    ORDER BY nama_kegiatan ASC
");

if ($kat) {
    while ($k = $kat->fetch_assoc()) {
        $kategori_list[] = $k;
    }
}

// ======================================
// AMBIL DATA EDIT
// ======================================
$editData = null;

if (isset($_GET['edit'])) {

    $id_edit = (int) $_GET['edit'];

    if ($id_edit > 0) {

        $stmt = $conn->prepare("SELECT * FROM tb_kalender WHERE id=?");
        $stmt->bind_param("i", $id_edit);
        $stmt->execute();

        $result = $stmt->get_result();
        $editData = $result->fetch_assoc();

        $stmt->close();
    }
}

// ======================================
// SIMPAN (INSERT / UPDATE)
// ======================================
if (isset($_POST['simpan'])) {

    $judul       = trim($_POST['judul']);
    $kategori_id = (int) $_POST['kategori'];
    $tanggal     = $_POST['tanggal'];
    $waktu       = !empty($_POST['waktu']) ? $_POST['waktu'] : null;
    $keterangan  = trim($_POST['keterangan']);
    $pengulangan = !empty($_POST['pengulangan']) ? $_POST['pengulangan'] : null;

    if (isset($_POST['id_edit'])) {

        // ===== UPDATE =====
        $id_edit = (int) $_POST['id_edit'];

        $stmt = $conn->prepare("
            UPDATE tb_kalender
            SET judul=?, kategori_id=?, tanggal=?, waktu=?, pengulangan=?, keterangan=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "sissssi",
            $judul,
            $kategori_id,
            $tanggal,
            $waktu,
            $pengulangan,
            $keterangan,
            $id_edit
        );

        $stmt->execute();
        $stmt->close();

        header("Location: kalender.php?msg=updated#tab2");
        exit;
    } else {

        // ===== INSERT =====
        $stmt = $conn->prepare("
            INSERT INTO tb_kalender
            (judul, kategori_id, tanggal, waktu, pengulangan, keterangan)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sissss",
            $judul,
            $kategori_id,
            $tanggal,
            $waktu,
            $pengulangan,
            $keterangan
        );

        $stmt->execute();
        $stmt->close();

        header("Location: kalender.php?msg=success#tab2");
        exit;
    }
}

// ======================================
// HAPUS ACARA (AMAN)
// ======================================
if (isset($_POST['hapus'])) {

    $id = (int) $_POST['id'];

    if ($id > 0) {

        $stmt = $conn->prepare("DELETE FROM tb_kalender WHERE id=?");

        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: kalender.php?msg=deleted#tab2");
    exit;
}

// ======================================
// AMBIL SEMUA DATA (JOIN KATEGORI)
// ======================================
$events = [];



// ================== DATA UNTUK KALENDER (SEMUA KATEGORI) ==================

$calendarEvents = [];

// Ambil semua progress
$progressList = [];
$resProgress = $conn->query("SELECT * FROM tb_kalender_progress");
if ($resProgress) {
    while ($row = $resProgress->fetch_assoc()) {
        $progressList[] = $row;
    }
}

$resCal = $conn->query("
    SELECT 
        tb_kalender.*, 
        tb_kegiatan.nama_kegiatan
    FROM tb_kalender
    JOIN tb_kegiatan 
        ON tb_kalender.kategori_id = tb_kegiatan.id
    WHERE tb_kegiatan.status='aktif'
    ORDER BY tb_kalender.tanggal ASC
");

if ($resCal) {
    while ($r = $resCal->fetch_assoc()) {
        $calendarEvents[] = $r;
    }
}

// ================= PAGINATION =================

$limit = 10; // jumlah data per halaman
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$start = ($page - 1) * $limit;

// ================== NON LIBUR NASIONAL ==================

$totalRes = $conn->query("
    SELECT COUNT(*) as total
    FROM tb_kalender
    JOIN tb_kegiatan 
        ON tb_kalender.kategori_id = tb_kegiatan.id
    WHERE tb_kegiatan.status='aktif'
    AND tb_kegiatan.nama_kegiatan != 'Libur Nasional'
");

$totalRow = $totalRes->fetch_assoc();
$totalData = $totalRow['total'];
$totalPage = ceil($totalData / $limit);

$res = $conn->query("
    SELECT 
        tb_kalender.*, 
        tb_kegiatan.nama_kegiatan
    FROM tb_kalender
    JOIN tb_kegiatan 
        ON tb_kalender.kategori_id = tb_kegiatan.id
    WHERE tb_kegiatan.status='aktif'
    AND tb_kegiatan.nama_kegiatan != 'Libur Nasional'
    ORDER BY tb_kalender.tanggal ASC
    LIMIT $start, $limit
");

$events = [];

if ($res) {
    while ($r = $res->fetch_assoc()) {
        $events[] = $r;
    }
}

// ================== PAGINATION LIBUR NASIONAL ==================

$liburLimit = 10;

$liburPage = isset($_GET['libur_page']) ? (int)$_GET['libur_page'] : 1;
if ($liburPage < 1) $liburPage = 1;

$liburStart = ($liburPage - 1) * $liburLimit;

// Hitung total libur
$totalLiburRes = $conn->query("
    SELECT COUNT(*) as total
    FROM tb_kalender
    JOIN tb_kegiatan 
        ON tb_kalender.kategori_id = tb_kegiatan.id
    WHERE tb_kegiatan.status='aktif'
    AND tb_kegiatan.nama_kegiatan = 'Libur Nasional'
");

$totalLiburRow = $totalLiburRes->fetch_assoc();
$totalLiburData = $totalLiburRow['total'];
$totalLiburPage = ceil($totalLiburData / $liburLimit);

// Ambil data libur sesuai halaman
$liburData = [];

$resLibur = $conn->query("
    SELECT 
        tb_kalender.*, 
        tb_kegiatan.nama_kegiatan
    FROM tb_kalender
    JOIN tb_kegiatan 
        ON tb_kalender.kategori_id = tb_kegiatan.id
    WHERE tb_kegiatan.status='aktif'
    AND tb_kegiatan.nama_kegiatan = 'Libur Nasional'
    ORDER BY tb_kalender.tanggal ASC
    LIMIT $liburStart, $liburLimit
");

if ($resLibur) {
    while ($r = $resLibur->fetch_assoc()) {
        $liburData[] = $r;
    }
}
?>

<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "KOMITE PPI";

?>
<!--end-->




<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Kalender PPI | PPI PHBW</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="/assets/css/utama.css?v=10">

    <style>
        /*header {*/

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

        /*================== SImbol Bintang ================*/

        .star-rating {
            cursor: pointer;
            font-size: 28px;
            /* 🔥 lebih besar */
            letter-spacing: 4px;
            display: flex;
            align-items: center;
        }

        .star {
            color: #e5e7eb;
            /* abu lebih soft */
            transition: 0.2s;
        }

        .star.filled {
            color: #f97316;
            /* 🟠 ORANGE */
        }

        .star:hover {
            transform: scale(1.2);
        }

        /* ================= FINAL CLEAN CONTAINER ================= */

        .container.kalender {
            background: #ffffff;
            padding: 0px 30px 30px 30px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            margin: 20px 0 40px 0;
        }


        /* Rapikan Tab */
        .tab-wrapper {
            display: inline-flex;
            background: #f1f5ff;
            padding: 6px;
            border-radius: 14px;
            gap: 8px;
            margin-bottom: 25px;
        }

        /* =========== Progress ========== */
        .progress-percent {
            font-size: 12px;
            font-weight: 600;
            margin-top: 4px;
            color: #f97316;
        }

        .progress-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 110px;
            flex-wrap: nowrap;
            /* ⛔ jangan turun baris */
            white-space: nowrap;
            /* ⛔ jangan pecah baris */
        }

        .progress-percent {
            font-weight: 600;
            font-size: 14px;
            color: #f97316;
        }

        /* =========== Kalender lebih clean =========== */
        .calendar {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.04);
        }

        /* =========== Animasi halus =========== */

        .day {
            transition: all 0.2s ease;
        }

        .day:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }


        .month {
            font-size: 24px;
            font-weight: 700;
            min-width: 220px;
            text-align: center;
        }

        .month {
            font-size: 20px;
            font-weight: 600;
        }

        .navs button {
            background: #e5e8ff;
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            margin: 0 3px;
            cursor: pointer;
            font-size: 16px;
        }

        .day:hover {
            transform: scale(1.02);
        }

        .day.inactive {
            opacity: 0.45;
        }

        .event {
            padding: 4px 6px;
            font-size: 11px;
            border-radius: 5px;
            line-height: 1.2;
            color: white;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            width: 100%;
            /* penting */
            box-sizing: border-box;
            /* penting */
        }


        .kategori-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 8px;
            color: white;
            font-size: 12px;
            font-weight: 500;
        }

        /* ===== LIBUR NASIONAL ===== */
        .day.libur {
            background: #fee2e2 !important;
            border: 2px solid #dc2626 !important;
        }

        .day.libur .dateNum {
            color: #b91c1c;
            font-weight: 700;
        }


        /*Tombol prev dan next */
        /* Wrapper bulan */
        .controls>div {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Tombol Prev & Next */
        /* Tombol Prev & Next */
        #prevBtn,
        #nextBtn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #427ac4;
            background: rgb(99, 188, 208);
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;

            /* 👇 Depth */
            box-shadow:
                0 4px 10px rgba(0, 0, 0, 0.08),
                0 2px 4px rgba(66, 122, 196, 0.15);
        }

        /* Hover effect */
        #prevBtn:hover,
        #nextBtn:hover {
            background: #eaf2ff;
            color: #030616;
            transform: translateY(-3px);
            box-shadow:
                0 8px 16px rgba(0, 0, 0, 0.12),
                0 4px 8px rgba(66, 122, 196, 0.25);
        }

        /* Saat ditekan */
        #prevBtn:active,
        #nextBtn:active {
            transform: translateY(0);
            box-shadow:
                0 3px 6px rgba(0, 0, 0, 0.08);
        }

        /* Judul bulan */
        .month {
            font-size: 22px;
            font-weight: 700;
            min-width: 180px;
            text-align: center;
        }


        @media (max-width:768px) {

            .layout {
                display: block !important;
            }

            .layout main {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .container.kalender {
                margin: 10px !important;
                padding: 15px !important;
                border-radius: 14px;
            }

        }

        @media (max-width:768px) {

            .calendar {
                grid-template-columns: repeat(7, 1fr);
            }

            .day {
                min-height: 70px;
                padding-top: 28px;
            }

            .dateNum {
                font-size: 12px;
            }

            .event {
                font-size: 8px;
                border-radius: 4px;
                padding: 2px 4px;
            }

            .month {
                font-size: 16px;
                min-width: auto;
            }

            #prevBtn,
            #nextBtn {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }

        }


        @media (max-width:768px) {

            table {
                min-width: 650px;
            }

            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

        }

        @media (max-width:768px) {

            .progress-wrapper {
                display: flex;
                flex-direction: row;
                /* 🔥 paksa horizontal */
                align-items: center;
                gap: 6px;
            }

            .star-rating {
                font-size: 22px;
                /* sedikit lebih kecil supaya muat */
            }

        }


        /*today*/
        /* Hari ini */
        .day.today {
            background: #dbeafe !important;
            border: 2px solid #2563eb;
        }

        .day.today .dateNum {
            background: #2563eb;
            color: white !important;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: auto;
        }

        /*end*/

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 0;
            border: 1px solid #e2e8f0;
        }

        .weekday {
            text-align: center;
            font-weight: 600;
            padding: 10px 0;
            background: #3b49df;
            color: white;
            font-size: 13px;
            border: 1px solid #e2e8f0;
        }

        .day {
            min-height: 110px;
            padding: 42px 8px 8px 8px;
            /* ruang untuk tanggal */
            border: 1px solid #e2e8f0;
            background: white;
            position: relative;

            display: flex;
            flex-direction: column;
            gap: 4px;
            /* jarak antar event rapi */
        }

        .dateNum {
            font-size: 14px;
            font-weight: 600;
        }


        /*tanggal Merah*/
        /* Header Minggu merah */
        .weekday:nth-child(1) {
            background: #dc2626;
            color: white;
        }

        /* Semua kolom Minggu merah */
        .calendar>.day:nth-child(7n+8) {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* Angka tanggal Minggu lebih tegas */
        .calendar>.day:nth-child(7n+8) .dateNum {
            color: #b91c1c;
            font-weight: 700;
        }


        /*Posisi Tengah Ats*/
        .dateNum {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 14px;
            font-weight: 600;
        }

        h3 {
            margin-top: 30px;
        }


        /*======= CSS tab ======*/

        .tab-wrapper {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: #e5e7eb;
            font-weight: 600;
        }

        .tab-btn.active {
            background: #3b49df;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }



        /* Tombol Tambah */
        .add-btn {
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
        }

        .add-btn:hover {
            background: #15803d;
        }

        /* TABEL */
        .table-wrapper {
            overflow-x: auto;
            width: 100%;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: var(0 6px 18px rgba(20, 24, 66, 0.08));
        }

        .day.selected-day {
            outline: 2px solid #2563eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            /* masih boleh ada */
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #3b49df;
            color: white;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background: #f0f7ff;
        }

        .btn-delete {
            background: #ff4444;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
        }

        .legend-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 10px 0 20px 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            background: white;
            padding: 6px 10px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        /*=====CSS tombol Filter=====*/
        .controls {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            /* isi dorong ke kanan */
            margin-bottom: 20px;
        }

        .month-nav {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-input {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            width: 220px;
            font-size: 14px;
        }

        .filter-input:focus {
            outline: none;
            border-color: #3b49df;
            box-shadow: 0 0 0 3px rgba(59, 73, 223, 0.2);
        }

        .month-nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }


        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(3px);
            justify-content: center;
            align-items: center;
            z-index: 100;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 450px;
            box-shadow: var(0 6px 18px rgba(20, 24, 66, 0.08));
            animation: fadeUp .3s ease;
        }

        @keyframes fadeUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal h2 {
            text-align: center;
            color: #1a2a80;
            margin-bottom: 10px;
        }

        label {
            font-weight: 600;
            font-size: 14px;
            margin-top: 10px;
            display: block;
        }

        .btn-save {
            background: #2563eb;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            width: 100%;
            margin-top: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            width: 100%;
            margin-top: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-content form {
            margin-top: 10px;
        }

        .modal-content label {
            margin-bottom: 4px;
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            margin-top: 4px;
            margin-bottom: 14px;
            box-sizing: border-box;
            /* ini penting */
            font-size: 14px;
        }

        .modal-content textarea {
            resize: vertical;
            min-height: 80px;
        }

        @media (max-width:768px) {

            .day {
                min-height: 80px;
            }

            .weekday {
                font-size: 11px;
                padding: 6px 0;
            }

            .event {
                font-size: 9px;
                padding: 3px 4px;
                line-height: 1.1;

                white-space: normal;
                /* boleh turun */
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                /* maksimal 2 baris */
                -webkit-box-orient: vertical;
            }

            .add-btn {
                width: 100%;
                justify-content: center;
                padding: 14px 0;
                font-size: 15px;
                border-radius: 12px;
                box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
            }

            .tab-wrapper {
                margin-bottom: 20px;
            }

            .add-btn {
                margin-top: 10px;
                margin-bottom: 20px;
            }

            .controls {
                position: static;
                display: flex;
                flex-direction: column;
                /* susun ke bawah */
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
                /* lebih rapat */
            }

            .month-nav {
                position: static;
                transform: none;
            }

            .filter-input {
                width: 100%;
                max-width: 260px;
            }

            main {
                padding: 12px;
                /* lebih kecil */
                max-width: 100%;
                /* full */
                margin: 0;
            }

            .day {
                padding: 42px 4px 4px 4px;
                /* ruang untuk tanggal */

            }

            /* ================= MOBILE TAB STYLE PREMIUM ================= */
            @media (max-width:768px) {

                .tab-wrapper {
                    display: flex;
                    width: 100%;
                    margin-bottom: 20px;
                    gap: 8px;
                    background: #f1f5ff;
                    border-radius: 14px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
                }

                .tab-btn {
                    flex: 1;
                    border: none;
                    border-radius: 10px;
                    padding: 12px 0;
                    font-size: 14px;
                    font-weight: 600;
                    background: #e5e7eb;
                    transition: all 0.25s ease;
                }

                .tab-btn.active {
                    background: #3b49df;
                    color: white;
                    box-shadow: 0 4px 10px rgba(59, 73, 223, 0.35);
                    transform: translateY(-2px);
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

            <div class="container kalender">

                <div class="page-hero">
                    <div>
                        <h1>📅 Kalender PPI</h1>
                        <small>Pengelolaan Master Data Aplikasi</small>
                    </div>
                    <button class="hero-btn" onclick="kembaliDashboard()">🏠 Dashboard</button>
                </div>

                <!--TAB BUTTON -->
                <div class="tab-wrapper">
                    <button class="tab-btn active" data-tab="tab1">📅 Kalender</button>
                    <button class="tab-btn" data-tab="tab2">📋 Kelola Acara</button>
                </div>

                <!--================= TAB 1 ================= -->
                <div id="tab1" class="tab-content active">

                    <!--===== CONTROL BULAN + FILTER ===== -->
                    <div class="controls">

                        <!--Navigasi Bulan -->
                        <div class="month-nav">
                            <button type="button" id="prevBtn">‹</button>
                            <span class="month" id="monthLabel"></span>
                            <button type="button" id="nextBtn">›</button>
                        </div>

                        <!--Filter Kategori -->
                        <input
                            type="text"
                            id="filterKategori"
                            class="filter-input"
                            placeholder="🔎 Filter kategori...">

                    </div>

                    <!--===== KALENDER GRID ===== -->
                    <div id="calendar" class="calendar"></div>

                    <!--===== TABEL HASIL TANGGAL DIPILIH ===== -->
                    <h3 style="margin-top:30px;">
                        📋 Daftar Acara (Tanggal Dipilih)
                    </h3>

                    <!--Legend Warna Kategori -->
                    <div id="legendKategori" class="legend-wrapper"></div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th style="width:120px;">Tanggal</th>
                                    <th>Judul</th>
                                    <th style="width:160px;">Kategori</th>
                                    <th style="width:100px;">Waktu</th>
                                    <th style="width:130px;">Pengulangan</th>
                                    <th>Keterangan</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>

                            <tbody id="filteredTable">
                                <tr>
                                    <td colspan="7" align="center">
                                        Klik tanggal pada kalender untuk melihat acara
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                </div>

                <!--================= TAB 2 ================= -->
                <div id="tab2" class="tab-content">

                    <button class="add-btn" id="openModal">
                        ➕ Tambah Acara
                    </button>

                    <h3 style="margin-top:20px;">📋 Semua Daftar Acara</h3>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Waktu</th>
                                    <th>Pengulangan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody id="eventTable">
                                <?php if (!empty($events)): ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($events as $e): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($e['tanggal']); ?></td>
                                            <td><?= htmlspecialchars($e['judul']); ?></td>
                                            <td><?= htmlspecialchars($e['nama_kegiatan']); ?></td>
                                            <td><?= !empty($e['waktu']) ? htmlspecialchars($e['waktu']) : '-'; ?></td>
                                            <td><?= !empty($e['pengulangan']) ? htmlspecialchars($e['pengulangan']) : '-'; ?></td>
                                            <td><?= !empty($e['keterangan']) ? htmlspecialchars($e['keterangan']) : '-'; ?></td>
                                            <td style="white-space:nowrap;">

                                                <!--TOMBOL EDIT -->
                                                <a href="kalender.php?edit=<?= $e['id']; ?>#tab2"
                                                    style="background:#2563eb;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;margin-right:5px;">
                                                    Edit
                                                </a>

                                                <!--TOMBOL HAPUS -->
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $e['id']; ?>">
                                                    <button type="submit"
                                                        name="hapus"
                                                        onclick="return confirm('Hapus acara ini?')"
                                                        class="btn-delete">
                                                        Hapus
                                                    </button>
                                                </form>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" align="center">Belum ada acara.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                    <!--===== PAGINATION ===== -->
                    <div style="margin-top:20px;text-align:center;">

                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1; ?>#tab2"
                                style="padding:6px 12px;background:#e5e7eb;border-radius:6px;text-decoration:none;margin-right:5px;">
                                ⬅ Prev
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPage; $i++): ?>
                            <a href="?page=<?= $i; ?>#tab2"
                                style="
                       padding:6px 10px;
                       margin:2px;
                       border-radius:6px;
                       text-decoration:none;
                       <?= $i == $page ? 'background:#3b49df;color:white;' : 'background:#e5e7eb;' ?>
                       ">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPage): ?>
                            <a href="?page=<?= $page + 1; ?>#tab2"
                                style="padding:6px 12px;background:#e5e7eb;border-radius:6px;text-decoration:none;margin-left:5px;">
                                Next ➡
                            </a>
                        <?php endif; ?>

                    </div>

                    <h3 style="margin-top:40px;">📌 Daftar Libur Nasional</h3>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Waktu</th>
                                    <th>Pengulangan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($liburData)): ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($liburData as $e): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($e['tanggal']); ?></td>
                                            <td><?= htmlspecialchars($e['judul']); ?></td>
                                            <td><?= htmlspecialchars($e['nama_kegiatan']); ?></td>
                                            <td><?= $e['waktu'] ?: '-'; ?></td>
                                            <td><?= $e['pengulangan'] ?: '-'; ?></td>
                                            <td><?= $e['keterangan'] ?: '-'; ?></td>

                                            <td style="white-space:nowrap;">
                                                <!--TOMBOL EDIT -->
                                                <a href="kalender.php?edit=<?= $e['id']; ?>#tab2"
                                                    style="background:#2563eb;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;margin-right:5px;">
                                                    Edit
                                                </a>

                                                <!--TOMBOL HAPUS -->
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $e['id']; ?>">
                                                    <button type="submit"
                                                        name="hapus"
                                                        onclick="return confirm('Hapus libur nasional ini?')"
                                                        class="btn-delete">
                                                        Hapus
                                                    </button>
                                                </form>

                                            </td>


                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" align="center">Belum ada data Libur Nasional.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top:20px;text-align:center;">

                        <?php if ($liburPage > 1): ?>
                            <a href="?page=<?= $page; ?>&libur_page=<?= $liburPage - 1; ?>#tab2"
                                style="padding:6px 12px;background:#e5e7eb;border-radius:6px;text-decoration:none;margin-right:5px;">
                                ⬅ Prev
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalLiburPage; $i++): ?>
                            <a href="?page=<?= $page; ?>&libur_page=<?= $i; ?>#tab2"
                                style="
                                           padding:6px 10px;
                                           margin:2px;
                                           border-radius:6px;
                                           text-decoration:none;
                                           <?= $i == $liburPage ? 'background:#3b49df;color:white;' : 'background:#e5e7eb;' ?>
                                           ">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($liburPage < $totalLiburPage): ?>
                            <a href="?page=<?= $page; ?>&libur_page=<?= $liburPage + 1; ?>#tab2"
                                style="padding:6px 12px;background:#e5e7eb;border-radius:6px;text-decoration:none;margin-left:5px;">
                                Next ➡
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

                <!--================= MODAL FORM ================= -->
                <div class="modal" id="modalForm">

                    <div class="modal-content">

                        <h2>
                            <?= isset($editData) && $editData ? '✏️ Edit Acara' : '➕ Tambah Acara'; ?>
                        </h2>

                        <form method="POST">

                            <?php if (isset($editData) && $editData): ?>
                                <input type="hidden" name="id_edit" value="<?= $editData['id']; ?>">
                            <?php endif; ?>

                            <!--JUDUL -->
                            <label>Judul Acara</label>
                            <input
                                type="text"
                                name="judul"
                                required
                                value="<?= isset($editData) ? htmlspecialchars($editData['judul']) : ''; ?>">

                            <!--KATEGORI -->
                            <label>Kategori</label>
                            <select name="kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori_list as $k): ?>
                                    <option
                                        value="<?= $k['id']; ?>"
                                        <?= (isset($editData) && $editData['kategori_id'] == $k['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($k['nama_kegiatan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!--TANGGAL -->
                            <label>Tanggal</label>
                            <input
                                type="date"
                                name="tanggal"
                                required
                                value="<?= isset($editData) ? $editData['tanggal'] : ''; ?>">

                            <!--WAKTU -->
                            <label>Waktu (opsional)</label>
                            <input
                                type="time"
                                name="waktu"
                                value="<?= isset($editData) ? $editData['waktu'] : ''; ?>">

                            <!--PENGULANGAN -->
                            <label>Pengulangan</label>
                            <select name="pengulangan">
                                <option value="">-- Tidak Berulang --</option>

                                <?php
                                $repeatOptions = [
                                    'harian' => 'Setiap Hari',
                                    'mingguan_1' => 'Seminggu 1x (Senin)',
                                    'mingguan_2' => 'Seminggu 2x (Senin & Kamis)',
                                    'bulanan' => 'Setiap Bulan (Tgl 01)',
                                    'triwulan' => 'Setiap Triwulan',
                                    'semester' => 'Setiap Semester',
                                    'tahunan' => 'Setiap Tahun'
                                ];

                                foreach ($repeatOptions as $val => $label):
                                ?>
                                    <option
                                        value="<?= $val; ?>"
                                        <?= (isset($editData) && $editData['pengulangan'] == $val) ? 'selected' : ''; ?>>
                                        <?= $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!--KETERANGAN -->
                            <label>Keterangan</label>
                            <textarea name="keterangan"><?= isset($editData) ? htmlspecialchars($editData['keterangan']) : ''; ?></textarea>

                            <!--BUTTON -->
                            <button type="submit" class="btn-save" name="simpan">
                                <?= isset($editData) && $editData ? '💾 Update Acara' : '💾 Simpan'; ?>
                            </button>

                            <button type="button" class="btn-cancel" id="closeModal">
                                ❌ Batal
                            </button>

                        </form>

                    </div>

                </div>

            </div>


        </main>

    </div>


    <script src="/assets/js/utama.js?v=5"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {


            const events = <?php echo json_encode($calendarEvents); ?>;

            const progressData = <?php echo json_encode($progressList); ?>;

            const categoryColors = generateCategoryColors();

            const cal = document.getElementById("calendar");
            const monthLabel = document.getElementById("monthLabel");
            const filteredTable = document.getElementById("filteredTable");

            let date = new Date();
            let activeFilter = '';
            let selectedDate = null;

            function getProgress(eventId, tanggal) {

                // Cari event aslinya
                const eventObj = events.find(e => e.id == eventId);

                // 🔴 KHUSUS LIBUR NASIONAL
                if (eventObj && eventObj.nama_kegiatan.toLowerCase() === "libur nasional") {

                    const today = new Date();
                    const targetDate = new Date(tanggal);

                    // Kalau hari ini >= tanggal tersebut → 100%
                    if (today >= targetDate) {
                        return 5; // 5 bintang = 100%
                    } else {
                        return 0;
                    }
                }

                // === Selain Libur Nasional normal dari database ===
                const found = progressData.find(p =>
                    p.kalender_id == eventId && p.tanggal == tanggal
                );

                return found ? parseInt(found.progress) : 0;
            }

            function isRecurringMatch(event, isoDate) {

                if (!event.pengulangan) return false;

                const eventDate = new Date(event.tanggal);
                const checkDate = new Date(isoDate);

                const eventDay = eventDate.getDate();
                const checkDay = checkDate.getDate();

                const eventMonth = eventDate.getMonth();
                const checkMonth = checkDate.getMonth();

                switch (event.pengulangan) {

                    case 'harian':
                        return checkDate >= eventDate && checkDate.getDay() !== 0;

                    case 'mingguan_1':
                        return checkDate >= eventDate && checkDate.getDay() === 1;

                    case 'mingguan_2':
                        return checkDate >= eventDate &&
                            (checkDate.getDay() === 1 || checkDate.getDay() === 4);

                    case 'bulanan':

                        if (checkDate < eventDate) return false;

                        // tanggal target bulan ini
                        let target = new Date(checkDate.getFullYear(), checkDate.getMonth(), eventDay);

                        // kalau jatuh hari Minggu → tambah 1 hari (jadi Senin)
                        if (target.getDay() === 0) {
                            target.setDate(target.getDate() + 1);
                        }

                        return checkDate.toDateString() === target.toDateString();

                    case 'triwulan':

                        if (checkDate < eventDate) return false;

                        if ((checkMonth % 3) !== (eventMonth % 3)) return false;

                        let targetTriwulan = new Date(checkDate.getFullYear(), checkMonth, eventDay);

                        if (targetTriwulan.getDay() === 0) {
                            targetTriwulan.setDate(targetTriwulan.getDate() + 1);
                        }

                        return checkDate.toDateString() === targetTriwulan.toDateString();

                    case 'semester':

                        if (checkDate < eventDate) return false;

                        // cek apakah bulan ini adalah bulan semester (interval 6 bulan)
                        if (Math.abs(checkMonth - eventMonth) % 6 !== 0) return false;

                        // buat tanggal target di bulan semester
                        let targetSemester = new Date(checkDate.getFullYear(), checkMonth, eventDay);

                        // kalau Minggu → geser ke Senin
                        if (targetSemester.getDay() === 0) {
                            targetSemester.setDate(targetSemester.getDate() + 1);
                        }

                        return checkDate.toDateString() === targetSemester.toDateString();

                    case 'tahunan':
                        return checkDate >= eventDate &&
                            checkDay === eventDay &&
                            checkMonth === eventMonth;

                    default:
                        return false;
                }
            }

            // ================= TAB SWITCH =================
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                    this.classList.add('active');
                    document.getElementById(this.dataset.tab).classList.add('active');
                });
            });


            // =====Kotak Legend yg ada warnanya =====

            function renderLegend(currentYear, currentMonth) {

                const legendWrapper = document.getElementById('legendKategori');
                legendWrapper.innerHTML = '';

                const kategoriMap = {};

                const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

                for (let day = 1; day <= daysInMonth; day++) {

                    const isoDate = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;

                    events.forEach(e => {

                        const match =
                            e.tanggal === isoDate ||
                            isRecurringMatch(e, isoDate);

                        if (match) {

                            if (!kategoriMap[e.nama_kegiatan]) {
                                kategoriMap[e.nama_kegiatan] = {
                                    total: 0,
                                    count: 0
                                };
                            }

                            kategoriMap[e.nama_kegiatan].total += getProgress(e.id, isoDate);
                            kategoriMap[e.nama_kegiatan].count++;
                        }

                    });
                }

                Object.keys(kategoriMap).forEach(cat => {

                    const data = kategoriMap[cat];

                    const percent = data.count > 0 ?
                        Math.round((data.total / (data.count * 5)) * 100) :
                        0;

                    const item = document.createElement('div');
                    item.className = 'legend-item';

                    const colorBox = document.createElement('div');
                    colorBox.className = 'legend-color';
                    colorBox.style.background = getColor(cat);

                    const text = document.createElement('span');
                    text.textContent = `${cat} (${percent}%)`;

                    item.appendChild(colorBox);
                    item.appendChild(text);
                    legendWrapper.appendChild(item);
                });

            }

            // === Generate warna berbeda jauh ===
            function generateCategoryColors() {

                const kategoriSet = new Set(events.map(e => e.nama_kegiatan));
                const kategoriArray = Array.from(kategoriSet).sort();

                const total = kategoriArray.length;
                const colorMap = {};

                kategoriArray.forEach((cat, index) => {

                    const hue = Math.round((index * 360) / total);
                    colorMap[cat] = `hsl(${hue}, 75%, 38%)`;

                });

                return colorMap;
            }

            // ================= RENDER CALENDAR =================
            function renderCalendar() {

                const year = date.getFullYear();
                const month = date.getMonth();

                monthLabel.textContent =
                    date.toLocaleString('id-ID', {
                        month: 'long',
                        year: 'numeric'
                    });
                renderLegend(year, month);

                const firstDay = new Date(year, month, 1).getDay();
                const lastDate = new Date(year, month + 1, 0).getDate();

                cal.innerHTML = '';

                // Header weekday
                const weekdays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                weekdays.forEach(d => {
                    const head = document.createElement('div');
                    head.className = 'weekday';
                    head.textContent = d;
                    cal.appendChild(head);
                });

                const days = [];

                for (let i = 0; i < firstDay; i++) {
                    days.push({
                        num: '',
                        inactive: true
                    });
                }

                for (let i = 1; i <= lastDate; i++) {
                    days.push({
                        num: i,
                        inactive: false
                    });
                }

                days.forEach(d => {

                    const cell = document.createElement('div');

                    const today = new Date();
                    const isToday =
                        d.num &&
                        year === today.getFullYear() &&
                        month === today.getMonth() &&
                        d.num === today.getDate();

                    cell.className = 'day' +
                        (d.inactive ? ' inactive' : '') +
                        (isToday ? ' today' : '');

                    if (d.num) {
                        cell.innerHTML = `<div class="dateNum">${d.num}</div>`;
                    }

                    const iso = d.num ?
                        `${year}-${String(month+1).padStart(2,'0')}-${String(d.num).padStart(2,'0')}` :
                        null;

                    if (d.num) {

                        const dayEvents = events.filter(e => {

                            const matchDate = e.tanggal === iso || isRecurringMatch(e, iso);

                            if (!matchDate) return false;

                            if (activeFilter === '') return true;

                            return e.nama_kegiatan.toLowerCase().includes(activeFilter);
                        });


                        // ===== CEK LIBUR NASIONAL =====
                        const isLibur = dayEvents.some(e =>
                            e.nama_kegiatan &&
                            e.nama_kegiatan.toLowerCase() === 'libur nasional'
                        );

                        if (isLibur) {
                            cell.classList.add('libur');
                        }

                        dayEvents.forEach(e => {

                            const tag = document.createElement('div');
                            tag.className = 'event';
                            tag.style.background = getColor(e.nama_kegiatan);

                            const progress = getProgress(e.id, iso);

                            if (progress === 5) {
                                tag.innerHTML = `👑 ${e.judul}`;
                            } else {
                                tag.textContent = e.judul;
                            }

                            cell.appendChild(tag);

                        });

                        cell.addEventListener('click', function() {

                            document.querySelectorAll('.day')
                                .forEach(d => d.classList.remove('selected-day'));

                            cell.classList.add('selected-day');

                            highlightDate(iso);
                        });
                    }

                    cal.appendChild(cell);
                });

            }

            // ================= FILTER TABLE =================
            function highlightDate(tanggal) {

                selectedDate = tanggal;

                const filtered = events.filter(e => {

                    const matchDate = e.tanggal === tanggal || isRecurringMatch(e, tanggal);
                    if (!matchDate) return false;

                    if (activeFilter === '') return true;

                    return e.nama_kegiatan.toLowerCase().includes(activeFilter);
                });

                filteredTable.innerHTML = '';

                if (filtered.length === 0) {
                    filteredTable.innerHTML = `
      <tr>
        <td colspan="7" align="center">
          Tidak ada acara
        </td>
      </tr>`;
                    return;
                }

                filtered.forEach((e, i) => {
                    filteredTable.innerHTML += `
      <tr>
        <td>${i+1}</td>
        <td>${tanggal}</td>
        <td>${e.judul}</td>
        <td>
          <span class="kategori-badge"
                style="background:${getColor(e.nama_kegiatan)}">
            ${e.nama_kegiatan}
          </span>
        </td>
        <td>${e.waktu ? e.waktu : '-'}</td>
        <td>${e.pengulangan ? e.pengulangan : '-'}</td>
        <td>${e.keterangan ? e.keterangan : '-'}</td>
            <td>
              <div class="progress-wrapper">
                  ${e.nama_kegiatan.toLowerCase() === "libur nasional"
                     ? "" 
                     : generateStars(getProgress(e.id, tanggal), e.id)
                    }
                  <span class="progress-percent">
                     ${getProgress(e.id, tanggal) * 20}%
                  </span>
              </div>
            </td>
      </tr>
    `;
                });
            }

            // ================= WARNA KATEGORI =================
            function getColor(cat) {

                if (!cat) return "#475569";

                // 🔴 FIX khusus Libur Nasional
                if (cat.toLowerCase() === "libur nasional") {
                    // return "#dc2626";
                    return "#ef4444"; // merah cerah modern
                }

                return categoryColors[cat] || "#475569";
            }


            function generateStars(progress, id) {

                let starsHTML = `<div class="star-rating" data-id="${id}">`;

                for (let i = 1; i <= 5; i++) {
                    starsHTML += `
      <span class="star ${progress >= i ? 'filled' : ''}"
            data-value="${i}">
        ★
      </span>
    `;
                }

                starsHTML += `</div>`;

                return starsHTML;
            }

            document.addEventListener("click", function(e) {

                if (e.target.classList.contains("star")) {

                    const value = e.target.getAttribute("data-value");
                    const parent = e.target.closest(".star-rating");
                    const id = parent.getAttribute("data-id");

                    fetch("kalender.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `ajax=update_progress&id=${id}&progress=${value}&tanggal=${selectedDate}`
                        })

                        .then(res => res.text())
                        .then(() => {

                            parent.querySelectorAll(".star").forEach(star => {
                                star.classList.remove("filled");

                                if (star.getAttribute("data-value") <= value) {
                                    star.classList.add("filled");
                                }
                            });

                            // 🔥 UPDATE PERSENTASE LANGSUNG
                            const percentDiv = parent.parentElement.querySelector(".progress-percent");
                            if (percentDiv) {
                                percentDiv.textContent = (value * 20) + "%";
                            }

                            // UPDATE progressData realtime
                            let existing = progressData.find(p =>
                                p.kalender_id == id && p.tanggal == selectedDate
                            );

                            if (existing) {
                                existing.progress = parseInt(value);
                            } else {
                                progressData.push({
                                    kalender_id: id,
                                    tanggal: selectedDate,
                                    progress: parseInt(value)
                                });
                            }

                            // UPDATE LEGEND REALTIME
                            renderLegend(date.getFullYear(), date.getMonth());

                            // 🔥 TAMBAHKAN INI
                            renderCalendar();

                        });

                }

            });

            // ================= NAV BULAN =================
            document.getElementById("prevBtn").addEventListener('click', function() {
                date.setMonth(date.getMonth() - 1);
                renderCalendar(); // <- WAJIB
            });

            document.getElementById("nextBtn").addEventListener('click', function() {
                date.setMonth(date.getMonth() + 1);
                renderCalendar(); // <- WAJIB
            });

            // ================= MODAL =================
            const modal = document.getElementById('modalForm');
            const openBtn = document.getElementById('openModal');
            const closeBtn = document.getElementById('closeModal');

            if (openBtn) {
                openBtn.onclick = function() {

                    // Reset form ke mode tambah
                    modal.querySelector("form").reset();

                    // Hapus hidden id_edit kalau ada
                    let hiddenEdit = modal.querySelector('input[name="id_edit"]');
                    if (hiddenEdit) {
                        hiddenEdit.remove();
                    }

                    // Ubah judul modal
                    modal.querySelector("h2").innerHTML = "➕ Tambah Acara";

                    modal.style.display = 'flex';
                };
            }

            if (closeBtn) {
                closeBtn.onclick = function() {
                    window.location.href = "kalender.php";
                };
            }
            window.onclick = function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            };

            // FILTER KATEGORI
            document.getElementById('filterKategori').addEventListener('input', function() {
                activeFilter = this.value.toLowerCase();
                renderCalendar();
            });





            renderCalendar();

            <?php if (isset($editData) && $editData): ?>
                // Buka otomatis tab 2
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                document.querySelector('[data-tab="tab2"]').classList.add('active');
                document.getElementById('tab2').classList.add('active');

                // Buka modal otomatis
                document.getElementById('modalForm').style.display = 'flex';
            <?php endif; ?>

        });
    </script>

    <!--=====Pignation=====-->
    <script>
        if (window.location.hash === "#tab2") {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            document.querySelector('[data-tab="tab2"]').classList.add('active');
            document.getElementById('tab2').classList.add('active');
        }
    </script>

    <script>
        function kembaliDashboard() {
            window.location.href = "../dashboard.php";
        }
    </script>


</body>

</html>