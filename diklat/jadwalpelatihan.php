<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";

// ===============================
// MODE EDIT
// ===============================
$edit_mode = false;
$data_edit = null;

if(isset($_GET['edit'])){
    $edit_mode = true;
    $id_edit = (int)$_GET['edit'];

    $q = mysqli_query($conn,"SELECT * FROM tb_pelatihan WHERE id=$id_edit");
    $data_edit = mysqli_fetch_assoc($q);
}


// ===============================
// SIMPAN DATA KE DATABASE
// ===============================
if (isset($_POST['submit'])) {

    $id = $_POST['id'] ?? '';

    $tanggal = $_POST['tanggal'];
    $jenis_id = (int) $_POST['jenis_pelatihan_id'];

    $penyelenggara = mysqli_real_escape_string($conn,$_POST['penyelenggara']);
    $tempat = mysqli_real_escape_string($conn,$_POST['tempat']);
    $keterangan = mysqli_real_escape_string($conn,$_POST['keterangan']);

    $tanggal_baru = date('Y-m-d', strtotime($tanggal));

    if($id){
        $query = "UPDATE tb_pelatihan SET
        tanggal='$tanggal_baru',
        jenis_pelatihan_id='$jenis_id',
        penyelenggara='$penyelenggara',
        tempat='$tempat',
        keterangan='$keterangan'
        WHERE id='$id'";
    }else{
        $query = "INSERT INTO tb_pelatihan
        (tanggal,jenis_pelatihan_id,penyelenggara,tempat,keterangan)
        VALUES
        ('$tanggal_baru','$jenis_id','$penyelenggara','$tempat','$keterangan')";
    }

    mysqli_query($conn,$query);

    echo "<script>
    alert('Data berhasil disimpan');
    window.location.href='jadwalpelatihan.php';
    </script>";
}

// ===============================
// HAPUS DATA DARI DATABASE
// ===============================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $hapus = mysqli_query($conn, "DELETE FROM tb_pelatihan WHERE id=$id");

    if ($hapus) {
        echo "<script>
            alert('🗑️ Data berhasil dihapus!');
            window.location.href='jadwalpelatihan.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('❌ Gagal menghapus data!');
            window.history.back();
        </script>";
        exit;
    }
}
?>

<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "DIKLAT";

?>
<!--end-->

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jadwal Pelatihan PPI | PPI PHBW</title>
  
      <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  
  <style>
   

main {
    background: #f1f5f9;
    
}
    
    /* ============================= */
/* CONTAINER UTAMA */
/* ============================= */
.container.struktur {
    background: #ffffff;
    padding: 10px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    margin: 10px auto;
    width: calc(100% - 40px);
    max-width: 1400px;
}


/* ============================= */
/* HEADER JADWAL PELATIHAN */
/* ============================= */
.container.struktur header {
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    padding: 16px 22px;
    border-radius: 16px;
    margin: 0 0 25px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;

    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}

/* Judul */
.container.struktur header h1 {
    margin: 0;
    padding: 12px ;
    font-size: 1.1rem;
    font-weight: 600;
    color: #ffffff;
    letter-spacing: 0.3px;
}

/* Tombol dashboard */
.dashboard-btn {
    background: #ffffff;
    color: #1e3a8a;
    border: none;
    padding: 8px 15px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    
}

.dashboard-btn:hover {
    background: #f1f5ff;
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}

/* ============================= */
/* NAVBAR TAB */
/* ============================= */
.navbar {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    flex-wrap: wrap;
    -webkit-overflow-scrolling: touch;
}

.navbar button {
    background: #f1f5f9;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.25s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.navbar button:hover {
    background: #dbeafe;
}

.navbar button.active {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: #ffffff;
    box-shadow: 0 5px 15px rgba(37,99,235,0.25);
    transform: scale(1.03);
}



/* ============================= */
/* TAB CONTENT */
/* ============================= */
.tab {
    display: none;
    animation: fadeIn 0.3s ease;
}

.tab.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ============================= */
/* FORM STYLE */
/* ============================= */
.jadwal {
    width: 100%;
    padding: 20px 0;
}

.jadwal form {
    width: 100%;
    background: #ffffff;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}

.jadwal label {
    display: block;
    font-weight: 600;
    margin-top: 12px;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.jadwal h2 {
    margin-bottom: 15px;
}

.jadwal table,
#calendar {
    width: 100%;
}

.jadwal input,
.jadwal textarea {
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 0.9rem;
    transition: 0.2s ease;
}

.jadwal input:focus,
.jadwal textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
}

.tab {
    width: 100%;
}

/* Tombol Simpan */
button.save {
    margin-top: 18px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.25s ease;
}

button.save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
}

/* ============================= */
/* TABLE STYLE */
/* ============================= */
.jadwal table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}

.jadwal th {
    background: #2563eb;
    color: white;
    padding: 12px;
    text-align: left;
    font-size: 0.85rem;
}

.jadwal td {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.85rem;
}

.jadwal tr:hover {
    background: #f1f5f9;
}

/* ============================= */
/* DELETE BUTTON */
/* ============================= */
.delete-btn {
    background: #ef4444;
    border: none;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.2s ease;
}

.delete-btn:hover {
    background: #dc2626;
}

/* ============================= */
/* KALENDER */
/* ============================= */
#calendar {
    margin-top: 20px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 14px;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);
}


/* ============================= */
/* KALENDER TAHUNAN */
/* ============================= */

.year-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.month-box {
    background: #ffffff;
    width: 100%;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
}

.month-box h4 {
    text-align: center;
    margin-bottom: 10px;
    color: #1e3a8a;
}

.month-box table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
}

.month-box th {
    background: #2563eb;
    color: white;
    padding: 5px;
}

.month-box th:first-child {
    background: #dc2626;
}

.month-box td {
    padding: 6px;
    text-align: center;
    border: 1px solid #e2e8f0;
    position: relative;
}

.month-box td:first-child {
    background: #fef2f2;
}

.month-box td:first-child .date-number {
    color: #b91c1c;
    font-weight: 600;
}


.year-header {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.year-header h2 {
    margin: 0;
    color: #1e3a8a;
}

.year-header button {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: 0.2s ease;
}

.year-header button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}


.edit-btn{
    background:#f59e0b;
    color:white;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    margin-right:4px;
}

.edit-btn:hover{
    background:#d97706;
}

/* ============================= */
/* CSS TABEL KHUSUS KALENDER */
/* ============================= */



.calendar-table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

.calendar-table th {
    background: #1e3a8a;
    color: white;
    padding: 10px;
    font-size: 0.85rem;
}

.calendar-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.85rem;
}

.calendar-table tr:hover {
    background: #f1f5f9;
}

.month-box td {
    padding: 6px;
    text-align: center;
    border: 1px solid #e2e8f0;
}

.month-box td .date-number {
    display: inline-block;
    width: 26px;
    height: 26px;
    line-height: 26px;
    border-radius: 6px;
    transition: 0.2s ease;
}

/* Highlight jika ada event */
.month-box td.has-event .date-number {
    background: #2563eb;
    color: white;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(37,99,235,0.4);
}

.month-box td.has-event:first-child .date-number {
    background: #dc2626;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(220,38,38,0.35);
}

.month-box td.has-event:hover .date-number {
    background: #1e40af;
    transform: scale(1.1);
}

.month-box td.has-event:first-child:hover .date-number {
    background: #b91c1c;
}

/* Hover effect */
.month-box td.has-event:hover .date-number {
    background: #fca5a5;
}


/* ============================= */
/* RESPONSIVE */
/* ============================= */
@media (max-width: 768px) {
    .container.struktur header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .navbar {
        flex-direction: column;
    }

    .jadwal form {
        max-width: 100%;
    }

    .jadwal table {
        font-size: 0.75rem;
    }
}

@media (max-width: 768px) {
    .container.struktur header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .dashboard-btn {
        width: 100%;
        text-align: center;
    }
}

/* ============================= */
/* MOBILE OPTIMIZATION */
/* ============================= */
@media (max-width: 768px) {

    #calendar {
        padding: 10px 6px;
    }

    /* Container lebih rapat */
    .container.struktur {
        padding: 15px;
        width: 100%;
        margin: 0;
        border-radius: 0;
    }

    /* Header jadi vertikal */
    .container.struktur header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .dashboard-btn {
        width: 100%;
        text-align: center;
    }

    /* Navbar jadi 1 kolom */
    .navbar {
        flex-direction: column;
    }

    .navbar button {
        width: 100%;
        text-align: center;
    }

    /* Form lebih lega */
    .jadwal form {
        padding: 18px;
    }

    .jadwal input,
    .jadwal textarea {
        font-size: 1rem;
    }

    /* Table bisa scroll horizontal */
    .jadwal table,
    .calendar-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    /* Kalender grid jadi 1 kolom */
    .year-grid {
        grid-template-columns: 1fr;
    }

    /* Month box lebih lega */
    .month-box {
        padding: 12px;
    }

    /* Ukuran tanggal lebih besar */
    .month-box td .date-number {
        width: 30px;
        height: 30px;
        line-height: 30px;
        font-size: 0.8rem;
    }

}

@media (max-width: 480px) {

    .month-box table {
        font-size: 0.7rem;
    }

    .month-box td {
        padding: 4px;
    }

    .year-header {
        flex-direction: column;
        gap: 10px;
    }

}

@media (max-width: 768px){
    #calendar{
        padding:10px;
    }

    .month-box{
        margin:0;
    }

    .month-box h4{
        font-size:1rem;
    }
}

/* ============================= */
/* MOBILE PATCH (HP) */
/* ============================= */
@media (max-width: 900px) {
    .layout {
        display: block !important;
    }

    .sidebar {
        position: fixed !important;
        left: -260px;
        top: 0;
        height: 100%;
        z-index: 999;
        transition: left 0.3s ease !important;
        transform: none !important;
    }

    .sidebar.active,
    .sidebar.open {
        left: 0;
    }

    main {
        margin-left: 0 !important;
        width: 100% !important;
    }

    .container.struktur {
        width: 100% !important;
        margin: 0;
        border-radius: 0;
        padding: 12px;
    }

    .container.struktur header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        margin-bottom: 14px;
    }

    .container.struktur header h1 {
        font-size: 1rem;
        padding: 0;
    }

    .dashboard-btn {
        width: auto !important;
        max-width: 100%;
        text-align: center;
        padding: 10px 12px;
        white-space: normal;
        line-height: 1.3;
        align-self: stretch;
    }

    .navbar {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }

    .navbar button {
        width: 100% !important;
        min-width: 0;
        white-space: normal;
        font-size: 0.8rem;
        padding: 12px 6px;
        text-align: center;
        border-radius: 10px;
        line-height: 1.3;
    }

    .jadwal form {
        padding: 14px;
        border-radius: 12px;
    }

    .jadwal input,
    .jadwal textarea,
    .jadwal select {
        font-size: 16px !important;
    }

    .jadwal table,
    .calendar-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }

    .scroll-hint {
        display: block;
        margin: 4px 0 8px;
        font-size: 11px;
        color: #94a3b8;
        text-align: right;
    }

    .year-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .month-box {
        padding: 10px;
        border-radius: 10px;
    }

    .month-box table {
        font-size: 0.72rem;
    }

    .year-header {
        flex-direction: column;
        gap: 10px;
        margin-bottom: 14px;
    }

    .year-header button {
        width: 100%;
    }

    button.save {
        width: 100%;
        padding: 11px 14px;
    }
}

@media (max-width: 480px) {
    .container.struktur header h1 {
        font-size: 0.95rem;
        line-height: 1.3;
    }

    .month-box table {
        font-size: 0.85rem;
    }

    .month-box td {
        padding: 6px;
    }

    .month-box td .date-number {
        width: 32px;
        height: 32px;
        line-height: 32px;
        font-size: 0.85rem;
    }
}

/* ============================= */
/* FIX KALENDER MOBILE */
/* ============================= */
*,
*::before,
*::after {
    box-sizing: border-box;
}

#calendar,
.year-grid,
.month-box,
.month-box table {
    width: 100%;
}

.month-box {
    overflow-x: hidden;
}

.month-box table {
    display: table !important;
    table-layout: fixed !important;
    border-collapse: collapse;
    overflow-x: visible !important;
    white-space: normal !important;
}

.month-box th,
.month-box td {
    width: calc(100% / 7);
    padding: 5px 2px;
    text-align: center;
}

.month-box td .date-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.2em;
    height: 2.2em;
    border-radius: 6px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .container.struktur {
        width: 100% !important;
        max-width: 100% !important;
        padding: 12px;
    }

    #calendar {
        padding: 10px 0;
    }

    .year-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .month-box {
        width: 100%;
        padding: 10px;
    }

    .month-box table {
        font-size: 0.8rem;
    }
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

            <div class="container struktur"> 
            
            
    
              <header>
                <h1>📅 Jadwal Pelatihan PPI | PPI PHBW</h1>
                <button class="dashboard-btn" onclick="kembaliDashboard()">🏠 Kembali ke Dashboard</button>
              </header>
            
              <div class="navbar">
                <button onclick="showTab('kalender')">📆 Kalender Pelatihan</button>
                <button onclick="showTab('rekap')">📋 Daftar Jadwal</button>
                <button onclick="showTab('input')">🧾 Input Jadwal</button>

              </div>
            
              <div class="jadwal">
                <!-- TAB INPUT -->
                <div id="input" class="tab active">
                  <h2>🧾 Tambah / Edit Jadwal Pelatihan</h2>
                  <form method="POST" action="">

                <input type="hidden" name="id" value="<?= ($edit_mode && $data_edit) ? $data_edit['id'] : '' ?>">
                    <label>Tanggal Pelatihan</label>
                    <input type="date" name="tanggal" required
value="<?= ($edit_mode && $data_edit) ? $data_edit['tanggal'] : '' ?>">

            
                    <label>Nama Jenis Pelatihan</label>
                    <select name="jenis_pelatihan_id" required style="width:100%; padding:9px; border-radius:8px;">
                        <option value="">-- Pilih Jenis Pelatihan --</option>
                        <?php
                        $jenis = mysqli_query($conn, "SELECT * FROM tb_jenis_pelatihan WHERE status='aktif' ORDER BY nama_pelatihan ASC");
                        while($j = mysqli_fetch_assoc($jenis)){
                        
                        $selected = ($edit_mode && $data_edit['jenis_pelatihan_id']==$j['id']) ? "selected" : "";
                        
                        echo "<option value='{$j['id']}' $selected>{$j['nama_pelatihan']}</option>";
                        }
                                                ?>
                    </select>
            
                    <label>Penyelenggara</label>
                    <input type="text" name="penyelenggara"
value="<?= $edit_mode ? $data_edit['penyelenggara'] : '' ?>"
placeholder="Contoh: Komite PPI RS PHBW"
required>
            
                    <label>Tempat / Lokasi</label>
                    <input type="text" name="tempat"
value="<?= $edit_mode ? $data_edit['tempat'] : '' ?>"
placeholder="Contoh: Aula Utama RS PHBW"
required>
            
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Opsional: Narasumber atau catatan tambahan"><?= $edit_mode ? $data_edit['keterangan'] : '' ?></textarea>
            
                    <button type="submit" class="save" name="submit">💾 Simpan Jadwal</button>
                  </form>
                </div>
            
                <!-- TAB REKAP -->
                <div id="rekap" class="tab">
                  <h2>📋 Daftar Jadwal Pelatihan</h2>
                                    <p class="scroll-hint">Geser tabel ke samping jika kolom tidak muat</p>
                  <div style="overflow-x:auto;">
                  <table>
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Pelatihan</th>
                        <th>Penyelenggara</th>
                        <th>Tempat</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $no = 1;
                      $result = mysqli_query($conn, "
                        SELECT 
                            p.*, 
                            j.nama_pelatihan
                        FROM tb_pelatihan p
                        LEFT JOIN tb_jenis_pelatihan j 
                            ON p.jenis_pelatihan_id = j.id
                        ORDER BY p.tanggal DESC
                    ");
                      if (mysqli_num_rows($result) > 0) {
                          while ($row = mysqli_fetch_assoc($result)) {
                              echo "<tr>
                                      <td>{$no}</td>
                                      <td>{$row['tanggal']}</td>
                                      <td>{$row['nama_pelatihan']}</td>
                                      <td>{$row['penyelenggara']}</td>
                                      <td>{$row['tempat']}</td>
                                      <td>{$row['keterangan']}</td>
                                      <td>
                                        <a href='?edit={$row['id']}' class='edit-btn'>Edit</a>
                                        <button type='button' class='delete-btn' onclick='hapusData({$row['id']})'>Hapus</button>
                                      </td>
                                    </tr>";
                              $no++;
                          }
                      } else {
                          echo "<tr><td colspan='7' style='text-align:center;'>Belum ada data pelatihan</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                  </div>
                </div>
            
                <!-- TAB KALENDER -->
                <div id="kalender" class="tab">
                  <h2>📆 Kalender Pelatihan</h2>
                
                  <div id="calendar"></div>
                
                  <div id="yearTableWrapper"></div>
                </div>
              </div>
 
            </div>


        </main>

    </div>


<script src="<?= asset('assets/js/utama.js') ?>"></script>

<script>
  // ==============================
  // Navigasi Tab
  // ==============================
const defaultTab = <?= $edit_mode ? "'input'" : "'kalender'" ?>;

function showTab(tabId) {
  document.querySelectorAll('.navbar button').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

  document.querySelector(`.navbar button[onclick="showTab('${tabId}')"]`).classList.add('active');
  document.getElementById(tabId).classList.add('active');

  if (tabId === 'kalender') renderCalendar();
}

  function hapusData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
      window.location.href = '?hapus=' + id;
    }
  }

  function kembaliDashboard() {
    window.location.href = "/dashboard.php";
  }

  // ==============================
  // Data Pelatihan dari PHP
  // ==============================
  const pelatihanData = <?php
    $dataKalender = [];
    $query = mysqli_query($conn, "
    SELECT 
        p.*, 
        j.nama_pelatihan
    FROM tb_pelatihan p
    LEFT JOIN tb_jenis_pelatihan j 
        ON p.jenis_pelatihan_id = j.id
    ");
    while ($r = mysqli_fetch_assoc($query)) {
      $dataKalender[] = $r;
    }
    echo json_encode($dataKalender);
  ?>;

  // ==============================
  // Render Kalender Dinamis
  // ==============================
  let currentMonth = new Date().getMonth();
  let currentYear = new Date().getFullYear();

  function renderCalendar() {
  const monthNames = ["Januari","Februari","Maret","April","Mei","Juni",
                      "Juli","Agustus","September","Oktober","November","Desember"];
  
  let yearHTML = `
        <div class="year-header">
          <button onclick="changeYear(-1)">⬅</button>
          <h2>Kalender Tahun ${currentYear}</h2>
          <button onclick="changeYear(1)">➡</button>
        </div>
    <div class="year-grid">
  `;

  for (let month = 0; month < 12; month++) {

    const firstDay = new Date(currentYear, month, 1);
    const lastDay = new Date(currentYear, month + 1, 0);

    yearHTML += `
      <div class="month-box">
        <h4>${monthNames[month]} ${currentYear}</h4>
        <table class="calendar">
          <thead>
            <tr>
              <th>M</th><th>S</th><th>S</th><th>R</th><th>K</th><th>J</th><th>S</th>
            </tr>
          </thead>
          <tbody>
            <tr>
    `;

    let dayOfWeek = firstDay.getDay();
    for (let i = 0; i < dayOfWeek; i++) {
      yearHTML += "<td></td>";
    }

for (let date = 1; date <= lastDay.getDate(); date++) {

    const dateStr = `${currentYear}-${String(month + 1).padStart(2,"0")}-${String(date).padStart(2,"0")}`;
    const events = pelatihanData.filter(e => e.tanggal === dateStr);
    const hasEvent = events.length > 0;

    yearHTML += `
        <td class="${hasEvent ? 'has-event' : ''}">
            <span class="date-number">${date}</span>
        </td>
    `;

    if ((dayOfWeek + date) % 7 === 0) yearHTML += "</tr><tr>";
}

    yearHTML += `
            </tr>
          </tbody>
        </table>
      </div>
    `;
  }

  yearHTML += `</div>`;
  document.getElementById("calendar").innerHTML = yearHTML;
  
  renderYearTable();
}


function renderYearTable() {

  const filtered = pelatihanData.filter(item =>
      item.tanggal.startsWith(currentYear.toString())
  );

  let tableHTML = `
    <h2 style="margin-top:40px;">
      📋 Daftar Jadwal Tahun ${currentYear}
    </h2>
    <table class="calendar-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Nama</th>
          <th>Penyelenggara</th>
          <th>Tempat</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
  `;

  if (filtered.length === 0) {
      tableHTML += `
        <tr>
          <td colspan="6" style="text-align:center;">
            Tidak ada jadwal di tahun ${currentYear}
          </td>
        </tr>
      `;
  } else {

      filtered.sort((a,b)=> a.tanggal.localeCompare(b.tanggal));

      filtered.forEach((item,index)=>{
          tableHTML += `
            <tr>
              <td>${index+1}</td>
              <td>${item.tanggal}</td>
              <td>${item.nama_pelatihan ?? '-'}</td>
              <td>${item.penyelenggara}</td>
              <td>${item.tempat}</td>
              <td>${item.keterangan ?? '-'}</td>
            </tr>
          `;
      });
  }

  tableHTML += `</tbody></table>`;

  document.getElementById("yearTableWrapper").innerHTML = tableHTML;
}


function changeYear(offset) {
  currentYear += offset;
  renderCalendar();
}



  function changeMonth(offset) {
    currentMonth += offset;
    if (currentMonth < 0) {
      currentMonth = 11;
      currentYear--;
    } else if (currentMonth > 11) {
      currentMonth = 0;
      currentYear++;
    }
    renderCalendar();
  }

  // Auto render kalender pertama kali
//   renderCalendar();
  
  // Default tab saat halaman dibuka
document.addEventListener("DOMContentLoaded", function() {
    showTab(defaultTab);
});
  
</script>

</body>
</html>
