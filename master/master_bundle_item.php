<?php
include_once '../koneksi.php';
include "../cek_akses.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    die("Request tidak valid");
}

/* ===============================
   SETTING TETAP KHUSUS BUNDLE
=============================== */
$table = "tb_bundle_item";
$redirect = "master_bundle_item.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ===============================
   SIMPAN DATA
=============================== */
if(isset($_POST['action']) && $_POST['action']=='save'){

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: $redirect?msg=error");
        exit;
    }

    $nama = trim($_POST['nama_item'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $nama = preg_replace('/\s+/', ' ', $nama);

    if($nama === ''){
        header("Location: $redirect?msg=empty");
        exit;
    }

    if(mb_strlen($nama,'UTF-8') > 255){
        header("Location: $redirect?msg=toolong");
        exit;
    }

    // cek duplikat
    $cek = $conn->prepare("SELECT id,status FROM $table WHERE nama_item=? LIMIT 1");
    $cek->bind_param("s", $nama);
    $cek->execute();
    $result = $cek->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();

        if($row['status']=='nonaktif'){
            $update = $conn->prepare("UPDATE $table SET status='aktif' WHERE id=?");
            $update->bind_param("i",$row['id']);
            $update->execute();
            header("Location: $redirect?msg=reactivated");
            exit;
        } else {
            header("Location: $redirect?msg=exist");
            exit;
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO $table (nama_item, deskripsi, status)
        VALUES (?, ?, 'aktif')
    ");
    $stmt->bind_param("ss", $nama, $deskripsi);
    $stmt->execute();

    header("Location: $redirect?msg=success");
    exit;
}

/* ===============================
   UPDATE
=============================== */
if(isset($_POST['action']) && $_POST['action']=='update'){

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: $redirect?msg=error");
        exit;
    }

    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama_item']);
    $deskripsi = trim($_POST['deskripsi']);

    if($id<=0 || $nama==''){
        header("Location: $redirect?msg=error");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE $table 
        SET nama_item=?, deskripsi=? 
        WHERE id=? AND status='aktif'
    ");
    $stmt->bind_param("ssi",$nama,$deskripsi,$id);
    $stmt->execute();

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header("Location: $redirect?msg=updated");
    exit;
}

/* ===============================
   DELETE (Soft Delete)
=============================== */
if(isset($_POST['delete'])){

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: $redirect?msg=error");
        exit;
    }

    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("UPDATE $table SET status='nonaktif' WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header("Location: $redirect?msg=deleted");
    exit;
}

/* ===============================
   AMBIL DATA
=============================== */
$data = $conn->query("
    SELECT id, nama_item, deskripsi 
    FROM $table
    WHERE status='aktif'
    ORDER BY nama_item ASC
");

$pageTitle = "MASTER BUNDLE ITEM";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Master Bundle Item | PPI PHBW</title>

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
                    <div>📦 Master Bundle Item</div>
                    <button onclick="window.location='/master/master-data.php'" class="save">
                        🔙 Kembali
                    </button>
                </header>

                <div class="card">
                    <h2>Tambah Bundle Item</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="save">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">

                        <input type="text" name="nama_item" required placeholder="Nama Item Bundle">

                        <input type="text" name="deskripsi" placeholder="Deskripsi">
                    
                        <button type="submit" class="save">Tambah</button>
                    
                    </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Daftar Bundle Item</h2>
                    <div class="search-box">
                        <input type="text" id="search" placeholder="Cari Bundle Item...">
                    </div>

                    <div class="table-wrapper">
                        <table>

                            <thead>
                                <tr>
                                <th>Nama Item</th>
                                <th>Deskripsi</th>
                                <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row=$data->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_item']); ?></td>
                                    <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                                
                                    <td>
                                        <button class="edit"
                                            onclick="openEditModal(
                                                '<?= $row['id']; ?>',
                                                '<?= htmlspecialchars($row['nama_item']); ?>',
                                                '<?= htmlspecialchars($row['deskripsi']); ?>'
                                            )">
                                            Edit
                                        </button>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <button name="delete" class="delete"
                                                onclick="return confirm('Yakin ingin menghapus bundle item ini?')">
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
                    <h3>Edit Bundle Item</h3>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <input type="text" name="nama_item" id="editNama" required>
                        <input type="text" name="deskripsi" id="editDeskripsi">

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
        function openEditModal(id, nama, deskripsi) {
            document.getElementById("editId").value = id;
            document.getElementById("editNama").value = nama;
            document.getElementById("editDeskripsi").value = deskripsi;
            document.getElementById("editModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }
    </script>

</body>

</html>