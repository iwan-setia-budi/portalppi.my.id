<?php
include_once '../koneksi.php';
include "../cek_akses.php";

/* ===== SIMPAN DATA ===== */
if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $tanggal = $_POST['tanggal'];
    $nama_supervisor = $_POST['nama_supervisor'];
    $jabatan = $_POST['jabatan'];
    $unit = $_POST['unit'];
    $jenis_supervisi = isset($_POST['jenis_supervisi'])
        ? implode(", ", $_POST['jenis_supervisi'])
        : '-';

    $nama_petugas = $_POST['nama_petugas'];

    $tanda_tangan = $_POST['tanda_tangan'];

    $stmt = $conn->prepare("INSERT INTO tb_supervisi_ipcn 
        (tanggal,nama_supervisor,jabatan,unit,jenis_supervisi,nama_petugas,tanda_tangan)
        VALUES (?,?,?,?,?,?,?)");

    $stmt->bind_param(
        "sssssss",
        $tanggal,
        $nama_supervisor,
        $jabatan,
        $unit,
        $jenis_supervisi,
        $nama_petugas,
        $tanda_tangan
    );

    $stmt->execute();
    header("Location: supervisi_ipcn.php");
    exit;
}

/* ===== HAPUS ===== */
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = (int) $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tb_supervisi_ipcn WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: supervisi_ipcn.php");
    exit;
}

$dataRows = [];
$data = safe_query($conn, "SELECT * FROM tb_supervisi_ipcn ORDER BY id DESC");
if ($data) {
    while ($row = $data->fetch_assoc()) {
        $dataRows[] = $row;
    }
}

$tahunListRows = [];
$tahunList = safe_query($conn, "SELECT DISTINCT YEAR(tanggal) as tahun FROM tb_supervisi_ipcn ORDER BY tahun DESC");
if ($tahunList) {
    while ($row = $tahunList->fetch_assoc()) {
        $tahunListRows[] = $row;
    }
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
    <title>Supervisi PPI</title>


    <link rel="stylesheet" href="/assets/css/utama.css?v=15">

    <!-- === Link CSS eksternal === -->

    <style>
        /* ================= WRAPPER ================= */
        .supervisi {
            padding: 26px;
        }

        /* ================= NAV TAB ================= */
        .supervisi nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .supervisi nav button {
            background: var(--card);
            border: 1px solid #dbeafe;
            padding: 8px 16px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 500;
            transition: .2s;
        }

        .supervisi nav button.active {
            background: var(--blue-3);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 136, 229, .3);
        }

        .supervisi h2 {
            margin-top: 10px;
            margin-bottom: 25px;
        }


        /* ================= FORM ================= */
        .supervisi #formSupervisi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .supervisi .form-group {
            display: flex;
            flex-direction: column;
        }

        .supervisi label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .supervisi input,
        .supervisi select {
            width: 100%;
            padding: 10px 14px;
            border-radius: 14px;
            border: 2px solid #cbd5e1;
            background: #f8fafc;
            transition: .25s;
        }

        .supervisi input:focus,
        .supervisi select:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
            outline: none;
        }

        .supervisi .save {
            grid-column: 1/-1;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .supervisi .save:hover {
            transform: translateY(-2px);
        }


        /* ================= HEADER ================= */
        header {
            background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
            color: white;
            padding: 20px 24px;
            border-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
        }

        header div {
            font-size: 18px;
            font-weight: 600;
        }

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


        /* ================= TABLE ================= */

        .supervisi #tableSupervisi {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            background: white;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
        }


        .supervisi thead {
            background: #1e40af;
            color: white;
        }

        .supervisi th,
        .supervisi td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .supervisi td {
            vertical-align: middle;
        }

        .supervisi tbody tr:hover {
            background: #f8fafc;
        }

        .supervisi td img {
            max-width: 100%;
            height: auto;
        }

        /* Proporsi kolom */
        .supervisi th:nth-child(1),
        .supervisi td:nth-child(1) {
            width: 10%;
        }

        .supervisi th:nth-child(2),
        .supervisi td:nth-child(2) {
            width: 14%;
        }

        .supervisi th:nth-child(3),
        .supervisi td:nth-child(3) {
            width: 8%;
        }

        .supervisi th:nth-child(4),
        .supervisi td:nth-child(4) {
            width: 10%;
        }

        .supervisi th:nth-child(5),
        .supervisi td:nth-child(5) {
            width: 28%;
        }

        .supervisi th:nth-child(6),
        .supervisi td:nth-child(6) {
            width: 10%;
        }

        .supervisi th:nth-child(7),
        .supervisi td:nth-child(7) {
            width: 12%;
        }

        .supervisi th:nth-child(8),
        .supervisi td:nth-child(8) {
            width: 8%;
        }



        /* ================= BUTTON ================= */
        .supervisi .delete-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .supervisi .simpanpdf {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            padding: 8px 14px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
        }

        /* ================= TAB ================= */
        .supervisi .tab {
            display: none;
        }

        .supervisi .tab.active {
            display: block;
        }

        .supervisi .filterTahun {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        /* ================= TABLE ================= */

        .supervisi .table-container {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            display: block;
            -webkit-overflow-scrolling: touch;
        }


        .supervisi th,
        .supervisi td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .supervisi td:nth-child(5) {
            white-space: normal;
            word-break: break-word;
            max-width: 300px;
        }



        main {
            overflow-x: auto;
        }

        .supervisi {
            overflow-x: auto;
        }



        /* ================= JENIS SUPERVISI GRID ================= */

        .supervisi .jenis-group {
            grid-column: 1 / -1;
        }

        .supervisi .label-utama {
            margin-bottom: 12px;
        }

        .supervisi .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px 20px;
        }

        .supervisi .check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: .2s;
        }

        .supervisi .check-item:hover {
            background: #e0f2fe;
            border-color: #3b82f6;
        }

        .supervisi .check-item input {
            width: 16px;
            height: 16px;
        }

        #signature-pad {
            touch-action: none;
        }


        .pagination {
            margin-top: 12px;
            text-align: center;
        }

        .pagination button {
            margin: 4px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
        }

        .pagination button:hover {
            background: #2563eb;
            color: white;
        }


        @media (max-width: 768px) {

            .supervisi {
                padding: 15px;
            }

            .supervisi #tableSupervisi {
                min-width: 900px;
                /* paksa tabel tetap lebar */
                table-layout: auto;
                /* jangan fixed di mobile */
            }

            .supervisi th,
            .supervisi td {
                white-space: nowrap;
                /* cegah teks turun */
                font-size: 12px;
                padding: 8px;
            }

            .supervisi .table-container {
                overflow-x: auto;
                display: block;
                scrollbar-width: thin;
            }

            .supervisi .table-container::after {
                content: "← geser →";
                display: block;
                font-size: 11px;
                text-align: center;
                padding: 5px;
                color: #64748b;
            }

            .supervisi .filterTahun {
                flex-direction: column;
                align-items: stretch;
            }

            .supervisi .filterTahun select,
            .supervisi .filterTahun input,
            .supervisi .filterTahun button {
                width: 100%;
            }

        }

        /* Mobile improvement */
        @media(max-width:576px) {
            header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .tab-wrapper {
                gap: 10px;
            }

            .tab-btn {
                flex: 1;
                text-align: center;
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

            <div class="supervisi">

                <header>
                    <div>💊 Supervisi | PPI PHBW</div>
                    <button class="dashboard-btn" onclick="kembaliDashboard()">🏠 Kembali ke Dashboard</button>
                </header>

                <nav>
                    <button class="active" onclick="showTab('formTab')">📝 Form Supervisi</button>
                    <button onclick="showTab('rekapTab')">📊 Rekap Supervisi</button>
                    <button onclick="showTab('grafikTab')">📈 Grafik Kunjungan</button>
                </nav>

                <!-- TAB FORM -->
                <div id="formTab" class="tab active">

                    <h2>Form Supervisi IPCN</h2>

                    <form method="post" id="formSupervisi">
                        <input type="hidden" name="action" value="save">

                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" required>
                        </div>

                        <div class="form-group">
                            <label>Nama Supervisor</label>
                            <input type="text" name="nama_supervisor" required>
                        </div>

                        <div class="form-group">
                            <label>Jabatan</label>
                            <select name="jabatan" required>
                                <option value="IPCD">IPCD</option>
                                <option value="IPCN">IPCN</option>
                            </select>
                        </div>



                        <div class="form-group">
                            <label>Unit Dikunjungi</label>

                            <?php
                            $unitList = $conn->query("SELECT nama_unit FROM tb_unit ORDER BY nama_unit ASC");
                            ?>

                            <select name="unit" required>
                                <option value="">-- Pilih Unit --</option>

                                <?php while ($u = $unitList->fetch_assoc()): ?>
                                    <option value="<?= $u['nama_unit']; ?>">
                                        <?= $u['nama_unit']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>



                        <div class="form-group jenis-group">
                            <label class="label-utama">Jenis Supervisi</label>

                            <div class="checkbox-grid">

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Kebersihan Tangan">
                                    <span>Kebersihan tangan</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="APD">
                                    <span>Alat Pelindung Diri</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Dekontaminasi">
                                    <span>Dekontaminasi</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Kesehatan Lingkungan">
                                    <span>Kesehatan lingkungan</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Limbah">
                                    <span>Pengolahan limbah</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Linen">
                                    <span>Penatalaksanaan linen</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Kesehatan Petugas">
                                    <span>Perlindungan kesehatan petugas</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Penempatan Pasien">
                                    <span>Penempatan pasien</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Higiene Respirasi">
                                    <span>Higiene respirasi</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Penyuntikan Aman">
                                    <span>Praktik penyuntikan aman</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Transmisi">
                                    <span>Kewaspadaan transmisi</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Bundle">
                                    <span>Surveilance & Bundle</span>
                                </label>

                                <label class="check-item">
                                    <input type="checkbox" name="jenis_supervisi[]" value="Antibiotika">
                                    <span>Antibiotika</span>
                                </label>

                            </div>
                        </div>


                        <div class="form-group">
                            <label>Nama Petugas Unit</label>
                            <input type="text" name="nama_petugas" required>
                        </div>


                        <div class="form-group jenis-group">
                            <label>Tanda Tangan Supervisor</label>

                            <canvas id="signature-pad"
                                style="border:1px solid #ccc; border-radius:12px; width:100%; max-width:600px; height:200px;">
                            </canvas>


                            <input type="hidden" name="tanda_tangan" id="tanda_tangan">

                            <div style="margin-top:10px;">
                                <button type="button" onclick="clearSignature()"
                                    style="padding:6px 12px; border-radius:8px; border:none; background:#64748b; color:white;">
                                    Hapus Tanda Tangan
                                </button>
                            </div>
                        </div>


                        <button type="submit" class="save">💾 Simpan</button>
                    </form>

                </div>

                <div id="rekapTab" class="tab">

                    <h2>Rekap Supervisi IPCN</h2>

                    <div class="filterTahun">
                        <select id="filterTahun" onchange="filterData()">
                            <option value="semua">Semua Tahun</option>
                            <?php foreach ($tahunListRows as $t): ?>
                                <option value="<?= $t['tahun'] ?>"><?= $t['tahun'] ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input type="month" id="filterBulan" onchange="filterData()">

                        <input type="text" id="filterUnit" placeholder="Filter Unit" onkeyup="filterData()">

                        <button class="simpanpdf" onclick="simpanPDF()">📄 Simpan PDF</button>
                    </div>

                    <div class="table-container" id="tableContainer">
                        <table id="tableSupervisi">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Supervisor</th>
                                    <th>Jabatan</th>
                                    <th>Unit</th>
                                    <th>Jenis Supervisi</th>
                                    <th>Petugas</th>
                                    <th>Tanda Tangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataRows as $row): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($row['tanggal']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['nama_supervisor']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['jabatan']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['unit']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['jenis_supervisi']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['nama_petugas']) ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['tanda_tangan'])): ?>
                                                <img src="<?= $row['tanda_tangan']; ?>" width="120">
                                            <?php endif; ?>
                                        </td>


                                        <td>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button class="delete-btn"
                                                    onclick="return confirm('Yakin ingin menghapus data ini?')">🗑</button>

                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div id="grafikTab" class="tab">

                    <h2>Grafik Kunjungan Supervisi IPCN</h2>

                    <div class="filterTahun">
                        <input type="month" id="grafikBulan" onchange="loadGrafik()">
                        <button class="simpanpdf" onclick="loadGrafik()">🔄 Refresh Grafik</button>
                    </div>

                    <canvas id="grafikKunjungan" height="120"></canvas>

                </div>



            </div>

        </main>

    </div>

    <script src="/assets/js/utama.js?v=5"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            /* =========================
               TAB NAVIGATION
            ========================== */
            window.showTab = function(id) {

                // Nonaktifkan semua tab
                document.querySelectorAll(".supervisi .tab")
                    .forEach(tab => tab.classList.remove("active"));

                // Nonaktifkan semua tombol
                document.querySelectorAll(".supervisi nav button")
                    .forEach(btn => btn.classList.remove("active"));

                // Aktifkan tab yang dipilih
                const activeTab = document.getElementById(id);
                if (activeTab) {
                    activeTab.classList.add("active");
                }

                if (id === 'grafikTab') {
                    loadGrafik();
                }


                // Aktifkan tombol yang sesuai
                document.querySelectorAll(".supervisi nav button")
                    .forEach(btn => {
                        if (btn.getAttribute("onclick") &&
                            btn.getAttribute("onclick").includes(id)) {
                            btn.classList.add("active");
                        }
                    });
            };


            /* =========================
               FILTER DATA
            ========================== */
            window.filterData = function() {

                const tahunSelect = document.getElementById("filterTahun");
                const bulanInput = document.getElementById("filterBulan");
                const unitInput = document.getElementById("filterUnit");

                if (!tahunSelect) return;

                const tahun = tahunSelect.value;
                const bulan = bulanInput ? bulanInput.value : "";
                const unit = unitInput ? unitInput.value.toLowerCase() : "";

                const rows = document.querySelectorAll("#tableSupervisi tbody tr");

                rows.forEach(row => {

                    const tgl = row.children[0].innerText.trim(); // format: YYYY-MM-DD
                    const rowTahun = tgl.substring(0, 4);
                    const rowBulan = tgl.substring(0, 7); // YYYY-MM
                    const rowUnit = row.children[3].innerText.toLowerCase();

                    let cocok = true;

                    // Filter Tahun
                    if (tahun !== "semua" && rowTahun !== tahun) {
                        cocok = false;
                    }

                    // Filter Bulan
                    if (bulan && rowBulan !== bulan) {
                        cocok = false;
                    }

                    // Filter Unit
                    if (unit && !rowUnit.includes(unit)) {
                        cocok = false;
                    }

                    row.style.display = cocok ? "" : "none";
                });

            };


            /* =========================
               SIMPAN PDF
            ========================== */
            window.simpanPDF = async function() {

                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF("l", "pt", "a4");

                const rows = document.querySelectorAll("#tableSupervisi tbody tr");

                let startY = 100;

                // ===== HEADER =====
                pdf.setFontSize(16);
                pdf.text("REKAP SUPERVISI IPCN", pdf.internal.pageSize.getWidth() / 2, 40, {
                    align: "center"
                });

                pdf.setFontSize(10);
                pdf.text(
                    "Dicetak pada: " + new Date().toLocaleDateString("id-ID"),
                    pdf.internal.pageSize.getWidth() / 2,
                    60, {
                        align: "center"
                    }
                );




                // ===== TABLE TANPA TTD DULU =====
                // ambil hanya row yang tampil
                const allRows = document.querySelectorAll("#tableSupervisi tbody tr");

                let visibleRows = [];
                let bodyData = [];

                allRows.forEach(row => {

                    // ✅ Hanya ambil yang TIDAK terfilter
                    if (row.style.display === "none") return;

                    visibleRows.push(row);

                    bodyData.push([
                        row.children[0].innerText,
                        row.children[1].innerText,
                        row.children[2].innerText,
                        row.children[3].innerText,
                        row.children[4].innerText,
                        row.children[5].innerText,
                        ""
                    ]);
                });


                // ===== TAMBAHKAN GAMBAR TTD KE SETIAP BARIS =====
                pdf.autoTable({
                    startY: 80,
                    head: [
                        [
                            "Tanggal",
                            "Supervisor",
                            "Jabatan",
                            "Unit",
                            "Jenis Supervisi",
                            "Petugas",
                            "Tanda Tangan"
                        ]
                    ],
                    body: bodyData,
                    theme: "grid",
                    styles: {
                        fontSize: 9,
                        cellPadding: 6,
                        valign: "middle",
                        minCellHeight: 45
                    },
                    headStyles: {
                        fillColor: [30, 64, 175],
                        textColor: 255,
                        halign: "center"
                    },
                    columnStyles: {
                        4: {
                            cellWidth: 220
                        },
                        6: {
                            cellWidth: 100
                        }
                    },

                    didDrawCell: function(data) {

                        if (data.column.index === 6 && data.cell.section === 'body') {

                            const rowIndex = data.row.index;
                            const row = visibleRows[rowIndex];
                            if (!row) return;

                            const img = row.querySelector("td:nth-child(7) img");
                            if (!img) return;

                            const base64 = img.src;
                            const cell = data.cell;

                            const imgProps = pdf.getImageProperties(base64);

                            const maxWidth = cell.width - 10;
                            const maxHeight = cell.height - 10;

                            let ratio = imgProps.width / imgProps.height;

                            let imgWidth = maxWidth;
                            let imgHeight = imgWidth / ratio;

                            if (imgHeight > maxHeight) {
                                imgHeight = maxHeight;
                                imgWidth = imgHeight * ratio;
                            }

                            const x = cell.x + (cell.width - imgWidth) / 2;
                            const y = cell.y + (cell.height - imgHeight) / 2;

                            pdf.addImage(base64, "PNG", x, y, imgWidth, imgHeight);
                        }
                    }
                });



                pdf.save("Rekap_Supervisi_IPCN.pdf");
            };




        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const canvas = document.getElementById("signature-pad");
            const ctx = canvas.getContext("2d");
            const inputSignature = document.getElementById("tanda_tangan");

            /* =========================
               RESIZE CANVAS AGAR PRESISI
            ========================== */
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);

                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;

                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            }

            resizeCanvas();

            ctx.lineWidth = 2.5;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            ctx.strokeStyle = "#000";

            let drawing = false;

            /* =========================
               GET POSITION
            ========================== */
            function getPosition(e) {
                const rect = canvas.getBoundingClientRect();
                if (e.touches) {
                    return {
                        x: e.touches[0].clientX - rect.left,
                        y: e.touches[0].clientY - rect.top
                    };
                } else {
                    return {
                        x: e.clientX - rect.left,
                        y: e.clientY - rect.top
                    };
                }
            }

            /* =========================
               START DRAW
            ========================== */
            function start(e) {
                drawing = true;
                const pos = getPosition(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            }

            /* =========================
               DRAW
            ========================== */
            function draw(e) {
                if (!drawing) return;
                e.preventDefault();

                const pos = getPosition(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            }

            /* =========================
               STOP
            ========================== */
            function stop() {
                if (!drawing) return;
                drawing = false;
                saveSignature();
            }

            /* =========================
               EVENT LISTENER
            ========================== */
            canvas.addEventListener("mousedown", start);
            canvas.addEventListener("mousemove", draw);
            canvas.addEventListener("mouseup", stop);
            canvas.addEventListener("mouseleave", stop);

            canvas.addEventListener("touchstart", start);
            canvas.addEventListener("touchmove", draw);
            canvas.addEventListener("touchend", stop);

            /* =========================
               CLEAR
            ========================== */
            window.clearSignature = function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                inputSignature.value = "";
            }

            /* =========================
               SAVE BASE64
            ========================== */
            function saveSignature() {
                inputSignature.value = canvas.toDataURL("image/png");
            }


        });

        let chart;

        window.loadGrafik = function() {

            const bulan = document.getElementById("grafikBulan").value;

            const rows = document.querySelectorAll("#tableSupervisi tbody tr");

            let dataUnit = {};

            rows.forEach(row => {

                const tgl = row.children[0].innerText.trim();
                const rowBulan = tgl.substring(0, 7);
                const unit = row.children[3].innerText.trim();

                if (bulan && rowBulan !== bulan) return;

                if (!dataUnit[unit]) {
                    dataUnit[unit] = 0;
                }

                dataUnit[unit]++;
            });

            const labels = Object.keys(dataUnit);
            const values = Object.values(dataUnit);

            const ctx = document.getElementById("grafikKunjungan").getContext("2d");

            if (chart) {
                chart.destroy();
            }

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: values,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

        };
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const rows = document.querySelectorAll("#tableSupervisi tbody tr");
            const rowsPerPage = 10;
            let currentPage = 1;

            function showPage(page) {
                currentPage = page;

                rows.forEach((row, index) => {
                    row.style.display =
                        (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ?
                        "" :
                        "none";
                });
            }

            function createPagination() {
                const pageCount = Math.ceil(rows.length / rowsPerPage);
                const container = document.createElement("div");
                container.className = "pagination";

                for (let i = 1; i <= pageCount; i++) {
                    const btn = document.createElement("button");
                    btn.innerText = i;
                    btn.onclick = () => showPage(i);
                    container.appendChild(btn);
                }

                document.getElementById("tableContainer")
                    .appendChild(container);
            }

            if (rows.length > rowsPerPage) {
                createPagination();
                showPage(1);
            }
        });
    </script>

    <script>
        function kembaliDashboard() {
            window.location.href = "../dashboard.php";
        }
    </script>


</body>

</html>