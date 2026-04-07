<?php
include_once '../koneksi.php';
include "../cek_akses.php";


// ===== CSRF TOKEN GENERATE =====

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protect direct post manipulation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST)) {
        die("Request tidak valid");
    }
}

$masters = [
    
    "unit" => [
        "table" => "tb_unit",
        "field" => "nama_unit",
        "judul" => "Unit"
    ],
    "profesi" => [
        "table" => "tb_profesi",
        "field" => "nama_profesi",
        "judul" => "Profesi"
    ],
    "tindakan" => [
        "table" => "tb_tindakan",
        "field" => "nama_tindakan",
        "judul" => "Tindakan"
    ],
    "laporan" => [
        "table" => "tb_jenis_laporan",
        "field" => "nama_laporan",
        "judul" => "Laporan"
    ],
    "materi" => [
        "table" => "tb_jenis_materi",
        "field" => "nama_materi",
        "judul" => "Materi"
    ],
    "pelatihan" => [
        "table" => "tb_jenis_pelatihan",
        "field" => "nama_pelatihan",
        "judul" => "Pelatihan"
    ],
    "dokumen" => [
        "table" => "tb_jenis_dokumen",
        "field" => "nama_dokumen",
        "judul" => "Dokumen"
    ],
    "rapat" => [
        "table" => "tb_jenis_rapat",
        "field" => "nama_rapat",
        "judul" => "Rapat"
    ],
    "referensi" => [
        "table" => "tb_jenis_referensi",
        "field" => "nama_referensi",
        "judul" => "Referensi"
    ],
    "elemen" => [
        "table" => "tb_jenis_elemen",
        "field" => "nama_elemen",
        "judul" => "Elemen"
    ],
    "form_brosur" => [
        "table" => "tb_form_brosur",
        "field" => "nama_form_brosur",
        "judul" => "Form dan Brosur"
    ],
    "kegiatan" => [
        "table" => "tb_kegiatan",
        "field" => "nama_kegiatan",
        "judul" => "Kegiatan"
    ],
    "jenis_regulasi" => [
        "table" => "tb_jenis_regulasi",
        "field" => "nama_regulasi",
        "judul" => "Regulasi"
    ],
    "sumber_referensi" => [
        "table" => "tb_sumber_referensi",
        "field" => "nama_sumber",
        "judul" => "Sumber Referensi"
    ],


];


$type = $_GET['type'] ?? '';

if ($type === '') {
    header("Location: master-data.php");
    exit;
}

if (!isset($masters[$type])) {
    die("Master tidak ditemukan");
}



$table = $masters[$type]['table'];
$field = $masters[$type]['field'];
$judul = $masters[$type]['judul'];


$redirect = "master.php?type=$type";



// Whitelist final check
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || 
    !preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
    die("Struktur tidak valid");
}





if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


/* ===== SIMPAN ===== */
/* ===== SIMPAN ===== */
if(isset($_POST['action']) && $_POST['action']=='save'){

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: $redirect&msg=error");
        exit;
    }

    $nama = trim($_POST[$field] ?? '');
    $nama = preg_replace('/\s+/', ' ', $nama);

    if($nama === ''){
        header("Location: $redirect&msg=empty");
        exit;
    }

    if(mb_strlen($nama, 'UTF-8') > 100){
        header("Location: $redirect&msg=toolong");
        exit;
    }

    // CEK DUPLIKAT
    $cek = $conn->prepare("
        SELECT id, status 
        FROM $table 
        WHERE $field=?
        LIMIT 1
    ");
    $cek->bind_param("s", $nama);
    $cek->execute();
    $result = $cek->get_result();

    if($result->num_rows > 0){

        $row = $result->fetch_assoc();

        if($row['status'] == 'nonaktif'){

            $update = $conn->prepare("
                UPDATE $table
                SET status='aktif'
                WHERE id=?
            ");
            $update->bind_param("i", $row['id']);
            $update->execute();

            header("Location: $redirect&msg=reactivated");
            exit;

        } else {

            header("Location: $redirect&msg=exist");
            exit;
        }
    }

    // INSERT BARU
    $stmt = $conn->prepare("
        INSERT INTO $table($field, status)
        VALUES (?, 'aktif')
    ");

    $stmt->bind_param("s", $nama);
    $stmt->execute();

    header("Location: $redirect&msg=success");
    exit;
}


/* ===== UPDATE ===== */
if(isset($_POST['action']) && $_POST['action']=='update'){

    // ==== VALIDASI CSRF ====
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: $redirect&msg=error");
        exit;
    }

    // ==== VALIDASI ID ====
    $id = (int)$_POST['id'];
    if($id <= 0){
        header("Location: $redirect&msg=error");
        exit;
    }

    // ==== VALIDASI NAMA ====
    $nama = trim($_POST[$field] ?? '');
    $nama = preg_replace('/\s+/', ' ', $nama);

    if($nama === ''){
            header("Location: $redirect&msg=empty");
            exit;
    }

    if(mb_strlen($nama, 'UTF-8') > 100){
        header("Location: $redirect&msg=toolong");
        exit;
    }

    // ==== CEK DATA LAMA ====
    $cek = $conn->prepare("
        SELECT $field, status 
        FROM $table 
        WHERE id=?
        LIMIT 1
    ");
    $cek->bind_param("i", $id);
    $cek->execute();
    $result = $cek->get_result();

    if($result->num_rows == 0){
        header("Location: $redirect&msg=notfound");
        exit;
    }

    $lama = $result->fetch_assoc();

    // Kalau status nonaktif, tidak boleh update
    if($lama['status'] !== 'aktif'){
        header("Location: master.php?type=$type&msg=inactive");
        exit;
    }

    // Kalau tidak ada perubahan
    if($lama[$field] === $nama){
        header("Location: master.php?type=$type&msg=nochange");
        exit;
    }

    // ==== CEK DUPLIKAT KE RECORD LAIN ====
    $cekDuplikat = $conn->prepare("
        SELECT id 
        FROM $table 
        WHERE $field=? 
        AND id<>?
        LIMIT 1
    ");
    $cekDuplikat->bind_param("si", $nama, $id);
    $cekDuplikat->execute();
    $dup = $cekDuplikat->get_result();

    if($dup->num_rows > 0){
        header("Location: master.php?type=$type&msg=exist");
        exit;
    }

    // ==== LAKUKAN UPDATE ====
    $stmt = $conn->prepare("
        UPDATE $table
        SET $field=? 
        WHERE id=? AND status='aktif'
    ");

    if(!$stmt){
        header("Location: $redirect&msg=error");
        exit;
    }

    $stmt->bind_param("si", $nama, $id);

    if($stmt->execute()){

        // Rotate token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Logging
        $user = $_SESSION['nama_user'] ?? 'Unknown';
        $aksi = "Update Jenis $judul";
        $ket  = "Mengubah kategori ID {$id} menjadi: {$nama}";

        $log = $conn->prepare("
            INSERT INTO log_aktivitas (nama_user, aksi, keterangan) 
            VALUES (?, ?, ?)
        ");
        if($log){
            $log->bind_param("sss", $user, $aksi, $ket);
            $log->execute();
        }

        header("Location: $redirect&msg=updated");
        exit;

    }

    header("Location: $redirect&msg=error");
    exit;
}

/* ===== DELETE ===== */
if(isset($_POST['delete'])){
    
    // Validasi CSRF
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {

    header("Location: $redirect&msg=error");
    exit;
}

    $id = (int)$_POST['id'];

    // Validasi ID
    if($id <= 0){
        header("Location: $redirect&msg=error");
        exit;
    }

    $stmt = $conn->prepare("UPDATE $table SET status='nonaktif' WHERE id=?");

    if(!$stmt){
        header("Location: $redirect&msg=error");
        exit;
    }

    $stmt->bind_param("i", $id);

if($stmt->execute()){

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    $user = $_SESSION['nama_user'] ?? 'Unknown';
    $aksi = "Hapus Jenis $judul";
    $ket = "Menghapus kategori ID: " . $id;

    $log = $conn->prepare("INSERT INTO log_aktivitas (nama_user, aksi, keterangan) VALUES (?, ?, ?)");
    if($log){
        $log->bind_param("sss", $user, $aksi, $ket);
        $log->execute();
    }

    header("Location: $redirect&msg=deleted");
    exit;
}
    
    else {
        header("Location: $redirect&msg=error");
    }

    exit;
}



$data = $conn->query("
    SELECT id, $field 
    FROM $table 
    WHERE status='aktif' 
    ORDER BY $field ASC
");

$pageTitle = "MASTER JENIS " . strtoupper($judul);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Master Jenis <?= $judul ?> | PPI PHBW</title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="/assets/css/utama.css?v=10">


    <style>
        .container-master {
            padding: 26px;
        }

        header {
            background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
            color: white;
            padding: 20px 24px;
            border-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .card {
            background: var(--card);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card h2 {
            margin-bottom: 20px;
            font-size: 20px;
        }

        input[type=text] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #cbd5e1;
            border-radius: 14px;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            /* ← TAMBAHKAN INI */
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 16px;
            width: 350px;
        }


        /* Wrapper scroll */
        .table-wrapper {
            max-height: 420px;
            /* kira2 10 baris */
            overflow-y: auto;
            border-radius: 12px;
        }

        /* Header sticky */
        thead th {
            position: sticky;
            top: 0;
            background: #1e40af;
            color: white;
            z-index: 10;
        }

        /* Border halus */
        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Hover modern */
        tbody tr:hover {
            background: #f1f5f9;
            transition: 0.2s;
        }


        button.edit {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 6px;
        }


        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
        }

        button.save {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            cursor: pointer;
        }

        button.delete {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
        }
    </style>

</head>

<body>


    <div class="layout">
        <?php include_once '../sidebar.php'; ?>
        <main>
            <?php include_once '../topbar.php'; ?>

            <div class="container-master">

                <header>
                    <div>📄 Master Jenis <?= $judul ?></div>
                    <button onclick="window.location='/master/master-data.php'" class="save">
                        🔙 Kembali
                    </button>
                </header>

                <div class="card">
                    <h2>Tambah <?= $judul ?></h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="save">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">

                        <input type="text" name="<?= $field ?>" required 
                            placeholder="Masukkan Nama <?= $judul ?>">
                    
                        <button type="submit" class="save">Tambah</button>
                    
                    </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Daftar <?= $judul ?></h2>
                    <div class="search-box">
                        <input type="text" id="search" placeholder="Cari <?= $judul ?>...">
                    </div>

                    <div class="table-wrapper">
                        <table>

                            <thead>
                                <tr>
                                <th>Nama <?= $judul ?></th>
                                <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row=$data->fetch_assoc()): ?>
                                <tr>
                                <td><?= htmlspecialchars($row[$field]); ?></td>
                                
                                    <td>
                                        <button class="edit"
                                            onclick="openEditModal('<?= $row['id']; ?>','<?= htmlspecialchars($row[$field]); ?>')">
                                            Edit
                                        </button>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <button name="delete" class="delete"
                                                onclick="return confirm('Yakin ingin menghapus <?= $judul ?> ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>

                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div id="editModal" class="modal">
                <div class="modal-content">
                    <h3>Edit <?= $judul ?></h3>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <input type="text" name="<?= $field ?>" id="editNama"
                            style="width:100%; padding:12px; margin:15px 0; border-radius:12px; border:2px solid #e2e8f0;"
                            required>

                        <button type="submit" class="save">Update</button>
                        <button type="button" class="delete" onclick="closeModal()">Batal</button>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script src="/assets/js/utama.js?v=5"></script>
    <script>
        document.getElementById("search").addEventListener("keyup", function () {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("table tbody tr");

            rows.forEach(row => {
                row.style.display =
                    row.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });
    </script>


    <script>
        function openEditModal(id, nama) {
            document.getElementById("editId").value = id;
            document.getElementById("editNama").value = nama;
            document.getElementById("editModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }
    </script>

</body>

</html>