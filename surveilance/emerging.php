<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
$csrfToken = csrf_token();

// ====== SIMPAN DATA ======
if (isset($_POST['action']) && $_POST['action'] == 'save') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf();
    }

    $tahun = intval($_POST['tahun'] ?? 0);
    $bulan = trim($_POST['bulan'] ?? '');
    $jenis = trim($_POST['jenis'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $numerator = (float) ($_POST['numerator'] ?? 0);
    $denominator = (float) ($_POST['denominator'] ?? 0);
    $hasil = (float) ($_POST['hasil'] ?? 0);
    $satuan = trim($_POST['satuan'] ?? '');

    $sql = "INSERT INTO tb_emerging (tahun, bulan, jenis, unit, numerator, denominator, hasil, satuan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssddds", $tahun, $bulan, $jenis, $unit, $numerator, $denominator, $hasil, $satuan);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ====== HAPUS DATA ======
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        ppi_abort_csrf();
    }

    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM tb_emerging WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// ====== TAMPILKAN DATA ======
$emergingStmt = $conn->prepare("SELECT id, tahun, bulan, unit, numerator, denominator, hasil, satuan FROM tb_emerging WHERE jenis = ? ORDER BY id DESC");
$emergingJenis = 'Emerging';
$emergingStmt->bind_param("s", $emergingJenis);
$emergingStmt->execute();
$emerging = $emergingStmt->get_result();

$purulenStmt = $conn->prepare("SELECT id, tahun, bulan, unit, numerator, denominator, hasil, satuan FROM tb_emerging WHERE jenis = ? ORDER BY id DESC");
$purulenJenis = 'Purulen';
$purulenStmt->bind_param("s", $purulenJenis);
$purulenStmt->execute();
$purulen = $purulenStmt->get_result();
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
    <title>Surveilans Infeksi Emerging & Purulen | PPI PHBW</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        /* ================= WRAPPER ================= */
        .emerging {
            padding: 26px;
            min-width: 0;
        }

        /* ================= HEADER ================= */
        .emerging header {
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

        .emerging header div {
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
        .emerging nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .emerging nav button {
            background: var(--card);
            border: 1px solid #dbeafe;
            padding: 8px 16px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 500;
            transition: .2s;
        }

        .emerging nav button.active {
            background: var(--blue-3);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 136, 229, .3);
        }

        /* ================= CARD FORM ================= */
        #input {
            background: var(--card);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }


        #input label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
        }

        #input input,
        #input select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #cbd5e1;
            border-radius: 14px;
            font-size: 14px;
            background: #f8fafc;
            transition: all .25s ease;
        }

        #input input:hover,
        #input select:hover {
            border-color: #94a3b8;
        }

        #input input:focus,
        #input select:focus {
            outline: none;
            background: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
        }

        #input button.save {
            grid-column: 1/-1;
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

        #input button.save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, .3);
        }


        /* ================= Form new ================= */

        .form-group {
            display: flex;
            flex-direction: column;
        }


        #formEmerging {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
        }

        /* === Heading spacing improvement === */
        .emerging h2 {
            margin-bottom: 22px;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }


        /* ================= FILTER ================= */
        .filterTahun {
            margin-bottom: 16px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filterTahun select {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
        }

        /* ================= PDF BUTTON ================= */
        .simpanpdf {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
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

            .emerging {
                padding: 16px;
            }

            .emerging header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            table {
                min-width: 700px;
            }

        }

        /* ================= MOBILE CARD TABLE MODE ================= */
        @media (max-width:576px) {

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
                font-size: 13px;
                padding: 8px 0;
                border: none;
            }

            table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #475569;
            }

            .delete-btn {
                align-self: flex-end;
            }

        }

        /* =====================================================
   DARK MODE
===================================================== */
        body.dark-mode .emerging #input {
            background: #111827;
        }

        body.dark-mode .emerging h2 {
            color: #e2e8f0;
        }

        body.dark-mode #input label {
            color: #94a3b8;
        }

        body.dark-mode #input input,
        body.dark-mode #input select {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }

        body.dark-mode #input input:focus,
        body.dark-mode #input select:focus {
            background: #253348;
            border-color: #3b82f6;
        }

        body.dark-mode .emerging nav button {
            background: #1e293b;
            border-color: #334155;
            color: #94a3b8;
        }

        body.dark-mode .emerging nav button.active {
            background: var(--blue-3);
            color: white;
        }

        body.dark-mode .dashboard-btn {
            background: #1e293b;
            color: #93c5fd;
        }

        body.dark-mode .filterTahun select {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
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

            <div class="container emerging">


                <header>
                    <div>🦠 Surveilans Infeksi Emerging & Purulen | PPI PHBW</div>
                    <button class="dashboard-btn" onclick="kembaliDashboard()">🏠 Kembali ke Dashboard</button>
                </header>

                <nav>
                    <button class="active" onclick="showTab('input')">🧾 Input Data</button>
                    <button onclick="showTab('Emerging')">🌍 Rekap Infeksi Emerging</button>
                    <button onclick="showTab('Purulen')">💉 Rekap Infeksi Purulen</button>
                </nav>


                <!-- TAB INPUT -->
                <div id="input" class="tab active">
                    <h2>🧾 Form Input Surveilans Infeksi</h2>

                    <form method="post" id="formEmerging">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="save">

                        <div class="form-group">
                            <label for="tahun">Tahun</label>
                            <input type="number" name="tahun" id="tahun" placeholder="Misal: 2025" min="2020" required>
                        </div>

                        <div class="form-group">
                            <label for="bulan">Bulan</label>
                            <select name="bulan" id="bulan" required>
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
                            <select name="jenis" id="jenis" required>
                                <option value="">Pilih Jenis</option>
                                <option value="Emerging">Infeksi Emerging</option>
                                <option value="Purulen">Infeksi Purulen</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="unit">Nama Unit / Ruangan</label>
                            <input type="text" name="unit" id="unit" placeholder="Contoh: IGD, ICU, Ruang Rawat"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="num">Numerator (Kasus Infeksi Ditemukan)</label>
                            <input type="number" name="numerator" id="num" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="denum">Denominator (Total Pasien / Sampel)</label>
                            <input type="number" name="denominator" id="denum" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="tipeHasil">Jenis Hasil</label>
                            <select id="tipeHasil">
                                <option value="persentase">Persentase (%)</option>
                                <option value="permil">Permil (‰)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="hasil">Hasil (otomatis)</label>
                            <input type="text" name="hasil" id="hasil" readonly required>
                        </div>

                        <input type="hidden" name="satuan" id="satuan" value="%">

                        <button type="submit" class="save">💾 Simpan Data</button>
                    </form>


                </div>

                <!-- TAB EMERGING -->
                <div id="Emerging" class="tab">
                    <h2>🌍 Rekap Infeksi Emerging</h2>

                    <!-- 🔹 Filter Tahun & Tombol Simpan PDF -->
                    <div class="filterTahun">
                        <label for="filterTahunEmerging">Filter Tahun:</label>
                        <select id="filterTahunEmerging" onchange="filterTahun('Emerging')">
                            <option value="semua">Semua Tahun</option>
                            <?php
                            $tahunList = $conn->query("SELECT DISTINCT tahun FROM tb_emerging WHERE jenis='Emerging' ORDER BY tahun DESC");
                            while ($t = $tahunList->fetch_assoc()) {
                                $tahunValue = (int) ($t['tahun'] ?? 0);
                                echo "<option value='{$tahunValue}'>{$tahunValue}</option>";
                            }
                            ?>
                        </select>
                        <button class="simpanpdf" onclick="simpanPDF('Emerging')">
                            📄 Simpan PDF
                        </button>
                    </div>

                    <!-- 🔹 Tabel Emerging -->
                    <div class="table-container" id="tableEmergingContainer">
                        <table id="tableEmerging">
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
                            <tbody>
                                <?php while ($row = $emerging->fetch_assoc()): ?>

                                    <tr>
                                        <td data-label="Tahun">
                                            <?= (int) $row['tahun'] ?>
                                        </td>
                                        <td data-label="Bulan">
                                            <?= htmlspecialchars($row['bulan'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Unit">
                                            <?= htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Numerator">
                                            <?= htmlspecialchars((string) $row['numerator'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Denominator">
                                            <?= htmlspecialchars((string) $row['denominator'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Hasil">
                                            <?= htmlspecialchars((string) $row['hasil'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Satuan">
                                            <?= htmlspecialchars($row['satuan'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Aksi">
                                            <form method="post">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <button type="submit" class="delete-btn"
                                                    onclick="return confirm('Yakin hapus data ini?')">🗑️</button>
                                            </form>
                                        </td>
                                    </tr>


                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- TAB PURULEN -->
                <div id="Purulen" class="tab">
                    <h2>💉 Rekap Infeksi Purulen</h2>

                    <!-- 🔹 Filter Tahun & Tombol Simpan PDF -->
                    <div class="filterTahun">
                        <label for="filterTahunPurulen">Filter Tahun:</label>
                        <select id="filterTahunPurulen" onchange="filterTahun('Purulen')">
                            <option value="semua">Semua Tahun</option>
                            <?php
                            $tahunListPurulen = $conn->query("SELECT DISTINCT tahun FROM tb_emerging WHERE jenis='Purulen' ORDER BY tahun DESC");
                            while ($tp = $tahunListPurulen->fetch_assoc()) {
                                $tahunValue = (int) ($tp['tahun'] ?? 0);
                                echo "<option value='{$tahunValue}'>{$tahunValue}</option>";
                            }
                            ?>
                        </select>
                        <button class="simpanpdf" type="button" onclick="simpanPDF('Purulen')">
                            📄 Simpan PDF
                        </button>
                    </div>

                    <!-- 🔹 Tabel Purulen -->
                    <div class="table-container" id="tablePurulenContainer">
                        <table id="tablePurulen">
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
                            <tbody>
                                <?php while ($row = $purulen->fetch_assoc()): ?>

                                    <tr>
                                        <td data-label="Tahun">
                                            <?= (int) $row['tahun'] ?>
                                        </td>
                                        <td data-label="Bulan">
                                            <?= htmlspecialchars($row['bulan'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Unit">
                                            <?= htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Numerator">
                                            <?= htmlspecialchars((string) $row['numerator'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Denominator">
                                            <?= htmlspecialchars((string) $row['denominator'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Hasil">
                                            <?= htmlspecialchars((string) $row['hasil'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Satuan">
                                            <?= htmlspecialchars($row['satuan'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td data-label="Aksi">
                                            <form method="post">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <button type="submit" class="delete-btn"
                                                    onclick="return confirm('Yakin hapus data ini?')">🗑️</button>
                                            </form>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </main>

    </div>



    <script src="<?= asset('assets/js/utama.js') ?>"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // === Hitung hasil otomatis ===
        document.getElementById('num').addEventListener('input', hitungHasil);
        document.getElementById('denum').addEventListener('input', hitungHasil);
        document.getElementById('tipeHasil').addEventListener('change', hitungHasil);

        function hitungHasil() {
            const num = parseFloat(document.getElementById('num').value);
            const denum = parseFloat(document.getElementById('denum').value);
            const tipe = document.getElementById('tipeHasil').value;
            const hasilInput = document.getElementById('hasil');
            const satuanInput = document.getElementById('satuan');

            if (num > 0 && denum > 0) {
                const hasil = tipe === 'persentase' ? (num / denum) * 100 : (num / denum) * 1000;
                hasilInput.value = hasil.toFixed(2);
                satuanInput.value = tipe === 'persentase' ? '%' : '‰';
            } else {
                hasilInput.value = '';
            }
        }

        // === Navigasi antar tab ===
        function showTab(id) {
            document.querySelectorAll('nav button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector(`nav button[onclick="showTab('${id}')"]`).classList.add('active');
            document.getElementById(id).classList.add('active');
        }

        // === Tombol kembali ke dashboard ===
        function kembaliDashboard() {
            window.location.href = "../dashboard.php"; // ubah path sesuai lokasi dashboard kamu
        }

        // === Filter Tahun ===
        function filterTahun(jenis) {
            const tahun = document.getElementById('filterTahun' + jenis).value;
            const rows = document.querySelectorAll(`#table${jenis} tbody tr`);

            rows.forEach(row => {
                const tahunCell = row.children[0].innerText.trim();
                if (tahun === 'semua' || tahunCell === tahun) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // === Simpan PDF ===
        async function simpanPDF(jenis) {
            const container = document.getElementById(`table${jenis}Container`);
            const tahun = document.getElementById(`filterTahun${jenis}`).value;
            const judul = jenis === 'Emerging' ? 'Rekap Infeksi Emerging' : 'Rekap Infeksi Purulen';

            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF('p', 'pt', 'a4');

            const header = `
    <h2 style="text-align:center;">🦠 ${judul}</h2>
    <p style="text-align:center;">${tahun === 'semua' ? 'Semua Tahun' : 'Tahun ' + tahun}</p>
  `;

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = header + container.outerHTML + `
    <p style="text-align:center; font-size:10pt; margin-top:20px;">
      Dicetak oleh Dashboard Surveilans PPI PHBW — ${new Date().toLocaleDateString('id-ID')}
    </p>
  `;
            document.body.appendChild(tempDiv);

            const canvas = await html2canvas(tempDiv, {
                scale: 2
            });
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = 550;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let position = 40;

            pdf.addImage(imgData, 'PNG', 30, position, imgWidth, imgHeight);
            pdf.save(`${judul.replace(/\s+/g, '_')}.pdf`);

            tempDiv.remove();
        }
    </script>


</body>

</html>

<?php
$emergingStmt->close();
$purulenStmt->close();
?>