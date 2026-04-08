<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
$csrfToken = csrf_token();

// === SIMPAN DATA ===
if (isset($_POST['action']) && $_POST['action'] == 'save') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf('text');
    }

    $tahun = intval($_POST['tahun'] ?? 0);
    $bulan = trim($_POST['bulan'] ?? '');
    $jenis = trim($_POST['jenis'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $numerator = (float) ($_POST['numerator'] ?? 0);
    $denominator = (float) ($_POST['denominator'] ?? 0);
    $hasil = (float) ($_POST['hasil'] ?? 0);
    $satuan = trim($_POST['satuan'] ?? '');

    $stmt = mysqli_prepare($conn, "INSERT INTO tb_surveillance_antibiotik_mdro 
          (tahun, bulan, jenis, unit, numerator, denominator, hasil, satuan)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isssddds", $tahun, $bulan, $jenis, $unit, $numerator, $denominator, $hasil, $satuan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    exit("success");
}

// === HAPUS DATA ===
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf('text');
    }

    $id = intval($_POST['id'] ?? 0);
    $stmt = mysqli_prepare($conn, "DELETE FROM tb_surveillance_antibiotik_mdro WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    exit("deleted");
}

// === AMBIL DATA ===
if (isset($_GET['load'])) {
    $jenis = trim($_GET['jenis'] ?? '');
    $stmt = mysqli_prepare($conn, "SELECT id, tahun, bulan, unit, numerator, denominator, hasil, satuan FROM tb_surveillance_antibiotik_mdro WHERE jenis = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "s", $jenis);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    mysqli_stmt_close($stmt);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>

<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "SURVEILANCE";
?>
<!--end-->


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Surveilans Antibiotik & MDRO | PPI PHBW</title>


    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">


    <style>
        /* ================= WRAPPER ================= */
        .surveilans {
            padding: 26px;
            min-width: 0;
        }

        /* ================= HEADER ================= */
        .surveilans header {
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

        .surveilans header div {
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

        /* ================= TAB NAV ================= */
        .surveilans nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .surveilans nav button {
            background: var(--card);
            border: 1px solid #dbeafe;
            padding: 8px 16px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 500;
            transition: .2s;
        }

        .surveilans nav button.active {
            background: var(--blue-3);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 136, 229, .3);
        }

        /* ================= CARD FORM ================= */
        .tab h2 {
            margin-bottom: 14px;
            font-size: 20px;
            font-weight: 700;
        }

        #input {
            background: var(--card);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        /* ================= FORM WRAPPER ================= */
        #formSurveilans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
            margin-top: 10px;
        }

        /* ================= FORM GROUP ================= */
        .form-group {
            display: flex;
            flex-direction: column;
        }

        /* LABEL */
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
        }

        /* INPUT & SELECT */
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #cbd5e1;
            border-radius: 14px;
            font-size: 14px;
            background: #f8fafc;
            transition: all .25s ease;
        }

        /* HOVER */
        .form-group input:hover,
        .form-group select:hover {
            border-color: #94a3b8;
        }

        /* FOCUS EFFECT */
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            background: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
        }

        /* RESULT FULL WIDTH */
        .result {
            grid-column: 1 / -1;
            background: #e0f2fe;
            padding: 16px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            color: #0f172a;
            border: 1px solid #bae6fd;
        }

        /* BUTTON FULL WIDTH */
        .save {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s ease;
        }

        .save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, .3);
        }

        /* MOBILE */
        @media(max-width:768px) {
            #formSurveilans {
                grid-template-columns: 1fr;
            }
        }


        /* ================= FILTER ================= */
        .tab>div {
            margin-bottom: 15px;
        }

        /* ================= PDF BUTTON ================= */
        .simpanpdf {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 12px;
            transition: .2s;
        }

        .simpanpdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(37, 99, 235, .3);
        }

        /* ================= TABLE ================= */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 14px;
            overflow: hidden;
            min-width: 750px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
        }

        thead {
            background: #1e40af;
        }

        thead th {
            padding: 14px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: white;
        }

        tbody td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        /* DELETE BUTTON */
        .delete-btn {
            background: #ef4444;
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: white;
            transition: .2s;
        }

        .delete-btn:hover {
            background: #dc2626;
        }

        /* ================= TAB VISIBILITY ================= */
        .tab {
            display: none;
        }

        .tab.active {
            display: block;
        }

        /* ================= RESPONSIVE ================= */
        @media(max-width:768px) {

            .surveilans {
                padding: 16px;
            }

            .surveilans header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            nav {
                gap: 6px;
            }

            nav button {
                font-size: 13px;
                padding: 6px 12px;
            }

            table {
                min-width: 700px;
            }

        }


        /* ================= MOBILE CARD TABLE MODE ================= */
        @media (max-width: 576px) {

            table {
                min-width: 100%;
            }

            table thead {
                display: none;
            }

            table,
            table tbody,
            table tr,
            table td {
                display: block;
                width: 100%;
            }

            table tr {
                margin-bottom: 14px;
                background: white;
                padding: 14px;
                border-radius: 14px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
            }

            table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border: none;
                font-size: 14px;
            }

            table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #475569;
            }

            .delete-btn {
                margin-top: 6px;
            }

        }

        /* =====================================================
   DARK MODE
===================================================== */
        body.dark-mode .surveilans #input {
            background: #111827;
        }

        body.dark-mode .tab h2 {
            color: #e2e8f0;
        }

        body.dark-mode .form-group label {
            color: #94a3b8;
        }

        body.dark-mode .form-group input,
        body.dark-mode .form-group select {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .form-group input:focus,
        body.dark-mode .form-group select:focus {
            background: #253348;
            border-color: #3b82f6;
        }

        body.dark-mode .result {
            background: #0f2744;
            border-color: #1e3a5f;
            color: #e2e8f0;
        }

        body.dark-mode .surveilans nav button {
            background: #1e293b;
            border-color: #334155;
            color: #94a3b8;
        }

        body.dark-mode .surveilans nav button.active {
            background: var(--blue-3);
            color: white;
        }

        body.dark-mode .dashboard-btn {
            background: #1e293b;
            color: #93c5fd;
        }

        body.dark-mode table {
            background: #111827;
        }

        body.dark-mode tbody td {
            color: #e2e8f0;
            border-color: #1e293b;
        }

        body.dark-mode tbody tr:hover {
            background: #1e293b;
        }

        body.dark-mode table tr {
            background: #111827;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode table td::before {
            color: #93c5fd;
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

            <div class="container surveilans">



                <header>
                    <div>💊 Surveilans Antibiotik & MDRO | PPI PHBW</div>
                    <button class="dashboard-btn" onclick="kembaliDashboard()">🏠 Kembali ke Dashboard</button>
                </header>

                <nav>
                    <button class="active" onclick="showTab('input')">🧾 Input Data</button>
                    <button onclick="showTab('Antibiotik')">💊 Rekap Antibiotik</button>
                    <button onclick="showTab('MDRO')">🦠 Rekap MDRO</button>
                </nav>


                <!-- TAB INPUT -->
                <div id="input" class="tab active">
                    <h2>🧾 Input Data Surveilans</h2>


                    <form id="formSurveilans">

                        <div class="form-group">
                            <label for="tahun">Tahun</label>
                            <input type="number" id="tahun" placeholder="Misal: 2025" min="2020" required>
                        </div>

                        <div class="form-group">
                            <label for="bulan">Bulan</label>
                            <select id="bulan" required>
                                <option value="">Pilih Bulan</option>
                                <option>Januari</option>
                                <option>Februari</option>
                                <option>Maret</option>
                                <option>April</option>
                                <option>Mei</option>
                                <option>Juni</option>
                                <option>Juli</option>
                                <option>Agustus</option>
                                <option>September</option>
                                <option>Oktober</option>
                                <option>November</option>
                                <option>Desember</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="jenis">Jenis Surveilans</label>
                            <select id="jenis" required>
                                <option value="">Pilih Jenis</option>
                                <option value="Antibiotik">Penggunaan Antibiotik</option>
                                <option value="MDRO">MDRO (Organisme Multi Drug Resistant)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="unit">Nama Unit / Ruangan</label>
                            <input type="text" id="unit" placeholder="Contoh: ICU, Ruang Bedah, dll" required>
                        </div>

                        <div class="form-group">
                            <label for="num">Numerator (Kasus / Isolat Positif)</label>
                            <input type="number" id="num" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="denum">Denominator (Total Pasien / Kultur)</label>
                            <input type="number" id="denum" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="tipeHasil">Jenis Hasil</label>
                            <select id="tipeHasil">
                                <option value="persentase">Persentase (%)</option>
                                <option value="permil">Permil (‰)</option>
                            </select>
                        </div>

                        <div class="result" id="hasil">Hasil: -</div>

                        <button type="button" class="save" onclick="simpanData()">
                            💾 Simpan Data
                        </button>

                    </form>




                </div>

                <!-- TAB REKAP ANTIBIOTIK -->
                <div id="Antibiotik" class="tab">
                    <h2>💊 Rekap Penggunaan Antibiotik</h2>

                    <!-- Filter Tahun -->
                    <div style="margin-bottom:15px;">
                        <label for="filterAntibiotik">Filter Tahun:</label>
                        <select id="filterAntibiotik" onchange="filterTable('Antibiotik')">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>

                    <!-- Tombol Simpan PDF -->
                    <button class="simpanpdf" onclick="exportPDF('Antibiotik')">
                        📄 Simpan PDF
                    </button>

                    <div class="table-container">
                        <table id="tableAntibiotik">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
                                    <th>Unit</th>
                                    <th>Numerator</th>
                                    <th>Denominator</th>
                                    <th>Hasil</th>
                                    <th>Satuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB REKAP MDRO -->
                <div id="MDRO" class="tab">
                    <h2>🦠 Rekap MDRO (Multi Drug Resistant Organisms)</h2>

                    <!-- Filter Tahun -->
                    <div style="margin-bottom:15px;">
                        <label for="filterMDRO">Filter Tahun:</label>
                        <select id="filterMDRO" onchange="filterTable('MDRO')">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>

                    <!-- Tombol Simpan PDF -->
                    <button class="simpanpdf" onclick="exportPDF('MDRO')">
                        📄 Simpan PDF
                    </button>

                    <div class="table-container">
                        <table id="tableMDRO">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
                                    <th>Unit</th>
                                    <th>Numerator</th>
                                    <th>Denominator</th>
                                    <th>Hasil</th>
                                    <th>Satuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>




            </div>

        </main>

    </div>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        const csrfToken = <?= json_encode($csrfToken) ?>;

        function showTab(tabId) {
            document.querySelectorAll('nav button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`nav button[onclick="showTab('${tabId}')"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        function kembaliDashboard() {
            window.location.href = "/dashboard.php";
        }

        /* ================= HITUNG REALTIME ================= */
        ["num", "denum", "tipeHasil"].forEach(id => {
            document.getElementById(id).addEventListener("input", hitungHasilRealtime);
        });

        function hitungHasilRealtime() {
            const num = parseFloat(document.getElementById("num").value);
            const den = parseFloat(document.getElementById("denum").value);
            const tipe = document.getElementById("tipeHasil").value;
            const hasilBox = document.getElementById("hasil");

            if (!num || !den) {
                hasilBox.textContent = "Hasil: -";
                return;
            }

            let hasil = tipe === "persentase" ? (num / den) * 100 : (num / den) * 1000;
            const satuan = tipe === "persentase" ? "%" : "‰";

            hasilBox.textContent = `Hasil: ${hasil.toFixed(2)} ${satuan}`;
        }


        function simpanData() {
            const tahun = document.getElementById("tahun").value;
            const bulan = document.getElementById("bulan").value;
            const jenis = document.getElementById("jenis").value;
            const unit = document.getElementById("unit").value;
            const num = parseFloat(document.getElementById("num").value);
            const denum = parseFloat(document.getElementById("denum").value);
            const tipe = document.getElementById("tipeHasil").value;
            const hasilBox = document.getElementById("hasil");
            if (!tahun || !bulan || !jenis || !unit || !num || !denum) {
                alert("⚠️ Lengkapi semua data!");
                return;
            }
            let hasil = tipe === "persentase" ? (num / denum) * 100 : (num / denum) * 1000;
            const satuan = tipe === "persentase" ? "%" : "‰";
            hasil = hasil.toFixed(2);
            hasilBox.textContent = `Hasil: ${hasil} ${satuan}`;
            const fd = new FormData();
            fd.append("action", "save");
            fd.append("tahun", tahun);
            fd.append("bulan", bulan);
            fd.append("jenis", jenis);
            fd.append("unit", unit);
            fd.append("numerator", num);
            fd.append("denominator", denum);
            fd.append("hasil", hasil);
            fd.append("satuan", satuan);
            fd.append("csrf_token", csrfToken);
            fetch("", {
                method: "POST",
                body: fd
            }).then(r => r.text()).then(r => {
                if (r === "success") {
                    alert("✅ Data berhasil disimpan!");
                    loadTable(jenis);
                    document.getElementById("formSurveilans").reset();
                    hasilBox.textContent = "Hasil: -";
                } else alert("❌ Gagal menyimpan data!");
            });
        }

        function loadTable(jenis) {
            const tbody = document.querySelector(`#table${jenis} tbody`);
            tbody.innerHTML = "";
            fetch(`?load=1&jenis=${jenis}`).then(r => r.json()).then(data => {
                data.forEach(row => {
                    const tr = document.createElement("tr");


                    tr.innerHTML = `
                    <td data-label="Tahun">${row.tahun}</td>
                    <td data-label="Bulan">${row.bulan}</td>
                    <td data-label="Unit">${row.unit}</td>
                    <td data-label="Numerator">${row.numerator}</td>
                    <td data-label="Denominator">${row.denominator}</td>
                    <td data-label="Hasil">${row.hasil}</td>
                    <td data-label="Satuan">${row.satuan}</td>
                    <td data-label="Aksi">
                      <button class='delete-btn' onclick="deleteData(${row.id}, '${jenis}')">🗑️</button>
                    </td>
                    `;

                    tbody.appendChild(tr);
                });
                updateYearOptions(jenis);
            });
        }

        function deleteData(id, jenis) {
            if (!confirm("Yakin ingin menghapus data ini?")) return;
            const fd = new FormData();
            fd.append("action", "delete");
            fd.append("id", id);
            fd.append("csrf_token", csrfToken);
            fetch("", {
                method: "POST",
                body: fd
            }).then(r => r.text()).then(r => {
                if (r === "deleted") {
                    alert("🗑️ Data dihapus!");
                    loadTable(jenis);
                } else alert("❌ Gagal hapus!");
            });
        }

        // === FILTER DAN PDF ===
        function updateYearOptions(jenis) {
            const table = document.getElementById("table" + jenis);
            const select = document.getElementById("filter" + jenis);
            const years = new Set();
            table.querySelectorAll("tbody tr").forEach(row => {
                const y = row.cells[0].textContent;
                if (y) years.add(y);
            });
            select.innerHTML = '<option value="">Semua Tahun</option>';
            years.forEach(y => {
                const opt = document.createElement("option");
                opt.value = y;
                opt.textContent = y;
                select.appendChild(opt);
            });
        }

        function filterTable(jenis) {
            const tahun = document.getElementById("filter" + jenis).value;
            const rows = document.querySelectorAll("#table" + jenis + " tbody tr");
            rows.forEach(r => {
                r.style.display = (tahun === "" || r.cells[0].textContent === tahun) ? "" : "none";
            });
        }

        function exportPDF(jenis) {
            const table = document.getElementById("table" + jenis);
            const title = document.querySelector(`#${jenis} h2`).textContent;
            const filter = document.getElementById("filter" + jenis).value || "";
            const tahunInfo = filter ? `Tahun ${filter}` : "Semua Tahun";
            const laporan = document.createElement("div");
            laporan.innerHTML = `
    <div style='text-align:center;font-family:Poppins'>
      <h2 style='color:#1a2a80;'>${title}</h2>
      <p style='color:#2563eb'>${tahunInfo}</p>
    </div>
    <table border='1' cellspacing='0' cellpadding='6' style='width:100%;border-collapse:collapse;font-size:12px;text-align:center'>
      <thead style='background:#2563eb;color:white'><tr><th>Tahun</th><th>Bulan</th><th>Unit</th><th>Numerator</th><th>Denominator</th><th>Hasil</th><th>Satuan</th></tr></thead>
      <tbody>${Array.from(table.querySelectorAll("tbody tr")).filter(r => r.style.display !== "none").map(r => `<tr>${Array.from(r.cells).slice(0, 7).map(c => `<td>${c.textContent}</td>`).join("")}</tr>`).join("")}</tbody>
    </table>
    <p style='text-align:center;font-size:11px;color:#555;margin-top:10px;'>Dicetak oleh Dashboard Surveilans PPI PHBW — ${new Date().toLocaleDateString('id-ID')}</p>`;
            const opt = {
                margin: 0.5,
                filename: title.replaceAll(" ", "_") + "_" + (filter || "Semua_Tahun") + ".pdf",
                image: {
                    type: "jpeg",
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: "in",
                    format: "a4",
                    orientation: "portrait"
                }
            };
            html2pdf().set(opt).from(laporan).save();
        }

        ["Antibiotik", "MDRO"].forEach(loadTable);
    </script>

</body>

</html>