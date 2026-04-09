<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";

function isAjaxRequest()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function respondTemuan($success, $message)
{
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
        exit;
    }

    if ($success) {
        header("Location: temuan_supervisi.php");
        exit;
    }

    echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "'); window.history.back();</script>";
    exit;
}

/* ================== SIMPAN DATA ================== */
if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $tanggal = $_POST['tanggal'];
    $unit = $_POST['unit'];
    $temuan = $_POST['temuan'];
    $tindak = $_POST['tindak_lanjut'];
    $rekom = $_POST['rekomendasi'];

    $fotoName = null;
    if (!empty($_FILES['foto']['name'])) {
        $safeOriginalName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename((string) $_FILES['foto']['name']));
        $fotoName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '_' . $safeOriginalName;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/" . $fotoName)) {
            respondTemuan(false, 'Upload foto gagal. Coba ulangi.');
        }
    }

    $stmt = $conn->prepare("INSERT INTO tb_supervise 
        (tanggal,unit,foto,temuan,tindak_lanjut,rekomendasi)
        VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss", $tanggal, $unit, $fotoName, $temuan, $tindak, $rekom);
    $ok = $stmt->execute();
    $stmt->close();

    respondTemuan($ok, $ok ? 'Data berhasil disimpan.' : 'Gagal menyimpan data.');
}

/* ================== UPDATE ================== */
if (isset($_POST['action']) && $_POST['action'] == 'update') {

    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $unit = $_POST['unit'];
    $temuan = $_POST['temuan'];
    $tindak = $_POST['tindak_lanjut'];
    $rekom = $_POST['rekomendasi'];

    // jika upload foto baru
    if (!empty($_FILES['foto']['name'])) {
        $safeOriginalName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename((string) $_FILES['foto']['name']));
        $fotoName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '_' . $safeOriginalName;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/" . $fotoName)) {
            respondTemuan(false, 'Upload foto baru gagal. Coba ulangi.');
        }

        $stmt = $conn->prepare("UPDATE tb_supervise 
            SET tanggal=?, unit=?, foto=?, temuan=?, tindak_lanjut=?, rekomendasi=? 
            WHERE id=?");
        $stmt->bind_param("ssssssi", $tanggal, $unit, $fotoName, $temuan, $tindak, $rekom, $id);
    } else {
        $stmt = $conn->prepare("UPDATE tb_supervise 
            SET tanggal=?, unit=?, temuan=?, tindak_lanjut=?, rekomendasi=? 
            WHERE id=?");
        $stmt->bind_param("sssssi", $tanggal, $unit, $temuan, $tindak, $rekom, $id);
    }

    $ok = $stmt->execute();
    $stmt->close();

    respondTemuan($ok, $ok ? 'Data berhasil diperbarui.' : 'Gagal memperbarui data.');
}



/* ================== HAPUS ================== */
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $stmt = $conn->prepare("DELETE FROM tb_supervise WHERE id=?");
    $stmt->bind_param("i", $_POST['id']);
    $ok = $stmt->execute();
    $stmt->close();
    respondTemuan($ok, $ok ? 'Data berhasil dihapus.' : 'Gagal menghapus data.');
}

$where = [];

if (!empty($_GET['tahun'])) {
    $tahun = $conn->real_escape_string($_GET['tahun']);
    $where[] = "YEAR(tanggal) = '$tahun'";
}

if (!empty($_GET['bulan'])) {
    $bulan = (int)$_GET['bulan']; // paksa jadi angka
    $where[] = "MONTH(tanggal) = $bulan";
}


if (!empty($_GET['unit'])) {
    $unit = $conn->real_escape_string($_GET['unit']);
    $where[] = "unit = '$unit'";
}

$sql = "SELECT * FROM tb_supervise";

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY tanggal DESC";

$dataRows = [];
$data = safe_query($conn, $sql);
if ($data) {
    while ($row = $data->fetch_assoc()) {
        $dataRows[] = $row;
    }
}

$tahunListRows = [];
$tahunList = safe_query($conn, "
    SELECT DISTINCT YEAR(tanggal) as tahun 
    FROM tb_supervise 
    ORDER BY tahun DESC
");
if ($tahunList) {
    while ($row = $tahunList->fetch_assoc()) {
        $tahunListRows[] = $row;
    }
}

$tahunList2Rows = [];
$tahunList2 = safe_query($conn, "
    SELECT DISTINCT YEAR(tanggal) as tahun 
    FROM tb_supervise 
    ORDER BY tahun DESC
");
if ($tahunList2) {
    while ($row = $tahunList2->fetch_assoc()) {
        $tahunList2Rows[] = $row;
    }
}

$unitListRows = [];
$unitList = safe_query($conn, "
    SELECT DISTINCT unit 
    FROM tb_supervise 
    ORDER BY unit ASC
");
if ($unitList) {
    while ($row = $unitList->fetch_assoc()) {
        $unitListRows[] = $row;
    }
}

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

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        /* ===== FILTER HASIL DATA ===== */
        #filterForm {
            background: #f1f5f9;
            padding: 20px 25px;
            border-radius: 18px;
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            align-items: center;
        }

        #filterForm label {
            font-weight: 600;
            font-size: 15px;
            margin-right: 8px;
        }

        #filterForm select {
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: white;
            font-size: 14px;
            transition: 0.2s ease;
        }

        #filterForm select:hover {
            border-color: #2563eb;
        }

        #filterForm select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
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

        .btn-pdf {
            margin-bottom: 20px;
            margin-top: 10px;
        }


        #formSupervise input,
        #formSupervise textarea,
        #formSupervise select {
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

        .aksi-col {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .aksi-col form {
            margin: 0;
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
            height: 420px;
            /* tinggi tetap */
            margin-top: 20px;
        }

        .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }


        .container-supervise {
            padding: 26px;
        }

        /* body.dark-mode .container-supervise {
            color: #e2e8f0;
        } */

        body.dark-mode #filterForm {
            background: #1e293b;
        }

        body.dark-mode #filterForm label,
        body.dark-mode .form-group label,
        body.dark-mode .card h2,
        body.dark-mode .card h3 {
            color: #e2e8f0;
        }

        body.dark-mode #filterForm select,
        body.dark-mode #formSupervise input,
        body.dark-mode #formSupervise textarea,
        body.dark-mode #formSupervise select {
            background: #0f172a;
            color: #e2e8f0;
            border-color: #334155;
        }

        body.dark-mode .card,
        body.dark-mode table,
        body.dark-mode tr {
            background: #111827;
            color: #e2e8f0;
        }

        body.dark-mode tbody tr:nth-child(even) {
            background: #1f2937;
        }

        body.dark-mode #modalLihat {
            background: rgba(0, 0, 0, .65);
        }

        body.dark-mode #modalLihat .modal-card {
            background: #111827;
            color: #e2e8f0;
        }

        body.dark-mode .dashboard-btn {
            background: linear-gradient(180deg, #ffffff, #dbeafe) !important;
            color: #0f172a !important;
            border: none !important;
            box-shadow: 0 10px 22px rgba(15, 23, 42, .22);
        }

        body.dark-mode .dashboard-btn:hover {
            background: linear-gradient(180deg, #ffffff, #eff6ff) !important;
            color: #0f172a !important;
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
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
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

        .save-status {
            grid-column: 1/-1;
            margin-top: -8px;
            font-size: 13px;
            color: #334155;
            min-height: 18px;
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
                    <div>💊 Temuan Supervisi | PPI PHBW</div>
                    <div class="header-actions">
                        <button class="dashboard-btn" onclick="kembaliDashboard()">🏠 Kembali ke Dashboard</button>
                    </div>
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

                        <?php
                        $editData = null;

                        if (isset($_GET['edit'])) {
                            $id = (int)$_GET['edit'];
                            $result = safe_query($conn, "SELECT * FROM tb_supervise WHERE id=$id");
                            if ($result) {
                                $editData = $result->fetch_assoc();
                            }
                        }
                        ?>




                        <form method="post" enctype="multipart/form-data" id="formSupervise">
                            <input type="hidden" name="action" value="<?= $editData ? 'update' : 'save' ?>">

                            <?php if ($editData): ?>
                                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                            <?php endif; ?>


                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" required value="<?= $editData['tanggal'] ?? '' ?>">

                            </div>

                            <div class="form-group">
                                <label>Nama Unit</label>

                                <?php
                                $unitList = $conn->query("SELECT nama_unit FROM tb_unit ORDER BY nama_unit ASC");
                                ?>

                                <select name="unit" required>
                                    <option value="">-- Pilih Unit --</option>

                                    <?php while ($u = $unitList->fetch_assoc()): ?>
                                        <option value="<?= $u['nama_unit']; ?>" <?= (isset($editData['unit']) &&
                                                                                    $editData['unit'] == $u['nama_unit']) ? 'selected' : '' ?>>
                                            <?= $u['nama_unit']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
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
                                <textarea name="temuan" rows="3"><?= $editData['temuan'] ?? '' ?></textarea>

                            </div>

                            <div class="form-group full">
                                <label>Tindak Lanjut</label>
                                <textarea name="tindak_lanjut"
                                    rows="3"><?= $editData['tindak_lanjut'] ?? '' ?></textarea>
                            </div>

                            <div class="form-group full">
                                <label>Rekomendasi</label>
                                <textarea name="rekomendasi" rows="3"><?= $editData['rekomendasi'] ?? '' ?></textarea>
                            </div>

                            <button type="submit" class="save">💾 Simpan Data</button>
                            <div id="saveStatus" class="save-status" aria-live="polite"></div>
                        </form>



                    </div>
                </div>

                <!-- ================= HASIL TAB ================= -->
                <div id="hasil" class="tab">

                    <div class="card" style="margin-bottom:20px;">
                        <h2 style="margin-bottom:0px; font-size:20px; font-weight:700;">
                            Data Hasil Supervisi
                        </h2>

                        <form method="get" id="filterForm"
                            style="display:flex; gap:15px; flex-wrap:wrap; align-items:end;">


                            <!-- Tahun -->
                            <div>
                                <label>Tahun</label>
                                <select name="tahun" onchange="document.getElementById('filterForm').submit()">

                                    <option value="">Semua</option>
                                    <?php
                                    $tahunSelected = $_GET['tahun'] ?? '';
                                    foreach ($tahunList2Rows as $t):
                                    ?>
                                        <option value="<?= $t['tahun'] ?>" <?= ($tahunSelected == $t['tahun']) ? 'selected' : '' ?>>
                                            <?= $t['tahun'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Bulan -->
                            <div>
                                <label>Bulan</label>
                                <select name="bulan" onchange="document.getElementById('filterForm').submit()">

                                    <option value="">Semua</option>
                                    <?php
                                    $bulanSelected = $_GET['bulan'] ?? '';
                                    for ($i = 1; $i <= 12; $i++):
                                        $val = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    ?>
                                        <option value="<?= $val ?>" <?= ($bulanSelected == $val) ? 'selected' : '' ?>>
                                            <?= date("F", strtotime("2024-$val-01")) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                                <tr>
                                    <th>Tanggal</th>
                                    <th>Unit</th>
                                    <th>Temuan</th>
                                    <th>Tindak Lanjut</th>
                                    <th>Rekomendasi</th>
                                    <th>Foto</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($dataRows as $row): ?>
                                    <tr>
                                        <td data-label="Tanggal">
                                            <?= $row['tanggal'] ?>
                                        </td>
                                        <td data-label="Unit">
                                            <?= $row['unit'] ?>
                                        </td>
                                        <td data-label="Temuan">
                                            <?= substr($row['temuan'], 0, 40) ?>...
                                        </td>

                                        <td data-label="Tindak Lanjut">
                                            <?= substr($row['tindak_lanjut'], 0, 40) ?>...
                                        </td>

                                        <td data-label="Rekomendasi">
                                            <?= substr($row['rekomendasi'], 0, 40) ?>...
                                        </td>

                                        <td>
                                            <?php if ($row['foto']): ?>
                                                <img src="../uploads/<?= $row['foto']; ?>" width="70">
                                            <?php endif; ?>
                                        </td>


                                        <td data-label="Aksi" class="aksi-col">


                                            <button class="action-btn view" data-tanggal="<?= $row['tanggal'] ?>"
                                                data-unit="<?= htmlspecialchars($row['unit']) ?>"
                                                data-temuan="<?= htmlspecialchars($row['temuan']) ?>"
                                                data-tindak="<?= htmlspecialchars($row['tindak_lanjut']) ?>"
                                                data-rekom="<?= htmlspecialchars($row['rekomendasi']) ?>"
                                                data-foto="<?= $row['foto'] ?>" onclick="lihatData(
        this.dataset.tanggal,
        this.dataset.unit,
        this.dataset.temuan,
        this.dataset.tindak,
        this.dataset.rekom,
        this.dataset.foto
    )">
                                                Lihat
                                            </button>




                                            <a href="?edit=<?= $row['id'] ?>" class="action-btn edit">
                                                Edit
                                            </a>

                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button class="action-btn delete"
                                                    onclick="return confirm('Hapus data ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="modalLihat" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); 
     justify-content:center; align-items:center;">

                            <div class="modal-card"
                                style="background:white; padding:25px; width:90%; max-width:500px; border-radius:16px;">
                                <h3>Detail Supervisi</h3>
                                <div id="isiModal"></div>
                                <br>
                                <button onclick="tutupModal()">Tutup</button>
                            </div>
                        </div>

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
                                <?php foreach ($tahunListRows as $t): ?>
                                    <option value="<?= $t['tahun']; ?>">
                                        <?= $t['tahun']; ?>
                                    </option>
                                <?php endforeach; ?>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>


    <script>
        /* ================= TAB ================= */

        function showTab(id, btn) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

            document.getElementById(id).classList.add('active');

            if (btn) {
                btn.classList.add('active');
            }
        }

        /* ================= GRAFIK ================= */

        let chartInstance;

        function loadGrafik() {

            const bulan = document.getElementById('filterBulan')?.value || "";
            const tahun = document.getElementById('filterTahun')?.value || "";

            fetch(`grafik_supervise.php?bulan=${bulan}&tahun=${tahun}`)
                .then(res => res.json())
                .then(data => {

                    const ctx = document.getElementById('grafikSupervise').getContext('2d');

                    if (chartInstance) {
                        chartInstance.destroy();
                    }

                    chartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Jumlah Temuan',
                                data: data.values,
                                backgroundColor: 'rgba(37, 99, 235, 0.7)',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
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


        function kembaliDashboard() {
            window.location.href = "/dashboard.php";
        }
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
        (function() {
            const form = document.getElementById('formSupervise');
            const statusEl = document.getElementById('saveStatus');
            const saveBtn = form ? form.querySelector('button.save') : null;

            if (!form || !saveBtn) {
                return;
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const xhr = new XMLHttpRequest();
                const action = formData.get('action');
                const submitLabel = action === 'update' ? 'Memperbarui...' : 'Menyimpan...';

                saveBtn.disabled = true;
                saveBtn.textContent = submitLabel;
                statusEl.textContent = 'Menyiapkan upload...';

                xhr.open('POST', window.location.pathname + window.location.search, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.onprogress = function(evt) {
                    if (evt.lengthComputable) {
                        const percent = Math.round((evt.loaded / evt.total) * 100);
                        statusEl.textContent = 'Upload ' + percent + '%';
                    }
                };

                xhr.onload = function() {
                    saveBtn.disabled = false;
                    saveBtn.textContent = '💾 Simpan Data';

                    let response = null;
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (err) {
                        response = null;
                    }

                    if (xhr.status >= 200 && xhr.status < 300 && response && response.success) {
                        statusEl.textContent = response.message || 'Berhasil.';

                        if (action === 'update') {
                            window.location.href = 'temuan_supervisi.php';
                            return;
                        }

                        form.reset();
                        const preview = document.getElementById('previewFoto');
                        if (preview) {
                            preview.style.display = 'none';
                            preview.removeAttribute('src');
                        }
                    } else {
                        statusEl.textContent = (response && response.message) ? response.message : 'Gagal menyimpan data.';
                    }
                };

                xhr.onerror = function() {
                    saveBtn.disabled = false;
                    saveBtn.textContent = '💾 Simpan Data';
                    statusEl.textContent = 'Koneksi terputus saat upload. Coba lagi.';
                };

                xhr.send(formData);
            });
        })();
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const urlParams = new URLSearchParams(window.location.search);

            if (
                urlParams.has('tahun') ||
                urlParams.has('bulan') ||
                urlParams.has('unit')
            ) {
                // aktifkan tab hasil
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

                document.getElementById('hasil').classList.add('active');
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
            }

        });
    </script>

    <script>
        function lihatData(tanggal, unit, temuan, tindak, rekom, foto) {

            let fotoHtml = '';

            if (foto) {
                fotoHtml = `
                    <div style="text-align:center; margin-bottom:15px;">
                        <img src="../uploads/${foto}" 
                            style="
                                max-width:100%;
                                max-height:280px;
                                width:auto;
                                height:auto;
                                border-radius:12px;
                                object-fit:contain;
                                box-shadow:0 4px 12px rgba(0,0,0,.15);
                            ">
                    </div>
                    `;


            }

            document.getElementById('isiModal').innerHTML = `
        ${fotoHtml}
        <p><strong>Tanggal:</strong> ${tanggal}</p>
        <p><strong>Unit:</strong> ${unit}</p>
        <p><strong>Temuan:</strong> ${temuan}</p>
        <p><strong>Tindak Lanjut:</strong> ${tindak}</p>
        <p><strong>Rekomendasi:</strong> ${rekom}</p>
    `;

            document.getElementById('modalLihat').style.display = 'flex';
        }

        function tutupModal() {
            document.getElementById('modalLihat').style.display = 'none';
        }
    </script>

    <script>
        window.exportPDFTemuan = async function() {

            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF("l", "pt", "a4");

            const rows = document.querySelectorAll("#hasil table tbody tr");

            let bodyData = [];

            rows.forEach(row => {

                if (row.style.display === "none") return;

                const btn = row.querySelector(".view");

                bodyData.push([
                    btn.dataset.tanggal,
                    btn.dataset.unit,
                    btn.dataset.temuan,
                    btn.dataset.tindak,
                    btn.dataset.rekom,
                    ""
                ]);
            });



            pdf.setFontSize(16);
            pdf.text("LAPORAN TEMUAN SUPERVISI PPI",
                pdf.internal.pageSize.getWidth() / 2,
                40, {
                    align: "center"
                }
            );

            pdf.setFontSize(10);
            pdf.text(
                "Dicetak: " + new Date().toLocaleDateString("id-ID"),
                pdf.internal.pageSize.getWidth() / 2,
                60, {
                    align: "center"
                }
            );

            // ===== Tambahkan Foto Setelah Table Dibuat =====
            pdf.autoTable({
                startY: 80,
                head: [
                    [
                        "Tanggal",
                        "Unit",
                        "Temuan",
                        "Tindak Lanjut",
                        "Rekomendasi",
                        "Foto"
                    ]
                ],
                body: bodyData,
                theme: "grid",
                styles: {
                    fontSize: 10,
                    cellPadding: 8,
                    valign: "middle",
                    overflow: 'linebreak',
                    minCellHeight: 75 // 👈 ini penting
                },




                columnStyles: {
                    0: {
                        cellWidth: 65
                    }, // Tanggal
                    1: {
                        cellWidth: 85
                    }, // Unit
                    2: {
                        cellWidth: 190
                    }, // Temuan (utama)
                    3: {
                        cellWidth: 160
                    }, // Tindak Lanjut
                    4: {
                        cellWidth: 150
                    }, // Rekomendasi
                    5: {
                        cellWidth: 115
                    } // Foto
                },




                didDrawCell: function(data) {

                    if (data.column.index === 5 && data.cell.section === 'body') {

                        const imgElement = rows[data.row.index].querySelector("img");

                        if (imgElement) {

                            let imgWidth = imgElement.naturalWidth;
                            let imgHeight = imgElement.naturalHeight;

                            const maxWidth = 60;
                            const maxHeight = 55;

                            let ratio = Math.min(maxWidth / imgWidth, maxHeight / imgHeight);

                            let newWidth = imgWidth * ratio;
                            let newHeight = imgHeight * ratio;

                            pdf.addImage(
                                imgElement.src,
                                "JPEG",
                                data.cell.x + (data.cell.width - newWidth) / 2,
                                data.cell.y + (data.cell.height - newHeight) / 2,
                                newWidth,
                                newHeight
                            );
                        }
                    }
                }
            });




            pdf.save("Laporan_Temuan_Supervisi.pdf");
        }
    </script>

    <!-- <script>
        (function() {
            const storageKey = 'temuan_supervisi_theme';
            const toggleBtn = document.getElementById('toggleDarkMode');

            function applyTheme(theme) {
                const isDark = theme === 'dark';
                document.body.classList.toggle('dark-mode', isDark);

                if (toggleBtn) {
                    toggleBtn.textContent = isDark ? '☀️ Mode Terang' : '🌙 Mode Gelap';
                    toggleBtn.setAttribute('aria-label', isDark ? 'Ubah ke mode terang' : 'Ubah ke mode gelap');
                }
            }

            const savedTheme = localStorage.getItem(storageKey);
            applyTheme(savedTheme === 'dark' ? 'dark' : 'light');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
                    localStorage.setItem(storageKey, nextTheme);
                    applyTheme(nextTheme);
                });
            }
        })();
    </script> -->


</body>

</html>