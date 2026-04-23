<?php
require_once __DIR__ . '/../../config/assets.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../koneksi.php';
include __DIR__ . '/../../cek_akses.php';

$conn = $koneksi;
$pageTitle = "EDIT AUDIT KEBERSIHAN TANGAN";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$profesiList = [
    "Dokter Spesialis",
    "Dokter Jaga",
    "Perawat/Bidan",
    "Analis",
    "Radiografer",
    "Fisioterapis",
    "P. Kebersihan",
    "P. Gizi",
    "P. Farmasi"
];

$ruanganList = [
    "UGD",
    "HD",
    "Poli",
    "OK",
    "VK",
    "ICU",
    "Perina",
    "St. Yosef",
    "St. Teresia",
    "St. Lukas",
    "St. Anna",
    "Radiologi",
    "Laboratorium",
    "Rehabilitasi Medik",
    "Farmasi",
    "Gizi/Dapur",
    "Cleaning Service"
];

if ($id <= 0) {
    die("ID audit tidak valid.");
}

$stmt = mysqli_prepare($conn, "
    SELECT *
    FROM audit_hand_hygiene
    WHERE id = ?
");

if (!$stmt) {
    die("Gagal menyiapkan query data audit.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    die("Data audit tidak ditemukan.");
}

$message = '';

if (isset($_POST['update'])) {
    $tanggal_audit = trim($_POST['tanggal_audit'] ?? '');
    $nama_petugas  = trim($_POST['nama_petugas'] ?? '');
    $profesi       = trim($_POST['profesi'] ?? '');
    $ruangan       = trim($_POST['ruangan'] ?? '');
    $keterangan    = trim($_POST['keterangan'] ?? '');

    if ($tanggal_audit === '' || $nama_petugas === '' || $profesi === '' || $ruangan === '') {
        $message = '<div class="alert alert-danger">Semua field wajib harus diisi.</div>';
    } elseif (!in_array($profesi, $profesiList, true)) {
        $message = '<div class="alert alert-danger">Profesi tidak valid.</div>';
    } elseif (!in_array($ruangan, $ruanganList, true)) {
        $message = '<div class="alert alert-danger">Ruangan tidak valid.</div>';
    } else {
        $stmtUpdate = mysqli_prepare($conn, "
            UPDATE audit_hand_hygiene
            SET tanggal_audit = ?, nama_petugas = ?, profesi = ?, ruangan = ?, keterangan = ?
            WHERE id = ?
        ");

        if (!$stmtUpdate) {
            $message = '<div class="alert alert-danger">Gagal menyiapkan query update.</div>';
        } else {
            mysqli_stmt_bind_param(
                $stmtUpdate,
                "sssssi",
                $tanggal_audit,
                $nama_petugas,
                $profesi,
                $ruangan,
                $keterangan,
                $id
            );

            if (mysqli_stmt_execute($stmtUpdate)) {
                mysqli_stmt_close($stmtUpdate);
                header("Location: detail_audit.php?id=" . $id . "&status=updated");
                exit;
            } else {
                $message = '<div class="alert alert-danger">Gagal memperbarui data audit.</div>';
                mysqli_stmt_close($stmtUpdate);
            }
        }
    }

    $data['tanggal_audit'] = $tanggal_audit;
    $data['nama_petugas'] = $nama_petugas;
    $data['profesi'] = $profesi;
    $data['ruangan'] = $ruangan;
    $data['keterangan'] = $keterangan;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Audit Kebersihan Tangan</title>
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body.audit-page {
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,0.12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(14,165,233,0.10), transparent 30%),
                linear-gradient(180deg, #eef4fb 0%, #e7eef8 100%);
            min-height: 100vh;
            color: #16325c;
        }

.page {
    width: 100%;
    padding: 20px 22px 32px;
}

.container {
    width: 100%;
    max-width: none;
    margin: 0;
}

.hero-card {
    background: linear-gradient(135deg, #173f95 0%, #2459cc 52%, #4d8dff 100%);
    border-radius: 24px;
    padding: 22px 24px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    box-shadow: 0 18px 36px rgba(37, 88, 190, 0.22);
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.hero-badge {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.22);
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 12px;
}

.hero-title {
    font-size: 28px;
    font-weight: 800;
    line-height: 1.15;
    margin-bottom: 8px;
}

.hero-subtitle {
    font-size: 15px;
    color: rgba(255,255,255,0.92);
    line-height: 1.55;
    max-width: 680px;
}


      .hero-id {
    min-width: 180px;
    text-align: center;
    padding: 18px 20px;
    border-radius: 20px;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.22);
}

.hero-id .id-number {
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 6px;
}

.hero-id .id-label {
    font-size: 16px;
    font-weight: 600;
}

      .form-card {
    background: rgba(255,255,255,0.92);
    border: 1px solid #d9e5f4;
    border-radius: 24px;
    box-shadow: 0 12px 28px rgba(30, 64, 128, 0.08);
    padding: 22px;
}

.section-title {
    font-size: 22px;
    font-weight: 800;
    color: #173f79;
    margin-bottom: 8px;
}

.section-subtitle {
    font-size: 14px;
    color: #617ba3;
    margin-bottom: 20px;
    line-height: 1.55;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

 .form-label {
    font-size: 14px;
    font-weight: 800;
    color: #23406d;
}

        .required {
            color: #e54848;
        }

.form-control,
.form-textarea {
    width: 100%;
    border: 1.5px solid #cbdcf0;
    background: linear-gradient(180deg, #ffffff, #f6faff);
    border-radius: 16px;
    padding: 13px 14px;
    font-size: 15px;
    color: #173f79;
    outline: none;
    transition: 0.2s ease;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
}

        .form-control:focus,
        .form-textarea:focus {
            border-color: #4d8dff;
            box-shadow: 0 0 0 4px rgba(77,141,255,0.12);
        }

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.action-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
}

.btn-modern {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 800;
    font-size: 14px;
    transition: 0.2s ease;
}

.mini-info {
    margin-top: 16px;
    padding: 14px 16px;
    border-radius: 16px;
    background: linear-gradient(180deg, #f8fbff, #eef5ff);
    border: 1px solid #dbe7f5;
    color: #5c759a;
    font-size: 13px;
    line-height: 1.7;
}

        .btn-primary {
            background: linear-gradient(135deg, #2459cc, #4d8dff);
            color: #fff;
            box-shadow: 0 10px 22px rgba(37, 88, 190, 0.22);
        }

        .btn-primary:hover,
        .btn-secondary:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: linear-gradient(180deg, #ffffff, #edf3fb);
            color: #24436c;
            border: 1px solid #c7d7ed;
        }

        .alert {
            padding: 16px 18px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #fff1f1;
            color: #c93535;
            border: 1px solid #f2b8b8;
        }



@media (max-width: 860px) {
    .page {
        padding: 18px 16px 28px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .hero-title {
        font-size: 24px;
    }

    .hero-subtitle {
        font-size: 14px;
    }
}

@media (max-width: 640px) {
    .page {
        padding: 14px;
    }

    .hero-card {
        padding: 18px 16px;
        border-radius: 20px;
        gap: 14px;
    }

    .hero-badge {
        font-size: 11px;
        padding: 7px 12px;
        margin-bottom: 10px;
    }

    .hero-title {
        font-size: 22px;
    }

    .hero-subtitle {
        font-size: 13px;
    }

    .hero-id {
        width: 100%;
    }

    .hero-id .id-number {
        font-size: 30px;
    }

    .hero-id .id-label {
        font-size: 15px;
    }

    .form-card {
        padding: 18px;
        border-radius: 20px;
    }

    .section-title {
        font-size: 20px;
    }

    .action-row {
        flex-direction: column;
    }

    .btn-modern {
        width: 100%;
    }
}
    </style>
</head>
<body class="audit-page">
    <div class="layout">

        <?php include_once __DIR__ . '/../../sidebar.php'; ?>

        <main>
            <?php include_once __DIR__ . '/../../topbar.php'; ?>

            <div class="page">
                <div class="container">

                    <div class="hero-card">
                        <div>
                            <div class="hero-badge">✏️ EDIT AUDIT KEBERSIHAN TANGAN</div>
                            <h1 class="hero-title">Perbarui Data Audit</h1>
                            <p class="hero-subtitle">
                                Ubah informasi data audit dengan tampilan yang lebih modern, nyaman, dan profesional.
                            </p>
                        </div>

                        <div class="hero-id">
                            <div class="id-number"><?= (int) $data['id'] ?></div>
                            <div class="id-label">Audit ID</div>
                        </div>
                    </div>

                    <div class="form-card">
                        <?= $message ?>

                        <h2 class="section-title">Form Edit Audit</h2>
                        <p class="section-subtitle">
                            Silakan perbarui data petugas, tanggal audit, profesi, ruangan, dan keterangan sesuai kebutuhan.
                        </p>

                        <form method="post">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tanggal Audit <span class="required">*</span></label>
                                    <input
                                        type="date"
                                        name="tanggal_audit"
                                        class="form-control"
                                        value="<?= htmlspecialchars($data['tanggal_audit'] ?? '') ?>"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Nama Petugas <span class="required">*</span></label>
                                    <input
                                        type="text"
                                        name="nama_petugas"
                                        class="form-control"
                                        value="<?= htmlspecialchars($data['nama_petugas'] ?? '') ?>"
                                        placeholder="Masukkan nama petugas"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Profesi <span class="required">*</span></label>
                                    <select name="profesi" class="form-control" required>
                                        <option value="">Pilih profesi</option>
                                        <?php foreach ($profesiList as $item): ?>
                                            <option value="<?= htmlspecialchars($item) ?>" <?= ($data['profesi'] ?? '') === $item ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($item) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Ruangan <span class="required">*</span></label>
                                    <select name="ruangan" class="form-control" required>
                                        <option value="">Pilih ruangan</option>
                                        <?php foreach ($ruanganList as $item): ?>
                                            <option value="<?= htmlspecialchars($item) ?>" <?= ($data['ruangan'] ?? '') === $item ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($item) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group full">
                                    <label class="form-label">Keterangan</label>
                                    <textarea
                                        name="keterangan"
                                        class="form-textarea"
                                        placeholder="Tambahkan keterangan bila diperlukan"><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="action-row">
                                <button type="submit" name="update" class="btn-modern btn-primary">💾 Simpan Perubahan</button>
                                <a href="detail_audit.php?id=<?= (int) $data['id'] ?>" class="btn-modern btn-secondary">← Kembali ke Detail</a>
                                <a href="../kebersihantangan.php?tab=tab-data" class="btn-modern btn-secondary">📋 Kembali ke Data</a>
                            </div>

                            <div class="mini-info">
                                Halaman ini memperbarui data utama audit. Kalau nanti kamu mau, edit detail observasi 5 moment juga bisa saya buatkan dalam halaman ini.
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>