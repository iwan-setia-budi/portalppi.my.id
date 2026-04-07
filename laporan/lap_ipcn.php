<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<title>Laporan Bulanan PPI | PPI PHBW</title>

<!-- ====== STYLES ====== -->
<style>
:root{
  --navy:#1a237e;
  --blue:#3b49df;
  --sky:#eef1ff;
  --green:#43a047;
  --red:#d32f2f;
  --border:#dce0f0;
  --card:#ffffff;
  --transition: all 0.18s ease-in-out;
  --radius:12px;
  --shadow: 0 6px 18px rgba(16,24,64,0.07);
  --muted:#6b7280;
}

*{box-sizing:border-box}
body{
  margin:0;
  font-family: Inter, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  background: linear-gradient(180deg, #f6f8ff 0%, #eef3ff 100%);
  color:#222;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
}

/* Header */
header{
  background: linear-gradient(90deg,var(--navy),var(--blue));
  color:#fff;
  padding:18px 28px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  box-shadow: 0 6px 18px rgba(16,24,64,0.12);
}
header h1{ margin:0; font-size:1.05rem; letter-spacing:0.2px; }
.header-actions{ display:flex; gap:10px; align-items:center; }

/* Container */
.container{
  max-width:1100px;
  margin:28px auto;
  background:var(--card);
  border-radius:16px;
  padding:26px;
  box-shadow: var(--shadow);
}

/* Title */
.title{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:12px;
}
.title h2{
  margin:0;
  color:var(--navy);
  font-size:1.15rem;
  display:flex;
  align-items:center;
  gap:10px;
  padding-bottom:6px;
  border-bottom:3px solid var(--blue);
}

/* Buttons */
.btn{
  border:0;
  padding:10px 14px;
  border-radius:10px;
  cursor:pointer;
  font-weight:600;
  transition:var(--transition);
}
.btn-primary{ background:var(--blue); color:#fff; }
.btn-primary:hover{ transform:translateY(-2px); box-shadow:0 8px 20px rgba(59,73,223,0.18); }
.btn-outline{
  background:transparent;
  color:var(--navy);
  border:1px solid rgba(26,35,126,0.08);
}
.btn-danger{ background:var(--red); color:#fff; }
.icon { margin-right:8px; }

/* Search */
.search-wrap{ margin:16px 0 4px 0; display:flex; gap:10px; align-items:center; }
#search{
  flex:1;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid var(--border);
  font-size:0.95rem;
}

/* Table */
.table-wrap{ margin-top:18px; overflow:auto; border-radius:12px; }
table{ width:100%; border-collapse:collapse; min-width:860px; }
th, td{
  padding:12px 14px;
  text-align:left;
  border-bottom:1px solid var(--border);
  vertical-align:middle;
  font-size:0.95rem;
}
th{
  background:linear-gradient(180deg,#f5f7ff 0%, #eef1ff 100%);
  color:var(--navy);
  font-weight:700;
}
tr:hover td{ background:#fbfcff; }
.actions{ white-space:nowrap; text-align:center; }

/* small buttons inside table */
.tbtn{ border:0; padding:8px 10px; border-radius:8px; color:#fff; cursor:pointer; font-size:0.85rem; margin:0 4px; }
.tbtn.view{ background:var(--blue); }
.tbtn.download{ background:var(--green); }
.tbtn.delete{ background:var(--red); }

/* Empty state */
.empty{
  padding:26px;
  text-align:center;
  color:var(--muted);
  background:#fbfbff;
  border-radius:10px;
  margin-top:12px;
}

/* Footer */
footer{ text-align:center; color:#7b7f88; margin-top:18px; }

/* ===== POPUP MODAL ===== */
.modal {
  display:none;
  position:fixed;
  z-index:9999;
  left:0; top:0;
  width:100%; height:100%;
  background:rgba(6,10,26,0.45);
  align-items:center;
  justify-content:center;
  padding:20px;
}
.modal.open{ display:flex; }
.modal-card{
  width:100%;
  max-width:720px;
  background:#fff;
  border-radius:14px;
  padding:20px;
  box-shadow: 0 20px 50px rgba(14,18,50,0.25);
  animation: pop 0.18s ease;
  position:relative;
}
@keyframes pop{
  from { transform: translateY(8px) scale(.98); opacity:0 }
  to { transform: translateY(0) scale(1); opacity:1 }
}
.modal-close{
  position:absolute; right:14px; top:12px; font-size:20px; cursor:pointer; color:#555;
}

/* Form inside modal */
.form-grid{
  display:grid;
  grid-template-columns: repeat(2, 1fr);
  gap:12px;
}
.form-grid .full{ grid-column: 1 / -1; }

input, select, textarea{
  width:100%;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid var(--border);
  font-size:0.95rem;
}
textarea{ min-height:88px; resize:vertical; }

/* small helper text */
.hint{ font-size:0.85rem; color:var(--muted); margin-top:8px; }

/* responsive */
@media (max-width:760px){
  .form-grid{ grid-template-columns: 1fr; }
  table{ min-width:700px; }
}
/* Tombol outline putih di header */
.btn.white {
  color: #fff !important;
  border: 1px solid rgba(255,255,255,0.6) !important;
  background: transparent;
}
.btn.white:hover {
  background: rgba(255,255,255,0.15) !important;
  border-color: rgba(255,255,255,0.8) !important;
  transform: translateY(-1px);
}

</style>
</head>
<body>

<header>
  <h1>🧾 Laporan Bulanan PPI | PPI PHBW</h1>
  <div class="header-actions">
<button class="btn btn-outline white" onclick="location.href='/dashboard.php'">🏠 Kembali</button>

  </div>
</header>

<div class="container">
  <div class="title">
    <h2>📅 Input Laporan Bulanan</h2>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-primary" onclick="openModal()">➕ Tambah Laporan</button>
      <button class="btn" id="btnClearAll" title="Hapus semua" onclick="hapusSemua()">
        🧹 Hapus Semua
      </button>
    </div>
  </div>

  <div class="search-wrap">
    <input id="search" placeholder="🔍 Cari laporan (bulan, tahun, jenis, uraian...)" />
    <div style="min-width:120px; text-align:right; color:var(--muted); font-size:0.9rem;">
      <span id="countLabel">0 laporan</span>
    </div>
  </div>

  <div class="table-wrap" id="tableWrap">
    <table id="tabelLaporan" aria-live="polite">
      <thead>
        <tr>
          <th style="width:56px">No</th>
          <th>Bulan</th>
          <th>Tahun</th>
          <th>Jenis</th>
          <th>Uraian</th>
          <th>Penanggung Jawab</th>
          <th style="width:220px;text-align:center">Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div id="emptyState" class="empty" style="display:none;">
    Belum ada laporan. Klik tombol "➕ Tambah Laporan" untuk membuat laporan baru.
  </div>

  <footer>
    © 2025 PPI PHBW — Laporan Bulanan | Desain Dashboard Profesional
  </footer>
</div>

<!-- ===== MODAL POPUP ===== -->
<div id="modal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="modal-card" role="document">
    <div class="modal-close" onclick="closeModal()">✖</div>
    <h3 style="margin:0 0 10px 0; color:var(--navy)">📝 Tambah Laporan Bulanan</h3>

    <div class="form-grid">
      <select id="bulan">
        <option value="">Pilih Bulan</option>
        <option>Januari</option><option>Februari</option><option>Maret</option><option>April</option>
        <option>Mei</option><option>Juni</option><option>Juli</option><option>Agustus</option>
        <option>September</option><option>Oktober</option><option>November</option><option>Desember</option>
      </select>

      <input type="number" id="tahun" placeholder="Tahun" min="2020" max="2100" />

      <select id="jenis">
        <option value="">Jenis Laporan</option>
        <option>Surveilans Infeksi</option>
        <option>Audit & Monitoring</option>
        <option>Pelatihan & Edukasi</option>
        <option>MDRO & Hasil Kultur</option>
        <option>Kegiatan PPI Lainnya</option>
      </select>

      <input type="file" id="file" accept=".pdf,.xlsx,.xls,.docx,.doc,.jpg,.jpeg,.png" />

      <textarea id="uraian" class="full" placeholder="Uraian singkat laporan (misal: Rekap Surveilans ISK bulan Juli)"></textarea>

      <input id="penanggung" class="full" placeholder="Penanggung Jawab (misal: Tim PPI Ruang Rawat Inap)" />
    </div>

    <div style="display:flex;gap:10px;margin-top:12px; justify-content:flex-end;">
      <button class="btn btn-outline" onclick="closeModal()">Batal</button>
      <button class="btn btn-primary" onclick="tambahLaporan()">💾 Simpan Laporan</button>
    </div>

    <p class="hint">File (opsional): PDF, Excel, Word, atau gambar. File disimpan di browser (IndexedDB).</p>
  </div>
</div>

<!-- ===== PREVIEW MODAL ===== -->
<div id="previewModal" class="modal" aria-hidden="true">
  <div class="modal-card" style="max-width:900px;">
    <div class="modal-close" onclick="closePreview()">✖</div>
    <div id="previewContent"></div>
    <div style="display:flex; justify-content:flex-end; margin-top:12px;">
      <button class="btn btn-outline" onclick="closePreview()">Tutup</button>
    </div>
  </div>
</div>

<!-- ====== SCRIPTS ====== -->
<script>
/*
  Laporan Bulanan (single page)
  - Penyimpanan menggunakan IndexedDB (lokal di browser)
  - Modal popup untuk input laporan
  - Preview file (pdf/image) bila tersedia
  - Unduh, Hapus, Pencarian
*/

/* ---------- IndexedDB setup ---------- */
const DB_NAME = "laporan_bulanan_ppi";
const STORE_NAME = "laporan";
let db = null;

function openDB(){
  return new Promise((resolve, reject) => {
    if (!window.indexedDB) {
      alert("IndexedDB tidak didukung di browser ini. Data tidak akan tersimpan secara lokal.");
      reject("IndexedDB not supported");
      return;
    }
    const req = indexedDB.open(DB_NAME, 1);
    req.onupgradeneeded = function(e){
      const idb = e.target.result;
      if (!idb.objectStoreNames.contains(STORE_NAME)) {
        idb.createObjectStore(STORE_NAME, { keyPath: "id", autoIncrement: true });
      }
    };
    req.onsuccess = function(e){
      db = e.target.result;
      resolve();
    };
    req.onerror = function(e){
      console.error("DB error", e);
      reject(e);
    };
  });
}

function addData(obj){
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, "readwrite");
    const store = tx.objectStore(STORE_NAME);
    const r = store.add(obj);
    r.onsuccess = () => resolve(r.result);
    r.onerror = (err) => reject(err);
  });
}
function getAllData(){
  return new Promise((resolve) => {
    const tx = db.transaction(STORE_NAME, "readonly");
    const store = tx.objectStore(STORE_NAME);
    const req = store.getAll();
    req.onsuccess = (e) => resolve(e.target.result || []);
    req.onerror = () => resolve([]);
  });
}
function deleteById(id){
  return new Promise((resolve) => {
    const tx = db.transaction(STORE_NAME, "readwrite");
    tx.objectStore(STORE_NAME).delete(id).onsuccess = resolve;
  });
}
function clearAll(){
  return new Promise((resolve) => {
    const tx = db.transaction(STORE_NAME, "readwrite");
    tx.objectStore(STORE_NAME).clear().onsuccess = resolve;
  });
}

/* ---------- UI helpers ---------- */
const modal = document.getElementById("modal");
const previewModal = document.getElementById("previewModal");
const previewContent = document.getElementById("previewContent");
const searchInput = document.getElementById("search");
const countLabel = document.getElementById("countLabel");

function openModal(){
  modal.classList.add("open");
  modal.setAttribute("aria-hidden","false");
}
function closeModal(){
  modal.classList.remove("open");
  modal.setAttribute("aria-hidden","true");
  resetForm();
}
function openPreview(){
  previewModal.classList.add("open");
  previewModal.setAttribute("aria-hidden","false");
}
function closePreview(){
  previewModal.classList.remove("open");
  previewModal.setAttribute("aria-hidden","true");
  previewContent.innerHTML = "";
}

/* reset form fields in modal */
function resetForm(){
  document.getElementById("bulan").value = "";
  document.getElementById("tahun").value = "";
  document.getElementById("jenis").value = "";
  document.getElementById("file").value = "";
  document.getElementById("uraian").value = "";
  document.getElementById("penanggung").value = "";
}

/* ---------- Core: tambah, render, lihat, unduh, hapus ---------- */

async function tambahLaporan(){
  const bulan = document.getElementById("bulan").value.trim();
  const tahun = document.getElementById("tahun").value.trim();
  const jenis = document.getElementById("jenis").value.trim();
  const uraian = document.getElementById("uraian").value.trim();
  const penanggung = document.getElementById("penanggung").value.trim();
  const fileInput = document.getElementById("file");

  if (!bulan || !tahun || !jenis || !uraian) {
    alert("Lengkapi semua kolom (bulan, tahun, jenis, uraian) sebelum menyimpan.");
    return;
  }

  let fileObj = null;
  if (fileInput.files && fileInput.files[0]) {
    const f = fileInput.files[0];
    // Simpan File/Blob langsung (IndexedDB mendukung Blob/File)
    fileObj = {
      name: f.name,
      type: f.type,
      size: f.size,
      blob: f // File adalah Blob turunan, bisa disimpan langsung
    };
  }

  const data = {
    bulan, tahun, jenis, uraian, penanggung,
    file: fileObj,
    createdAt: new Date().toISOString()
  };

  try {
    await addData(data);
    closeModal();
    await render();
  } catch(err){
    console.error("Gagal menyimpan:", err);
    alert("Terjadi kesalahan saat menyimpan. Cek console.");
  }
}

async function render(){
  const all = await getAllData();
  const tbody = document.querySelector("#tabelLaporan tbody");
  const term = (searchInput.value || "").toLowerCase().trim();

  let filtered = all;
  if (term) {
    filtered = all.filter(d => {
      return (d.bulan && d.bulan.toLowerCase().includes(term)) ||
             (d.tahun && d.tahun.toString().includes(term)) ||
             (d.jenis && d.jenis.toLowerCase().includes(term)) ||
             (d.uraian && d.uraian.toLowerCase().includes(term)) ||
             (d.penanggung && d.penanggung.toLowerCase().includes(term));
    });
  }

  // urutkan berdasarkan tanggal terbaru
  filtered.sort((a,b) => new Date(b.createdAt) - new Date(a.createdAt));

  tbody.innerHTML = "";
  if (filtered.length === 0) {
    document.getElementById("emptyState").style.display = "block";
    countLabel.innerText = `${all.length} laporan (menampilkan 0)`;
    return;
  } else {
    document.getElementById("emptyState").style.display = "none";
    countLabel.innerText = `${all.length} laporan (menampilkan ${filtered.length})`;
  }

  filtered.forEach((d, idx) => {
    const tr = document.createElement("tr");
    const no = idx + 1;

    const uraianShort = d.uraian.length > 80 ? d.uraian.substring(0,77) + "..." : d.uraian;

    tr.innerHTML = `
      <td>${no}</td>
      <td>${escapeHtml(d.bulan || "-")}</td>
      <td>${escapeHtml(d.tahun || "-")}</td>
      <td>${escapeHtml(d.jenis || "-")}</td>
      <td title="${escapeHtml(d.uraian || '')}">${escapeHtml(uraianShort)}</td>
      <td>${escapeHtml(d.penanggung || "-")}</td>
      <td class="actions">
        <button class="tbtn view" title="Lihat detail" onclick="lihatLaporan(${d.id})">👁️ Lihat</button>
        ${d.file ? `<button class="tbtn download" title="Unduh file" onclick="unduhFile(${d.id})">⬇️ Unduh</button>` : ""}
        <button class="tbtn delete" title="Hapus" onclick="hapusData(${d.id})">🗑️ Hapus</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

/* lihat detail + preview file bila ada */
async function lihatLaporan(id){
  const all = await getAllData();
  const d = all.find(x => x.id === id);
  if (!d) { alert("Data tidak ditemukan."); return; }

  // Build detail HTML
  let html = `<h3 style="margin:0 0 10px 0; color:var(--navy)">📄 Detail Laporan</h3>`;
  html += `<p><strong>Bulan:</strong> ${escapeHtml(d.bulan || "-")} &nbsp; <strong>Tahun:</strong> ${escapeHtml(d.tahun || "-")}</p>`;
  html += `<p><strong>Jenis:</strong> ${escapeHtml(d.jenis || "-")}</p>`;
  html += `<p><strong>Penanggung Jawab:</strong> ${escapeHtml(d.penanggung || "-")}</p>`;
  html += `<p><strong>Uraian:</strong><br>${nl2br(escapeHtml(d.uraian || "-"))}</p>`;
  html += `<p style="color:var(--muted); font-size:0.9rem">Dibuat: ${new Date(d.createdAt).toLocaleString()}</p>`;

  if (d.file) {
    // Tampilkan preview jika tipe mendukung
    const type = d.file.type || "";
    html += `<hr style="margin:8px 0">`;
    html += `<p><strong>File terlampir:</strong> ${escapeHtml(d.file.name)} (${Math.round((d.file.size||0)/1024)} KB)</p>`;
    // create objectURL
    try {
      const url = URL.createObjectURL(d.file.blob);
      if (type === "application/pdf") {
        html += `<div style="height:600px;"><iframe src="${url}" style="width:100%;height:100%;border:0;border-radius:8px;"></iframe></div>`;
      } else if (type.startsWith("image/")) {
        html += `<div style="text-align:center"><img src="${url}" alt="preview" style="max-width:100%; max-height:600px; border-radius:8px; box-shadow: 0 8px 20px rgba(16,24,64,0.06)"></div>`;
      } else {
        html += `<p>Preview tidak tersedia untuk tipe file ini. Anda dapat mengunduh file jika ingin membukanya.</p>`;
        html += `<p><a href="${url}" download="${encodeURIComponent(d.file.name)}" style="display:inline-block;margin-top:8px;" class="btn btn-primary">⬇️ Unduh File</a></p>`;
      }
      // keep URL for release when closing preview
      previewContent._lastURL = url;
    } catch(e){
      console.warn("preview error", e);
      html += `<p style="color:var(--muted)">Tidak dapat menampilkan preview file.</p>`;
    }
  }

  previewContent.innerHTML = html;
  openPreview();
}

/* unduh file */
async function unduhFile(id){
  const all = await getAllData();
  const d = all.find(x => x.id === id);
  if (!d || !d.file) { alert("File tidak ditemukan"); return; }
  const url = URL.createObjectURL(d.file.blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = d.file.name;
  document.body.appendChild(a);
  a.click();
  a.remove();
  setTimeout(()=>URL.revokeObjectURL(url), 1500);
}

/* hapus tunggal */
async function hapusData(id){
  if (!confirm("Hapus laporan ini?")) return;
  await deleteById(id);
  await render();
}

/* hapus semua */
async function hapusSemua(){
  if (!confirm("Hapus SEMUA laporan? Tindakan ini tidak dapat dibatalkan.")) return;
  await clearAll();
  await render();
}

/* ---------- Utilities ---------- */
function escapeHtml(str){
  if (str === null || str === undefined) return "";
  return String(str).replace(/[&<>"']/g, (s) => {
    const map = { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' };
    return map[s];
  });
}
function nl2br(str){ return String(str).replace(/\n/g, "<br>"); }

/* ---------- Event listeners ---------- */
searchInput.addEventListener("input", debounce(() => render(), 180));
window.addEventListener("click", (ev) => {
  // close modal when click outside content
  if (ev.target === modal) closeModal();
  if (ev.target === previewModal) closePreview();
});
window.addEventListener("beforeunload", () => {
  // revoke any existing created URL
  if (previewContent._lastURL) {
    URL.revokeObjectURL(previewContent._lastURL);
  }
});

/* simple debounce */
function debounce(fn, t){
  let timer;
  return function(...args){
    clearTimeout(timer);
    timer = setTimeout(()=>fn.apply(this, args), t);
  }
}

/* ---------- Init ---------- */
(async function init(){
  try {
    await openDB();
    await render();
  } catch(e){
    console.warn("Init error", e);
  }
})();
</script>
</body>
</html>
