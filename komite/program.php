<?php
include_once '../koneksi.php';
include "../cek_akses.php";
$conn = $koneksi;

// === TAMBAH DATA ===
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_program']);
    $desk = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $pj = mysqli_real_escape_string($conn, $_POST['penanggung_jawab']);
    $mulai = $_POST['tanggal_mulai'];
    $selesai = $_POST['tanggal_selesai'];

    $file = $_FILES['file_program'];
    $namaFile = basename($file['name']);
    $targetDir = "../uploads/program/";
    $namaUnik = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $namaFile);
    $targetFile = $targetDir . $namaUnik;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if ($fileType != "pdf") {
        echo "<script>alert('❌ Hanya file PDF yang diizinkan!');window.location='program.php';</script>";
        exit;
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        mysqli_query($conn, "INSERT INTO tb_program_ppi (nama_program, deskripsi, penanggung_jawab, tanggal_mulai, tanggal_selesai, file_path)
                         VALUES ('$nama', '$desk', '$pj', '$mulai', '$selesai', '$targetFile')");
        echo "<script>alert('✅ Program berhasil disimpan!');window.location='program.php';</script>";
    } else {
        echo "<script>alert('⚠️ Gagal mengunggah file! Pastikan folder uploads/program dapat ditulis.');window.location='program.php';</script>";
    }
}

// === HAPUS DATA ===
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $q = mysqli_query($conn, "SELECT file_path FROM tb_program_ppi WHERE id='$id'");
    $data = mysqli_fetch_assoc($q);
    if ($data && file_exists($data['file_path'])) unlink($data['file_path']);
    mysqli_query($conn, "DELETE FROM tb_program_ppi WHERE id='$id'");
    echo "<script>alert('🗑️ Data program dihapus.');window.location='program.php';</script>";
    exit;
}

// === AMBIL DATA ===
$res = mysqli_query($conn, "SELECT * FROM tb_program_ppi ORDER BY id ASC");
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
    <link rel="stylesheet" href="/assets/css/utama.css?v=10">

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
                                        echo "<tr>
                          <td data-label='No'>$no</td>
                          <td data-label='Nama Program'>{$r['nama_program']}</td>
                          <td data-label='Deskripsi'>{$r['deskripsi']}</td>
                          <td data-label='Penanggung Jawab'>{$r['penanggung_jawab']}</td>
                          <td data-label='Periode'>$periode</td>
                          <td data-label='File'>
                            <a href='/$file' target='_blank' class='btn btn-dashboard'>Lihat</a>
                          </td>
                          <td data-label='Aksi' class='actions'>
                            <a href='?hapus={$r['id']}' 
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



    <script src="/assets/js/utama.js?v=5"></script>

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