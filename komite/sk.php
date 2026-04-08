<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;
$csrfToken = csrf_token();

// === TAMBAH DATA ===
if (isset($_POST['simpan'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf();
    }

    $nomor = trim($_POST['nomor_sk'] ?? '');
    $judul = trim($_POST['judul_sk'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');

    $uploadError = '';
    $targetFile = ppi_store_uploaded_pdf($_FILES['file_sk'] ?? null, __DIR__ . '/../uploads/sk', $uploadError);
    if ($targetFile === false) {
        echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='sk.php';</script>";
        exit;
    }

    $insertStmt = mysqli_prepare($conn, "INSERT INTO tb_sk (nomor_sk, judul_sk, tanggal, link_file)
                         VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($insertStmt, "ssss", $nomor, $judul, $tanggal, $targetFile);
    if (mysqli_stmt_execute($insertStmt)) {
        echo "<script>alert('✅ Data SK berhasil disimpan!');window.location='sk.php';</script>";
        exit;
    } else {
        ppi_unlink_upload($targetFile, __DIR__ . '/../uploads/sk');
        echo "<script>alert('⚠️ Gagal mengunggah file! Pastikan folder uploads/sk dapat ditulis.');window.location='sk.php';</script>";
    }
    mysqli_stmt_close($insertStmt);
}

// === HAPUS DATA ===
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if (!csrf_validate($_GET['csrf'] ?? '') || $id <= 0) {
        ppi_abort_csrf();
    }

    $selectStmt = mysqli_prepare($conn, "SELECT link_file FROM tb_sk WHERE id = ?");
    mysqli_stmt_bind_param($selectStmt, "i", $id);
    mysqli_stmt_execute($selectStmt);
    $q = mysqli_stmt_get_result($selectStmt);
    $data = mysqli_fetch_assoc($q);
    mysqli_stmt_close($selectStmt);
    if ($data) {
        ppi_unlink_upload($data['link_file'], __DIR__ . '/../uploads/sk');
    }

    $deleteStmt = mysqli_prepare($conn, "DELETE FROM tb_sk WHERE id = ?");
    mysqli_stmt_bind_param($deleteStmt, "i", $id);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
    echo "<script>alert('🗑️ Data berhasil dihapus!');window.location='sk.php';</script>";
    exit;
}

// === PENCARIAN ===
$cari = $_GET['cari'] ?? '';
if ($cari !== '') {
    $likeCari = '%' . $cari . '%';
    $searchStmt = mysqli_prepare($conn, "SELECT id, nomor_sk, judul_sk, tanggal, link_file FROM tb_sk WHERE nomor_sk LIKE ? OR judul_sk LIKE ? ORDER BY tanggal DESC");
    mysqli_stmt_bind_param($searchStmt, "ss", $likeCari, $likeCari);
    mysqli_stmt_execute($searchStmt);
    $res = mysqli_stmt_get_result($searchStmt);
} else {
    $searchStmt = null;
    $res = mysqli_query($conn, "SELECT id, nomor_sk, judul_sk, tanggal, link_file FROM tb_sk ORDER BY tanggal DESC");
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar SK | PPI PHBW</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">


    <style>
        .container.sk {
            padding: 0 22px 30px 22px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            margin: 20px 0 40px 0;
        }

        .sk-header {
            background: linear-gradient(135deg, #1e3a8a, #3b49df);
            color: white;
            padding: 24px 24px;
            /* ⬆ tambah tinggi */
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 14px;
            margin-bottom: 30px;
            /* tambah jarak bawah */
        }

        .sk-header h1 {
            font-size: 1.25rem;
            /* lebih kecil */
            font-weight: 600;
            margin: 0;
        }

        .sk-header small {
            display: block;
            font-size: 13px;
            opacity: 0.8;
            font-weight: 400;
        }


        button {
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-weight: 600;
            cursor: pointer;
        }


        /* Search */
        .search-box {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .search-box input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            min-width: 240px;
        }

        /* Table */
        .table-container {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: linear-gradient(180deg, #3b49df, #1e3a8a);
            color: white;
            text-transform: uppercase;
            font-size: .9rem;
        }

        tr:hover td {
            background: #f9fafb;
        }

        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ================= SYSTEM BUTTON ================= */

        /* Base button */
        .btn,
        .btn-add,
        .btn-del,
        .btn-cancel,
        .btn-view {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        /* Hover animation */
        .btn:hover,
        .btn-add:hover,
        .btn-del:hover,
        .btn-cancel:hover,
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        /* Dashboard white style */
        .btn-white {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            background: white;
            color: #1e3a8a;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.25s ease;
        }

        .btn-white:hover {
            background: #f1f5ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }


        /* Active effect */
        .btn:active,
        .btn-add:active,
        .btn-del:active,
        .btn-cancel:active,
        .btn-view:active {
            transform: translateY(0);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        /* ================= VARIANTS ================= */

        /* Primary (Dashboard) */
        .btn {
            background: #3b49df;
            color: white;
        }

        .btn:hover {
            background: #2f3bd1;
        }

        /* Tambah */
        .btn-add {
            background: #16a34a;
            color: white;
        }

        .btn-add:hover {
            background: #15803d;
        }

        /* Hapus */
        .btn-del {
            background: #dc2626;
            color: white;
        }

        .btn-del:hover {
            background: #b91c1c;
        }

        /* Lihat */
        .btn-view {
            background: #1e40af;
            color: white;
        }

        .btn-view:hover {
            background: #1d4ed8;
        }

        /* Cancel */
        .btn-cancel {
            background: #6b7280;
            color: white;
        }

        .btn-cancel:hover {
            background: #4b5563;
        }

        /* Full width button (modal) */
        button.full {
            width: 100%;
            margin-top: 10px;
        }


        /* Modal */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .overlay.show {
            display: flex;
        }

        .popup-form {
            background: white;
            border-radius: 12px;
            padding: 24px;
            width: 90%;
            max-width: 450px;
            box-shadow: var(--shadow);
        }

        .popup-form h2 {
            text-align: center;
            color: var(--primary);
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 500;
        }

        input[type=text],
        input[type=date],
        input[type=file] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 4px;
        }

        button.full {
            width: 100%;
            margin-top: 10px;
        }

        @media(max-width:768px) {
            main {
                padding: 16px;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box input,
            button {
                width: 100%;
            }

            .btn-add {
                width: 100%;
            }

            table {
                font-size: 13px;
            }
        }

        @media (max-width:768px) {


            .container.sk {
                margin: 10px !important;
                padding: 15px !important;
                border-radius: 14px;
            }


        }


        /* ================= MOBILE CARD TABLE ================= */
        @media (max-width:768px) {

            table {
                min-width: 100%;
            }

            table thead {
                display: none;
            }

            table,
            tbody,
            tr,
            td {
                display: block;
                width: 100%;
            }

            tr {
                background: white;
                margin-bottom: 15px;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                padding: 12px;
            }

            td {
                border: none;
                padding: 6px 0;
                text-align: left;
                position: relative;
                padding-left: 110px;
                font-size: 13px;
            }

            td:before {
                position: absolute;
                left: 0;
                top: 6px;
                width: 100px;
                font-weight: 600;
                color: #374151;
            }

            td:nth-child(1):before {
                content: "No";
            }

            td:nth-child(2):before {
                content: "Nomor SK";
            }

            td:nth-child(3):before {
                content: "Judul SK";
            }

            td:nth-child(4):before {
                content: "Tanggal";
            }

            td:nth-child(5):before {
                content: "File";
            }

            td:nth-child(6):before {
                content: "Aksi";
            }

            .actions {
                justify-content: flex-start;
            }

        }

        @media (max-width:768px) {

            .btn,
            .btn-add,
            .btn-del,
            .btn-view {
                width: 100%;
            }
        }

        /* ================= HEADER MOBILE FIX ================= */
        @media (max-width:768px) {

            .sk-header {
                flex-direction: column;
                align-items: center;
                /* 🔥 center semua */
                text-align: center;
                /* 🔥 teks jadi center */
                ;
                gap: 14px;
                padding: 20px;
            }

            .sk-header h1 {
                font-size: 18px;
                line-height: 1.3;
            }

            .sk-header small {
                font-size: 12px;
            }

            .btn-white {
                width: 100%;
                justify-content: center;
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

            <div class="container sk">

                <div class="sk-header">
                    <div>
                        <h1>📜 Daftar Surat Keputusan (SK)</h1>
                        <small>Pengelolaan SK Komite PPI</small>
                    </div>
                    <a href="/dashboard.php" class="btn-white">🏠 Dashboard</a>
                </div>

                <!--========= ISI ==========-->
                <div class="search-box">
                    <form method="get" style="display:flex;gap:8px;width:100%;flex-wrap:wrap;">
                        <input type="text" name="cari" placeholder="🔍 Cari Nomor atau Judul SK..." value="<?= htmlspecialchars($cari) ?>">
                        <button type="submit" class="btn">🔍 Cari</button>
                        <button type="button" class="btn-add" onclick="bukaForm()">➕ Tambah SK</button>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor SK</th>
                                <th>Judul SK</th>
                                <th>Tanggal</th>
                                <th>File</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($res) > 0) {
                                while ($r = mysqli_fetch_assoc($res)) {
                                    $tgl = date('d F Y', strtotime($r['tanggal']));
                                    $file = str_replace("../", "", $r['link_file']);
                                                                        $nomor = htmlspecialchars($r['nomor_sk'], ENT_QUOTES, 'UTF-8');
                                                                        $judul = htmlspecialchars($r['judul_sk'], ENT_QUOTES, 'UTF-8');
                                                                        $deleteUrl = '?hapus=' . (int) $r['id'] . '&csrf=' . urlencode($csrfToken);
                                    echo "<tr>
                              <td>$no</td>
                                                            <td>{$nomor}</td>
                                                            <td>{$judul}</td>
                              <td>$tgl</td>
                                                            <td><a href='/$file' target='_blank' rel='noopener noreferrer' class='btn-view'>Lihat</a></td>
                                                            <td class='actions'><a href='{$deleteUrl}' onclick=\"return confirm('Yakin hapus data ini?')\" class='btn-del'>Hapus</a></td>
                            </tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr><td colspan='6' align='center'>Tidak ada data ditemukan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!--========= ISI emd ==========-->


                <!-- FORM TAMBAH -->
                <div class="overlay" id="formOverlay">
                    <div class="popup-form">
                        <h2>Tambah Data SK</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <?= csrf_input() ?>
                            <label>Nomor SK</label>
                            <input type="text" name="nomor_sk" required placeholder="Contoh: SK/003/PPI/2025">
                            <label>Judul SK</label>
                            <input type="text" name="judul_sk" required placeholder="Contoh: SK Koordinator Hand Hygiene">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" required>
                            <label>Unggah File (PDF)</label>
                            <input type="file" name="file_sk" accept="application/pdf" required>
                            <button type="submit" name="simpan" class="btn-add full">💾 Simpan</button>
                            <button type="button" class="btn-cancel full" onclick="tutupForm()">❌ Batal</button>
                        </form>
                    </div>
                </div>

                <?php if ($searchStmt instanceof mysqli_stmt) mysqli_stmt_close($searchStmt); ?>

            </div>

        </main>

    </div>


    <script src="<?= asset('assets/js/utama.js') ?>"></script>

    <script>
        const overlay = document.getElementById('formOverlay');

        function bukaForm() {
            overlay.classList.add('show');
        }

        function tutupForm() {
            overlay.classList.remove('show');
        }
        window.onclick = e => {
            if (e.target == overlay) overlay.classList.remove('show');
        };
    </script>



</body>

</html>