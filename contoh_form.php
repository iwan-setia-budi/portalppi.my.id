<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Surveilans ISK, IDO, VAP & IADP | PPI PHBW</title>
  <style>
    :root {
      --primary: #1a2a80;
      --secondary: #3b49df;
      --bg: #f8f9fc;
      --card-bg: #ffffff;
      --border: #dce0f0;
    }

    body {
      font-family: "Segoe UI", sans-serif;
      background-color: var(--bg);
      color: #222;
      margin: 0;
      padding: 0;
    }

    header {
      background-color: var(--primary);
      color: white;
      text-align: center;
      padding: 15px 0;
      font-size: 1.4em;
      font-weight: bold;
      letter-spacing: 0.5px;
    }

    main {
      max-width: 1000px;
      margin: 30px auto;
      background-color: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      padding: 20px 30px;
    }

    h2 {
      color: var(--primary);
      border-bottom: 2px solid var(--secondary);
      padding-bottom: 5px;
      margin-top: 30px;
    }

    label {
      font-weight: 600;
      display: block;
      margin-top: 12px;
      margin-bottom: 4px;
    }

    input, select, textarea {
      width: 100%;
      padding: 8px;
      border-radius: 6px;
      border: 1px solid var(--border);
      box-sizing: border-box;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      border: 1px solid var(--border);
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: var(--primary);
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f2f4ff;
    }

    button {
      margin-top: 15px;
      background-color: var(--secondary);
      color: white;
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    button:hover {
      background-color: #2f3cbf;
    }

    footer {
      text-align: center;
      padding: 20px;
      color: gray;
      font-size: 0.9em;
    }

    .section {
      margin-bottom: 40px;
    }
  </style>
</head>
<body>
  <header>📊 Form Surveilans Infeksi RS (ISK | IDO | VAP | IADP)</header>

  <main>
    <!-- Identitas Pasien -->
    <div class="section">
      <h2>Identitas Pasien</h2>
      <label>Nama Pasien</label>
      <input type="text" placeholder="Nama lengkap pasien">

      <label>No. Rekam Medis</label>
      <input type="text" placeholder="Nomor RM">

      <label>Ruang Rawat</label>
      <input type="text" placeholder="Nama ruang rawat pasien">

      <label>Tanggal Surveilans</label>
      <input type="date">
    </div>

    <!-- Surveilans ISK -->
    <div class="section">
      <h2>🧫 Surveilans Infeksi Saluran Kemih (ISK)</h2>
      <table>
        <tr>
          <th>Kriteria</th>
          <th>Ya</th>
          <th>Tidak</th>
          <th>Keterangan</th>
        </tr>
        <tr>
          <td>Pasien dengan pemasangan kateter urin</td>
          <td><input type="radio" name="isk1" value="ya"></td>
          <td><input type="radio" name="isk1" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
        <tr>
          <td>Tanda infeksi (demam, nyeri suprapubik, urin keruh)</td>
          <td><input type="radio" name="isk2" value="ya"></td>
          <td><input type="radio" name="isk2" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
      </table>
    </div>

    <!-- Surveilans IDO -->
    <div class="section">
      <h2>🩹 Surveilans Infeksi Daerah Operasi (IDO)</h2>
      <table>
        <tr>
          <th>Kriteria</th>
          <th>Ya</th>
          <th>Tidak</th>
          <th>Keterangan</th>
        </tr>
        <tr>
          <td>Luka operasi kemerahan atau bernanah</td>
          <td><input type="radio" name="ido1" value="ya"></td>
          <td><input type="radio" name="ido1" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
        <tr>
          <td>Terjadi infeksi dalam 30 hari pasca operasi</td>
          <td><input type="radio" name="ido2" value="ya"></td>
          <td><input type="radio" name="ido2" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
      </table>
    </div>

    <!-- Surveilans VAP -->
    <div class="section">
      <h2>🫁 Surveilans Ventilator Associated Pneumonia (VAP)</h2>
      <table>
        <tr>
          <th>Kriteria</th>
          <th>Ya</th>
          <th>Tidak</th>
          <th>Keterangan</th>
        </tr>
        <tr>
          <td>Pasien menggunakan ventilator ≥ 48 jam</td>
          <td><input type="radio" name="vap1" value="ya"></td>
          <td><input type="radio" name="vap1" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
        <tr>
          <td>Tanda pneumonia (demam, sputum purulen, infiltrat baru)</td>
          <td><input type="radio" name="vap2" value="ya"></td>
          <td><input type="radio" name="vap2" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
      </table>
    </div>

    <!-- Surveilans IADP -->
    <div class="section">
      <h2>💉 Surveilans Infeksi Aliran Darah Primer (IADP)</h2>
      <table>
        <tr>
          <th>Kriteria</th>
          <th>Ya</th>
          <th>Tidak</th>
          <th>Keterangan</th>
        </tr>
        <tr>
          <td>Pasien menggunakan kateter vena sentral</td>
          <td><input type="radio" name="iadp1" value="ya"></td>
          <td><input type="radio" name="iadp1" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
        <tr>
          <td>Tanda infeksi (demam, menggigil, hasil kultur positif)</td>
          <td><input type="radio" name="iadp2" value="ya"></td>
          <td><input type="radio" name="iadp2" value="tidak"></td>
          <td><input type="text" placeholder="Keterangan tambahan"></td>
        </tr>
      </table>
    </div>

    <!-- Tombol Simpan -->
    <div style="text-align: right;">
      <button type="submit">💾 Simpan Data Surveilans</button>
    </div>
  </main>

  <footer>
    © 2025 PPI PHBW — Form Surveilans Infeksi Rumah Sakit
  </footer>
</body>
</html>
