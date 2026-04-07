<?php
include_once '../koneksi.php';
include "../cek_akses.php";

/* ===== SIMPAN ===== */
if(isset($_POST['action']) && $_POST['action']=='save'){
    $nama = trim($_POST['nama_unit']);

    if($nama != ''){
        $cek = $conn->prepare("SELECT id FROM tb_unit WHERE nama_unit=?");
        $cek->bind_param("s",$nama);
        $cek->execute();
        $cek->store_result();

        if($cek->num_rows == 0){
            $stmt = $conn->prepare("INSERT INTO tb_unit (nama_unit) VALUES (?)");
            $stmt->bind_param("s",$nama);
            $stmt->execute();
        }
    }

    header("Location: unit.php");
    exit;
}

/* ===== UPDATE ===== */
if(isset($_POST['action']) && $_POST['action']=='update'){
    $id = $_POST['id'];
    $nama = trim($_POST['nama_unit']);

    $stmt = $conn->prepare("UPDATE tb_unit SET nama_unit=? WHERE id=?");
    $stmt->bind_param("si",$nama,$id);
    $stmt->execute();

    header("Location: unit.php");
    exit;
}

/* ===== DELETE ===== */
if(isset($_POST['delete'])){
    $stmt = $conn->prepare("DELETE FROM tb_unit WHERE id=?");
    $stmt->bind_param("i",$_POST['id']);
    $stmt->execute();

    header("Location: unit.php");
    exit;
}

$data = $conn->query("SELECT * FROM tb_unit ORDER BY nama_unit ASC");

$pageTitle = "MASTER UNIT";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Master Profesi | PPI PHBW</title>

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

        .card h2 {
            margin-bottom: 20px;
            font-size: 20px;
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
            transition: .2s;
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


        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-wrapper {
            max-height: 420px;
            /* kira-kira 10 baris */
            overflow-y: auto;
            border-radius: 12px;
        }

        /* header tetap sticky */
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
                    <div>🏥 Master Data Unit</div>
                    <button onclick="window.location='/master/master-data.php'" class="btn-save">
                        🏠 Kembali
                    </button>
                </header>

                <div class="card">
                    <h2>Tambah Unit</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="save">
                        <div class="form-row">
                            <input type="text" name="nama_unit" required placeholder="Masukkan Nama Unit">
                            <button type="submit" class="btn-save">Tambah</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Daftar Unit</h2>

                    <div class="search-box">
                        <input type="text" id="searchUnit" placeholder="Cari unit...">
                    </div>

                    <div class="table-wrapper">
                        <table id="unitTable">

                            <thead>
                                <tr>
                                    <th>Nama Unit</th>
                                    <th width="160">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row=$data->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($row['nama_unit']); ?>
                                    </td>
                                    <td>
                                        <button class="btn-edit"
                                            onclick="openEditModal('<?= $row['id']; ?>','<?= htmlspecialchars($row['nama_unit']); ?>')">
                                            Edit
                                        </button>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <button name="delete" class="btn-delete"
                                                onclick="return confirm('Hapus unit ini?')">
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
                    <h3>Edit Unit</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <input type="text" name="nama_unit" id="editNama"
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
        document.getElementById("searchUnit").addEventListener("keyup", function () {
            let value = this.value.toLowerCase();
            document.querySelectorAll("#unitTable tbody tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
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