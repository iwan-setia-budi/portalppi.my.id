<?php
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
<?php $pageTitle = "LAPORAN TAHUNAN"; ?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Laporan Triwulan & Tahunan Komite PPI | PPI PHBW</title>
<link rel="stylesheet" href="/assets/css/utama.css?v=15">
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

/* TAB NAVIGATION */
.tab-nav {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}
.tab-btn {
  background: #e0e7ff;
  color: var(--navy);
  border: none;
  padding: 10px 22px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.95em;
  transition: 0.2s;
  color: var(--navy);
}
.tab-btn:hover { background: #c7d2fe; }
.tab-btn.active {
  background: linear-gradient(135deg, var(--navy), var(--blue));
  color: white;
}

/* LAP CONTENT */
.lap-content {
  background: white;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.07);
}

/* FORM */
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
button.save { background: var(--blue); border: none; padding: 8px 14px; border-radius: 8px;  }
button.save:hover { background: #283593; }
button.clear { background: var(--red); border: none; padding: 8px 14px; border-radius: 8px;  }
button.clear:hover { background: #b71c1c; }

/* TABLE */
h2 { color: var(--navy); border-bottom: 3px solid var(--blue); padding-bottom: 6px; margin-top: 0; }
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
input#searchTri, input#searchTahunan { width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); margin: 10px 0; }

/* TAB CONTENT */
.tab { display: none; }
.tab.active { display: block; }

/* FIX CSS GRID OVERFLOW — wajib agar main tidak meluber */
main { min-width: 0; overflow-x: hidden; }

/* RESPONSIVE MOBILE */
@media (max-width: 900px) {

  /* Container */
  .container-lap {
    padding: 12px;
    overflow-x: hidden;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
  }

  /* Hero stack vertikal */
  .page-hero {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
    padding: 18px 20px;
    border-radius: 14px;
  }

  .page-hero h1 { font-size: 17px; }
  .page-hero small { font-size: 12px; }

  .hero-btn {
    align-self: stretch;
    text-align: center;
    padding: 10px;
  }

  /* Tab nav scroll horizontal, tidak wrap */
  .tab-nav {
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
    gap: 8px;
    scrollbar-width: none;
  }
  .tab-nav::-webkit-scrollbar { display: none; }

  .tab-btn {
    white-space: nowrap;
    flex-shrink: 0;
    padding: 9px 14px;
    font-size: 0.85em;
  }

  /* Konten */
  .lap-content { padding: 14px; }

  /* Form — font-size 16px agar tidak auto-zoom di iOS */
  input, textarea, select { font-size: 16px !important; }

  /* Table swipe */
  .table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Kolom ringkasan/rekomendasi lebih sempit di mobile */
  table { font-size: 0.82em; }
  th, td { padding: 8px 6px; }
}

/* SCROLL HINT */
.scroll-hint {
  display: none;
  font-size: 11px;
  color: #94a3b8;
  text-align: right;
  margin: 4px 0 4px;
  letter-spacing: 0.02em;
}
@media (max-width: 768px) { .scroll-hint { display: block; } }

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
          <h1>Laporan Triwulan &amp; Tahunan</h1>
          <small>Manajemen Laporan Komite PPI</small>
        </div>
        <button class="hero-btn" onclick="goDashboard()">🏠 Dashboard</button>
      </div>

      <div class="tab-nav">
        <button class="tab-btn active" onclick="showTab('triwulan', this)">🗓️ Laporan Triwulan</button>
        <button class="tab-btn" onclick="showTab('semester', this)">📆 Laporan Semester</button>
        <button class="tab-btn" onclick="showTab('tahunan', this)">📅 Laporan Tahunan</button>
      </div>

      <div class="lap-content">
        <!-- TRIWIULAN -->
  <section id="triwulan" class="tab active">
    <h2>🗓️ Laporan Triwulan Komite PPI</h2>
    <select id="periode">
      <option value="">Pilih Triwulan</option>
      <option>Triwulan I (Jan–Mar)</option>
      <option>Triwulan II (Apr–Jun)</option>
      <option>Triwulan III (Jul–Sep)</option>
      <option>Triwulan IV (Okt–Des)</option>
    </select>
    <input type="number" id="tahunTri" placeholder="Tahun (contoh: 2025)" min="2020" max="2100">
    <input type="text" id="penanggungTri" placeholder="Penanggung Jawab">
    <textarea id="ringkasanTri" placeholder="Ringkasan kegiatan selama triwulan"></textarea>
    <textarea id="rekomendasiTri" placeholder="Rekomendasi & tindak lanjut"></textarea>
    <input type="file" id="fileTri" accept=".pdf,.docx,.xlsx,.jpg,.png">

    <div style="margin:10px 0;">
      <button class="save" onclick="simpan('Tri')">💾 Simpan</button>
      <button class="clear" onclick="hapusSemua('Tri')">🧹 Hapus Semua</button>
    </div>

    <input type="search" id="searchTri" placeholder="🔍 Cari berdasarkan tahun, triwulan, atau penanggung...">
    <p class="scroll-hint">← geser tabel →</p>
    <div class="table-wrapper">
      <table id="tabelTri">
        <thead>
          <tr>
            <th>No</th>
            <th>Tahun</th>
            <th>Periode</th>
            <th>Penanggung</th>
            <th>Ringkasan</th>
            <th>Rekomendasi</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  <!-- SEMESTER -->
  <section id="semester" class="tab">
    <h2>📆 Laporan Semester Komite PPI</h2>
    <select id="semesterPeriode">
      <option value="">Pilih Semester</option>
      <option>Semester I (Jan–Jun)</option>
      <option>Semester II (Jul–Des)</option>
    </select>
    <input type="number" id="tahunSemester" placeholder="Tahun (contoh: 2025)" min="2020" max="2100">
    <input type="text" id="penanggungSemester" placeholder="Penanggung Jawab">
    <textarea id="ringkasanSemester" placeholder="Ringkasan kegiatan selama semester"></textarea>
    <textarea id="rekomendasiSemester" placeholder="Rekomendasi &amp; tindak lanjut"></textarea>
    <input type="file" id="fileSemester" accept=".pdf,.docx,.xlsx,.jpg,.png">

    <div style="margin:10px 0;">
      <button class="save" onclick="simpan('Semester')">💾 Simpan</button>
      <button class="clear" onclick="hapusSemua('Semester')">🧹 Hapus Semua</button>
    </div>

    <input type="search" id="searchSemester" placeholder="🔍 Cari berdasarkan tahun, semester, atau penanggung...">
    <p class="scroll-hint">← geser tabel →</p>
    <div class="table-wrapper">
      <table id="tabelSemester">
        <thead>
          <tr>
            <th>No</th>
            <th>Tahun</th>
            <th>Periode</th>
            <th>Penanggung</th>
            <th>Ringkasan</th>
            <th>Rekomendasi</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  <!-- TAHUNAN -->
  <section id="tahunan" class="tab">
    <h2>📅 Laporan Tahunan Komite PPI</h2>
    <input type="number" id="tahunTahunan" placeholder="Tahun (contoh: 2025)" min="2020" max="2100">
    <input type="text" id="penanggungTahunan" placeholder="Penanggung Jawab">
    <textarea id="ringkasanTahunan" placeholder="Ringkasan kegiatan tahunan"></textarea>
    <textarea id="rekomendasiTahunan" placeholder="Rekomendasi & tindak lanjut tahun berikutnya"></textarea>
    <input type="file" id="fileTahunan" accept=".pdf,.docx,.xlsx,.jpg,.png">

    <div style="margin:10px 0;">
      <button class="save" onclick="simpan('Tahunan')">💾 Simpan</button>
      <button class="clear" onclick="hapusSemua('Tahunan')">🧹 Hapus Semua</button>
    </div>

    <input type="search" id="searchTahunan" placeholder="🔍 Cari berdasarkan tahun atau penanggung...">
    <p class="scroll-hint">← geser tabel →</p>
    <div class="table-wrapper">
      <table id="tabelTahunan">
        <thead>
          <tr>
            <th>No</th>
            <th>Tahun</th>
            <th>Penanggung</th>
            <th>Ringkasan</th>
            <th>Rekomendasi</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>
      </div><!-- /.lap-content -->
    </div><!-- /.container-lap -->
  </main>
</div><!-- /.layout -->

<!-- MODAL LIHAT DOKUMEN -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Detail Laporan</span>
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
let currentFileBlob = null;
const DB_NAME="ppi_triwulan_tahunan";
const DB_VER=2;
const STORE_TRI="triwulan";
const STORE_SEM="semester";
const STORE_TAHUN="tahunan";

async function openDB(){
  return new Promise(res=>{
    const req=indexedDB.open(DB_NAME,DB_VER);
    req.onupgradeneeded=e=>{
      const db=e.target.result;
      if(!db.objectStoreNames.contains(STORE_TRI)) db.createObjectStore(STORE_TRI,{keyPath:"id",autoIncrement:true});
      if(!db.objectStoreNames.contains(STORE_SEM)) db.createObjectStore(STORE_SEM,{keyPath:"id",autoIncrement:true});
      if(!db.objectStoreNames.contains(STORE_TAHUN)) db.createObjectStore(STORE_TAHUN,{keyPath:"id",autoIncrement:true});
    };
    req.onsuccess=e=>{db=e.target.result;res();};
  });
}

function addData(store,data){
  const tx=db.transaction(store,"readwrite");
  tx.objectStore(store).add(data);
}
function getAll(store){
  return new Promise(res=>{
    const tx=db.transaction(store,"readonly");
    tx.objectStore(store).getAll().onsuccess=e=>res(e.target.result);
  });
}

/* SIMPAN */
async function simpan(type){
  const store = type==="Tri"?STORE_TRI:(type==="Semester"?STORE_SEM:STORE_TAHUN);
  const tahun=document.getElementById(`tahun${type}`).value;
  const penanggung=document.getElementById(`penanggung${type}`).value.trim();
  const ringkasan=document.getElementById(`ringkasan${type}`).value.trim();
  const rekomendasi=document.getElementById(`rekomendasi${type}`).value.trim();
  const file=document.getElementById(`file${type}`).files[0];
  const periode = type==="Tri" ? document.getElementById("periode").value : (type==="Semester" ? document.getElementById("semesterPeriode").value : null);

  if(!tahun || !penanggung || !ringkasan){
    alert("Lengkapi Tahun, Penanggung, dan Ringkasan!");
    return;
  }

  let fileObj=null;
  if(file){ fileObj={nama:file.name,tipe:file.type,blob:file}; }

  const sectionId = type==="Tri"?"triwulan":(type==="Semester"?"semester":"tahunan");
  addData(store,{tahun,periode,penanggung,ringkasan,rekomendasi,file:fileObj,tanggal:new Date().toISOString()});
  document.querySelectorAll(`#${sectionId} input, #${sectionId} textarea, #${sectionId} select`).forEach(i=>i.value="");
  render(type);
}

/* RENDER */
async function render(type){
  const store = type==="Tri"?STORE_TRI:(type==="Semester"?STORE_SEM:STORE_TAHUN);
  const data=await getAll(store);
  const tbody=document.querySelector(`#tabel${type} tbody`);
  const search=document.getElementById(`search${type}`).value.toLowerCase();
  tbody.innerHTML="";

  data.filter(d=>
    d.tahun.toString().includes(search) ||
    (d.penanggung||"").toLowerCase().includes(search) ||
    (d.ringkasan||"").toLowerCase().includes(search)
  ).sort((a,b)=>b.tahun-a.tahun).forEach((d,i)=>{
    const tr=document.createElement("tr");
    tr.innerHTML=`
      <td>${i+1}</td>
      <td>${d.tahun}</td>
      ${(type==="Tri"||type==="Semester")?`<td>${d.periode||"-"}</td>`:""}
      <td>${d.penanggung}</td>
      <td>${(d.ringkasan||"").slice(0,60)}${d.ringkasan?.length>60?'...':''}</td>
      <td>${(d.rekomendasi||"").slice(0,60)}${d.rekomendasi?.length>60?'...':''}</td>
      <td class="actions">
        <button class="view" onclick="lihat('${type}',${d.id})">👁️</button>
        ${d.file?`<button class="download" onclick="unduh('${type}',${d.id})">⬇️</button>`:""}
        <button class="delete" onclick="hapus('${type}',${d.id})">🗑️</button>
      </td>`;
    tbody.appendChild(tr);
  });
}

/* HELPER */
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }

/* AKSI */
function lihat(type,id){
  const store=type==="Tri"?STORE_TRI:(type==="Semester"?STORE_SEM:STORE_TAHUN);
  getAll(store).then(all=>{
    const d=all.find(f=>f.id===id);
    const label=type==="Tri"?"TRIWULAN":(type==="Semester"?"SEMESTER":"TAHUNAN");
    document.getElementById('modalTitle').textContent=`📄 Laporan ${label} — ${d.tahun}`;
    let html='';
    if(d.periode) html+=`<div class="modal-field"><div class="modal-label">Periode</div><div class="modal-value">${esc(d.periode)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Tahun</div><div class="modal-value">${esc(d.tahun)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Penanggung Jawab</div><div class="modal-value">${esc(d.penanggung)}</div></div>`;
    html+=`<div class="modal-field"><div class="modal-label">Ringkasan Kegiatan</div><div class="modal-value">${esc(d.ringkasan)}</div></div>`;
    if(d.rekomendasi) html+=`<div class="modal-field"><div class="modal-label">Rekomendasi &amp; Tindak Lanjut</div><div class="modal-value">${esc(d.rekomendasi)}</div></div>`;
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
function unduh(type,id){
  const store=type==="Tri"?STORE_TRI:(type==="Semester"?STORE_SEM:STORE_TAHUN);
  getAll(store).then(all=>{
    const d=all.find(f=>f.id===id);
    if(!d.file)return alert("Tidak ada file!");
    const url=URL.createObjectURL(d.file.blob);
    const a=document.createElement("a");a.href=url;a.download=d.file.nama;a.click();URL.revokeObjectURL(url);
  });
}
function hapus(type,id){
  const store=type==="Tri"?STORE_TRI:(type==="Semester"?STORE_SEM:STORE_TAHUN);
  if(!confirm("Hapus laporan ini?"))return;
  const tx=db.transaction(store,"readwrite");
  tx.objectStore(store).delete(id).onsuccess=()=>render(type);
}
function hapusSemua(type){
  const store=type==="Tri"?STORE_TRI:(type==="Semester"?STORE_SEM:STORE_TAHUN);
  if(!confirm(`Hapus semua laporan ${type==="Tri"?"triwulan":(type==="Semester"?"semester":"tahunan")}?`))return;
  const tx=db.transaction(store,"readwrite");
  tx.objectStore(store).clear().onsuccess=()=>render(type);
}

/* TAB SWITCH */
function showTab(id,btn){
  document.querySelectorAll(".tab-btn").forEach(b=>b.classList.remove("active"));
  btn.classList.add("active");
  document.querySelectorAll(".tab").forEach(t=>t.classList.remove("active"));
  document.getElementById(id).classList.add("active");
}
document.getElementById("searchTri").addEventListener("input",()=>render("Tri"));
document.getElementById("searchSemester").addEventListener("input",()=>render("Semester"));
document.getElementById("searchTahunan").addEventListener("input",()=>render("Tahunan"));

(async function init(){
  await openDB();
  render("Tri");
  render("Semester");
  render("Tahunan");
})();
function goDashboard(){ window.location.href="/dashboard.php"; }
document.getElementById('modalOverlay').addEventListener('click',function(e){ if(e.target===this) tutupModal(); });
</script>
<script src="/assets/js/utama.js?v=5"></script>
</body>
</html>
