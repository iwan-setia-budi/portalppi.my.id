<?php
require_once __DIR__ . '/../config/assets.php'; 
include_once '../koneksi.php';
include_once '../cek_akses.php';

/* ===== VALIDASI PARAMETER ===== */
if (!isset($_GET['tanggal']) || !isset($_GET['ket'])) {
    echo "<script>alert('Data tidak lengkap');history.back();</script>";
    exit;
}

$tanggal = mysqli_real_escape_string($koneksi, $_GET['tanggal']);
$ket     = mysqli_real_escape_string($koneksi, $_GET['ket']);

$fotos = mysqli_query($koneksi, "
    SELECT id, foto 
    FROM tb_audit_laundry_foto
    WHERE tanggal='$tanggal'
    AND keterangan='$ket'
    ORDER BY id DESC
");
?>


<!--Tulisan di topbar otomatis-->
<?php
$pageTitle = "LIHAT fFOTO AUDIT LAUNDRY";
?>
<!--end-->


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        <?php echo $pageTitle; ?> | PPI PHBW
    </title>

    <!-- === Link CSS eksternal === -->
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        /* ================= WRAPPER ================= */
        .foto-wrapper {
            max-width: 1100px;
            margin: 20px auto 40px auto;
            background: #fff;
            border-radius: 16px;
            padding: 24px 26px 30px;
            box-shadow: 0 12px 30px rgba(28, 71, 150, 0.10);
        }

        /* ================= HEADER ================= */
        .foto-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .foto-title h2 {
            margin: 0;
            color: #2b60d3;
            font-size: 22px;
        }

        .foto-meta {
            font-size: 13px;
            color: #607a92;
            margin-top: 6px;
            line-height: 1.6;
        }

        /* ================= BUTTON ================= */
        .btn-back {
            padding: 10px 18px;
            background: #2b60d3;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 6px 16px rgba(43, 96, 211, 0.35);
            transition: .2s ease;
        }

        .btn-back:hover {
            background: #1f4fb5;
            transform: translateY(-2px);
        }

        /* ================= GRID FOTO ================= */
        .foto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }

        .foto-card {
            background: #fff;
            border-radius: 14px;
            padding: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transition: .2s ease;
        }

        .foto-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.14);
        }

        .foto-card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            border-radius: 12px;
            display: block;
        }

        /* ================= EMPTY ================= */
        .foto-empty {
            text-align: center;
            padding: 50px;
            color: #999;
            font-size: 14px;
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

            <div class="main-content">
                <?php include '../breadcrumb.php'; ?>

                <div class="foto-wrapper">

                    <div class="foto-header">
                        <div class="foto-title">
                            <h2>Detail Foto Audit Laundry</h2>
                            <div class="foto-meta">
                                Tanggal: <b>
                                    <?= htmlspecialchars($tanggal) ?>
                                </b><br>
                                Keterangan: <b>
                                    <?= htmlspecialchars($ket) ?>
                                </b>
                            </div>
                        </div>

                        <a href="audit_laundry.php?tab=foto" class="btn-back">← Kembali</a>

                    </div>

                    <?php if (mysqli_num_rows($fotos) > 0): ?>
                    <div class="foto-grid">
                        <?php while($f = mysqli_fetch_assoc($fotos)): ?>
                        <div class="foto-card">
                            <a href="../uploads/audit_laundry/<?= $f['foto'] ?>" target="_blank">
                                <img src="../uploads/audit_laundry/<?= $f['foto'] ?>" alt="Foto Audit">
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="foto-empty">
                        Tidak ada foto untuk data ini.
                    </div>
                    <?php endif; ?>

                </div>

            </div>


        </main>

    </div>



    <script src="<?= asset('assets/js/utama.js') ?>"></script>



</body>

</html>