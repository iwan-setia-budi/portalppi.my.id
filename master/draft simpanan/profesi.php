<?php
include_once '../koneksi.php';
include "../cek_akses.php";

/* ===== SIMPAN ===== */
if(isset($_POST['action']) && $_POST['action']=='save'){
    $nama = trim($_POST['nama_profesi']);

    $stmt = $conn->prepare("INSERT INTO tb_profesi (nama_profesi) VALUES (?)");
    $stmt->bind_param("s",$nama);
    $stmt->execute();

    header("Location: profesi.php");
    exit;
}

/* ===== UPDATE ===== */
if(isset($_POST['action']) && $_POST['action']=='update'){
    $id = $_POST['id'];
    $nama = trim($_POST['nama_profesi']);

    $stmt = $conn->prepare("UPDATE tb_profesi SET nama_profesi=? WHERE id=?");
    $stmt->bind_param("si",$nama,$id);
    $stmt->execute();

    header("Location: profesi.php");
    exit;
}


/* ===== DELETE ===== */
if(isset($_POST['delete'])){
    $stmt = $conn->prepare("DELETE FROM tb_profesi WHERE id=?");
    $stmt->bind_param("i",$_POST['id']);
    $stmt->execute();

    header("Location: profesi.php");
    exit;
}

$data = $conn->query("SELECT * FROM tb_profesi ORDER BY nama_profesi ASC");

$pageTitle = "MASTER PROFESI";
include '../layout.php';
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
            z-index: 9999;   /* ← TAMBAHKAN INI */
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
                    <div>👩‍⚕️ Master Profesi</div>
                    <button onclick="window.location='/master/master-data.php'" class="save">
                        🔙 Kembali
                    </button>
                </header>

                <div class="card">
                    <h2>Tambah Profesi</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="save">
                        <div style="display:flex; gap:10px;">
                            <input type="text" name="nama_profesi" required placeholder="Masukkan Nama Profesi">
                            <button type="submit" class="save">Tambah</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Daftar Profesi</h2>
                    <div class="search-box">
                        <input type="text" id="searchProfesi" placeholder="Cari profesi...">
                    </div>

                    <div class="table-wrapper">
                        <table>

                            <thead>
                                <tr>
                                    <th>Nama Profesi</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row=$data->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= $row['nama_profesi']; ?>
                                    </td>
                                    <td>
                                        <button class="edit"
                                            onclick="openEditModal('<?= $row['id']; ?>','<?= htmlspecialchars($row['nama_profesi']); ?>')">
                                            Edit
                                        </button>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <button name="delete" class="delete"
                                                onclick="return confirm('Hapus profesi ini?')">
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
                    <h3>Edit Profesi</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <input type="text" name="nama_profesi" id="editNama"
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
        document.getElementById("searchProfesi").addEventListener("keyup", function () {
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