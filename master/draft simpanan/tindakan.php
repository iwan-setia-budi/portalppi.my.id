<?php
include_once '../koneksi.php';
include "../cek_akses.php";

/* ===== SIMPAN ===== */
if(isset($_POST['action']) && $_POST['action']=='save'){
    $nama = trim($_POST['nama_tindakan']);

    if($nama != ''){
        $cek = $conn->prepare("SELECT id FROM tb_tindakan WHERE nama_tindakan=?");
        $cek->bind_param("s",$nama);
        $cek->execute();
        $cek->store_result();

        if($cek->num_rows == 0){
            $stmt = $conn->prepare("INSERT INTO tb_tindakan (nama_tindakan) VALUES (?)");
            $stmt->bind_param("s",$nama);
            $stmt->execute();
        }
    }

    header("Location: tindakan.php");
    exit;
}

/* ===== UPDATE ===== */
if(isset($_POST['action']) && $_POST['action']=='update'){
    $id = $_POST['id'];
    $nama = trim($_POST['nama_tindakan']);

    $stmt = $conn->prepare("UPDATE tb_tindakan SET nama_tindakan=? WHERE id=?");
    $stmt->bind_param("si",$nama,$id);
    $stmt->execute();

    header("Location: tindakan.php");
    exit;
}

/* ===== DELETE ===== */
if(isset($_POST['delete'])){
    $stmt = $conn->prepare("DELETE FROM tb_tindakan WHERE id=?");
    $stmt->bind_param("i",$_POST['id']);
    $stmt->execute();

    header("Location: tindakan.php");
    exit;
}

$data = $conn->query("SELECT * FROM tb_tindakan ORDER BY nama_tindakan ASC");

$pageTitle = "MASTER TINDAKAN";
include '../layout.php';
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Master Tindakan | PPI PHBW</title>

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
            box-shadow: var(--shadow-md);
        }

        .card {
            background: var(--card);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 10px;
        }

        .form-row input {
            flex: 1;
            padding: 12px 16px;
            border-radius: 14px;
            border: 2px solid #cbd5e1;
        }

        .form-row input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
        }

        .btn-save {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }

        .search-box {
            margin-bottom: 15px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .table-wrapper {
            max-height: 420px;
            overflow-y: auto;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            position: sticky;
            top: 0;
            background: #1e40af;
            color: white;
            z-index: 2;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 16px;
            width: 350px;
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
                    <div>🩺 Master Data Tindakan</div>
                    <button onclick="window.location='/master/master-data.php'" class="btn-save">
                        🔙 Kembali
                    </button>
                </header>

                <div class="card">
                    <h2>Tambah Tindakan</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="save">
                        <div class="form-row">
                            <input type="text" name="nama_tindakan" required placeholder="Masukkan Nama Tindakan">
                            <button type="submit" class="btn-save">Tambah</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Daftar Tindakan</h2>

                    <div class="search-box">
                        <input type="text" id="searchTindakan" placeholder="Cari tindakan...">
                    </div>

                    <div class="table-wrapper">
                        <table id="tindakanTable">
                            <thead>
                                <tr>
                                    <th>Nama Tindakan</th>
                                    <th width="160">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row=$data->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($row['nama_tindakan']); ?>
                                    </td>
                                    <td>
                                        <button class="btn-edit"
                                            onclick="openEditModal('<?= $row['id']; ?>','<?= htmlspecialchars($row['nama_tindakan']); ?>')">
                                            Edit
                                        </button>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <button name="delete" class="btn-delete"
                                                onclick="return confirm('Hapus tindakan ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile;?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <h3>Edit Tindakan</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <input type="text" name="nama_tindakan" id="editNama"
                            style="width:100%; padding:12px; border-radius:12px; border:2px solid #e2e8f0; margin:15px 0;"
                            required>
                        <button type="submit" class="btn-save">Update</button>
                        <button type="button" class="btn-delete" onclick="closeModal()">Batal</button>
                    </form>
                </div>
            </div>
        </main>
    </div>


    <script src="/assets/js/utama.js?v=5"></script>

    <script>
        document.getElementById("searchTindakan").addEventListener("keyup", function () {
            let value = this.value.toLowerCase();
            document.querySelectorAll("#tindakanTable tbody tr").forEach(row => {
                row.style.display =
                    row.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });

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