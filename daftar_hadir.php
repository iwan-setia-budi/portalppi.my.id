<?php include_once 'header.php'; ?>
<?php include_once 'sidebar.php'; ?>


<style>
  body {
    font-family: "Times New Roman", serif;
    background: #f4f6f9;
    margin: 0;
    padding: 20px;
  }

  .tabs {
    display: flex;
    background: #ccc;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 15px;
  }

  .tab-button {
    flex: 1;
    text-align: center;
    padding: 10px;
    background: #ccc;
    cursor: pointer;
    font-weight: bold;
    border: none;
  }

  .tab-button.active {
    background: #005F80;
    color: white;
  }

  .tab-content {
    display: none;
    background: white;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 20px 30px;
  }

  .tab-content.active {
    display: block;
  }

  .container {
    max-width: 900px;
    margin: auto;
  }

  h2 {
    text-align: center;
    font-size: 16pt;
    font-weight: bold;
    margin: 0;
  }

  h3 {
    text-align: center;
    font-size: 12pt;
    margin-top: 4px;
    font-weight: normal;
  }

  .header-info {
    margin-top: 10px;
    margin-bottom: 12px;
    font-size: 12pt;
  }

  .header-info div {
    margin: 3px 0;
  }

  .header-info label {
    display: inline-block;
    width: 110px;
    font-weight: bold;
  }

  input.header-input {
    border: none;
    border-bottom: 1px dotted #000;
    width: 250px;
    font-size: 12pt;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11pt;
  }

  th, td {
    border: 1px solid #000;
    padding: 6px;
    text-align: center;
  }

  th {
    background: #005F80;
    color: white;
  }

  button {
    background: #005F80;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 4px;
    cursor: pointer;
    margin: 5px;
    font-size: 10pt;
  }

  button:hover { background: #00405a; }

  .delete-btn { background: #c82333; }
  .delete-btn:hover { background: #9a1b26; }

  .footer {
    text-align: right;
    font-size: 10pt;
    margin-top: 10px;
  }

  canvas {
    border: 1px solid #000;
    width: 100%;
    height: 100px;
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
  }

  .modal-content {
    background: #fff;
    margin: 8% auto;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
  }
</style>



<div class="container">
  <div class="tabs">
    <button class="tab-button active" onclick="openTab('formTab')">📋 Form Daftar Hadir</button>
    <button class="tab-button" onclick="openTab('rekapTab')">📑 Rekap Daftar Hadir</button>
  </div>

  <!-- === TAB FORM === -->
  <div id="formTab" class="tab-content active">
    <h2>DAFTAR HADIR</h2>
    <h3>Pelatihan PPI</h3>

    <div class="header-info">
      <div><label>Pertemuan</label>: <input type="text" id="pertemuan" class="header-input"></div>
      <div><label>Hari/Tanggal</label>: <input type="text" id="tanggal" class="header-input"></div>
      <div><label>Waktu</label>: <input type="text" id="waktu" class="header-input"></div>
    </div>

    <div style="text-align:right;">
      <button id="openModal">+ Tambah Data</button>
      <button id="downloadPDF">Unduh PDF</button>
      <button onclick="simpanRekap()">Simpan ke Rekap</button>
    </div>

    <table id="absensiTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>Jabatan</th>
          <th>Tanda Tangan</th>
          <th class="aksi-col">Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <div class="footer">Form/PHG/DIKLAT-001/Rev.00</div>
  </div>

  <!-- === TAB REKAP === -->
  <div id="rekapTab" class="tab-content">
    <h2>REKAP DAFTAR HADIR</h2>
    <table id="rekapTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Hari/Tanggal</th>
          <th>Pertemuan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- === MODAL === -->
<div id="modalForm" class="modal">
  <div class="modal-content">
    <span class="close" onclick="modal.style.display='none'" style="float:right;cursor:pointer;">&times;</span>
    <h3 style="text-align:center;">Tanda Tangan Peserta</h3>
    <label>Nama</label>
    <input type="text" id="nama">
    <label>Jabatan</label>
    <input type="text" id="jabatan">
    <label>Tanda Tangan</label>
    <canvas id="signaturePad"></canvas>
    <div style="text-align:center;margin-top:10px;">
      <button id="clearSign" class="delete-btn">Hapus</button>
      <button id="saveData">Simpan</button>
    </div>
  </div>
</div>

<!-- === SCRIPT === -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
  function openTab(tabName) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelector(`[onclick="openTab('${tabName}')"]`).classList.add('active');
    document.getElementById(tabName).classList.add('active');
  }

  // Modal tanda tangan
  const modal = document.getElementById("modalForm");
  const openModal = document.getElementById("openModal");
  const canvas = document.getElementById("signaturePad");
  const ctx = canvas.getContext("2d");
  let drawing = false;

  openModal.onclick = () => modal.style.display = "block";
  canvas.addEventListener("mousedown", () => drawing = true);
  canvas.addEventListener("mouseup", () => { drawing = false; ctx.beginPath(); });
  canvas.addEventListener("mousemove", e => {
    if (!drawing) return;
    const rect = canvas.getBoundingClientRect();
    ctx.lineWidth = 2;
    ctx.lineCap = "round";
    ctx.strokeStyle = "black";
    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
  });
  document.getElementById("clearSign").onclick = () => ctx.clearRect(0, 0, canvas.width, canvas.height);

  document.getElementById("saveData").onclick = () => {
    const nama = document.getElementById("nama").value;
    const jabatan = document.getElementById("jabatan").value;
    const ttd = canvas.toDataURL();
    if (!nama || !jabatan) return alert("Nama dan Jabatan wajib diisi!");

    const table = document.getElementById("absensiTable").querySelector("tbody");
    const row = table.insertRow();
    const no = row.insertCell(0);
    const nm = row.insertCell(1);
    const jb = row.insertCell(2);
    const sign = row.insertCell(3);
    const aksi = row.insertCell(4);
    no.innerHTML = table.rows.length;
    nm.innerHTML = nama;
    jb.innerHTML = jabatan;
    sign.innerHTML = `<img src='${ttd}' width='100' height='50'>`;
    aksi.innerHTML = `<button class='delete-btn' onclick='hapusBaris(this)'>Hapus</button>`;
    document.getElementById("nama").value = "";
    document.getElementById("jabatan").value = "";
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    modal.style.display = "none";
  };

  function hapusBaris(btn) {
    btn.closest("tr").remove();
  }

  // Simpan ke Rekap
  function simpanRekap() {
    const pertemuan = document.getElementById("pertemuan").value;
    const tanggal = document.getElementById("tanggal").value;
    const waktu = document.getElementById("waktu").value;
    if (!pertemuan || !tanggal || !waktu) return alert("Lengkapi kolom dulu!");

    const data = JSON.parse(localStorage.getItem("rekapData")) || [];
    data.push({ pertemuan, tanggal, waktu });
    localStorage.setItem("rekapData", JSON.stringify(data));
    tampilRekap();
    alert("Data disimpan ke rekap!");
  }

  function tampilRekap() {
    const tbody = document.querySelector("#rekapTable tbody");
    tbody.innerHTML = "";
    const data = JSON.parse(localStorage.getItem("rekapData")) || [];
    data.forEach((d, i) => {
      tbody.innerHTML += `
        <tr>
          <td>${i + 1}</td>
          <td>${d.tanggal}</td>
          <td>${d.pertemuan}</td>
          <td>
            <button onclick="lihatRekap(${i})">Lihat</button>
            <button class="delete-btn" onclick="hapusRekap(${i})">Hapus</button>
          </td>
        </tr>`;
    });
  }

  function lihatRekap(index) {
    const data = JSON.parse(localStorage.getItem("rekapData")) || [];
    const item = data[index];
    openTab("formTab");
    document.getElementById("pertemuan").value = item.pertemuan;
    document.getElementById("tanggal").value = item.tanggal;
    document.getElementById("waktu").value = item.waktu;
  }

  function hapusRekap(index) {
    const data = JSON.parse(localStorage.getItem("rekapData")) || [];
    data.splice(index, 1);
    localStorage.setItem("rekapData", JSON.stringify(data));
    tampilRekap();
  }

  tampilRekap();

  // ==== PDF GENERATOR ====
  const { jsPDF } = window.jspdf;
  document.getElementById("downloadPDF").onclick = () => {
    const aksiCols = document.querySelectorAll(".aksi-col");
    aksiCols.forEach(c => c.style.display = "none");

    const doc = new jsPDF("p", "mm", "a4");
    const margin = 15;
    let y = 20;

    doc.setFont("times", "bold");
    doc.setFontSize(14);
    doc.text("DAFTAR HADIR", 105, y, { align: "center" });

    y += 8;
    doc.setFontSize(11);
    doc.setFont("times", "normal");

    const pertemuan = document.getElementById("pertemuan").value;
    const tanggal = document.getElementById("tanggal").value;
    const waktu = document.getElementById("waktu").value;

    doc.autoTable({
      startY: y,
      theme: 'plain',
      styles: { font: 'times', fontSize: 11, cellPadding: 1 },
      columnStyles: { 0: { cellWidth: 35, fontStyle: 'bold' }, 1: { cellWidth: 4 }, 2: { cellWidth: 120 } },
      body: [
        ['Pertemuan', ':', pertemuan],
        ['Hari/Tanggal', ':', tanggal],
        ['Waktu', ':', waktu]
      ],
      margin: { left: margin }
    });

    y = doc.lastAutoTable.finalY + 5;

    const headers = ["No", "Nama", "Jabatan", "Tanda Tangan"];
    const body = [];
    const rows = document.querySelectorAll("#absensiTable tbody tr");
    rows.forEach((r, i) => {
      const nama = r.cells[1].textContent;
      const jabatan = r.cells[2].textContent;
      const img = r.cells[3].querySelector("img");
      body.push([i + 1, nama, jabatan, img ? img.src : ""]);
    });

    doc.autoTable({
      startY: y + 3,
      head: [headers],
      body: body.map(r => [r[0], r[1], r[2], ""]),
      theme: "grid",
      styles: { font: "times", fontSize: 10, cellPadding: 3, lineColor: [0, 0, 0], textColor: [0, 0, 0] },
      headStyles: { fillColor: [0, 95, 128], textColor: [255, 255, 255], fontStyle: "bold" },
      columnStyles: { 0: { cellWidth: 10 }, 1: { cellWidth: 80 }, 2: { cellWidth: 40 }, 3: { cellWidth: 50 } },
      didDrawCell: function(data) {
        if (data.column.index === 3 && data.row.index < body.length) {
          const sig = body[data.row.index][3];
          if (sig) doc.addImage(sig, "PNG", data.cell.x + 10, data.cell.y + 1, 30, 12);
        }
      },
      margin: { left: margin }
    });

    doc.setFontSize(9);
    doc.text("Form/PHG/DIKLAT-001/Rev.00", 200 - 12, 285, { align: "right" });
    doc.save("Daftar_Hadir_PPI.pdf");
    aksiCols.forEach(c => c.style.display = "");
  };
</script>



<?php include_once 'footer.php'; ?>

