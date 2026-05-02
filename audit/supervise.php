<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";
require_once __DIR__ . '/../include/audit_delete_auth.php';
$ppiAuditCanDelete = ppi_audit_delete_allowed();

/* ================== SIMPAN DATA ================== */
if(isset($_POST['action']) && $_POST['action']=='save'){
    $tanggal = $_POST['tanggal'];
    $unit = $_POST['unit'];
    $temuan = $_POST['temuan'];
    $tindak = $_POST['tindak_lanjut'];
    $rekom = $_POST['rekomendasi'];

    $fotoName = null;
    if(!empty($_FILES['foto']['name'])){
        $fotoName = time().'_'.$_FILES['foto']['name'];
        move_uploaded_file($_FILES['foto']['tmp_name'],"../uploads/".$fotoName);
    }

    $stmt = $conn->prepare("INSERT INTO tb_supervise 
        (tanggal,unit,foto,temuan,tindak_lanjut,rekomendasi)
        VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss",$tanggal,$unit,$fotoName,$temuan,$tindak,$rekom);
    $stmt->execute();

    header("Location: supervise.php");
    exit;
}

/* ================== HAPUS ================== */
if(isset($_POST['action']) && $_POST['action']=='delete'){
    if (!ppi_audit_delete_allowed()) {
        header('Location: supervise.php?delete_denied=1');
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM tb_supervise WHERE id=?");
    $stmt->bind_param("i",$_POST['id']);
    $stmt->execute();
    header("Location: supervise.php");
    exit;
}

$where = [];

if(!empty($_GET['tahun'])){
    $tahun = $conn->real_escape_string($_GET['tahun']);
    $where[] = "YEAR(tanggal) = '$tahun'";
}

if(!empty($_GET['bulan'])){
    $bulan = (int)$_GET['bulan']; // paksa jadi angka
    $where[] = "MONTH(tanggal) = $bulan";
}


if(!empty($_GET['unit'])){
    $unit = $conn->real_escape_string($_GET['unit']);
    $where[] = "unit = '$unit'";
}

$sql = "SELECT * FROM tb_supervise";

if(count($where) > 0){
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY tanggal DESC";

$data = $conn->query($sql);


$tahunList = $conn->query("
    SELECT DISTINCT YEAR(tanggal) as tahun 
    FROM tb_supervise 
    ORDER BY tahun DESC
");

?>




<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "AUDIT DAN SUPERVISI";
?>
<!--end-->




<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisi PPI</title>
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <!-- === Link CSS eksternal === -->


        <style>
        
        
     /* ================= HEADER ================= */
        header {
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

        header div {
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
        
        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1/-1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
        }

        #formSupervise input,
        #formSupervise textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #cbd5e1;
            border-radius: 14px;
            font-size: 14px;
            background: #f8fafc;
            transition: all .25s ease;
        }

        #formSupervise input:hover,
        #formSupervise textarea:hover {
            border-color: #94a3b8;
        }

        #formSupervise input:focus,
        #formSupervise textarea:focus {
            outline: none;
            background: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
        }


    .tab-wrapper {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    
    .tab-btn {
        padding: 10px 18px;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        background: #e2e8f0;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    /* Mobile improvement */
    @media(max-width:576px) {
        header {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        .tab-wrapper {
            gap: 10px;
        }
    
        .tab-btn {
            flex: 1;
            text-align: center;
        }
    }

    
    .grafik-filter {
        display: flex;
        gap: 10px;
        margin: 15px 0 20px 0;
        flex-wrap: wrap;
    }
    
    .grafik-filter select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
    }

    
    /* ================= GRAFIK FIXED SIZE ================= */
        
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 420px; /* tinggi tetap */
            margin-top: 20px;
        }
        
        .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }

    
        .container-supervise {
            padding: 26px;
        }

        .tab-btn {
            padding: 10px 20px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            margin-right: 10px;
            background: #e2e8f0;
            font-weight: 600;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
        }

        .tab {
            display: none;
            margin-top: 20px;
        }

        .tab.active {
            display: block;
        }


/* ================= CARD INPUT ================= */
.card {
    background: var(--card);
    padding: 24px;
    border-radius: var(--radius);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}


.card h2 {
    margin-bottom: 22px;
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
}

/* ================= GRID FORM ================= */
#formSupervise {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 22px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full {
    grid-column: 1/-1;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #334155;
}

/* ================= INPUT STYLE ================= */
#formSupervise input,
#formSupervise textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #cbd5e1;
    border-radius: 14px;
    font-size: 14px;
    background: #f8fafc;
    transition: all .25s ease;
}

#formSupervise input:hover,
#formSupervise textarea:hover {
    border-color: #94a3b8;
}

#formSupervise input:focus,
#formSupervise textarea:focus {
    outline: none;
    background: white;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
}

/* ================= BUTTON ================= */
button.save {
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

#previewFoto {
    margin-top: 12px;
    max-height: 200px;
    object-fit: cover;
    transition: all .3s ease;
}


button.save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(34, 197, 94, .3);
}


textarea {
    resize: vertical;
    min-height: 100px;
}

.tab {
    margin-top: 28px;
}



        /* table */

        .table-box {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
        }

        thead {
            background: #1e40af;
            color: white;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .view {
            background: #2563eb;
            color: white;
        }

        .edit {
            background: #f59e0b;
            color: white;
        }

        .delete {
            background: #ef4444;
            color: white;
        }

        @media(max-width:576px) {
            table thead {
                display: none;
            }

            table,
            tbody,
            tr,
            td {
                display: block;
                width: 100%;
            }

            tr {
                margin-bottom: 15px;
                background: white;
                border-radius: 12px;
                padding: 10px;
            }

            td {
                display: flex;
                justify-content: space-between;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
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

                <div class="container-supervise">
                    
                     <header>
                        <div>💊 Audit dan Supervisi | PPI PHBW</div>
                        <button class="dashboard-btn" onclick="kembaliDashboard()">🏠 Kembali ke Dashboard</button>
                    </header>

                    <!-- TAB BUTTON -->
                    <div class="tab-wrapper">
                        <button class="tab-btn active" onclick="showTab('input', this)">🧾 Input Data</button>
                        <button class="tab-btn" onclick="showTab('hasil', this)">📋 Hasil Data</button>
                        <button class="tab-btn" onclick="showTab('grafik', this)">📊 Grafik</button>
                    </div>

                    <!-- ================= INPUT TAB ================= -->
                    <div id="input" class="tab active">
                        <div class="card">
                            <h2>Form Input Supervisi</h2>


                            <form method="post" enctype="multipart/form-data" id="formSupervise">
                                <input type="hidden" name="action" value="save">

                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="tanggal" required>
                                </div>

                                <div class="form-group">
                                    <label>Nama Unit</label>
                                    <input type="text" name="unit" required>
                                </div>

                                <div class="form-group full">
                                    <div class="form-group full">
                                        <label>Upload Foto</label>
                                        <input type="file" name="foto" id="fotoInput" accept="image/*">
                                        
                                        <img id="previewFoto" 
                                             style="display:none; margin-top:10px; max-width:250px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.1);" />
                                    </div>

                                    
                                </div>

                                <div class="form-group full">
                                    <label>Penjelasan Temuan</label>
                                    <textarea name="temuan" rows="3"></textarea>
                                </div>

                                <div class="form-group full">
                                    <label>Tindak Lanjut</label>
                                    <textarea name="tindak_lanjut" rows="3"></textarea>
                                </div>

                                <div class="form-group full">
                                    <label>Rekomendasi</label>
                                    <textarea name="rekomendasi" rows="3"></textarea>
                                </div>

                                <button type="submit" class="save">💾 Simpan Data</button>
                            </form>



                        </div>
                    </div>

                    <!-- ================= HASIL TAB ================= -->
                    <div id="hasil" class="tab">
                        <div class="card" style="margin-bottom:20px;">
    <form method="get" id="filterForm" 
      style="display:flex; gap:15px; flex-wrap:wrap; align-items:end;">


        <!-- Tahun -->
        <div>
            <label>Tahun</label>
            <select name="tahun" onchange="document.getElementById('filterForm').submit()">

                <option value="">Semua</option>
                <?php
                $tahunSelected = $_GET['tahun'] ?? '';
                $tahunList2 = $conn->query("
                    SELECT DISTINCT YEAR(tanggal) as tahun 
                    FROM tb_supervise 
                    ORDER BY tahun DESC
                ");
                while($t=$tahunList2->fetch_assoc()):
                ?>
                <option value="<?= $t['tahun']?>"
                    <?= ($tahunSelected==$t['tahun'])?'selected':'' ?>>
                    <?= $t['tahun']?>
                </option>
                <?php endwhile;?>
            </select>
        </div>

        <!-- Bulan -->
        <div>
            <label>Bulan</label>
            <select name="bulan" onchange="document.getElementById('filterForm').submit()">

                <option value="">Semua</option>
                <?php
                $bulanSelected = $_GET['bulan'] ?? '';
                for($i=1;$i<=12;$i++):
                    $val = str_pad($i,2,'0',STR_PAD_LEFT);
                ?>
                <option value="<?= $val ?>"
                    <?= ($bulanSelected==$val)?'selected':'' ?>>
                    <?= date("F", strtotime("2024-$val-01")) ?>
                </option>
                <?php endfor;?>
            </select>
        </div>

        <!-- Unit -->
        <div>
            <label>Unit</label>
            <select name="unit" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua</option>
                <?php
                $unitSelected = $_GET['unit'] ?? '';
                $unitList = $conn->query("
                    SELECT DISTINCT unit 
                    FROM tb_supervise 
                    ORDER BY unit ASC
                ");
                while($u=$unitList->fetch_assoc()):
                ?>
                <option value="<?= $u['unit']?>"
                    <?= ($unitSelected==$u['unit'])?'selected':'' ?>>
                    <?= $u['unit']?>
                </option>
                <?php endwhile;?>
            </select>
        </div>
    </form>
</div>

                        
                        <div class="table-box">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Unit</th>
                                        <th>Temuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row=$data->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Tanggal">
                                            <?= $row['tanggal']?>
                                        </td>
                                        <td data-label="Unit">
                                            <?= $row['unit']?>
                                        </td>
                                        <td data-label="Temuan">
                                            <?= substr($row['temuan'],0,40)?>...
                                        </td>
                                        <td data-label="Aksi">
                                            <button class="action-btn view">Lihat</button>
                                            <button class="action-btn edit">Edit</button>
                                            <?php if ($ppiAuditCanDelete): ?>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['id']?>">
                                                <button class="action-btn delete"
                                                    onclick="return confirm('Hapus data ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile;?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ================= GRAFIK TAB ================= -->

                    <div id="grafik" class="tab">
                        <div class="card">
                            <h3>Grafik Supervisi per Unit</h3>
                    
                            <!-- FILTER -->
                            <div class="grafik-filter">
                                <select id="filterTahun">
                                    <option value="">Semua Tahun</option>
                                    <?php while($t = $tahunList->fetch_assoc()): ?>
                                        <option value="<?= $t['tahun']; ?>">
                                            <?= $t['tahun']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                    
                                <select id="filterBulan">
                                    <option value="">Semua Bulan</option>
                                    <option value="01">Januari</option>
                                    <option value="02">Februari</option>
                                    <option value="03">Maret</option>
                                    <option value="04">April</option>
                                    <option value="05">Mei</option>
                                    <option value="06">Juni</option>
                                    <option value="07">Juli</option>
                                    <option value="08">Agustus</option>
                                    <option value="09">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                    
                            <div class="chart-wrapper">
                                <canvas id="grafikSupervise"></canvas>
                            </div>
                        </div>
                    </div>

                </div>

        </main>

    </div>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
/* ================= TAB ================= */

function showTab(id, btn){
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

    document.getElementById(id).classList.add('active');

    if(btn){
        btn.classList.add('active');
    }
}

/* ================= GRAFIK ================= */

let chartInstance;

function loadGrafik(){

    const bulan = document.getElementById('filterBulan')?.value || "";
    const tahun = document.getElementById('filterTahun')?.value || "";

    fetch(`grafik_supervise.php?bulan=${bulan}&tahun=${tahun}`)
    .then(res => res.json())
    .then(data => {

        const ctx = document.getElementById('grafikSupervise').getContext('2d');

        if(chartInstance){
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx,{
            type:'bar',
            data:{
                labels:data.labels,
                datasets:[{
                    label:'Jumlah Temuan',
                    data:data.values,
                    backgroundColor:'rgba(37, 99, 235, 0.7)',
                    borderRadius:8
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                scales:{
                    y:{ beginAtZero:true }
                }
            }
        });
    });
}

/* === FILTER EVENT === */

document.getElementById('filterBulan')?.addEventListener('change', loadGrafik);
document.getElementById('filterTahun')?.addEventListener('change', loadGrafik);

/* === LOAD PERTAMA === */
loadGrafik();


function kembaliDashboard() { window.location.href = "/dashboard.php"; }
</script>


<script>
document.getElementById('fotoInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('previewFoto');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});
</script>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const urlParams = new URLSearchParams(window.location.search);

    if(
        urlParams.has('tahun') ||
        urlParams.has('bulan') ||
        urlParams.has('unit')
    ){
        // aktifkan tab hasil
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

        document.getElementById('hasil').classList.add('active');
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
    }

});

</script>


</body>

</html>