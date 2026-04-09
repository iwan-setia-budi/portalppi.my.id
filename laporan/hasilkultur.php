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
<?php $pageTitle = "HASIL KULTUR"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Hasil Kultur | PPI PHBW</title>
<link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
<style>
:root {
  --navy: #1a237e;
  --blue: #3b49df;
  --sky: #eef1ff;
  --green: #43a047;
  --red: #d32f2f;
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
.form-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}
input, select, textarea {
  padding: 10px;
  border: 1px solid var(--border);
  border-radius: 10px;
  font-size: 0.95em;
  width: 100%;
  box-sizing: border-box;
}
textarea { resize: vertical; }
button { font-weight: 600; cursor: pointer; color: white; transition: .2s; }
button.save { background: var(--blue); border: none; padding: 10px 16px; border-radius: 10px; }
button.save:hover { background: #283593; }
button.clear { background: var(--red); border: none; padding: 10px 16px; border-radius: 10px; }
button.clear:hover { background: #b71c1c; }

/* TABLE */
.table-wrapper { margin-top: 15px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.95em; }
th, td { padding: 10px 8px; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
th { background: var(--sky); color: var(--navy); font-weight: 600; }
tr:hover td { background: #f8f9ff; }
td.actions { text-align: center; white-space: nowrap; }
td.actions button { padding: 6px 10px; font-size: 0.85em; border-radius: 8px; margin: 0 3px; }
.view { background: var(--green); }
.delete { background: var(--red); }
input#search { width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); margin: 10px 0 15px; box-sizing: border-box; }

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
  .form-section { grid-template-columns: 1fr; }
  input, select, textarea { font-size: 16px !important; }
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
  max-width: 540px;
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
          <h1>Hasil Kultur Pasien</h1>
          <small>Data Hasil Kultur Mikrobiologi — Komite PPI</small>
        </div>
        <button class="hero-btn" onclick="goDashboard()">🏠 Dashboard</button>
      </div>

      <div class="lap-content">
        <h2>🧾 Input Data Hasil Kultur</h2>

        <div class="form-section">
          <input type="text" id="nama" placeholder="Nama Pasien">
          <input type="text" id="ruangan" placeholder="Ruangan">
          <input type="date" id="tanggal">
          <input type="text" id="spesimen" placeholder="Jenis Spesimen (misal: Urin, Sputum)">
          <input type="text" id="hasil" placeholder="Hasil (misal: E. coli, MRSA)">
          <textarea id="keterangan" rows="2" placeholder="Keterangan tambahan (misal: sensitif, resisten, dll)"></textarea>
        </div>

        <div style="margin:10px 0;">
          <button class="save" onclick="tambahData()">💾 Simpan Data</button>
          <button class="clear" onclick="hapusSemua()">🧹 Hapus Semua</button>
        </div>

        <input type="search" id="search" placeholder="🔍 Cari nama pasien, spesimen, atau hasil...">
        <p class="scroll-hint">← geser tabel →</p>

        <div class="table-wrapper">
          <table id="tabelKultur">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Pasien</th>
                <th>Ruangan</th>
                <th>Tanggal</th>
                <th>Spesimen</th>
                <th>Hasil</th>
                <th>Keterangan</th>
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

<!-- MODAL DETAIL KULTUR -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Detail Hasil Kultur</span>
      <button class="modal-close" onclick="tutupModal()">✕</button>
    </div>
    <div id="modalBody"></div>
    <div class="modal-actions">
      <button class="btn-close-modal" onclick="tutupModal()">Tutup</button>
    </div>
  </div>
</div>

<script>
let db;
const DB_NAME="hasil_kultur_ppi";
const STORE="kultur";

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

async function tambahData(){
  const nama=document.getElementById("nama").value.trim();
  const ruangan=document.getElementById("ruangan").value.trim();
  const tanggal=document.getElementById("tanggal").value;
  const spesimen=document.getElementById("spesimen").value.trim();
  const hasil=document.getElementById("hasil").value.trim();
  const keterangan=document.getElementById("keterangan").value.trim();

  if(!nama || !spesimen || !hasil){
    alert("Nama pasien, spesimen, dan hasil wajib diisi!");
    return;
  }

  addData({
    nama, ruangan, tanggal, spesimen, hasil, keterangan,
    created:new Date().toISOString()
  });

  document.querySelectorAll("input, textarea").forEach(i=>i.value="");
  render();
}

async function render(){
  const data=await getAll();
  const tbody=document.querySelector("#tabelKultur tbody");
  tbody.innerHTML="";
  const search=document.getElementById("search").value.toLowerCase();
  const filtered=data.filter(d=>
    d.nama.toLowerCase().includes(search) ||
    d.spesimen.toLowerCase().includes(search) ||
    d.hasil.toLowerCase().includes(search)
  );
  filtered.sort((a,b)=>new Date(b.created)-new Date(a.created));
  filtered.forEach((d,i)=>{
    const tr=document.createElement("tr");
    tr.innerHTML=`
      <td>${i+1}</td>
      <td>${d.nama}</td>
      <td>${d.ruangan||"-"}</td>
      <td>${d.tanggal?new Date(d.tanggal).toLocaleDateString():"-"}</td>
      <td>${d.spesimen}</td>
      <td>${d.hasil}</td>
      <td>${d.keterangan||"-"}</td>
      <td class="actions">
        <button class="view" onclick="lihatDetail(${d.id})">👁️ Detail</button>
        <button class="delete" onclick="hapusData(${d.id})">🗑️ Hapus</button>
      </td>`;
    tbody.appendChild(tr);
  });
}

/* HELPER */
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }

function lihatDetail(id){
  getAll().then(all=>{
    const d=all.find(i=>i.id===id);
    const tgl = d.tanggal ? new Date(d.tanggal).toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'}) : '-';
    document.getElementById('modalTitle').textContent=`🦫 Detail Kultur — ${d.nama}`;
    let html='';
    html+=`<div class="modal-field"><div class="modal-label">Nama Pasien</div><div class="modal-value">${esc(d.nama)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Ruangan</div><div class="modal-value">${esc(d.ruangan||'-')}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Tanggal</div><div class="modal-value">${esc(tgl)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Jenis Spesimen</div><div class="modal-value">${esc(d.spesimen)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Hasil Kultur</div><div class="modal-value">${esc(d.hasil)}</div></div>`;
    if(d.keterangan) html+=`<div class="modal-field"><div class="modal-label">Keterangan</div><div class="modal-value">${esc(d.keterangan)}</div></div>`;
    document.getElementById('modalBody').innerHTML=html;
    document.getElementById('modalOverlay').classList.add('active');
  });
}
function tutupModal(){
  document.getElementById('modalOverlay').classList.remove('active');
}
function hapusData(id){
  if(!confirm("Hapus data ini?"))return;
  const tx=db.transaction(STORE,"readwrite");
  tx.objectStore(STORE).delete(id).onsuccess=render;
}
function hapusSemua(){
  if(!confirm("Hapus SEMUA hasil kultur?"))return;
  const tx=db.transaction(STORE,"readwrite");
  tx.objectStore(STORE).clear().onsuccess=render;
}
document.getElementById("search").addEventListener("input",()=>render());

(async function init(){
  await openDB();
  render();
})();
function goDashboard(){window.location.href="<?= base_url('dashboard.php') ?>";}
document.getElementById('modalOverlay').addEventListener('click',function(e){ if(e.target===this) tutupModal(); });
</script>
<script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
