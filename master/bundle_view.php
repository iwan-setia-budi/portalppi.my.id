<?php
require_once __DIR__ . '/../config/assets.php';
include_once '../koneksi.php';
include "../cek_akses.php";

if(!isset($_GET['jenis'])){
    die("Jenis HAI tidak ditentukan");
}

$jenisKode = strtolower($_GET['jenis']);

// ambil id jenis
$stmt = $conn->prepare("SELECT id, kode FROM tb_jenis_hai WHERE LOWER(kode)=?");
$stmt->bind_param("s",$jenisKode);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Jenis HAI tidak ditemukan");
}

$jenis = $result->fetch_assoc();
$jenis_id = $jenis['id'];
$kode = strtoupper($jenis['kode']);

// ambil mapping
$query = $conn->prepare("
    SELECT m.*, b.nama_item
    FROM tb_bundle_mapping m
    JOIN tb_bundle_item b ON b.id = m.bundle_item_id
    WHERE m.jenis_hai_id = ?
    ORDER BY m.urutan ASC
");
$query->bind_param("i",$jenis_id);
$query->execute();
$data = $query->get_result();

$grouped = [];

while($row = $data->fetch_assoc()){
    if($jenisKode == "ido"){
        $key = $row['fase'] ?? 'lainnya';
    } else {
        $key = $row['kategori'] ?? 'lainnya';
    }
    $grouped[$key][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Bundle <?= $kode; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
    <style>

/* CONTAINER */

.bundle-container {
    padding: 28px 34px;
}

.bundle-hero {
    background: linear-gradient(135deg,#1e3a8a,#2563eb);
    padding: 32px;
    border-radius: 18px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 20px 45px rgba(37,99,235,.25);
}

.bundle-hero h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.bundle-hero p {
    margin-top: 6px;
    opacity: .85;
    font-size: 14px;
}

.bundle-empty {
    background: #fff3cd;
    padding: 20px;
    border-radius: 12px;
    color: #856404;
}

/* SECTION CARD */

.bundle-card {
    background: white;
    border-radius: 18px;
    padding: 28px;
    margin-bottom: 28px;
    box-shadow: 0 15px 35px rgba(0,0,0,.06);
    transition: .3s;
}

.bundle-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 45px rgba(0,0,0,.10);
}

.bundle-section-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 20px;
    text-transform: uppercase;
    color: #1e3a8a;
    letter-spacing: .05em;
}

/* ITEM LIST */

.bundle-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.bundle-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: .2s;
}

.bundle-item:hover {
    background: white;
    border-color: #2563eb;
    box-shadow: 0 6px 18px rgba(37,99,235,.15);
}

.bundle-number {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg,#2563eb,#1e40af);
    color: white;
    font-weight: 600;
    font-size: 14px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bundle-text {
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
}


/* HERO FLEX */

.bundle-hero {
    background: linear-gradient(135deg,#1e3a8a,#2563eb);
    padding: 32px;
    border-radius: 18px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 20px 45px rgba(37,99,235,.25);

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bundle-hero-left h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.bundle-hero-left p {
    margin-top: 6px;
    opacity: .85;
    font-size: 14px;
}

/* BUTTON BACK */

.btn-back-bundle {
    background: white;
    color: #1e3a8a;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 999px;
    font-weight: 600;
    transition: .25s;
    box-shadow: 0 6px 18px rgba(0,0,0,.2);
}

.btn-back-bundle:hover {
    transform: translateY(-3px);
    background: #f1f5f9;
}


/* RESPONSIVE */

@media(max-width:768px){
    .bundle-container {
        padding: 20px;
    }
    .bundle-hero {
        padding: 22px;
    }
    .bundle-card {
        padding: 20px;
    }
}


@media (max-width:768px){

    .bundle-hero {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .bundle-hero-right {
        width: 100%;
    }

    .btn-back-bundle {
        display: inline-block;
        width: 100%;
        text-align: center;
        padding: 12px 16px;
        border-radius: 12px;
    }
}

</style>
    
</head>
<body>

<div class="layout">
<?php include_once '../sidebar.php'; ?>
<main>
<?php include_once '../topbar.php'; ?>

<div class="bundle-container">

    <div class="bundle-hero">
    
        <div class="bundle-hero-left">
            <h1>📦 Bundle <?= $kode; ?></h1>
            <p>Standar pencegahan Healthcare Associated Infections</p>
        </div>
    
        <div class="bundle-hero-right">
            <a href="<?= base_url('master/master-data.php') ?>" class="btn-back-bundle">
                🏠 Kembali
            </a>
        </div>
    
    </div>

<?php if(empty($grouped)): ?>

    <div class="bundle-empty">
        ⚠️ Belum ada mapping bundle untuk jenis ini.
    </div>

<?php else: ?>

<?php foreach($grouped as $section => $items): ?>

    <div class="bundle-card">

        <div class="bundle-section-title">
            <?=
            $jenisKode == "ido"
            ? strtoupper(str_replace("_"," ",$section))
            : ucfirst($section);
            ?>
        </div>

        <div class="bundle-list">
            <?php $no = 1; ?>
            <?php foreach($items as $item): ?>

                <div class="bundle-item">
                    <div class="bundle-number"><?= $no++; ?></div>
                    <div class="bundle-text">
                        <?= htmlspecialchars($item['nama_item']); ?>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>

    </div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</main>

</div>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>

</body>
</html>