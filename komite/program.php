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

    $nama = trim($_POST['nama_program'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    $pj = trim($_POST['penanggung_jawab'] ?? '');
    $mulai = trim($_POST['tanggal_mulai'] ?? '');
    $selesai = trim($_POST['tanggal_selesai'] ?? '');

    $uploadError = '';
    $targetFile = ppi_store_uploaded_pdf($_FILES['file_program'] ?? null, __DIR__ . '/../uploads/program', $uploadError);

    if ($targetFile === false) {
        echo "<script>alert('❌ " . htmlspecialchars($uploadError, ENT_QUOTES, 'UTF-8') . "');window.location='program.php';</script>";
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO tb_program_ppi (nama_program, deskripsi, penanggung_jawab, tanggal_mulai, tanggal_selesai, file_path)
                         VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $nama, $desk, $pj, $mulai, $selesai, $targetFile);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('✅ Program berhasil disimpan!');window.location='program.php';</script>";
    } else {
        ppi_unlink_upload($targetFile, __DIR__ . '/../uploads/program');
        echo "<script>alert('⚠️ Gagal mengunggah file! Pastikan folder uploads/program dapat ditulis.');window.location='program.php';</script>";
    }
    mysqli_stmt_close($stmt);
}

// === HAPUS DATA ===
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if (!csrf_validate($_GET['csrf'] ?? '') || $id <= 0) {
        ppi_abort_csrf();
    }

    $selectStmt = mysqli_prepare($conn, "SELECT file_path FROM tb_program_ppi WHERE id = ?");
    mysqli_stmt_bind_param($selectStmt, "i", $id);
    mysqli_stmt_execute($selectStmt);
    $q = mysqli_stmt_get_result($selectStmt);
    $data = mysqli_fetch_assoc($q);
    mysqli_stmt_close($selectStmt);

    if ($data) {
        ppi_unlink_upload($data['file_path'], __DIR__ . '/../uploads/program');
    }

    $deleteStmt = mysqli_prepare($conn, "DELETE FROM tb_program_ppi WHERE id = ?");
    mysqli_stmt_bind_param($deleteStmt, "i", $id);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
    echo "<script>alert('🗑️ Data program dihapus.');window.location='program.php';</script>";
    exit;
}

// === AMBIL DATA ===
$res = mysqli_query($conn, "SELECT id, nama_program, deskripsi, penanggung_jawab, tanggal_mulai, tanggal_selesai, file_path FROM tb_program_ppi ORDER BY id ASC");
?>


<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "KOMITE PPI";
?>
<!--end-->


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Daftar Program PPI | PHBW</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        /* =====================================================
   PROGRAM PPI - MODERN PREMIUM VERSION
   Scoped aman dari utama.css
===================================================== */

        .container {
            padding: 30px;
            background: linear-gradient(180deg, #f4f8fd 0%, #eef5ff 100%);
            min-height: calc(100vh - 68px);
        }

        /* ================= HEADER ================= */
        .container header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .container header h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--blue-1);
            letter-spacing: .5px;
        }

        /* ================= BUTTON ================= */
        .container .btn {
            padding: 9px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: .25s ease;
            text-decoration: none;
            /*display: inline-block;*/

            display: flex;
            /* Ubah dari inline-block */
            justify-content: center;
            /* Center horizontal */
            align-items: center;
            /* Center vertical */
        }

        .container .btn-dashboard {
            background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
            color: white;
            box-shadow: 0 5px 15px rgba(30, 136, 229, .25);
        }

        .container .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 136, 229, .35);
        }

        .container .btn-tambah {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            box-shadow: 0 5px 15px rgba(34, 197, 94, .25);
        }

        .container .btn-tambah:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(34, 197, 94, .35);
        }

        .container .btn-hapus {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            padding: 6px 12px;
            border-radius: 12px;
        }

        .container .btn-hapus:hover {
            transform: scale(1.05);
        }

        .container .btn-cancel {
            background: #64748b;
            color: white;
        }

        /* ================= CONTENT CARD ================= */
        .container .content {
            background: rgba(255, 255, 255, .85);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .06);
            border: 1px solid rgba(0, 0, 0, .04);
            transition: .3s;
        }

        .container .content:hover {
            box-shadow: 0 25px 55px rgba(0, 0, 0, .08);
        }

        /* ================= TABLE ================= */
        .container .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        .container table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            border-radius: 14px;
            overflow: hidden;
        }

        .container thead {
            background: linear-gradient(90deg, var(--blue-2), var(--blue-3));
            color: white;
        }

        /* ================= TABLE (DESKTOP LEBIH RAPAT) ================= */

        .container thead th {
            padding: 10px 14px;
            /* sebelumnya 14px */
            font-size: 13px;
        }

        .container tbody td {
            padding: 7px 14px;
            /* sebelumnya 14px */
            font-size: 14px;
        }

        .container tbody tr {
            background: white;
            transition: .2s ease;
        }

        .container tbody tr:hover {
            background: #f5f9ff;
            /* lebih subtle */
            transform: none;
            /* hilangkan scale supaya tidak aneh */
        }

        .container tbody tr:last-child td {
            border-bottom: none;
        }

        .container .actions {
            text-align: left;
        }

        /* ================= OVERLAY POPUP ================= */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 40, 70, .55);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 999;
            animation: fadeIn .25s ease;
        }

        .overlay.show {
            display: flex;
        }

        /* ================= POPUP FORM ================= */
        .popup-form {
            background: white;
            width: 100%;
            max-width: 520px;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 35px 80px rgba(0, 0, 0, .25);
            animation: slideUp .35s ease;
            position: relative;
        }

        .popup-form h2 {
            margin-bottom: 18px;
            font-weight: 800;
            font-size: 20px;
            color: var(--blue-1);
        }

        .popup-form label {
            font-size: 13px;
            font-weight: 600;
            margin-top: 14px;
            display: block;
            color: #334155;
        }

        .popup-form input,
        .popup-form textarea {
            width: 100%;
            padding: 11px 14px;
            margin-top: 6px;
            border-radius: 12px;
            border: 1px solid #dbeafe;
            font-size: 14px;
            transition: .25s;
            background: #f9fbff;
        }

        .popup-form textarea {
            resize: vertical;
            min-height: 80px;
        }

        .popup-form input:focus,
        .popup-form textarea:focus {
            outline: none;
            border-color: var(--blue-3);
            background: white;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, .2);
        }

        .popup-form button {
            margin-top: 18px;
            margin-right: 6px;
        }

        /* ================= ANIMATIONS ================= */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(35px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }


        /* =====================================================
   MOBILE VERSION - SK STYLE (Seperti Gambar Anda)
===================================================== */
        @media(max-width:768px) {

            /* Container utama */
            .container {
                padding: 16px;
            }

            /* Header */
            .container header {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
            }

            .container header h1 {
                width: 100%;
                text-align: center;
            }

            /* 🔥 Samakan tombol atas & bawah */
            .container header .btn-dashboard,
            .container .content .btn-tambah {
                width: 100%;
                text-align: center;
            }

            /* 🔥 Samakan padding area agar lurus */
            .container .content {
                padding: 16px;
            }

            /* ================= TABLE ================= */

            .container table,
            .container thead,
            .container tbody,
            .container th,
            .container td,
            .container tr {
                display: block;
                width: 100%;
            }

            .container thead {
                display: none;
            }

            /* Card */
            .container tbody tr {
                background: #ffffff;
                border-radius: 18px;
                padding: 18px;
                margin-bottom: 20px;

                border: 1.5px solid rgba(15, 95, 166, .35);

                box-shadow: 0 4px 12px rgba(0, 0, 0, .08);

                transition: .25s;
            }

            .container tbody tr:hover {
                transform: translateY(-2px);
                border: 1.5px solid var(--blue-2);
                box-shadow: 0 8px 20px rgba(15, 95, 166, .18);
            }


            .container tbody td {
                display: flex;
                gap: 12px;
                /* jarak antara label dan value */
                align-items: flex-start;
                border: none;
                padding: 6px 0;
                font-size: 14px;
            }

            .container tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--blue-1);
                flex: 0 0 45%;
            }

            .container tbody td.actions {
                display: flex;
                gap: 12px;
                align-items: flex-start;
                margin-top: 8px;
            }

            .container .btn-hapus,
            .container .btn-dashboard {
                width: 100%;

            }

            .popup-form {
                max-height: 90vh;
                overflow-y: auto;
                padding: 22px;
            }

            /* 🔥 Buat content rata luar */
            .container .content {
                padding: 16px;
            }

            /* 🔥 Paksa tombol atas ikut padding yang sama */
            .container header .btn-dashboard {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }

            /* 🔥 Buat jarak header konsisten */
            .container header {
                padding: 0 16px;
            }

            .container .btn-hapus {
                padding: 10px 20px;
                /* tambah atas bawah */
                border-radius: 18px;
                /* lebih halus */
            }

            /* Dark mode - mobile card */
            body.dark-mode.program-page .container tbody tr {
                background: #1e293b;
                border-color: #334155;
            }

            body.dark-mode.program-page .container tbody tr:hover {
                background: #253348;
                border-color: #3b82f6;
            }
        }

        /* =====================================================
   DARK MODE
===================================================== */
        body.dark-mode.program-page .container {
            background: linear-gradient(180deg, #0b1220 0%, #0f172a 100%);
        }

        body.dark-mode.program-page .container header h1 {
            color: #93c5fd;
        }

        body.dark-mode.program-page .container .content {
            background: rgba(17, 24, 39, 0.9);
            border-color: #1e3a5f;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
        }

        body.dark-mode.program-page .container tbody tr {
            background: #111827;
        }

        body.dark-mode.program-page .container tbody tr:hover {
            background: #1e293b;
        }

        body.dark-mode.program-page .container tbody td {
            color: #e2e8f0;
            border-color: #1e293b;
        }

        body.dark-mode.program-page .container tbody td::before {
            color: #93c5fd;
        }

        body.dark-mode.program-page .popup-form {
            background: #1e293b;
            box-shadow: 0 35px 80px rgba(0, 0, 0, 0.6);
        }

        body.dark-mode.program-page .popup-form h2 {
            color: #93c5fd;
        }

        body.dark-mode.program-page .popup-form label {
            color: #cbd5e1;
        }

        body.dark-mode.program-page .popup-form input,
        body.dark-mode.program-page .popup-form textarea {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        body.dark-mode.program-page .popup-form input:focus,
        body.dark-mode.program-page .popup-form textarea:focus {
            background: #1e293b;
            border-color: #3b82f6;
        }

        body.dark-mode.program-page .container .content .btn-cancel {
            background: #334155;
        }
    </style>

</head>

<body class="program-page">

    <div class="layout">

        <!-- Link ke Sidebar -->
        <?php include_once '../sidebar.php'; ?>

        <main>

            <!-- Link Ke topbar -->
            <?php include_once '../topbar.php'; ?>

            <div class="container">

                <header>
                    <h1>🗂️ Daftar Program PPI - PHBW</h1>
                    <a href="/dashboard.php" class="btn btn-dashboard">🏠 Kembali ke Dashboard</a>
                </header>

                <div class="content">
                    <button class="btn btn-tambah" onclick="bukaForm()">+ Tambah Program</button>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Program</th>
                                    <th>Deskripsi</th>
                                    <th>Penanggung Jawab</th>
                                    <th>Periode</th>
                                    <th>File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($res) > 0) {
                                    while ($r = mysqli_fetch_assoc($res)) {
                                        $periode = date('M Y', strtotime($r['tanggal_mulai'])) . ' - ' . date('M Y', strtotime($r['tanggal_selesai']));
                                        $file = str_replace("../", "", $r['file_path']);
                                                                                $namaProgram = htmlspecialchars($r['nama_program'], ENT_QUOTES, 'UTF-8');
                                                                                $deskripsi = htmlspecialchars($r['deskripsi'], ENT_QUOTES, 'UTF-8');
                                                                                $penanggungJawab = htmlspecialchars($r['penanggung_jawab'], ENT_QUOTES, 'UTF-8');
                                                                                $deleteUrl = '?hapus=' . (int) $r['id'] . '&csrf=' . urlencode($csrfToken);
                                        echo "<tr>
                          <td data-label='No'>$no</td>
                                                    <td data-label='Nama Program'>{$namaProgram}</td>
                                                    <td data-label='Deskripsi'>{$deskripsi}</td>
                                                    <td data-label='Penanggung Jawab'>{$penanggungJawab}</td>
                                                    <td data-label='Periode'>{$periode}</td>
                          <td data-label='File'>
                                                        <a href='/$file' target='_blank' rel='noopener noreferrer' class='btn btn-dashboard'>Lihat</a>
                          </td>
                          <td data-label='Aksi' class='actions'>
                                                        <a href='{$deleteUrl}' 
                               onclick=\"return confirm('Yakin ingin menghapus data ini?')\" 
                               class='btn btn-hapus'>🗑️ Hapus</a>
                          </td>
                        </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='7' align='center'>Tidak ada data program.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FORM TAMBAH -->
                <div class="overlay" id="formOverlay">
                    <div class="popup-form">
                        <h2>Tambah Program PPI</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <?= csrf_input() ?>
                            <label>Nama Program</label>
                            <input type="text" name="nama_program" required placeholder="Contoh: Program Hand Hygiene">
                            <label>Deskripsi Singkat</label>
                            <textarea name="deskripsi" required placeholder="Contoh: Program meningkatkan kepatuhan cuci tangan."></textarea>
                            <label>Penanggung Jawab</label>
                            <input type="text" name="penanggung_jawab" required placeholder="Contoh: Ketua PPI">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" required>
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" required>
                            <label>Upload File (PDF)</label>
                            <input type="file" name="file_program" accept="application/pdf" required>
                            <button type="submit" name="simpan" class="btn btn-tambah">💾 Simpan</button>
                            <button type="button" class="btn btn-cancel" onclick="tutupForm()">❌ Batal</button>
                        </form>
                    </div>
                </div>

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
            if (e.target == overlay) tutupForm();
        }
    </script>



</body>

</html>