<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
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
    <title>Surveilans Infeksi RS | PPI PHBW</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        /* ================= CONTENT ================= */
        .surveilans {
            padding: 26px;
        }

        /* ================= HEADER CARD ================= */
        .surveilans .page-header {
            background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
            color: white;
            padding: 20px;
            border-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
        }


        .judul {
            font-weight: bold;
            font-size: 18px;
            padding-left: 6px;
        }

        .surveilans .page-header button {
            background: white;
            color: var(--blue-2);
            border: none;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
        }

        /* ================= TAB ================= */
        .surveilans .tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .surveilans .tab-nav button {
            background: var(--card);
            border: 1px solid #dbeafe;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: .3s;
        }

        .surveilans .tab-nav button.active {
            background: var(--blue-3);
            color: white;
        }

        /* ================= CARD FORM ================= */

        .surveilans form label {
            display: block;
            margin-top: 14px;
            font-weight: 600;
            font-size: 14px;
        }

        .surveilans form input,
        .surveilans form select {
            width: 100%;
            padding: 8px 10px;
            margin-top: 6px;
            border: 1px solid #dbeafe;
            border-radius: var(--radius-sm);
        }

        .surveilans .result {
            margin-top: 16px;
            padding: 10px;
            background: var(--blue-soft);
            border-radius: var(--radius-sm);
            font-weight: 600;
        }

        .surveilans .save {
            margin-top: 16px;
            background: var(--blue-2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            cursor: pointer;
        }

        /* ================= TABLE STYLE FOLLOW DASHBOARD ================= */
        /* Rata tengah untuk angka */
        .surveilans th:nth-child(1),
        .surveilans th:nth-child(2),
        .surveilans th:nth-child(3),
        .surveilans th:nth-child(4),
        .surveilans th:nth-child(5),
        .surveilans th:nth-child(6),
        .surveilans th:nth-child(7),
        .surveilans td:nth-child(1),
        .surveilans td:nth-child(2),
        .surveilans td:nth-child(3),
        .surveilans td:nth-child(4),
        .surveilans td:nth-child(5),
        .surveilans td:nth-child(6),
        .surveilans td:nth-child(7) {
            text-align: center;
        }



        /* ===== TABLE CLEAN PROFESSIONAL ===== */

        .surveilans table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
        }

        .surveilans thead {
            background: #1d4ed8;
            /* biru solid lebih clean */
        }

        .surveilans thead th {
            border-bottom: 2px solid #1e40af;
            color: white;
            padding: 14px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
        }

        .surveilans tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .surveilans tbody tr:last-child {
            border-bottom: none;
        }

        .surveilans tbody td {
            border-bottom: 1.5px solid #d1d5db;
            padding: 14px;
            font-size: 14px;
            color: #1e293b;
        }


        .surveilans tbody tr:hover {
            background: #f0f6f5;
        }


        .surveilans .delete-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
        }


        .surveilans .tab {
            display: none;
        }

        .surveilans .tab.active {
            display: block;
        }

        .surveilans .filter {
            margin-bottom: 15px;
        }

        .surveilans .card {
            background: var(--card);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .surveilans .btn-danger {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
        }

        .surveilans .btn-danger:hover {
            background: #dc2626;
        }


        /* ================= REKAP SECTION AUTO LAYOUT ================= */

        /* Bungkus area rekap supaya seperti section */
        .surveilans .tab>h2 {
            margin-bottom: 8px;
            font-size: 22px;
            font-weight: 700;
        }

        /* Filter dan tombol jadi satu baris */
        .surveilans .filter {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Biar tombol PDF naik ke kanan */
        .surveilans .table-container {
            margin-top: 16px;
        }


        /* Kasih garis pemisah elegan */
        .surveilans .tab>h2 {
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 15px;
        }

        /* Clear float agar tabel tidak naik */
        .surveilans table {
            clear: both;
        }

        /*===== end =====*/

        /* ==== BUTTON EXPORT HIJAU ==== */
        .surveilans .btn-export {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all .2s ease;
        }

        .surveilans .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(34, 197, 94, 0.3);
        }

        /* ================= MOBILE TABLE FIX ================= */
        /* ================= MOBILE OPTIMIZATION ================= */
        @media (max-width: 768px) {

            .surveilans {
                padding: 14px;
            }

            .surveilans .page-header {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .surveilans .tab-nav {
                gap: 6px;
            }

            .surveilans .tab-nav button {
                font-size: 13px;
                padding: 8px 10px;
            }

            .surveilans .filter {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .surveilans .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .surveilans table {
                min-width: 650px;
            }

            .surveilans thead th,
            .surveilans tbody td {
                padding: 10px;
                font-size: 12px;
            }

            .surveilans .delete-btn {
                font-size: 12px;
                padding: 6px 8px;
            }

            .surveilans .btn-export {
                width: 100%;
                margin-bottom: 10px;
            }

        }

        /* Override global overflow hidden */
        .surveilans {
            overflow-x: visible;
        }

        @media (max-width: 768px) {
            body {
                overflow-x: auto !important;
            }
        }



        /*NEW*/
        /* IZINKAN GRID ITEM MELEBAR */
        main {
            min-width: 0;
        }

        .layout {
            min-width: 0;
        }


        .surveilans {
            min-width: 0;
        }

        .surveilans .table-container {
            overflow-x: auto;
        }

        .surveilans table {
            min-width: 650px;
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

                <header class="page-header">
                    <div class="judul">📊 Surveilans Infeksi Rumah Sakit | PPI PHBW</div>
                    <button class="dashboard-btn" onclick="kembaliDashboard()">🏠 Kembali ke Dashboard</button>
                </header>

                <nav class="tab-nav">

                    <button class="active" onclick="showTab('input')">🧾 Input Data</button>
                    <button onclick="showTab('ISK')">🧫 Rekap ISK</button>
                    <button onclick="showTab('IDO')">🩹 Rekap IDO</button>
                    <button onclick="showTab('VAP')">🫁 Rekap VAP</button>
                    <button onclick="showTab('IADP')">💉 Rekap IADP</button>
                </nav>


                <!-- TAB INPUT -->

                <div id="input" class="tab active">
                    <div class="card">

                        <h2>🧾 Form Input Data Surveilans</h2>

                        <form id="formSurveilans">
                            <label>Tahun</label>
                            <input type="number" id="tahun" placeholder="Misal: 2025" min="2020" required>

                            <label>Bulan</label>
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

                            <label>Jenis Surveilans</label>
                            <select id="jenis" required>
                                <option value="">Pilih Jenis</option>
                                <option value="ISK">ISK (Infeksi Saluran Kemih)</option>
                                <option value="IDO">IDO (Infeksi Daerah Operasi)</option>
                                <option value="VAP">VAP (Ventilator Associated Pneumonia)</option>
                                <option value="IADP">IADP (Infeksi Aliran Darah Primer)</option>
                            </select>

                            <label>Numerator (Kasus Infeksi)</label>
                            <input type="number" id="num" placeholder="Jumlah kasus infeksi" min="0" required>

                            <label>Denominator (Pasien berisiko)</label>
                            <input type="number" id="denum" placeholder="Jumlah pasien berisiko" min="1" required>

                            <label>Jenis Hasil</label>
                            <select id="tipeHasil">
                                <option value="persentase">Persentase (%)</option>
                                <option value="permil">Permil (‰)</option>
                            </select>

                            <div class="result" id="hasil">Hasil: -</div>

                            <button type="button" class="save" onclick="simpanData()">💾 Simpan Data</button>
                        </form>

                    </div>
                </div>

                <!-- TAB REKAP -->
                <div id="ISK" class="tab">

                    <h2>🧫 Rekap ISK (Infeksi Saluran Kemih)</h2>

                    <!-- Filter Tahun -->
                    <div class="filter">

                        <label for="filterISK">Filter Tahun:</label>
                        <select id="filterISK" onchange="filterTable('ISK')">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>

                    <div class="table-container">

                        <button class="btn-export" onclick="exportPDF('ISK')">

                            📄 Simpan PDF
                        </button>

                        <table id="tableISK">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
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

                <!-- ================= REKAP IDO ================= -->
                <div id="IDO" class="tab">
                    <h2>🩹 Rekap IDO (Infeksi Daerah Operasi)</h2>
                    <div class="filter">
                        <label for="filterIDO">Filter Tahun:</label>
                        <select id="filterIDO" onchange="filterTable('IDO')">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>

                    <div class="table-container">
                        <button class="btn-export" onclick="exportPDF('IDO')">
                            📄 Simpan PDF
                        </button>

                        <table id="tableIDO">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
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

                <!-- ================= REKAP VAP ================= -->
                <div id="VAP" class="tab">
                    <h2>🫁 Rekap VAP (Ventilator Associated Pneumonia)</h2>
                    <div class="filter">
                        <label for="filterVAP">Filter Tahun:</label>
                        <select id="filterVAP" onchange="filterTable('VAP')">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>

                    <div class="table-container">
                        <button class="btn-export" onclick="exportPDF('VAP')">

                            📄 Simpan PDF
                        </button>

                        <table id="tableVAP">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
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

                <!-- ================= REKAP IADP ================= -->
                <div id="IADP" class="tab">
                    <h2>💉 Rekap IADP (Infeksi Aliran Darah Primer)</h2>
                    <div class="filter">
                        <label for="filterIADP">Filter Tahun:</label>
                        <select id="filterIADP" onchange="filterTable('IADP')">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>

                    <div class="table-container">
                        <button class="btn-export" onclick="exportPDF('IADP')">

                            📄 Simpan PDF
                        </button>

                        <table id="tableIADP">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
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

    <!-- ✅ Tambahkan pustaka PDF di luar script utama -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-nav button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector(`.tab-nav button[onclick="showTab('${tabId}')"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        function kembaliDashboard() {
            window.location.href = "/dashboard.php";
        }

        // === FILTER TAHUN ===
        function filterTable(jenis) {
            const select = document.getElementById("filter" + jenis);
            const tahun = select.value;
            const table = document.getElementById("table" + jenis);
            const rows = table.querySelectorAll("tbody tr");

            rows.forEach(row => {
                const cellTahun = row.cells[0].textContent;
                row.style.display = (tahun === "" || cellTahun === tahun) ? "" : "none";
            });
        }

        // === UPDATE DROPDOWN TAHUN OTOMATIS ===
        function updateYearOptions(jenis) {
            const table = document.getElementById("table" + jenis);
            const select = document.getElementById("filter" + jenis);
            const years = new Set();

            table.querySelectorAll("tbody tr").forEach(row => {
                const year = row.cells[0].textContent;
                if (year) years.add(year);
            });

            select.innerHTML = '<option value="">Semua Tahun</option>';
            years.forEach(y => {
                const opt = document.createElement("option");
                opt.value = y;
                opt.textContent = y;
                select.appendChild(opt);
            });
        }

        // === EKSPOR PDF ===
        function exportPDF(jenis) {
            const table = document.getElementById("table" + jenis);
            const title = document.querySelector(`#${jenis} h2`).textContent;
            const filter = document.getElementById("filter" + jenis)?.value || "";
            const tahunInfo = filter ? `Tahun ${filter}` : "Semua Tahun";

            const laporan = document.createElement("div");
            laporan.style.fontFamily = "Poppins, sans-serif";
            laporan.style.padding = "20px";
            laporan.style.transform = "scale(0.93)";
            laporan.style.transformOrigin = "top center";

            laporan.style.color = "#1e293b";
            laporan.innerHTML = `
  
  
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:5px;">
      <img src="https://portalppi.my.id/surveilance/assets/Primaya.png" alt="Logo Primaya" style="height:42px;margin-top:2px;">
      <div style="flex-grow:1;text-align:center;line-height:1.2;">
               <h2 style="color:#1a2a80;margin:0;font-size:16px;border:none;">${title}</h2>

        <h4 style="margin:3px 0 2px 0;color:#2563eb;font-size:13px;">${tahunInfo}</h4>
      </div>
    </div>
    <p style="text-align:right;font-size:11px;color:#555;margin:0;">
      Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')}
    </p>

    
    <table border="1" cellspacing="0" cellpadding="6" 
      style="width:100%;border-collapse:collapse;font-size:13px;text-align:center;">
      <thead style="background:#2563eb;color:white;">
        <tr>
          <th>Tahun</th>
          <th>Bulan</th>
          <th>Numerator</th>
          <th>Denominator</th>
          <th>Hasil</th>
          <th>Satuan</th>
        </tr>
      </thead>
      <tbody>
        ${Array.from(table.querySelectorAll("tbody tr"))
                    .filter(row => row.style.display !== "none")
                    .map(row => `
            <tr>
              ${Array.from(row.cells).slice(0, 6)
                            .map(cell => `<td>${cell.textContent}</td>`).join("")}
            </tr>
          `).join("")}
      </tbody>
    </table>
    <br><br>
    <p style="font-size:13px;text-align:left;">Catatan: Data ini dihasilkan otomatis oleh sistem dashboard surveilans infeksi rumah sakit PPI PHBW.</p>
    <p style="font-size:13px;text-align:center;margin-top:40px;">
      Mengetahui,<br><br><br><br><br><strong>Ketua Komite PPI</strong>
    </p>
  `;

            const opt = {
                margin: 0.5,
                filename: title.replaceAll(" ", "_") + "_" + (filter || "SemuaTahun") + ".pdf",
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

        // === SIMPAN DATA + UPDATE DROPDOWN ===
        function simpanData() {
            const tahun = document.getElementById("tahun").value;
            const bulan = document.getElementById("bulan").value;
            const jenis = document.getElementById("jenis").value;
            const num = parseFloat(document.getElementById("num").value);
            const denum = parseFloat(document.getElementById("denum").value);
            const tipe = document.getElementById("tipeHasil").value;
            const hasilBox = document.getElementById("hasil");

            if (!tahun || !bulan || !jenis || !num || !denum) {
                alert("⚠️ Lengkapi semua data!");
                return;
            }

            const hasil = tipe === "persentase" ? (num / denum) * 100 : (num / denum) * 1000;
            const satuan = tipe === "persentase" ? "%" : "‰";
            hasilBox.textContent = `Hasil: ${hasil.toFixed(2)} ${satuan}`;

            const formData = new FormData();
            formData.append("tahun", tahun);
            formData.append("bulan", bulan);
            formData.append("jenis", jenis);
            formData.append("numerator", num);
            formData.append("denominator", denum);
            formData.append("hasil", hasil.toFixed(2));
            formData.append("satuan", satuan);

            fetch("save_data.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(res => {
                    if (res === "success") {
                        alert(`✅ Data ${jenis} berhasil disimpan!`);
                        loadTable(jenis);
                        document.getElementById("formSurveilans").reset();
                        hasilBox.textContent = "Hasil: -";
                    } else {
                        alert("❌ Gagal menyimpan data!");
                    }
                });
        }

        function loadTable(jenis) {
            const tbody = document.querySelector(`#table${jenis} tbody`);
            tbody.innerHTML = "";

            fetch(`load_data.php?jenis=${jenis}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(row => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
          <td>${row.tahun}</td>
          <td>${row.bulan}</td>
          <td>${row.numerator}</td>
          <td>${row.denominator}</td>
          <td>${row.hasil}</td>
          <td>${row.satuan}</td>
          <td><button class="delete-btn" onclick="deleteData(${row.id}, '${jenis}')">🗑️ Hapus</button></td>
        `;
                        tbody.appendChild(tr);
                    });
                    updateYearOptions(jenis);
                });
        }

        function deleteData(id, jenis) {
            if (!confirm("Yakin ingin menghapus data ini?")) return;
            const fd = new FormData();
            fd.append("id", id);
            fetch("delete_data.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.text())
                .then(res => {
                    if (res === "deleted") {
                        alert("🗑️ Data berhasil dihapus!");
                        loadTable(jenis);
                    } else {
                        alert("❌ Gagal menghapus data!");
                    }
                });
        }

        // Auto load data saat buka halaman
        ["ISK", "IDO", "VAP", "IADP"].forEach(loadTable);
    </script>

</body>

</html>