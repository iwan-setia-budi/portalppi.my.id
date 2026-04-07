<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Aplikasi PPI - Grafik Kepatuhan Cuci Tangan</title>
    <style>
        :root {
            --navy: #1A2A80;
            --indigo: #3B38A0;
            --blue-soft: #7A85C1;
            --lavender: #B2B0E8;
            --card: #ffffff;
            --text: #1e1e2f;
            --muted: #6b7280;
        }

        body {
            font-family: 'Poppins', system-ui, sans-serif;
            background: var(--lavender);
            color: var(--text);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* === HEADER === */
        header.main-header {
            background: var(--navy);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 28px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        header.main-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        header.main-header nav {
            display: flex;
            gap: 14px;
        }

        header.main-header button {
            background: var(--blue-soft);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.3s;
        }

        header.main-header button:hover {
            background: var(--indigo);
            transform: translateY(-1px);
        }

        /* === LAYOUT === */
        .container {
            display: flex;
            flex: 1;
        }

        /* === SIDEBAR === */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, var(--indigo), var(--blue-soft));
            padding: 20px 14px;
            color: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            margin: 10px 0;
        }

        .sidebar a {
            text-decoration: none;
            color: #f1f5f9;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.45);
            color: white;
            font-weight: 600;
        }

        /* === CONTENT AREA === */
        #content {
            flex: 1;
            padding: 30px;
            background: linear-gradient(180deg, #ffffff, var(--lavender));
        }

        /* === CARD === */
        .card {
            background: var(--card);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            max-width: 950px;
            margin: auto;
        }

        .card header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        input#chartTitle {
            font-size: 20px;
            font-weight: 600;
            color: var(--indigo);
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
        }

        .chart-wrap {
            position: relative;
            margin-top: 20px;
        }

        canvas {
            max-width: 100%;
            height: auto
        }

        .controls {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 14px
        }

        .controls input[type='number'],
        select {
            width: 80px;
            padding: 6px;
            border-radius: 8px;
            border: 1px solid #e6eef8
        }

        .btn {
            background: var(--indigo);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn:hover {
            background: var(--navy);
        }

        .note {
            margin-top: 12px;
            font-size: 13px;
            color: var(--muted)
        }

        .section {
            margin-top: 20px;
        }

        .section h3 {
            margin-bottom: 6px;
            color: var(--indigo);
        }

        textarea {
            width: 100%;
            border: 1px solid #e0e7ff;
            border-radius: 8px;
            padding: 10px;
            resize: vertical;
            font-family: Poppins, sans-serif;
        }

        #analisa {
            min-height: 90px;
        }

        #rtl {
            min-height: 160px;
        }

        #pdfBtn {
            margin-top: 16px;
            background: var(--blue-soft);
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            font-size: 13px;
            text-align: center;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #f1f5ff;
            color: #0f172a;
        }

        .hidden {
            display: none;
        }

        /* === FOOTER === */
        footer.main-footer {
            background: var(--navy);
            color: white;
            text-align: center;
            padding: 14px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
    </style>
</head>

<body>

    <header class="main-header">
        <h1>🧴 Aplikasi Audit & Supervisi PPI</h1>
        <nav>
            <button
                onclick="alert('Aplikasi ini digunakan untuk monitoring kepatuhan PPI termasuk audit, supervisi, dan cuci tangan.')">Tentang
                Program</button>
            <button onclick="logout()">Logout</button>
        </nav>
    </header>

    <div class="container">
        <nav class="sidebar">
            <ul>
                <li><a href="index.html" class="active">🏠 Home</a></li>
                <li><a href="#">📋 Audit</a></li>
                <li><a href="#">🩺 Supervisi</a></li>
                <li><a href="#">✋ Cuci Tangan</a></li>
                <li><a href="rekap.html">📊 Rekap manual</a></li>
            </ul>
        </nav>

        <div id="content">
            <div class="card" id="reportCard">
                <header>
                    <div style="flex:1">
                        <input id="chartTitle" value="Grafik Kepatuhan Cuci Tangan" />
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:700;color:#0b1220">Target</div>
                        <input id="targetInput" type="number" min="0" max="100" value="90"
                            style="width:60px;text-align:center;padding:4px;border-radius:8px;border:1px solid #e6eef8;">%
                    </div>
                </header>

                <div class="chart-wrap">
                    <canvas id="hygieneChart" width="800" height="360"></canvas>
                </div>

                <div class="controls" id="inputArea">
                    <label style="font-size:13px;color:var(--muted)">Bulan:</label>
                    <select id="monthSelect">
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
                    Num: <input id="numInput" type="number" min="0" placeholder="Num">
                    Denum: <input id="denumInput" type="number" min="0" placeholder="Denum">
                    <button id="addMonthBtn" class="btn">Tambah / Update</button>
                    <button id="resetBtn" class="btn" style="background:#c0392b">Reset</button>
                </div>

                <p class="note">Masukkan <b>Num</b> dan <b>Denum</b>. Persentase dihitung otomatis dan grafik
                    diperbarui. Tabel muncul bila ada data.</p>

                <div id="dataTableWrap" class="hidden">
                    <table id="dataTable">
                        <thead>
                            <tr id="headRow">
                                <th>Bulan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="numRow">
                                <td>Num</td>
                            </tr>
                            <tr id="denumRow">
                                <td>Denum</td>
                            </tr>
                            <tr id="persenRow">
                                <td>Persentase</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="section">
                    <h3>Analisa</h3>
                    <textarea id="analisa" placeholder="Tuliskan analisa hasil kepatuhan di sini..."></textarea>
                </div>

                <div class="section">
                    <h3>Rencana Tindak Lanjut</h3>
                    <textarea id="rtl" placeholder="Tuliskan rencana tindak lanjut di sini..."></textarea>
                </div>

                <button id="pdfBtn" class="btn">📄 Simpan ke PDF</button>
            </div>
        </div>
    </div>

    <footer class="main-footer">© 2025 Tim PPI - Rumah Sakit Primaya Bhaktiwara</footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function logout() {
            if (confirm('Yakin ingin logout?')) {
                alert('Anda telah logout.');
            }
        }

        // ========== SCRIPT GRAFIK ==========
        const STORAGE_KEY = 'ppi_numden_data';
        const TITLE_KEY = 'ppi_chart_title';
        const TARGET_KEY = 'ppi_chart_target';
        const ANALISA_KEY = 'ppi_analisa';
        const RTL_KEY = 'ppi_rtl';
        let saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        let targetVal = Number(localStorage.getItem(TARGET_KEY) || 90);
        document.getElementById('targetInput').value = targetVal;
        document.getElementById('chartTitle').value = localStorage.getItem(TITLE_KEY) || 'Grafik Kepatuhan Cuci Tangan';
        document.getElementById('analisa').value = localStorage.getItem(ANALISA_KEY) || '';
        document.getElementById('rtl').value = localStorage.getItem(RTL_KEY) || '';

        document.getElementById('chartTitle').oninput = e => localStorage.setItem(TITLE_KEY, e.target.value);
        document.getElementById('analisa').oninput = e => localStorage.setItem(ANALISA_KEY, e.target.value);
        document.getElementById('rtl').oninput = e => localStorage.setItem(RTL_KEY, e.target.value);
        document.getElementById('targetInput').oninput = e => {
            targetVal = Number(e.target.value);
            localStorage.setItem(TARGET_KEY, targetVal);
            updateChart();
        };

        function colorFor(v) { if (v >= targetVal) return '#16a34a'; if (v >= targetVal - 5) return '#f59e0b'; return '#ef4444'; }
        const ctx = document.getElementById('hygieneChart').getContext('2d');
        const barLabel = {
            id: 'barLabel', afterDatasetsDraw(chart) {
                const { ctx } = chart; ctx.save();
                chart.data.datasets[0].data.forEach((val, i) => { const bar = chart.getDatasetMeta(0).data[i]; ctx.fillStyle = '#0b1220'; ctx.font = '600 12px Poppins'; ctx.textAlign = 'center'; ctx.fillText(val + '%', bar.x, bar.y - 6); }); ctx.restore();
            }
        };
        const chart = new Chart(ctx, {
            type: 'bar', data: {
                labels: [], datasets: [
                    { label: 'Kepatuhan (%)', data: [], backgroundColor: [], borderRadius: 8, barThickness: 44 },
                    { type: 'line', label: 'Target', data: [], borderColor: '#3B38A0', borderDash: [6, 6], pointRadius: 0, borderWidth: 2 }
                ]
            }, options: { scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: true } } }, plugins: [barLabel]
        });

        function updateTable() {
            const wrap = document.getElementById('dataTableWrap');
            const h = document.getElementById('headRow'), n = document.getElementById('numRow'), d = document.getElementById('denumRow'), p = document.getElementById('persenRow');
            h.innerHTML = '<th>Bulan</th>'; n.innerHTML = '<td>Num</td>'; d.innerHTML = '<td>Denum</td>'; p.innerHTML = '<td>Persentase</td>';
            const order = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            let hasData = false;
            order.forEach(m => {
                if (saved[m]) {
                    hasData = true;
                    h.innerHTML += `<th>${m}</th>`;
                    n.innerHTML += `<td>${saved[m].num || ''}</td>`;
                    d.innerHTML += `<td>${saved[m].denum || ''}</td>`;
                    p.innerHTML += `<td>${saved[m].persen || ''}%</td>`;
                }
            });
            wrap.classList.toggle('hidden', !hasData);
        }

        function updateChart() {
            const order = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const labels = order.filter(m => saved[m]);
            const data = labels.map(m => saved[m].persen || 0);
            chart.data.labels = labels;
            chart.data.datasets[0].data = data;
            chart.data.datasets[0].backgroundColor = data.map(v => colorFor(v));
            chart.data.datasets[1].data = labels.map(() => targetVal);
            chart.update();
            updateTable();
        }
        updateChart();

        document.getElementById('addMonthBtn').onclick = () => {
            const m = document.getElementById('monthSelect').value;
            const num = Number(document.getElementById('numInput').value);
            const den = Number(document.getElementById('denumInput').value);
            if (isNaN(num) || isNaN(den) || den <= 0) return alert('Masukkan Num dan Denum dengan benar!');
            const persen = Math.round((num / den) * 100);
            saved[m] = { num, denum: den, persen };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(saved));
            updateChart();
            document.getElementById('numInput').value = '';
            document.getElementById('denumInput').value = '';
        };
        document.getElementById('resetBtn').onclick = () => {
            if (!confirm('Hapus semua data?')) return;
            [STORAGE_KEY, TITLE_KEY, TARGET_KEY, ANALISA_KEY, RTL_KEY].forEach(k => localStorage.removeItem(k));
            location.reload();
        };
    </script>
</body>

</html>