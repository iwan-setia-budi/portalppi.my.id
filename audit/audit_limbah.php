<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include_once '../cek_akses.php';
$conn = $koneksi;

$fotos = mysqli_query($conn, "
    SELECT 
        tanggal,
        keterangan,
        COUNT(*) AS jumlah
    FROM tb_audit_limbah_foto
    GROUP BY tanggal, keterangan
    ORDER BY tanggal DESC
");

?>

<?php
if (isset($_GET['hapus_foto_group'])) {

    $tanggal = $_GET['hapus_foto_group'];
    $ket = $_GET['ket'];

    // ambil semua foto dulu (untuk hapus file)
    $q = mysqli_query($conn, "
        SELECT foto 
        FROM tb_audit_limbah_foto
        WHERE tanggal='$tanggal'
        AND keterangan='$ket'
    ");

    while ($d = mysqli_fetch_assoc($q)) {
        @unlink("../uploads/audit_limbah/" . $d['foto']);
    }

    // hapus dari database
    mysqli_query($conn, "
        DELETE FROM tb_audit_limbah_foto
        WHERE tanggal='$tanggal'
        AND keterangan='$ket'
    ");

    echo "<script>
        alert('Semua foto berhasil dihapus');
        location.href='audit_limbah.php?tab=foto';
    </script>";
    exit;
}
?>


<?php

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($conn, "DELETE FROM tb_audit_limbah_detail WHERE audit_id = $id");
    mysqli_query($conn, "DELETE FROM tb_audit_limbah WHERE id = $id");

    echo "<script>
            alert('Data audit berhasil dihapus');
            window.location.href = 'audit_limbah.php?tab=rekap';
        </script>";
    exit;
}
?>


<?php
if (isset($_POST['submit'])) {

    $data = json_decode($_POST['payload'], true);

    $tanggal = $data['tanggal'];
    $nama_petugas = $data['namaPet'];
    $nama_auditor = $data['namaAud'];
    $total_ya = $data['yaCount'];
    $persentase = $data['percent'];
    $kategori = $data['kategori'];
    $ket_tambahan = $data['ketTambahan'];
    $ttd_petugas = $data['petImg'];
    $ttd_auditor = $data['audImg'];

    // SIMPAN HEADER

    $sql = "INSERT INTO tb_audit_limbah 
(tanggal, nama_petugas, nama_auditor, total_ya, persentase, kategori, keterangan, ttd_petugas, ttd_auditor)

        
        VALUES (
            '$tanggal',
            '$nama_petugas',
            '$nama_auditor',
            '$total_ya',
            '$persentase',
            '$kategori',
            '$ket_tambahan',
            '$ttd_petugas',
            '$ttd_auditor'
        )";

    mysqli_query($conn, $sql);

    $audit_id = mysqli_insert_id($conn);

    // SIMPAN DETAIL
    foreach ($data['items'] as $i => $it) {

        $no = $i + 1;

        if ($it['y']) {
            $jawaban = 'YA';
        } elseif ($it['t']) {
            $jawaban = 'TIDAK';
        } else {
            continue;
        }

        $ket = $it['ket'];

        mysqli_query($conn, "
        INSERT INTO tb_audit_limbah_detail
        (audit_id, item_no, jawaban, keterangan)
        VALUES
        ('$audit_id', '$no', '$jawaban', '$ket')
    ");
    }


    echo "<script>
        alert('Data audit berhasil disimpan');
        location.href = 'audit_limbah.php?id=$audit_id';
    </script>";
    exit;



}
?>

<?php
$rekap = mysqli_query($conn, "
    SELECT id, tanggal, total_ya, persentase, kategori
    FROM tb_audit_limbah
    ORDER BY id DESC
");
?>




<?php
if (isset($_POST['simpan_foto'])) {

    $tanggal = $_POST['foto_tanggal'];
    $ket = $_POST['foto_ket'];

    $uploadDir = "../uploads/audit_limbah/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['foto']['tmp_name'] as $i => $tmp) {
        if (!is_uploaded_file($tmp))
            continue;

        $ext = pathinfo($_FILES['foto']['name'][$i], PATHINFO_EXTENSION);
        $nama = uniqid('limbah_', true) . '.' . $ext;

        move_uploaded_file($tmp, $uploadDir . $nama);

        mysqli_query($conn, "
            INSERT INTO tb_audit_limbah_foto (tanggal, foto, keterangan)
            VALUES ('$tanggal','$nama','$ket')
        ");
    }

    echo "<script>
        alert('Foto berhasil disimpan');
        location.href = 'audit_limbah.php?tab=foto';
    </script>";
    exit;

}
?>


<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "Audit Limbah";
?>
<!--end-->


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3.0" />
    <title>Audit Limbah</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        .main-content {
            --audit-brand: #2b60d3;
            --muted: #607a92;
            --card: #fff;
            --bg: #f7f9fc;
            --border: #d6e0f2;
            margin-left: 0px;
            padding: 16px;
            width: 100%;
        }



        * {
            box-sizing: border-box;
        }


        .wrapper {
            width: 100%;
            max-width: 1380px;
            margin: 0 auto 24px;
            background: var(--card);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 14px 40px rgba(43, 96, 211, 0.08);
        }

        @media print {
            .table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                height: auto !important;
            }
        }

        .header-row {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e7eefb;
        }

        #logo {
            width: 120px;
            height: 48px;
            object-fit: contain;
            border-radius: 6px;
            border: 1px dashed #e6eefc;
        }

        .title-wrap {
            flex: 1;
            text-align: left;
        }

        h1 {
            margin: 0;
            font-size: clamp(20px, 2vw, 28px);
            color: var(--audit-brand);
            font-weight: 700;
            line-height: 1.2;
        }

        .meta {
            font-size: 13px;
            color: var(--muted);
            margin-top: 6px;
        }

        .tabbar {
            display: flex;
            gap: 8px;
            margin: 14px 0 12px;
            flex-wrap: wrap;
        }

        .tab {
            padding: 10px 16px;
            border-radius: 12px;
            background: #eaf0ff;
            border: 1px solid #d8e4fb;
            cursor: pointer;
            font-weight: 600;
            min-width: 120px;
            transition: .2s ease;
        }

        .tab:hover {
            background: #dfe9ff;
        }

        .tab.active {
            background: var(--audit-brand);
            color: #fff;
        }

        .controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            padding: 14px 16px;
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7ff 100%);
            border: 1px solid #dce6f7;
            border-radius: 14px;
        }

        .controls label {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            color: #18314f;
        }

        .controls input[type="date"] {
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid #ccd9f5;
            border-radius: 10px;
            background: #fff;
        }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        .table-hint {
            display: none;
            margin: 0 0 10px;
            font-size: 12px;
            color: var(--muted);
        }

        .table-wrap {
            width: 100% !important;
        }

        .table-wrap table {
            width: 100% !important;
            min-width: unset !important;
        }


        /* FORM TAB saja yang bisa scroll horizontal */
        #formTab .table-wrap table {
            min-width: 760px;
            width: max-content;
        }

        #auditTable th:nth-child(1),
        #auditTable td:nth-child(1) {
            width: 52px;
            min-width: 52px;
        }

        #auditTable th:nth-child(2),
        #auditTable td:nth-child(2) {
            min-width: 360px;
        }

        #auditTable th:nth-child(3),
        #auditTable th:nth-child(4),
        #auditTable td:nth-child(3),
        #auditTable td:nth-child(4) {
            width: 64px;
            min-width: 64px;
        }

        #auditTable th:nth-child(5),
        #auditTable td:nth-child(5) {
            min-width: 220px;
        }

        #auditTable .ket {
            min-width: 180px;
            border: 1px solid #d7e2f5;
            border-radius: 8px;
            background: #fbfdff;
        }

        .audit-item-row td {
            transition: background .2s ease, border-color .2s ease;
        }

        /* Rekap & Foto normal full width */
        #rekapTab table,
        #fotoTab table {
            width: 100%;
            min-width: unset;
        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #eef4ff;
            border-right: 1px solid #f2f6ff;
            font-size: 13px;
        }

        th {
            background: #f0f6ff;
            color: #123;
            font-weight: 700;
            text-align: center;
        }

        td.center {
            text-align: center;
        }

        .result-box {
            margin-top: 12px;
            padding: 14px 16px;
            background: #eef4ff;
            border-left: 5px solid var(--audit-brand);
            border-radius: 12px;
            line-height: 1.6;
        }

        #resultSummary {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 14px;
            align-items: center;
        }

        .signatures {
            display: flex;
            gap: 18px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .sig {
            background: #fff;
            padding: 14px;
            border-radius: 14px;
            border: 1px solid var(--border);
            width: calc(50% - 9px);
            min-width: 260px;
        }

        .canvas-wrap {
            border: 1px dashed #cbd9f8;
            height: 140px;
            border-radius: 6px;
            background: #fafcff;
            position: relative;
        }

        canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        .small {
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 8px;
            border: 0;
            background: var(--audit-brand);
            color: #fff;
            cursor: pointer;
        }

        .btn-secondary {
            background: #6e8ccf;
        }

        .bottom-toolbar {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 18px;
        }

        .rekap-table {
            width: 100%;
            border-collapse: collapse;
        }

        #rekapTab td:last-child a,
        #fotoTab td:last-child a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 6px 10px;
            border-radius: 8px;
            text-decoration: none;
            margin: 2px 4px 2px 0;
            background: #edf4ff;
        }

        #rekapTab td:last-child a[style*="red"],
        #fotoTab td:last-child a[style*="red"] {
            background: #fff1f1;
        }

        .rekap-table th,
        .rekap-table td {
            padding: 8px;
            border: 1px solid #e6eefc;
        }

        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(10, 20, 40, 0.45);
            z-index: 9999;
        }

        .modal .card {
            width: 90%;
            max-width: 900px;
            background: #fff;
            padding: 16px;
            border-radius: 10px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .modal.show {
            display: flex;
        }

        .ket-full {
            background: #fff;
            padding: 14px;
            border-radius: 14px;
            border: 1px solid var(--border);
            margin-top: 12px;
        }

        .ket-full textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #dce6f7;
            resize: vertical;
            font-family: inherit;
        }

        .audit-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .audit-wrapper .table-wrap {
            overflow-x: auto;
        }


        /* ====== REKAP & FOTO (FULL WIDTH NORMAL) ====== */
        .rekap-table {
            width: 100%;
            min-width: unset;
        }

        #rekapTab table,
        #fotoTab table {
            width: 100%;
        }


        .audit-wrapper {
            position: relative;
            max-width: 100%;
        }




        /* ===== MOBILE OPTIMIZATION ===== */
        @media(max-width: 600px) {

            .main-content {
                padding: 8px;
            }

            #logo {
                width: 80px;
                height: auto;
            }

            h1 {
                font-size: 18px;
            }

            .tabbar {
                flex-wrap: wrap;
            }

            .tab {
                flex: 1 1 calc(50% - 8px);
                text-align: center;
                min-width: 0;
            }




            .canvas-wrap {
                height: 200px;
            }

            .signatures {
                flex-direction: column;
            }

            .sig {
                width: 100% !important;
            }

            .modal .card {
                width: 95%;
                padding: 12px;
            }

            .bottom-toolbar {
                flex-direction: column;
                /* stack ke bawah */
                align-items: stretch;
                /* full width */
                gap: 10px;
            }

            .bottom-toolbar .small {
                width: 100%;
                /* tombol melebar */
                padding: 14px;
                /* lebih nyaman disentuh */
                font-size: 15px;
                border-radius: 12px;
            }

            #formTab .table-wrap table {
                min-width: unset;
                width: 100% !important;
            }

            #formTab .table-wrap {
                border: none;
                background: transparent;
                box-shadow: none;
                overflow: visible;
            }

            #auditTable {
                display: block;
                width: 100% !important;
            }

            #auditTable thead {
                display: none;
            }

            #auditTable tbody {
                display: grid;
                gap: 10px;
            }

            #auditTable tr.audit-item-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                padding: 12px;
                border: 1px solid rgba(214, 224, 242, .95);
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            }

            #auditTable tr.audit-item-row td {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                padding: 0;
                border: none;
                font-size: 12px;
                min-width: 0;
            }

            #auditTable tr.audit-item-row td::before {
                content: attr(data-label);
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .02em;
                text-transform: uppercase;
                color: var(--muted);
            }

            #auditTable tr.audit-item-row .cell-monitoring,
            #auditTable tr.audit-item-row .cell-ket {
                grid-column: 1 / -1;
            }

            #auditTable tr.audit-item-row .cell-monitoring {
                font-size: 13px;
                line-height: 1.5;
            }

            #auditTable tr.audit-item-row .cell-no,
            #auditTable tr.audit-item-row .cell-ya,
            #auditTable tr.audit-item-row .cell-tidak {
                justify-content: center;
                min-height: 52px;
                border-radius: 10px;
                background: #f7faff;
                padding: 8px 10px;
                border: 1px solid #e3ebf9;
            }

            #auditTable tr.audit-item-row .cell-no {
                align-items: center;
                font-weight: 700;
                color: #193761;
            }

            #auditTable tr.audit-item-row .cell-ya input,
            #auditTable tr.audit-item-row .cell-tidak input {
                width: 18px;
                height: 18px;
                margin: 0;
            }

            #auditTable tr.audit-item-row .cell-ket input {
                width: 100% !important;
                min-width: 0;
            }

            #auditTable .cat-row {
                display: block;
                margin-top: 2px;
            }

            #auditTable .cat-row td {
                display: block;
                padding: 10px 12px;
                border: none;
                border-radius: 12px;
                font-size: 13px;
            }
        }


        @media(max-width:880px) {
            .sig {
                width: 100%;
            }

            #logo {
                width: 90px;
            }

            #formTab table {
                min-width: 680px;
            }
        }


        canvas {
            touch-action: none;
        }

        .foto-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .foto-grid img {
            height: 50px;
            border-radius: 4px;
            cursor: pointer;
        }


        .foto-view-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
        }

        .foto-view-grid img {
            width: 100%;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }

        @media(max-width: 768px) {

            .main-content {
                padding: 10px;
            }

            .wrapper {
                margin: 0 auto 16px;
                padding: 14px;
                border-radius: 16px;
            }

            .header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .title-wrap {
                width: 100%;
                text-align: left;
            }

            .controls {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .controls label {
                width: 100%;
                align-items: flex-start;
                flex-direction: column;
            }

            .controls input {
                width: 100%;
            }

            h1 {
                font-size: 18px;
                text-align: center;
            }

            .result-box {
                font-size: 13px;
            }

            #resultSummary {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            input[type="text"],
            input[type="date"],
            textarea {
                font-size: 16px;
                padding: 10px;
            }

            button {
                min-height: 44px;
            }

            .table-hint {
                display: block;
            }

            #rekapTab td:last-child,
            #fotoTab td:last-child {
                white-space: nowrap;
            }

            #rekapTab td:last-child a,
            #fotoTab td:last-child a {
                display: flex;
                width: 100%;
                margin: 4px 0;
            }
        }

        @media(max-width:600px) {
            .modal .card {
                max-height: 85vh;
                overflow-y: auto;
            }
        }

        #rekapTab .table-wrap {
            overflow-x: visible !important;
        }

        #rekapTab table {
            width: 100% !important;
            min-width: unset !important;
        }


        @media(max-width:600px) {

            .header-row {
                padding-bottom: 14px;
            }

            .wrapper {
                padding: 10px !important;
            }

            #rekapTab .table-wrap {
                margin: 0 -10px;
                /* tarik keluar padding wrapper */
                border-radius: 0;
            }

            #rekapTab table {
                width: 100%;
            }

        }


        /* Semua isi tbody di Rekap & Foto jadi center */
        #rekapTab tbody td,
        #fotoTab tbody td {
            text-align: center;
        }

        /* ===== FORM FOTO (RAPI & MODERN) ===== */
        .foto-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            background: #f8fbff;
            padding: 14px;
            border: 1px solid #d6e0f2;
            border-radius: 14px;
            margin: 15px 0;
        }

        .foto-form input[type="date"],
        .foto-form input[type="file"] {
            padding: 8px;
            border: 1px solid #ccd9f5;
            border-radius: 6px;
        }

        .foto-ket {
            flex: 1;
            min-width: 220px;
            padding: 8px;
            border: 1px solid #ccd9f5;
            border-radius: 6px;
        }

        .foto-form button {
            min-height: 38px;
        }

        /* MOBILE */
        @media(max-width:600px) {
            .foto-form {
                flex-direction: column;
                align-items: stretch;
            }

            .foto-form button {
                width: 100%;
            }
        }

        .tanggal-input {
            margin-left: 8px;
        }


        /* ============================================================
           DARK MODE — PREMIUM
        ============================================================ */
        @keyframes dmGlowBorder {

            0%,
            100% {
                box-shadow: 0 0 0 1px rgba(56, 189, 248, .18), 0 8px 32px rgba(0, 0, 0, .45);
            }

            50% {
                box-shadow: 0 0 0 1px rgba(56, 189, 248, .45), 0 8px 40px rgba(56, 189, 248, .12);
            }
        }

        @keyframes dmShimmerLine {
            0% {
                background-position: -400px 0;
            }

            100% {
                background-position: 400px 0;
            }
        }

        /* Variabel ulang untuk dark mode */
        body.dark-mode .main-content {
            --audit-brand: #38bdf8;
            --muted: #94a3b8;
            --card: #111827;
            --bg: #0b1220;
            --border: rgba(56, 189, 248, .18);
            background: var(--bg);
        }

        body.dark-mode main {
            background: #0b1220;
        }

        /* Wrapper utama */
        body.dark-mode .wrapper {
            background: #111827;
            border: 1px solid rgba(56, 189, 248, .15);
            box-shadow: 0 0 0 1px rgba(56, 189, 248, .1), 0 20px 60px rgba(0, 0, 0, .6);
            animation: dmGlowBorder 4s ease-in-out infinite;
        }

        /* Header judul */
        body.dark-mode h1 {
            color: #38bdf8;
            text-shadow: 0 0 18px rgba(56, 189, 248, .4);
        }

        body.dark-mode .meta {
            color: #94a3b8;
        }

        body.dark-mode #logo {
            border-color: rgba(56, 189, 248, .2);
            filter: brightness(.9);
        }

        /* Tab bar */
        body.dark-mode .tab {
            background: rgba(56, 189, 248, .08);
            color: #94a3b8;
            border: 1px solid rgba(56, 189, 248, .18);
            transition: all .25s;
        }

        body.dark-mode .tab:hover {
            background: rgba(56, 189, 248, .15);
            color: #e2e8f0;
            border-color: rgba(56, 189, 248, .45);
            box-shadow: 0 0 14px rgba(56, 189, 248, .2);
        }

        body.dark-mode .tab.active {
            background: linear-gradient(135deg, #0f5fa6, #1e88e5);
            color: #fff;
            border-color: rgba(56, 189, 248, .6);
            box-shadow: 0 0 22px rgba(56, 189, 248, .35), inset 0 1px 0 rgba(255, 255, 255, .1);
        }

        /* Tabel container */
        body.dark-mode .table-wrap {
            background: #0f172a;
            border-color: rgba(56, 189, 248, .15);
            box-shadow: inset 0 1px 0 rgba(56, 189, 248, .06);
        }

        body.dark-mode table {
            background: transparent;
        }

        body.dark-mode th {
            background: linear-gradient(180deg, #0f2744 0%, #0b1e35 100%);
            color: #7dd3fc;
            border-bottom: 1px solid rgba(56, 189, 248, .25);
            border-right: 1px solid rgba(56, 189, 248, .1);
            text-shadow: 0 0 10px rgba(56, 189, 248, .3);
            letter-spacing: .04em;
        }

        body.dark-mode td {
            color: #cbd5e1;
            border-bottom: 1px solid rgba(56, 189, 248, .07);
            border-right: 1px solid rgba(56, 189, 248, .05);
        }

        body.dark-mode tbody tr {
            background: transparent;
            transition: background .2s, box-shadow .2s;
        }

        body.dark-mode tbody tr:hover {
            background: rgba(56, 189, 248, .06);
            box-shadow: inset 0 0 0 1px rgba(56, 189, 248, .18);
        }

        /* Input / select dalam form */
        body.dark-mode input[type="date"],
        body.dark-mode input[type="text"],
        body.dark-mode input[type="file"],
        body.dark-mode textarea,
        body.dark-mode select {
            background: #0f172a;
            color: #e2e8f0;
            border-color: rgba(56, 189, 248, .25);
            caret-color: #38bdf8;
        }

        body.dark-mode input[type="date"]:focus,
        body.dark-mode input[type="text"]:focus,
        body.dark-mode textarea:focus {
            outline: none;
            border-color: rgba(56, 189, 248, .6);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, .12);
        }

        /* Checkbox estetika dark */
        body.dark-mode input[type="checkbox"] {
            accent-color: #38bdf8;
            width: 16px;
            height: 16px;
        }

        /* Keterangan input di kolom tabel */
        body.dark-mode td input[type="text"] {
            background: rgba(56, 189, 248, .06);
            border: 1px solid rgba(56, 189, 248, .2);
            border-radius: 6px;
            color: #e2e8f0;
            padding: 5px 8px;
        }

        /* Tombol utama */
        body.dark-mode .small,
        body.dark-mode button.small {
            background: linear-gradient(135deg, #0f5fa6, #1e88e5);
            border: 1px solid rgba(56, 189, 248, .4);
            box-shadow: 0 0 16px rgba(56, 189, 248, .25);
            transition: all .25s;
        }

        body.dark-mode .small:hover {
            background: linear-gradient(135deg, #1e88e5, #38bdf8);
            box-shadow: 0 0 28px rgba(56, 189, 248, .45);
            transform: translateY(-1px);
        }

        body.dark-mode .btn-secondary {
            background: rgba(110, 140, 207, .25);
            border: 1px solid rgba(110, 140, 207, .4);
        }

        body.dark-mode .btn-secondary:hover {
            background: rgba(110, 140, 207, .4);
            box-shadow: 0 0 18px rgba(110, 140, 207, .3);
        }

        /* Result box */
        body.dark-mode .result-box {
            background: rgba(30, 136, 229, .12);
            border-left-color: #38bdf8;
            color: #e2e8f0;
            box-shadow: inset 0 0 18px rgba(56, 189, 248, .06);
        }

        /* Signature area */
        body.dark-mode .sig {
            background: #0f172a;
            border-color: rgba(56, 189, 248, .2);
        }

        body.dark-mode .canvas-wrap {
            background: #0b1735;
            border-color: rgba(56, 189, 248, .3);
        }

        /* Keterangan tambahan area */
        body.dark-mode .ket-full {
            background: #0f172a;
            border-color: rgba(56, 189, 248, .18);
        }

        body.dark-mode .ket-full textarea {
            background: #0b1220;
            border-color: rgba(56, 189, 248, .25);
            color: #e2e8f0;
        }

        /* Foto form */
        body.dark-mode .foto-form {
            background: rgba(15, 23, 42, .8);
            border-color: rgba(56, 189, 248, .18);
        }

        body.dark-mode .foto-form input[type="date"],
        body.dark-mode .foto-form input[type="file"],
        body.dark-mode .foto-ket {
            background: #0b1220;
            border-color: rgba(56, 189, 248, .25);
            color: #e2e8f0;
        }

        /* Foto grid gambar */
        body.dark-mode .foto-view-grid img {
            border-color: rgba(56, 189, 248, .2);
            box-shadow: 0 4px 14px rgba(0, 0, 0, .4);
        }

        /* Modal */
        body.dark-mode .modal .card {
            background: #111827;
            border: 1px solid rgba(56, 189, 248, .25);
            box-shadow: 0 0 0 1px rgba(56, 189, 248, .15), 0 30px 80px rgba(0, 0, 0, .7);
            color: #e2e8f0;
        }

        /* Rekap table */
        body.dark-mode .rekap-table th,
        body.dark-mode .rekap-table td {
            border-color: rgba(56, 189, 248, .1);
        }

        /* Scrollbar dark mode */
        body.dark-mode .table-wrap::-webkit-scrollbar {
            height: 6px;
        }

        body.dark-mode .table-wrap::-webkit-scrollbar-track {
            background: #0b1220;
        }

        body.dark-mode .table-wrap::-webkit-scrollbar-thumb {
            background: rgba(56, 189, 248, .35);
            border-radius: 3px;
        }

        body.dark-mode .table-wrap::-webkit-scrollbar-thumb:hover {
            background: rgba(56, 189, 248, .6);
        }

        /* Garis shimmer atas wrapper */

        /* ===== CATEGORY ROW (light mode) ===== */
        .cat-row td {
            background: #dfe9ff;
            color: #1a355c;
        }

        /* ===== INFO BOX (light mode) ===== */
        .info-box {
            padding: 14px;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            color: #856404;
        }

        /* ===== DARK MODE — tambahan ===== */

        /* Category row dark */
        body.dark-mode .cat-row td {
            background: linear-gradient(90deg, rgba(15, 63, 116, .6), rgba(11, 18, 32, .4));
            color: #7dd3fc;
            border-bottom: 1px solid rgba(56, 189, 248, .2);
            text-shadow: 0 0 10px rgba(56, 189, 248, .25);
        }

        /* Info box dark */
        body.dark-mode .info-box {
            background: rgba(30, 58, 38, .5);
            border-color: rgba(74, 222, 128, .3);
            color: #86efac;
        }

        /* Label, strong, default text */
        body.dark-mode .wrapper label,
        body.dark-mode .wrapper strong,
        body.dark-mode .wrapper .controls label {
            color: #cbd5e1;
        }

        body.dark-mode .controls {
            background: linear-gradient(180deg, rgba(15, 23, 42, .92) 0%, rgba(11, 18, 32, .92) 100%);
            border-color: rgba(56, 189, 248, .18);
            box-shadow: inset 0 1px 0 rgba(56, 189, 248, .08);
        }

        body.dark-mode .controls input[type="date"] {
            background: #0f172a;
            color: #e2e8f0;
            border-color: rgba(56, 189, 248, .32);
            box-shadow: 0 0 0 1px rgba(56, 189, 248, .08);
            color-scheme: dark;
        }

        body.dark-mode .controls input[type="date"]:focus {
            border-color: rgba(56, 189, 248, .68);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, .14);
            outline: none;
        }

        body.dark-mode .controls input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(92%) sepia(8%) saturate(347%) hue-rotate(176deg) brightness(104%) contrast(95%);
            opacity: .92;
            cursor: pointer;
        }

        /* Link dalam tabel rekap */
        body.dark-mode .rekap-table a {
            color: #38bdf8;
        }

        body.dark-mode .rekap-table a[style*="red"] {
            color: #f87171 !important;
        }

        /* Sig label teks */
        body.dark-mode .sig label {
            color: #94a3b8;
            font-size: 13px;
            display: block;
            margin-bottom: 4px;
        }

        /* Tombol hapus tanda tangan */
        body.dark-mode .sig button {
            background: rgba(110, 140, 207, .2);
            color: #94a3b8;
            border: 1px solid rgba(56, 189, 248, .2);
            border-radius: 6px;
            padding: 6px 10px;
            cursor: pointer;
            transition: all .2s;
        }

        body.dark-mode .sig button:hover {
            background: rgba(56, 189, 248, .15);
            color: #e2e8f0;
            border-color: rgba(56, 189, 248, .45);
        }

        /* breadcrumb dark */
        body.dark-mode .breadcrumb {
            background: rgba(15, 23, 42, .8);
            border-color: rgba(56, 189, 248, .18);
            color: #94a3b8;
        }

        body.dark-mode .breadcrumb a {
            color: #38bdf8;
        }

        body.dark-mode .breadcrumb .sep {
            color: #475569;
        }

        /* Judul rekap h3 */
        body.dark-mode .wrapper h3 {
            color: #e2e8f0;
        }

        /* foto grid thumbnail border */
        body.dark-mode .foto-grid img {
            border: 1px solid rgba(56, 189, 248, .2);
            border-radius: 4px;
        }

        body.dark-mode .wrapper::before {
            content: '';
            display: block;
            height: 2px;
            background: linear-gradient(90deg, transparent, #38bdf8, transparent);
            background-size: 400px 2px;
            border-radius: 2px 2px 0 0;
            margin: -20px -20px 20px;
            animation: dmShimmerLine 3s linear infinite;
            opacity: .7;
        }

        @media(max-width: 600px) {
            body.dark-mode #formTab .table-wrap {
                background: transparent;
                border: none;
                box-shadow: none;
            }

            body.dark-mode #auditTable tr.audit-item-row {
                background: #121c30;
                border-color: rgba(56, 189, 248, .16);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .28);
            }

            body.dark-mode #auditTable tr.audit-item-row .cell-no,
            body.dark-mode #auditTable tr.audit-item-row .cell-ya,
            body.dark-mode #auditTable tr.audit-item-row .cell-tidak {
                background: rgba(56, 189, 248, .07);
                border-color: rgba(56, 189, 248, .14);
            }

            body.dark-mode #auditTable tr.audit-item-row td::before {
                color: #7dd3fc;
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


            <div class="main-content">

                <?php include_once '../breadcrumb.php'; ?>


                <!-- ====== KONTEN HALAMAN ====== -->

                <div class="wrapper audit-wrapper" id="printableArea">

                    <div class="header-row">
                        <img id="logo" alt="logo (ganti src nanti)"
                            src="<?= base_url('assets/images/logo phbw123.png') ?>" />
                        <div class="title-wrap">
                            <h1>Monitoring Audit & Supervisi PPI di Limbah Pihak ke 3 </h1>
                            <div class="meta">Form monitoring sesuai format</div>
                        </div>
                    </div>

                    <div class="tabbar">
                        <button class="tab active" data-tab="formTab">Form Audit</button>
                        <button class="tab" data-tab="rekapTab">Rekap</button>
                        <button class="tab" data-tab="fotoTab">Foto</button>
                    </div>

                    <!-- FORM TAB -->
                    <form method="POST" id="formAudit">
                        <input type="hidden" name="submit" value="1">
                        <input type="hidden" name="payload" id="payload">

                        <div id="formTab" class="tabContent">
                            <div class="controls">
                                <label><strong>Tanggal:</strong>
                                    <input id="tanggal" type="date" class="tanggal-input">
                                </label>
                                <div style="flex:1"></div>
                            </div>

                            <div class="table-hint">Geser tabel ke samping jika semua kolom belum terlihat.</div>

                            <div class="table-wrap">
                                <table id="auditTable" aria-label="Audit table">
                                    <thead>
                                        <tr>
                                            <th style="width:42px">NO</th>
                                            <th>MONITORING</th>
                                            <th style="width:60px">YA</th>
                                            <th style="width:60px">TIDAK</th>
                                            <th style="width:220px">KETERANGAN</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyAudit"></tbody>
                                </table>
                            </div>

                            <div class="result-box" id="resultBox">
                                <div id="resultSummary">
                                    Total YA: <b id="totalYa">0</b> dari <b id="totalItem">0</b>
                                    &nbsp; | &nbsp; Persentase Kepatuhan: <b id="percent">0.0%</b>
                                    &nbsp; | &nbsp; Kategori: <b id="kategori">MINIMAL</b>
                                </div>
                                <div style="margin-top:8px; font-size:13px; color:var(--muted)">
                                    Kriteria Kepatuhan: ≥ 85% : Baik &nbsp; | &nbsp; 76–84% : Intermediate &nbsp; |
                                    &nbsp; ≤ 75% :
                                    Minimal
                                </div>
                            </div>

                            <div class="ket-full">
                                <label style="font-size:13px; color:var(--muted);">Keterangan Tambahan</label>
                                <textarea id="ketTambahan"></textarea>
                            </div>

                            <div class="signatures">
                                <div class="sig">
                                    <label>Nama Petugas Limbah</label>
                                    <input id="namaPetugas" type="text"
                                        style="width:100%; padding:8px; margin-bottom:8px;">
                                    <div class="canvas-wrap"><canvas id="canvasPetugas"></canvas></div>
                                    <div style="margin-top:8px"><button type="button"
                                            onclick="clearSig('Petugas')">Hapus Tanda
                                            Tangan</button></div>
                                </div>
                                <div class="sig">
                                    <label>Nama Auditor</label>
                                    <input id="namaAuditor" type="text"
                                        style="width:100%; padding:8px; margin-bottom:8px;">
                                    <div class="canvas-wrap"><canvas id="canvasAuditor"></canvas></div>
                                    <div style="margin-top:8px"><button type="button"
                                            onclick="clearSig('Auditor')">Hapus Tanda
                                            Tangan</button></div>
                                </div>
                            </div>

                            <div class="bottom-toolbar">

                                <button type="submit" class="small">Simpan</button>

                                <button type="button" class="small btn-secondary" id="btnPDF">Unduh PDF</button>

                            </div>
                    </form>

                </div>


                <!-- REKAP TAB -->
                <div id="rekapTab" class="tabContent" style="display:none; margin-top:10px">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px">
                        <h3 style="margin:0">Rekap Hasil Audit</h3>
                        <div style="font-size:13px; color:var(--muted)">Data tersimpan di database
                        </div>
                    </div>


                    <div class="table-wrap" style="margin-top:10px">
                        <table class="rekap-table" id="rekapTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Total YA</th>
                                    <th>Kepatuhan</th>
                                    <th>Kategori</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($rekap) > 0): ?>
                                    <?php $no = 1;
                                    while ($r = mysqli_fetch_assoc($rekap)): ?>
                                        <tr>
                                            <td>
                                                <?= $no++ ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($r['tanggal']) ?>
                                            </td>
                                            <td style="text-align:center">
                                                <?= $r['total_ya'] ?>
                                            </td>
                                            <td style="text-align:center">
                                                <?= $r['persentase'] ?>%
                                            </td>
                                            <td style="text-align:center">
                                                <?= $r['kategori'] ?>
                                            </td>
                                            <td style="text-align:center">
                                                <a href="audit_limbah_detail.php?id=<?= $r['id'] ?>">Lihat</a>
                                                |
                                                <a href="?hapus=<?= $r['id'] ?>&tab=rekap"
                                                    onclick="return confirm('Yakin ingin menghapus data audit ini?')"
                                                    style="color:red;">
                                                    Hapus
                                                </a>

                                            </td>

                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; color:#999">
                                            Belum ada data audit
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                </div>



                <!-- FOTO TAB -->
                <div id="fotoTab" class="tabContent" style="display:none; margin-top:10px">



                    <div class="info-box">
                        <b>Info:</b><br>
                        Simpan data audit terlebih dahulu sebelum menambahkan foto.
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="foto-form">

                        <input type="date" name="foto_tanggal" required>

                        <input type="text" name="foto_ket" placeholder="Keterangan foto..." class="foto-ket">

                        <input type="file" name="foto[]" accept="image/*" multiple required>

                        <button type="submit" name="simpan_foto" class="small">
                            Tambah
                        </button>

                    </form>

                    <div style="overflow:auto">

                        <table class="rekap-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Jumlah Foto</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $no = 1;
                                while ($f = mysqli_fetch_assoc($fotos)): ?>
                                    <tr>
                                        <td>
                                            <?= $no++ ?>
                                        </td>
                                        <td>
                                            <?= $f['tanggal'] ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($f['keterangan']) ?>
                                        </td>
                                        <td style="text-align:center">
                                            <?= $f['jumlah'] ?>
                                        </td>
                                        <td style="text-align:center">
                                            <a href="audit_limbah_foto_detail.php?tanggal=<?= urlencode($f['tanggal']) ?>&ket=<?= urlencode($f['keterangan']) ?>">
                                                Lihat
                                            </a>

                                            <a href="?hapus_foto_group=<?= urlencode($f['tanggal']) ?>&ket=<?= urlencode($f['keterangan']) ?>&tab=foto"
                                                style="color:red" onclick="return confirm('Hapus semua foto ini?')">
                                                Hapus
                                            </a>


                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>

            <!-- Modal -->
            <div id="modal" class="modal" aria-hidden="true">
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px">
                        <h3 id="modalTitle">Detail Audit</h3>
                        <div><button onclick="closeModal()" style="padding:6px 10px">Tutup</button></div>
                    </div>
                    <div id="modalContent" style="margin-top:12px; max-height:60vh; overflow:auto; font-size:14px">
                    </div>
                </div>
            </div>

            <!-- MODAL FOTO -->
            <div id="fotoModal" class="modal">
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center">
                        <h3 id="fotoModalTitle">Detail Foto</h3>
                        <button onclick="closeFotoModal()">Tutup</button>
                    </div>

                    <div id="fotoModalContent" style="margin-top:12px"></div>

                    <div style="margin-top:12px; text-align:right">
                        <button onclick="downloadAllFotos()">Unduh Semua Foto</button>
                    </div>
                </div>
            </div>

            <!-- ====== END KONTEN ====== -->
        </main>

    </div>



    <!-- libs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>

        document.getElementById("formAudit").addEventListener("submit", function () {
            document.getElementById("payload").value =
                JSON.stringify(collectFormData());
        });
        // ---------- DATA ITEMS ----------
        const auditItems = {
            "Personal": [
                "Bersih, rapih, dan menggunakan pakaian yang sesuai",
                "Kuku pendek dan bersih",
                "Tidak menggunakan perhiasan tangan",
                "Rambut rapih, dan menggunakan APD dengan tepat ketika menangani limbah infeksius",
                "Staf mendapat vaksinasi penyakit menular",
                "Pemeriksaan kesehatan berkala untuk staf"
            ],

            "Pengangkutan": [
                "Penunjukan personil yang bertanggung jawab untuk setiap zona atau area",
                "Kantong limbah medis padat sebelum dimasukkan ke kendaraan pengangkut harus diletakkan dalam kontainer yang kuat dan tertutup",
                "Alat angkut tidak memiliki sudut tajam yang dapat merusak kantong, tertutup dan aman dari tumpahan cairan",
                "Kantong limbah medis padat harus aman dari jangkauan manusia maupun binatang",
                "Peralatan diberi label dan berwarna sesuai dengan kategori limbah",
                "Pelekatan simbol limbah B3 pada badan kendaraan pengangkut sebagai bentuk komunikasi bahaya atas limbah B3 yang diangkut",
                "Penerapan aturan segregasi dalam pemuatan limbah B3 ke dalam alat angkut",
                "Penerapan inspeksi kondisi limbah B3 yang diangkut oleh pengemudi",
                "Pastikan hanya melakukan bongkar-muat di lokasi yang sudah ditentukan",
                "Usahakan lokasi bongkar-muat dibuat tertutup (indoor), atau minimal memiliki atap",
                "Buat saluran penampungan tumpahan yang kedap air dan bak penampungan tumpahan yang buntu di lokasi bongkar-muat",
                "Tutup saluran penampungan limpasan air hujan saat kegiatan bongkar-muat berlangsung untuk menghindari masuknya tumpahan limbah B3 ke dalam saluran tersebut",
                "Hindari melakukan kegiatan bongkar-muat saat hujan untuk menghindari potensi tumpahan yang akan larut dan terbawa oleh limpasan air hujan",
                "Seluruh muatan harus diikat kuat selama dan posisinya diatur dengan baik sehingga bebannya terdistribusi secara merata di sumbu-sumbu kendaraan",
                "Pastikan pemuatan kemasan ke dalam kendaraan juga memperhitungkan kemudahan dan keamanan saat pembongkaran"
            ],

            "Penyimpanan sementara sebelum dimusnahkan": [
                "Tempat penampungan harus memiliki lantai yang kokoh dilengkapi dengan drainase yang baik dan mudah dibersihkan serta didesinfeksi",
                "Tidak boleh berada dekat dengan lokasi penyimpanan bahan makanan atau dapur",
                "Harus ada pencahayaan yang baik serta kemudahan akses untuk kendaraan pengumpul limbah",
                "Lokasi untuk tempat penyimpanan limbah yang berbahaya dan beracun minimum berjarak 50 meter dari lokasi fasilitas umum dan daerah bebas banjir sehingga aman dari kemungkinan terkena banjir",
                "Area penyimpanan harus diamankan untuk mencegah binatang, anak-anak, dll memasuki dan mengakses daerah tersebut",
                "Selain itu, harus kedap air (sebaiknya beton), terlindung dari air hujan, harus aman, dipagari dengan penanda yang tepat",
                "Penyimpanan limbah medis padat harus sesuai iklim tropis yaitu pada musim hujan paling lama 48 jam dan musim kemarau paling lama 24 jam"
            ],

            "Pengolahan Insinerator": [
                "Menggunakan pembakaran suhu tinggi (misalnya: pirolisis, gasifikasi, plasma arc). Pembakaran dilakukan dengan suhu 800°C sampai 1200°C",
                "Suatu sistem yang terkontrol dan terisolir dari lingkungannya agar sifat bahayanya hilang atau berkurang"
            ],

            "Pembuangan Akhir Limbah Medis": [
                "Hasil dari pengolahan limbah medis berupa abu",
                "Penimbunan (landfill)",
                "Lokasi bekas pengolahan dan penimbunan limbah medis B3 pun harus ditangani dengan baik untuk mencegah hal-hal yang tidak diinginkan",
                "Tempat atau lokasi yang diperuntukkan khusus sebagai tempat penimbunan (secure landfill) limbah medis didesain sesuai dengan persyaratan penimbunan limbah B3",
                "Tempat penimbunan mempunyai sistem pengumpulan dan pengolahan lindi"
            ]


        };


        // ---------- DOM refs ----------
        const tbodyAudit = document.getElementById('tbodyAudit');
        const totalItemEl = document.getElementById('totalItem'); // may be null if not shown
        const totalYaEl = document.getElementById('totalYa');
        const percentEl = document.getElementById('percent');
        const kategoriEl = document.getElementById('kategori');
        const btnPDF = document.getElementById('btnPDF');

        // ---------- generate table rows ----------
        function generateRows() {
            tbodyAudit.innerHTML = "";
            let index = 1;
            let total = 0;

            for (const [category, items] of Object.entries(auditItems)) {

                // Tambahkan baris kategori
                const catRow = document.createElement("tr");
                catRow.className = "cat-row";
                catRow.innerHTML = `
                <td colspan="5" style="font-weight:700; font-size:14px;">
                    ${category}
                </td>
            `;
                tbodyAudit.appendChild(catRow);

                // Tambahkan item-itemnya
                items.forEach(text => {
                    const tr = document.createElement("tr");
                    tr.className = "audit-item-row";
                    tr.innerHTML = `
                <td class="center cell-no" data-label="No">${index}</td>
                <td class="cell-monitoring" data-label="Monitoring">${text}</td>
                <td class="center cell-ya" data-label="Ya"><input type="checkbox" class="ya" data-idx="${index}"></td>
                <td class="center cell-tidak" data-label="Tidak"><input type="checkbox" class="tidak" data-idx="${index}"></td>
                <td class="cell-ket" data-label="Keterangan"><input type="text" class="ket" placeholder="Keterangan..." style="width:100%; padding:6px;"></td>
            `;
                    tbodyAudit.appendChild(tr);

                    index++;
                    total++;
                });
            }

            // update totalItems secara dinamis
            window.totalItems = total;

            if (totalItemEl) totalItemEl.textContent = total;
        }


        // ---------- exclusive checkbox logic ----------
        document.addEventListener('change', (e) => {
            if (e.target.matches('.ya')) {
                const i = e.target.dataset.idx;
                if (e.target.checked) {
                    const other = document.querySelector(`.tidak[data-idx="${i}"]`);
                    if (other) other.checked = false;
                }
            } else if (e.target.matches('.tidak')) {
                const i = e.target.dataset.idx;
                if (e.target.checked) {
                    const other = document.querySelector(`.ya[data-idx="${i}"]`);
                    if (other) other.checked = false;
                }
            }
            updateResult();
        });

        // ---------- update result ----------
        function updateResult() {
            const yaChecked = document.querySelectorAll('.ya:checked').length;
            const tidakChecked = document.querySelectorAll('.tidak:checked').length;

            const totalTerjawab = yaChecked + tidakChecked;

            const percent = totalTerjawab > 0
                ? (yaChecked / totalTerjawab * 100)
                : 0;

            let kat = 'MINIMAL';
            if (percent >= 85) kat = 'BAIK';
            else if (percent >= 76) kat = 'INTERMEDIATE';

            totalYaEl.textContent = yaChecked;



            if (totalItemEl) totalItemEl.textContent = totalTerjawab;



            percentEl.textContent = percent.toFixed(1) + '%';
            kategoriEl.textContent = kat;
        }


        // ---------- signature (canvas) ----------
        function setupSignature(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return null;

            function getSignatureStrokeColor() {
                return document.body.classList.contains('dark-mode') ? '#ffffff' : '#000000';
            }

            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                const ratio = window.devicePixelRatio || 1;

                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(rect.height * ratio);

                const ctx = canvas.getContext('2d');
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = getSignatureStrokeColor();

                // 🔥 GAMBAR ULANG TTD JIKA ADA
                const key = canvasId === 'canvasPetugas' ? 'Petugas' : 'Auditor';
                if (signatureImage[key]) {
                    const img = new Image();
                    img.onload = () => {
                        ctx.drawImage(img, 0, 0, rect.width, rect.height);
                    };
                    img.src = signatureImage[key];
                }
            }

            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            const ctx = canvas.getContext('2d');
            let drawing = false;
            ctx.strokeStyle = getSignatureStrokeColor();

            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                let clientX = (e.touches && e.touches[0]) ? e.touches[0].clientX : e.clientX;
                let clientY = (e.touches && e.touches[0]) ? e.touches[0].clientY : e.clientY;
                return { x: clientX - rect.left, y: clientY - rect.top };
            }

            canvas.addEventListener('pointerdown', (ev) => {
                drawing = true;
                const p = getPos(ev);
                ctx.strokeStyle = getSignatureStrokeColor();
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ev.preventDefault();
            });
            canvas.addEventListener('pointermove', (ev) => {
                if (!drawing) return;
                const p = getPos(ev);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            });


            canvas.addEventListener('pointerup', () => {
                drawing = false;
                ctx.beginPath();
                signatureImage[canvasId === 'canvasPetugas' ? 'Petugas' : 'Auditor'] =
                    canvas.toDataURL('image/png');
            });



            canvas.addEventListener('pointerleave', () => { drawing = false; ctx.beginPath(); });

            return {
                clear: () => { const r = canvas.getContext('2d'); r.clearRect(0, 0, canvas.width, canvas.height); },
                toDataURL: () => canvas.toDataURL('image/png'),
                canvasEl: canvas
            };
        }

        let sigPet = null, sigAud = null;
        let signatureImage = {
            Petugas: null,
            Auditor: null
        };


        function initSignatures() {
            sigPet = setupSignature('canvasPetugas');
            sigAud = setupSignature('canvasAuditor');
        }
        function clearSig(who) {
            if (who === 'Petugas' && sigPet) sigPet.clear();
            if (who === 'Auditor' && sigAud) sigAud.clear();
        }

        // ---------- collect form data ----------
        function collectFormData() {
            const tanggal = document.getElementById('tanggal').value || '';
            const ketTambahan = document.getElementById('ketTambahan') ? document.getElementById('ketTambahan').value : '';
            const namaPet = document.getElementById('namaPetugas').value || '';
            const namaAud = document.getElementById('namaAuditor').value || '';

            const rows = document.querySelectorAll('#tbodyAudit tr');
            const items = [];

            rows.forEach(row => {
                const yaEl = row.querySelector('.ya');
                const tidakEl = row.querySelector('.tidak');
                const ketEl = row.querySelector('.ket');

                // hanya baris pertanyaan (bukan judul kategori)
                if (yaEl || tidakEl) {
                    items.push({
                        y: yaEl ? yaEl.checked : false,
                        t: tidakEl ? tidakEl.checked : false,
                        ket: ketEl ? ketEl.value : ''
                    });
                }
            });



            const yaCount = items.filter(it => it.y).length;
            const tidakCount = items.filter(it => it.t).length;
            const totalTerjawab = yaCount + tidakCount;

            const percent = totalTerjawab > 0
                ? (yaCount / totalTerjawab * 100)
                : 0;


            let kat = 'MINIMAL';
            if (percent >= 85) kat = 'BAIK';
            else if (percent >= 76) kat = 'INTERMEDIATE';

            const petImg = sigPet ? sigPet.toDataURL() : '';
            const audImg = sigAud ? sigAud.toDataURL() : '';

            return { tanggal, namaPet, namaAud, ketTambahan, items, yaCount, percent: percent.toFixed(1), kategori: kat, petImg, audImg };
        }

        // ---------- storage ----------



        // ================== FOTO MODAL LOGIC ==================
        let currentFotoIndex = null;


        function closeFotoModal() {
            document.getElementById('fotoModal').classList.remove('show');
        }

        function downloadAllFotos() {
            if (currentFotoIndex === null) return;

            const store = JSON.parse(localStorage.getItem(FOTO_KEY) || '[]');
            const f = store[currentFotoIndex];
            if (!f) return;

            const images = f.imgs ? f.imgs : [];

            images.forEach((img, i) => {
                const a = document.createElement('a');
                a.href = img;
                a.download = `foto_audit_${currentFotoIndex + 1}_${i + 1}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        }



        // ---------- rekap ----------

        function closeModal() {
            document.getElementById('modal').classList.remove('show');
            document.getElementById('modal').setAttribute('aria-hidden', 'true');
        }



        function loadImageToCanvas(dataURL, canvasId) {
            if (!dataURL) return;
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = function () {
                const canvas = document.getElementById(canvasId);
                const rect = canvas.getBoundingClientRect();
                const ratio = window.devicePixelRatio || 1;
                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(rect.height * ratio);
                const ctx = canvas.getContext('2d');
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.scale(ratio, ratio);
                ctx.clearRect(0, 0, rect.width, rect.height);
                ctx.drawImage(img, 0, 0, rect.width, rect.height);
            };
            img.src = dataURL;
        }

        // ==== KONVERSI CANVAS TTD → IMG (WAJIB UNTUK PDF) ====
        function replaceCanvasWithImage(clone) {
            ["canvasPetugas", "canvasAuditor"].forEach(id => {
                const original = document.getElementById(id);         // canvas asli
                const clonedCanvas = clone.querySelector(`#${id}`);   // canvas dalam clone

                if (original && clonedCanvas) {
                    const img = document.createElement("img");
                    img.src = original.toDataURL("image/png");        // ambil gambar tanda tangan
                    img.style.width = clonedCanvas.style.width;
                    img.style.height = clonedCanvas.style.height;

                    clonedCanvas.replaceWith(img);                    // ganti canvas clone → gambar
                }
            });
        }



        document.getElementById("btnPDF").addEventListener("click", async () => {

            const wrapper = document.querySelector(".wrapper");
            const rows = Array.from(document.querySelectorAll("#tbodyAudit tr"));

            // ===== PEMBAGIAN HALAMAN PDF =====

            // HALAMAN 1 → 26 baris pertama
            const rowsPage1 = rows.slice(0, 26);

            // HALAMAN 2 → 26 baris berikutnya
            const rowsPage2 = rows.slice(26, 51);

            // HALAMAN 3 → sisanya
            const rowsPage3 = rows.slice(51);


            // Clone wrapper
            const clone1 = wrapper.cloneNode(true);
            const clone2 = wrapper.cloneNode(true);
            const clone3 = wrapper.cloneNode(true);

            replaceCanvasWithImage(clone1);
            replaceCanvasWithImage(clone2);
            replaceCanvasWithImage(clone3);


            // Tempat di luar layar
            [clone1, clone2, clone3].forEach(cl => {
                cl.style.position = "absolute";
                cl.style.left = "-9999px";
                cl.style.top = "-9999px";
                document.body.appendChild(cl);
            });

            // Kosongkan tabel
            clone1.querySelector("#tbodyAudit").innerHTML = "";
            clone2.querySelector("#tbodyAudit").innerHTML = "";
            clone3.querySelector("#tbodyAudit").innerHTML = "";


            // Isi tabel
            rowsPage1.forEach(r => clone1.querySelector("#tbodyAudit").appendChild(r.cloneNode(true)));
            rowsPage2.forEach(r => clone2.querySelector("#tbodyAudit").appendChild(r.cloneNode(true)));
            rowsPage3.forEach(r => clone3.querySelector("#tbodyAudit").appendChild(r.cloneNode(true)));


            // ===== HALAMAN 1 (TABEL SAJA) =====
            clone1.querySelector(".result-box").style.display = "none";
            clone1.querySelector(".signatures").style.display = "none";
            clone1.querySelector(".ket-full").style.display = "none";
            clone1.querySelector(".bottom-toolbar").style.display = "none";
            clone1.querySelector(".tabbar").style.display = "none";
            clone1.querySelector("#rekapTab").style.display = "none";

            // ===== HALAMAN 2 (TABEL LANJUTAN SAJA) =====
            clone2.querySelector(".header-row").style.display = "none";
            clone2.querySelector(".result-box").style.display = "none";
            clone2.querySelector(".signatures").style.display = "none";
            clone2.querySelector(".ket-full").style.display = "none";
            clone2.querySelector(".bottom-toolbar").style.display = "none";
            clone2.querySelector(".tabbar").style.display = "none";
            clone2.querySelector("#rekapTab").style.display = "none";

            // ===== HALAMAN 3 (HASIL + TTD) =====
            clone3.querySelector(".header-row").style.display = "none";
            clone3.querySelector(".tabbar").style.display = "none";
            clone3.querySelector("#rekapTab").style.display = "none";
            clone3.querySelector(".bottom-toolbar").style.display = "none";

            // ===== HILANGKAN TOMBOL "HAPUS TANDA TANGAN" DI PDF =====
            clone1.querySelectorAll(".signatures button").forEach(btn => btn.style.display = "none");
            clone2.querySelectorAll(".signatures button").forEach(btn => btn.style.display = "none");
            clone3.querySelectorAll(".signatures button").forEach(btn => btn.style.display = "none");

            // Render
            const canvas1 = await html2canvas(clone1, { scale: 2, backgroundColor: "#fff" });
            const canvas2 = await html2canvas(clone2, { scale: 2, backgroundColor: "#fff" });
            const canvas3 = await html2canvas(clone3, { scale: 2, backgroundColor: "#fff" });

            clone1.remove();
            clone2.remove();
            clone3.remove();

            const img1 = canvas1.toDataURL("image/png");
            const img2 = canvas2.toDataURL("image/png");
            const img3 = canvas3.toDataURL("image/png");


            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF("p", "mm", "a4");

            const margin = 15;
            const usableWidth = 210 - margin * 2;

            function add(img, canv, newPage = false) {
                const h = (canv.height * usableWidth) / canv.width;
                if (newPage) pdf.addPage();
                pdf.addImage(img, "PNG", margin, margin, usableWidth, h);
            }

            add(img1, canvas1, false); // halaman 1
            add(img2, canvas2, true);  // halaman 2
            add(img3, canvas3, true);  // halaman 3

            pdf.save("Audit_Limbah.pdf");
        });


        // ---------- tabs ----------
        document.querySelectorAll('.tab').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                btn.classList.add('active');
                switchTab(btn.getAttribute('data-tab'));
            });
        });
        function switchTab(id) {
            document.querySelectorAll('.tabContent').forEach(el => el.style.display = 'none');
            const el = document.getElementById(id);
            if (el) el.style.display = 'block';
        }


        // ---------- misc ----------
        let editingIndex = null;

        // ---------- init ----------
        function initAll() {
            generateRows();
            initSignatures();
            updateResult();
        }
        window.addEventListener('load', initAll);
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get("tab");

            if (tab === "foto") {
                // reset semua tab
                document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
                document.querySelectorAll(".tabContent").forEach(c => c.style.display = "none");

                // aktifkan TAB FOTO
                const btnFoto = document.querySelector('.tab[data-tab="fotoTab"]');
                const fotoTab = document.getElementById("fotoTab");

                if (btnFoto && fotoTab) {
                    btnFoto.classList.add("active");
                    fotoTab.style.display = "block";
                }
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get("tab");

            if (tab === "rekap") {
                // reset semua tab
                document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
                document.querySelectorAll(".tabContent").forEach(c => c.style.display = "none");

                // aktifkan TAB REKAP
                const btnRekap = document.querySelector('.tab[data-tab="rekapTab"]');
                const rekapTab = document.getElementById("rekapTab");

                if (btnRekap && rekapTab) {
                    btnRekap.classList.add("active");
                    rekapTab.style.display = "block";
                }
            }
        });
    </script>

    <!-- waktu tunggu habis -->

    <script>
        setInterval(() => {
            fetch('/audit/keep_alive.php', {
                credentials: 'same-origin'
            }).catch(() => { });
        }, 120000); // setiap 2 menit
    </script>



    <script src="<?= asset('assets/js/utama.js') ?>"></script>

</body>

</html>
