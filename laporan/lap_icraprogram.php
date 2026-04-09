<?php
require_once __DIR__ . '/../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../koneksi.php';
include "../cek_akses.php";

if (!isset($_SESSION['username'])) {
  header("Location: " . base_url('login.php'));
  exit();
}
?>
<?php $pageTitle = "ICRA PROGRAM"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Laporan ICRA Tahunan | PPI PHBW</title>
<link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
<style>
:root {
  --navy: #1a237e;
  --blue: #3b49df;
  --sky: #eef1ff;
  --red: #d32f2f;
  --green: #43a047;
  --border: #dce0f0;
  --card: #ffffff;
}

/* CONTAINER */
.container-lap { padding: 30px 40px; }

/* PAGE HERO */
.page-hero {
  background: linear-gradient(135deg, #1e3a8a, #2563eb);
  padding: 28px 32px;
  border-radius: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  box-shadow: 0 20px 50px rgba(37, 99, 235, .25);
}
.page-hero h1 { font-size: 22px; font-weight: 600; color: white; margin: 0; }
.page-hero small { display: block; opacity: .8; font-size: 13px; margin-top: 4px; color: white; }
.hero-btn {
  background: white;
  color: #1e3a8a;
  border: none;
  padding: 10px 18px;
  font-weight: 600;
  border-radius: 999px;
  cursor: pointer;
  transition: .2s;
}
.hero-btn:hover { transform: translateY(-3px); }

/* LAP CONTENT */
.lap-content {
  background: white;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.07);
}

/* FORM */
h2 { color: var(--navy); border-bottom: 3px solid var(--blue); padding-bottom: 6px; margin-top: 0; }
.premium-form {
  margin-top: 14px;
  padding: 16px;
  border-radius: 14px;
  border: 1px solid #dbe5f5;
  background: linear-gradient(180deg, #f8fbff, #f1f6ff);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.8), 0 10px 24px rgba(15,23,42,.06);
}
.form-section {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 14px;
}
.form-group { min-width: 0; }
.form-group.full { grid-column: 1 / -1; }
.form-group label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .03em;
  color: #1e3a8a;
  margin-bottom: 6px;
}
input, textarea, select {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--border);
  border-radius: 10px;
  font-size: 0.95em;
  margin-bottom: 10px;
  box-sizing: border-box;
}
button { font-weight: 600; cursor: pointer; color: white; }
button.save { background: var(--blue); border: none; padding: 8px 14px; border-radius: 8px; }
button.save:hover { background: #283593; }
button.clear { background: var(--red); border: none; padding: 8px 14px; border-radius: 8px; }
button.clear:hover { background: #b71c1c; }
.form-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 14px 0 8px;
}
.search-wrap {
  margin-top: 8px;
}
.search-wrap label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #1e3a8a;
  margin-bottom: 6px;
}

/* TABLE */
.table-wrapper { margin-top: 15px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.95em; }
th, td { padding: 10px 8px; border-bottom: 1px solid var(--border); text-align: left; }
th { background: var(--sky); color: var(--navy); }
tr:hover td { background: #f8f9ff; }
td.actions { text-align: center; white-space: nowrap; }
td.actions button { padding: 6px 10px; font-size: 0.85em; border-radius: 8px; margin: 0 3px; }
.view { background: var(--blue); }
.download { background: var(--green); }
.delete { background: var(--red); }
input#search { width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); margin: 10px 0; }

/* SCROLL HINT */
.scroll-hint {
  display: none;
  font-size: 11px;
  color: #94a3b8;
  text-align: right;
  margin: 4px 0;
  letter-spacing: 0.02em;
}

/* FIX CSS GRID OVERFLOW */
main { min-width: 0; overflow-x: hidden; }

/* RESPONSIVE MOBILE */
@media (max-width: 900px) {
  .container-lap { padding: 12px; overflow-x: hidden; width: 100%; max-width: 100%; box-sizing: border-box; }
  .page-hero { flex-direction: column; align-items: flex-start; gap: 14px; padding: 18px 20px; border-radius: 14px; }
  .page-hero h1 { font-size: 17px; }
  .page-hero small { font-size: 12px; }
  .hero-btn { align-self: stretch; text-align: center; padding: 10px; }
  .lap-content { padding: 14px; }
  .premium-form { padding: 12px; }
  .form-section { grid-template-columns: 1fr; gap: 10px; }
  .form-group.full { grid-column: auto; }
  .form-actions { flex-direction: column; }
  .form-actions button { width: 100%; }
  input, textarea, select { font-size: 16px !important; }
  .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .scroll-hint { display: block; }
  table { font-size: 0.82em; }
  th, td { padding: 8px 6px; }
}

/* MODAL */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.modal-overlay.active { display: flex; }
.modal-box {
  background: white;
  border-radius: 16px;
  padding: 24px;
  max-width: 560px;
  width: 100%;
  max-height: 88vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
  animation: modalIn .2s ease;
}
@keyframes modalIn { from { transform:scale(0.95);opacity:0; } to { transform:scale(1);opacity:1; } }
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  border-bottom: 2px solid var(--border);
  padding-bottom: 12px;
}
.modal-title { font-size: 16px; font-weight: 700; color: var(--navy); }
.modal-close {
  background: #f1f5f9;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 16px;
  cursor: pointer;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.modal-close:hover { background: #e2e8f0; }
.modal-field { margin-bottom: 12px; }
.modal-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #64748b;
  margin-bottom: 4px;
}
.modal-value {
  font-size: 14px;
  color: #1e293b;
  background: #f8fafc;
  border-radius: 8px;
  padding: 10px 12px;
  border: 1px solid var(--border);
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.6;
}
.modal-actions { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
.btn-open-file {
  background: var(--green);
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
}
.btn-open-file:hover { opacity: 0.85; }
.btn-close-modal {
  background: #e2e8f0;
  color: #334155;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
}
.btn-close-modal:hover { background: #cbd5e1; }
  /* ===== DARK MODE: PREMIUM ===== */
  body.dark-mode main {
    background:
      radial-gradient(circle at top, rgba(37, 99, 235, .12), transparent 38%),
      linear-gradient(180deg, #09111d, #0f1b2d 45%, #0d1728 100%);
  }
  body.dark-mode .container-lap,
  body.dark-mode .lap-content,
  body.dark-mode .premium-form,
  body.dark-mode .table-wrapper,
  body.dark-mode .modal-overlay .modal-box {
    background: linear-gradient(170deg, #16263b, #1b2d45);
    border: 1.5px solid rgba(59, 130, 246, .32);
    box-shadow: 0 14px 34px rgba(2, 6, 23, .36), inset 0 0 18px rgba(59, 130, 246, .08);
    color: #f8fafc;
  }
  body.dark-mode .page-hero {
    box-shadow: 0 20px 48px rgba(2, 6, 23, .45);
  }
  body.dark-mode h2,
  body.dark-mode .form-group label,
  body.dark-mode .search-wrap label,
  body.dark-mode .scroll-hint,
  body.dark-mode .modal-title,
  body.dark-mode .modal-label,
  body.dark-mode th,
  body.dark-mode td {
    color: #f8fafc;
  }
  body.dark-mode table,
  body.dark-mode thead,
  body.dark-mode tbody tr {
    background: #142238;
    color: #f8fafc;
  }
  body.dark-mode tbody tr:hover td {
    background: #1a2c46;
  }
  body.dark-mode th,
  body.dark-mode td {
    border-color: rgba(96, 165, 250, .2);
  }
  body.dark-mode th {
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    color: #eff6ff;
  }
  body.dark-mode input,
  body.dark-mode select,
  body.dark-mode textarea {
    background: #122035;
    color: #f8fafc;
    border: 1px solid rgba(59, 130, 246, .34);
  }
  body.dark-mode input::placeholder,
  body.dark-mode textarea::placeholder {
    color: rgba(248, 250, 252, .78);
  }
  body.dark-mode input[type="file"] {
    background: linear-gradient(180deg, #122035, #0f1d31) !important;
    color: #f8fafc;
    border: 1.5px dashed rgba(96, 165, 250, .38);
    box-shadow: inset 0 0 0 1px rgba(59, 130, 246, .08);
  }
  body.dark-mode input[type="file"]::file-selector-button,
  body.dark-mode input[type="file"]::-webkit-file-upload-button {
    background: linear-gradient(180deg, #ffffff, #dbeafe);
    color: #0f172a;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    margin-right: 10px;
    font-weight: 600;
    cursor: pointer;
  }
  body.dark-mode .modal-header {
    border-bottom-color: rgba(96, 165, 250, .25);
  }
  body.dark-mode .modal-value {
    background: #122035;
    color: #f8fafc;
    border-color: rgba(96, 165, 250, .2);
  }
  body.dark-mode .modal-close,
  body.dark-mode .btn-close-modal {
    background: #1e293b;
    color: #dbeafe;
    border: 1px solid rgba(96, 165, 250, .22);
  }
  body.dark-mode .modal-close:hover,
  body.dark-mode .btn-close-modal:hover {
    background: #273449;
  }
  body.dark-mode .hero-btn {
    background: linear-gradient(180deg, #ffffff, #dbeafe);
    color: #0f172a;
    border: none;
  }
</style>
</head>
<body>
<div class="layout">
  <?php include_once '../sidebar.php'; ?>
  <main>
    <?php include_once '../topbar.php'; ?>
    <div class="container-lap">

      <div class="page-hero">
        <div>
          <h1>Laporan ICRA Program</h1>
          <small>Infection Control Risk Assessment — Komite PPI</small>
        </div>
        <button class="hero-btn" onclick="goDashboard()">🏠 Dashboard</button>
      </div>

      <div class="lap-content">
        <h2>🧫 Input Laporan ICRA Tahunan</h2>

        <div class="premium-form">
          <div class="form-section">
            <div class="form-group">
              <label for="tahun">Tahun Laporan</label>
              <input type="number" id="tahun" placeholder="Contoh: 2025" min="2020" max="2100">
            </div>
            <div class="form-group">
              <label for="unit">Unit / Ruangan</label>
              <input type="text" id="unit" placeholder="Contoh: ICU / Rawat Inap">
            </div>
            <div class="form-group full">
              <label for="kegiatan">Jenis Kegiatan / Proyek</label>
              <input type="text" id="kegiatan" placeholder="Contoh: Renovasi ruang tindakan / pembangunan unit baru">
            </div>
            <div class="form-group full">
              <label for="risiko">Risiko Infeksi yang Diidentifikasi</label>
              <textarea id="risiko" placeholder="Contoh: debu, aerosol, air, kontak langsung"></textarea>
            </div>
            <div class="form-group full">
              <label for="mitigasi">Langkah Mitigasi / Pengendalian</label>
              <textarea id="mitigasi" placeholder="Contoh: isolasi area kerja, HEPA filter, cleaning tambahan"></textarea>
            </div>
            <div class="form-group">
              <label for="penanggung">Penanggung Jawab / PIC</label>
              <input type="text" id="penanggung" placeholder="Nama PIC / Unit Penanggung Jawab">
            </div>
            <div class="form-group">
              <label for="file">Lampiran Dokumen</label>
              <input type="file" id="file" accept=".pdf,.docx,.jpg,.png">
            </div>
          </div>

          <div class="form-actions">
          <button class="save" onclick="simpan()">💾 Simpan</button>
          <button class="clear" onclick="hapusSemua()">🧹 Hapus Semua</button>
          </div>
        </div>

        <div class="search-wrap">
          <label for="search">Pencarian Data</label>
          <input type="search" id="search" placeholder="🔍 Cari berdasarkan tahun, unit, atau kegiatan...">
        </div>
        <p class="scroll-hint">← geser tabel →</p>

        <div class="table-wrapper">
          <table id="tabelICRA">
            <thead>
              <tr>
                <th>No</th>
                <th>Tahun</th>
                <th>Unit / Ruangan</th>
                <th>Kegiatan</th>
                <th>Risiko</th>
                <th>Mitigasi</th>
                <th>Penanggung</th>
                <th style="text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div><!-- /.lap-content -->

    </div><!-- /.container-lap -->
  </main>
</div><!-- /.layout -->

<!-- MODAL LIHAT DOKUMEN -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Detail ICRA</span>
      <button class="modal-close" onclick="tutupModal()">✕</button>
    </div>
    <div id="modalBody"></div>
    <div class="modal-actions">
      <button class="btn-open-file" id="btnBukaFile" style="display:none" onclick="bukaFile()">📂 Buka Dokumen</button>
      <button class="btn-close-modal" onclick="tutupModal()">Tutup</button>
    </div>
  </div>
</div>

<script>
let db;
const DB_NAME="ppi_icra_tahunan";
const STORE="icra_tahunan";

async function openDB(){
  return new Promise(res=>{
    const req=indexedDB.open(DB_NAME,1);
    req.onupgradeneeded=e=>{
      e.target.result.createObjectStore(STORE,{keyPath:"id",autoIncrement:true});
    };
    req.onsuccess=e=>{db=e.target.result;res();};
  });
}
function addData(data){
  const tx=db.transaction(STORE,"readwrite");
  tx.objectStore(STORE).add(data);
}
function getAll(){
  return new Promise(res=>{
    const tx=db.transaction(STORE,"readonly");
    tx.objectStore(STORE).getAll().onsuccess=e=>res(e.target.result);
  });
}
async function simpan(){
  const tahun=document.getElementById("tahun").value;
  const unit=document.getElementById("unit").value.trim();
  const kegiatan=document.getElementById("kegiatan").value.trim();
  const risiko=document.getElementById("risiko").value.trim();
  const mitigasi=document.getElementById("mitigasi").value.trim();
  const penanggung=document.getElementById("penanggung").value.trim();
  const file=document.getElementById("file").files[0];

  if(!tahun || !unit || !kegiatan){
    alert("Lengkapi Tahun, Unit, dan Kegiatan!");
    return;
  }

  let fileObj=null;
  if(file){ fileObj={nama:file.name,tipe:file.type,blob:file}; }

  addData({tahun,unit,kegiatan,risiko,mitigasi,penanggung,file:fileObj,tanggal:new Date().toISOString()});
  document.querySelectorAll("input, textarea").forEach(i=>i.value="");
  render();
}
async function render(){
  const data=await getAll();
  const tbody=document.querySelector("#tabelICRA tbody");
  const search=document.getElementById("search").value.toLowerCase();
  tbody.innerHTML="";
  data.filter(d=>
    d.tahun.toString().includes(search) ||
    (d.unit||"").toLowerCase().includes(search) ||
    (d.kegiatan||"").toLowerCase().includes(search)
  ).sort((a,b)=>b.tahun-a.tahun).forEach((d,i)=>{
    const tr=document.createElement("tr");
    tr.innerHTML=`
      <td>${i+1}</td>
      <td>${d.tahun}</td>
      <td>${d.unit}</td>
      <td>${d.kegiatan}</td>
      <td>${d.risiko||'-'}</td>
      <td>${d.mitigasi||'-'}</td>
      <td>${d.penanggung||'-'}</td>
      <td class="actions">
        <button class="view" onclick="lihat(${d.id})">👁️</button>
        ${d.file?`<button class="download" onclick="unduh(${d.id})">⬇️</button>`:""}
        <button class="delete" onclick="hapus(${d.id})">🗑️</button>
      </td>`;
    tbody.appendChild(tr);
  });
}
/* HELPER */
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }

let currentFileBlob = null;

function lihat(id){
  getAll().then(all=>{
    const d=all.find(f=>f.id===id);
    document.getElementById('modalTitle').textContent=`📋 ICRA Tahunan — ${d.tahun}`;
    let html='';
    html+=`<div class="modal-field"><div class="modal-label">Tahun</div><div class="modal-value">${esc(d.tahun)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Unit / Ruangan</div><div class="modal-value">${esc(d.unit)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Jenis Kegiatan</div><div class="modal-value">${esc(d.kegiatan)}</div></div>`;
    if(d.risiko) html+=`<div class="modal-field"><div class="modal-label">Risiko Infeksi</div><div class="modal-value">${esc(d.risiko)}</div></div>`;
    if(d.mitigasi) html+=`<div class="modal-field"><div class="modal-label">Mitigasi / Pengendalian</div><div class="modal-value">${esc(d.mitigasi)}</div></div>`;
    if(d.penanggung) html+=`<div class="modal-field"><div class="modal-label">Penanggung Jawab</div><div class="modal-value">${esc(d.penanggung)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Tanggal Input</div><div class="modal-value">${new Date(d.tanggal).toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'})}</div></div>`;
    document.getElementById('modalBody').innerHTML=html;
    const btn=document.getElementById('btnBukaFile');
    if(d.file&&d.file.blob){ currentFileBlob=d.file.blob; btn.textContent=`📂 Buka: ${d.file.nama}`; btn.style.display=''; }
    else { currentFileBlob=null; btn.style.display='none'; }
    document.getElementById('modalOverlay').classList.add('active');
  });
}
function bukaFile(){
  if(!currentFileBlob) return;
  const url=URL.createObjectURL(currentFileBlob);
  window.open(url,'_blank');
  setTimeout(()=>URL.revokeObjectURL(url),15000);
}
function tutupModal(){
  document.getElementById('modalOverlay').classList.remove('active');
  currentFileBlob=null;
}
function unduh(id){
  getAll().then(all=>{
    const d=all.find(f=>f.id===id);
    if(!d.file)return alert("Tidak ada file!");
    const url=URL.createObjectURL(d.file.blob);
    const a=document.createElement("a");a.href=url;a.download=d.file.nama;a.click();URL.revokeObjectURL(url);
  });
}
function hapus(id){
  if(!confirm("Hapus laporan ini?"))return;
  const tx=db.transaction(STORE,"readwrite");
  tx.objectStore(STORE).delete(id).onsuccess=render;
}
function hapusSemua(){
  if(!confirm("Hapus semua laporan ICRA tahunan?"))return;
  const tx=db.transaction(STORE,"readwrite");
  tx.objectStore(STORE).clear().onsuccess=render;
}
document.getElementById("search").addEventListener("input",()=>render());
(async function init(){ await openDB(); render(); })();
function goDashboard(){ window.location.href="<?= base_url('dashboard.php') ?>"; }
document.getElementById('modalOverlay').addEventListener('click',function(e){ if(e.target===this) tutupModal(); });
</script>
<script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
