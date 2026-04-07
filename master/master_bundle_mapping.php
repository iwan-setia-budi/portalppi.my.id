<?php
include_once '../koneksi.php';
include "../cek_akses.php";

// testter Eror
ini_set('display_errors', 1);
error_reporting(E_ALL);
// tester eror end



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$table = "tb_bundle_mapping";
$redirect = "master_bundle_mapping.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ===============================
   AMBIL DATA DROPDOWN
=============================== */

$jenisHAI = $conn->query("
    SELECT id, kode 
    FROM tb_jenis_hai
    ORDER BY kode ASC
");

$bundleItems = $conn->query("
    SELECT id, nama_item 
    FROM tb_bundle_item
    ORDER BY nama_item ASC
");

/* ===============================
   SIMPAN DATA
=============================== */

if(isset($_POST['save'])){

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: $redirect?msg=error");
        exit;
    }

    $jenis = (int)$_POST['jenis_hai_id'];
    $bundle = (int)$_POST['bundle_item_id'];
    $kategori = !empty($_POST['kategori']) ? $_POST['kategori'] : null;
    $fase     = !empty($_POST['fase']) ? $_POST['fase'] : null;
    $urutan = (int)($_POST['urutan'] ?? 0);

    if($jenis <=0 || $bundle <=0){
        header("Location: $redirect?msg=empty");
        exit;
    }

    // CEK DUPLIKAT
    $cek = $conn->prepare("
        SELECT id FROM $table
        WHERE jenis_hai_id=? AND bundle_item_id=?
        LIMIT 1
    ");
    $cek->bind_param("ii",$jenis,$bundle);
    $cek->execute();
    $cek->store_result();

    if($cek->num_rows > 0){
        header("Location: $redirect?msg=exist");
        exit;
    }
    
    // cek jenis HAI apakah IDO
    $cekJenis = $conn->prepare("SELECT kode FROM tb_jenis_hai WHERE id=?");
    $cekJenis->bind_param("i",$jenis);
    $cekJenis->execute();
    $cekJenis->bind_result($kode_hai);
    $cekJenis->fetch();
    $cekJenis->close();
    
    if(strtolower($kode_hai) === 'ido'){
        $kategori = null;
        if(empty($fase)){
            header("Location: $redirect?msg=empty");
            exit;
        }
    }else{
        $fase = null;
        if(empty($kategori)){
            header("Location: $redirect?msg=empty");
            exit;
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO $table
        (bundle_item_id, jenis_hai_id, kategori, fase, urutan)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iissi", $bundle, $jenis, $kategori, $fase, $urutan);
    $stmt->execute();

    header("Location: $redirect?msg=success");
    exit;
}

/* ===============================
   HAPUS
=============================== */

if(isset($_POST['delete'])){

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: $redirect?msg=error");
        exit;
    }

    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("DELETE FROM $table WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();

    header("Location: $redirect?msg=deleted");
    exit;
}

/* ===============================
   AMBIL DATA LIST
=============================== */

$data = $conn->query("
    SELECT m.id,
           j.kode,
           b.nama_item,
           m.kategori,
           m.fase,
           m.urutan
    FROM tb_bundle_mapping m
    JOIN tb_jenis_hai j ON j.id = m.jenis_hai_id
    JOIN tb_bundle_item b ON b.id = m.bundle_item_id
    ORDER BY j.kode, m.urutan ASC
");
?>



<!DOCTYPE html>
<html>
<head>
    <title>Master Bundle Mapping</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/css/utama.css?v=10">
    
    <style>
        /* ================= MASTER MAPPING ================= */

.container-master {
    padding: 26px;
}

.header-master {
    background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
    color: white;
    padding: 20px 24px;
    border-radius: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    font-weight: 600;
    font-size: 18px;
}

.btn-back {
    background: #22c55e;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    color: white;
    cursor: pointer;
}

.card {
    background: var(--card);
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.07);
    margin-bottom: 22px;
}

.card h3 {
    margin-bottom: 18px;
}

/* ===== FORM ===== */

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    align-items: center;
}

.form-grid select,
.form-grid input {
    padding: 10px 14px;
    border-radius: 10px;
    border: 2px solid #cbd5e1;
    background: #f8fafc;
    transition: 0.2s;
}

.form-grid select:focus,
.form-grid input:focus {
    outline: none;
    border-color: #2563eb;
    background: white;
    box-shadow: 0 0 0 4px rgba(37,99,235,.15);
}

.btn-save {
    background: linear-gradient(135deg,#16a34a,#22c55e);
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 10px;
    cursor: pointer;
}

/* ===== TABLE ===== */

.table-wrapper {
    max-height: 450px;
    overflow-y: auto;
    border-radius: 12px;
}

.mapping-table {
    width: 100%;
    border-collapse: collapse;
}

.mapping-table thead {
    background: #1e3a8a;
    color: white;
}

.mapping-table th {
    padding: 12px;
    text-align: center;
    position: sticky;
    top: 0;
    z-index: 5;
}

.mapping-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
}

.mapping-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.mapping-table tbody tr:hover {
    background: #e0f2fe;
    transition: 0.2s;
}

/* ===== BUTTON DELETE ===== */

.btn-delete {
    background: #ef4444;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-delete:hover {
    background: #dc2626;
}


.mapping-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
}

.mapping-table thead tr th:first-child {
    border-top-left-radius: 12px;
}
.mapping-table thead tr th:last-child {
    border-top-right-radius: 12px;
}



.search-box {
    margin-bottom: 16px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px;
    border-radius: 14px;
    border: 2px solid #cbd5e1;
    background: #f8fafc;
    transition: 0.25s;
}

.search-box input:focus {
    outline: none;
    background: white;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,.15);
}

/* ================= MOBILE OPTIMIZATION ================= */

@media (max-width: 768px) {

    .container-master {
        padding: 16px;
    }

    /* HEADER */
    .header-master {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
        font-size: 16px;
    }

    .btn-back {
        width: 100%;
        text-align: center;
    }

    /* FORM jadi 1 kolom penuh */
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-grid select,
    .form-grid input,
    .btn-save {
        width: 100%;
    }

    /* TABLE jadi card-style */
    .mapping-table thead {
        display: none;
    }

    .mapping-table,
    .mapping-table tbody,
    .mapping-table tr,
    .mapping-table td {
        display: block;
        width: 100%;
    }

    .mapping-table tr {
        margin-bottom: 16px;
        padding: 12px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,.05);
    }

    .mapping-table td {
        text-align: left;
        padding: 6px 0;
        border: none;
        font-size: 14px;
    }

    .mapping-table td::before {
        font-weight: 600;
        display: block;
        color: #64748b;
        font-size: 12px;
        margin-bottom: 2px;
    }

    .mapping-table td:nth-child(1)::before { content: "Jenis"; }
    .mapping-table td:nth-child(2)::before { content: "Item"; }
    .mapping-table td:nth-child(3)::before { content: "Kategori"; }
    .mapping-table td:nth-child(4)::before { content: "Fase"; }
    .mapping-table td:nth-child(5)::before { content: "Urutan"; }
    .mapping-table td:nth-child(6)::before { content: "Aksi"; }

    .btn-delete {
        width: 100%;
        margin-top: 6px;
    }
}

    </style>
    
</head>
<body>

<div class="layout">
<?php include_once '../sidebar.php'; ?>
<main>
<?php include_once '../topbar.php'; ?>

<div class="container-master">

    <header class="header-master">
        <div>📦 Master Bundle Mapping</div>
        <button onclick="window.location='/master/master-data.php'" class="btn-back">
            🔙 Kembali
        </button>
    </header>

    <div class="card">
        <h3>Tambah Mapping</h3>

        <form method="post" class="form-mapping">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="form-grid">

                <select name="jenis_hai_id" id="jenis_hai" required>
                <option value="">-- Pilih Jenis HAI --</option>
                <?php while($row = $jenisHAI->fetch_assoc()): ?>
                    <option 
                        value="<?= $row['id']; ?>" 
                        data-kode="<?= strtolower($row['kode']); ?>"
                    >
                        <?= strtoupper($row['kode']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

                <select name="bundle_item_id" required>
                    <option value="">-- Pilih Bundle Item --</option>
                    <?php while($item = $bundleItems->fetch_assoc()): ?>
                        <option value="<?= $item['id']; ?>">
                            <?= htmlspecialchars($item['nama_item']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <select name="kategori" id="kategori">
                    <option value="">Kategori</option>
                    <option value="insersi">Insersi</option>
                    <option value="maintenance">Maintenance</option>
                </select>

                <select name="fase" id="fase" style="display:none;">
                    <option value="">Fase</option>
                    <option value="pre_op">Pre OP</option>
                    <option value="intra_op">Intra OP</option>
                    <option value="post_op">Post OP</option>
                </select>

                <input type="number" name="urutan" placeholder="Urutan" value="0">

                <button type="submit" name="save" class="btn-save">Simpan</button>

            </div>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Mapping</h3>
        
        <div class="search-box">
            <input type="text" id="searchMapping" placeholder="Cari Mapping...">
        </div>

        <div class="table-wrapper">
            <table class="mapping-table">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Item</th>
                        <th>Kategori</th>
                        <th>Fase</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $data->fetch_assoc()): ?>
                        <tr>
                            <td><?= strtoupper($row['kode']); ?></td>
                            <td><?= htmlspecialchars($row['nama_item']); ?></td>
                            <td><?= !empty($row['kategori']) ? ucfirst($row['kategori']) : '-'; ?></td>
                            <td><?= !empty($row['fase']) ? ucfirst(str_replace('_',' ',$row['fase'])) : '-'; ?></td>
                            <td><?= $row['urutan']; ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <button name="delete" class="btn-delete">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.getElementById("jenis_hai").addEventListener("change", function() {

let selectedOption = this.options[this.selectedIndex];
let kode = selectedOption.getAttribute("data-kode");

if(kode === "ido") {
    document.getElementById("fase").style.display = "block";
    document.getElementById("fase").required = true;

    document.getElementById("kategori").style.display = "none";
    document.getElementById("kategori").required = false;
} else {
    document.getElementById("fase").style.display = "none";
    document.getElementById("fase").required = false;

    document.getElementById("kategori").style.display = "block";
    document.getElementById("kategori").required = true;
}
});
</script>

<script>
document.getElementById("searchMapping").addEventListener("keyup", function () {

    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll(".mapping-table tbody tr");

    rows.forEach(row => {

        let text = row.innerText.toLowerCase();

        if(text.includes(value)){
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });

});
</script>

</main>
</div>

    <script src="/assets/js/utama.js?v=5"></script>

</body>
</html>