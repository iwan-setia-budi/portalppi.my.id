<?php
require_once __DIR__ . '/../config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__ . '/../koneksi.php';
include __DIR__ . '/../cek_akses.php';
$conn = $koneksi;

$pageTitle = "AUDIT GIZI";
$activeTab = $_GET['tab'] ?? 'tab-form';
$message = '';

$opsiJawaban = [
  'ya' => 'Ya',
  'tidak' => 'Tidak',
  'na' => 'NA'
];

$checklistSections = [
  'D01' => [
    'title' => 'Penerimaan Bahan Makanan Mentah',
    'items' => [
      'Bahan makanan mentah dimasukkan ke tempat penyimpanan dalam keadaan bersih (sudah dicuci)',
      'Penyimpanan bahan makanan mentah tidak menggunakan wadah dari luar',
      'Kualitas bahan makanan mentah yang diantar dalam keadaan baik (tidak busuk/tidak bonyok)',
      'Ada pencatatan suhu bahan makanan mentah protein hewani dari supplier (suhu < 7 derajat C)'
    ]
  ],
  'D02' => [
    'title' => 'Higiene dan Sanitasi Gudang',
    'items' => [
      'Ada rotasi penyimpanan barang lama dan baru (FIFO/FEFO)',
      'Cantumkan tanggal buka kemasan',
      'Tidak ada barang kadaluarsa',
      'Penyimpanan barang rapi dan sesuai jenisnya',
      'Bebas binatang/serangga (kucing, kecoa, semut, tikus)',
      'Penempatan barang minimal 15 cm dari lantai, 60 cm dari langit-langit, 5 cm dari dinding',
      'Tidak ada bahan kimia berbahaya di gudang penyimpanan',
      'Kemasan bahan makanan selalu dalam keadaan tertutup'
    ]
  ],
  'D03' => [
    'title' => 'Kebersihan Dapur',
    'items' => [
      'Air memenuhi syarat air minum, tidak terkontaminasi',
      'Pembuangan air kotor lancar, tertutup rapat',
      'Tempat penyimpanan bahan makanan tertutup dan bersih',
      'Tidak ada genangan air',
      'Tempat sampah tertutup dan dioperasikan dengan pedal',
      'Bebas serangga dan tikus, semut, kecoa, kucing',
      'Lantai bersih dari debu dan sampah',
      'Lawa-lawa tidak ada dan water intrusion',
      'Lantai kering/tidak licin',
      'Wastafel cuci tangan selalu bersih dan bebas dari peralatan',
      'Rak penyimpanan bersih (tidak ada noda dan debu)',
      'Ada checklist pembersihan rutin',
      'Kain lap bersih',
      'Ada jadwal pembersihan ruangan gizi'
    ]
  ],
  'D04' => [
    'title' => 'Tenaga Pengolah',
    'items' => [
      'Kebersihan perseorangan baik, berkuku pendek',
      'Tidak memakai perhiasan tangan',
      'Selalu mencuci tangan sebelum menjamah makanan',
      'Memakai tutup kepala',
      'Memakai masker dengan benar',
      'Memakai celemek/apron',
      'Menggunakan alas kaki bagian depan tertutup rapat',
      'Menggunakan APD lengkap terutama saat menjamah makanan matang menggunakan alat (penjepit/garpu/sarung tangan plastik)'
    ]
  ],
  'D05' => [
    'title' => 'Proses Pengolahan',
    'items' => [
      'Petugas menggunakan APD',
      'Cara pengolahan makanan bersih',
      'Tempat persiapan dan meja peracikan bersih',
      'Tempat persiapan dan meja peracikan bebas dari kecoa, semut, tikus, kucing',
      'Peralatan pengolahan tidak dicampur aduk penggunaannya (talenan dan pisau)'
    ]
  ],
  'D06' => [
    'title' => 'Cara Pengangkutan Makanan',
    'items' => [
      'Alat pengangkutan makanan/kereta makan bersih (tidak bau)',
      'Makanan senantiasa dalam keadaan tertutup',
      'Suhu trolley food warmer sesuai standar',
      'Ada checklist pembersihan trolley harian dan mingguan'
    ]
  ],
  'D07' => [
    'title' => 'Penyimpanan Dingin',
    'items' => [
      'Sesuai bahan makanan',
      'Sampel makanan disimpan di lemari pendingin selama 3x24 jam pada suhu 2-10 C',
      'Suhu freezer sesuai standar -5 C s.d -15 C',
      'Suhu chiller sesuai standar 8 C s.d 10 C',
      'Isi lemari pendingin tidak penuh sesak dan tidak sering buka tutup',
      'Ada form pemantauan suhu',
      'Diisi secara rutin'
    ]
  ],
  'D08' => [
    'title' => 'Cara Penyajian Makanan',
    'items' => [
      'Kebersihan alat dan tempat di lokasi penyajian baik',
      'Higiene perorangan baik',
      'Teknik penyajian baik, makanan ditutup wrap'
    ]
  ],
  'D09' => [
    'title' => 'Alat Makan',
    'items' => [
      'Alat makan dicuci dengan detergen/sabun lalu dibilas',
      'Alat makan disimpan dalam keadaan tidak basah dan tidak ada jamur',
      'Penyimpanan alat makan tertata rapi',
      'Lakukan perendaman alat makan dengan air panas suhu 70 C'
    ]
  ]
];

if (isset($_POST['simpan'])) {
  $tanggalAudit = $_POST['tanggal_audit'] ?? '';
  $catatanAudit = trim($_POST['catatan_audit'] ?? '');
  $namaPetugasUnit = trim($_POST['nama_petugas_unit'] ?? '');
  $tandaTanganPetugas = '';
  $signatureData = $_POST['signature_data'] ?? '';
  $jawaban = $_POST['jawaban'] ?? [];

  if (!$tanggalAudit || !$namaPetugasUnit) {
    $message = '<div class="info-box error">Lengkapi tanggal audit dan nama petugas unit.</div>';
  } else {
    mysqli_begin_transaction($conn);
    try {
      if (!preg_match('/^data:image\/png;base64,/', $signatureData)) {
        throw new RuntimeException('Tanda tangan belum diisi.');
      }

      $uploadDir = __DIR__ . '/../uploads/audit_gizi/';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      $signatureBase64 = substr($signatureData, strpos($signatureData, ',') + 1);
      $signatureBinary = base64_decode(str_replace(' ', '+', $signatureBase64), true);
      if ($signatureBinary === false || strlen($signatureBinary) === 0) {
        throw new RuntimeException('Format tanda tangan tidak valid.');
      }

      $signatureFileName = 'ttd_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
      $signaturePathAbs = $uploadDir . $signatureFileName;
      if (file_put_contents($signaturePathAbs, $signatureBinary) === false) {
        throw new RuntimeException('Gagal menyimpan tanda tangan.');
      }

      $tandaTanganPetugas = 'uploads/audit_gizi/' . $signatureFileName;

      $stmt = mysqli_prepare($conn, "INSERT INTO audit_gizi (tanggal_audit, catatan_audit, nama_petugas_unit, tanda_tangan_petugas) VALUES (?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "ssss", $tanggalAudit, $catatanAudit, $namaPetugasUnit, $tandaTanganPetugas);
      mysqli_stmt_execute($stmt);
      $auditId = mysqli_insert_id($conn);

      $stmtDetail = mysqli_prepare($conn, "INSERT INTO audit_gizi_detail (audit_id, kode_bagian, urutan_item, item_text, jawaban) VALUES (?, ?, ?, ?, ?)");
      foreach ($checklistSections as $kode => $section) {
        foreach ($section['items'] as $idx => $item) {
          $urutan = $idx + 1;
          $jawab = $jawaban[$kode][$urutan] ?? 'na';
          if (!isset($opsiJawaban[$jawab])) {
            $jawab = 'na';
          }
          mysqli_stmt_bind_param($stmtDetail, "isiss", $auditId, $kode, $urutan, $item, $jawab);
          mysqli_stmt_execute($stmtDetail);
        }
      }

      if (!empty($_FILES['dokumentasi_foto']['name'][0])) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $maxFiles = 5;
        $maxSize = 10 * 1024 * 1024;

        $stmtFoto = mysqli_prepare($conn, "INSERT INTO audit_gizi_foto (audit_id, nama_file, path_file, ukuran_file) VALUES (?, ?, ?, ?)");
        $jumlahFile = min(count($_FILES['dokumentasi_foto']['name']), $maxFiles);

        for ($i = 0; $i < $jumlahFile; $i++) {
          if ($_FILES['dokumentasi_foto']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
          }
          $original = $_FILES['dokumentasi_foto']['name'][$i];
          $tmp = $_FILES['dokumentasi_foto']['tmp_name'][$i];
          $size = (int) $_FILES['dokumentasi_foto']['size'][$i];
          $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
          if (!in_array($ext, $allowedExt, true) || $size > $maxSize) {
            continue;
          }

          $newName = 'gizi_' . $auditId . '_' . time() . '_' . $i . '.' . $ext;
          $target = $uploadDir . $newName;
          if (move_uploaded_file($tmp, $target)) {
            $relativePath = 'uploads/audit_gizi/' . $newName;
            mysqli_stmt_bind_param($stmtFoto, "issi", $auditId, $original, $relativePath, $size);
            mysqli_stmt_execute($stmtFoto);
          }
        }
      }

      mysqli_commit($conn);
      $message = '<div class="info-box success">Data audit gizi berhasil disimpan.</div>';
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $message = '<div class="info-box error">Gagal menyimpan data audit gizi.</div>';
    }
  }
}

$keywordData = trim($_GET['keyword_data'] ?? '');
$filterBulan = $_GET['bulan'] ?? '';
$filterTahun = $_GET['tahun'] ?? '';
$whereData = [];
if ($filterBulan !== '') {
  $whereData[] = "MONTH(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filterBulan) . "'";
}
if ($filterTahun !== '') {
  $whereData[] = "YEAR(a.tanggal_audit) = '" . mysqli_real_escape_string($conn, $filterTahun) . "'";
}
if ($keywordData !== '') {
  $keywordEsc = mysqli_real_escape_string($conn, $keywordData);
  $whereData[] = "(a.nama_petugas_unit LIKE '%$keywordEsc%' OR a.catatan_audit LIKE '%$keywordEsc%')";
}
$whereDataSql = count($whereData) ? 'WHERE ' . implode(' AND ', $whereData) : '';

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$qTotalData = mysqli_query($conn, "SELECT COUNT(*) AS total FROM audit_gizi a $whereDataSql");
$totalData = mysqli_fetch_assoc($qTotalData)['total'] ?? 0;
$totalPages = max(1, ceil($totalData / $limit));

$qData = mysqli_query($conn, "
  SELECT
    a.*,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(d.id) AS denum
  FROM audit_gizi a
  LEFT JOIN audit_gizi_detail d ON a.id = d.audit_id
  $whereDataSql
  GROUP BY a.id
  ORDER BY a.tanggal_audit DESC, a.id DESC
  LIMIT $limit OFFSET $offset
");

$qRekapBagian = mysqli_query($conn, "
  SELECT
    d.kode_bagian,
    SUM(CASE WHEN d.jawaban = 'ya' THEN 1 ELSE 0 END) AS num,
    COUNT(*) AS denum
  FROM audit_gizi a
  JOIN audit_gizi_detail d ON a.id = d.audit_id
  GROUP BY d.kode_bagian
  ORDER BY d.kode_bagian ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Audit Gizi | PPI PHBW</title>
  <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">
  <style>
    :root {
      --bg: #eef3f7;
      --card: #ffffff;
      --ink: #0f172a;
      --line: rgba(148, 163, 184, 0.35);
      --primary: #1e40af;
      --primary-2: #1e3a8a;
      --ring: rgba(30, 64, 175, 0.15);
      --radius-lg: 20px;
      --radius-md: 14px;
      --shadow-md: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .audit-page {
      background: radial-gradient(900px 420px at 18% -10%, rgba(37, 99, 235, 0.12), transparent 62%), var(--bg);
      min-height: 100vh;
      color: var(--ink);
    }

    .audit-wrapper {
      width: 100%;
      margin: 20px auto 34px;
      padding: 0 14px;
      box-sizing: border-box;
    }

    .hero-header,
    .section-card {
      background: var(--card);
      border-radius: var(--radius-lg);
      border: 1px solid var(--line);
      box-shadow: var(--shadow-md);
      padding: 20px;
      margin-bottom: 14px;
    }

    .hero-header h1 {
      margin: 0;
      font-size: 30px;
      line-height: 1.15;
      letter-spacing: -0.3px;
    }

    .subtitle {
      color: #64748b;
      margin: 10px 0 0;
      font-size: 14px;
      line-height: 1.5;
      font-weight: 600;
    }

    .tab-menu {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .tab-btn {
      padding: 10px 16px;
      border-radius: 999px;
      text-decoration: none;
      background: #fff;
      border: 1px solid #cbd5e1;
      color: #0f172a;
      font-weight: 800;
      transition: all .2s ease;
    }

    .tab-btn.active {
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
      border-color: transparent;
      box-shadow: 0 8px 18px rgba(30, 64, 175, 0.24);
    }

    .form-control,
    textarea.form-control {
      width: 100%;
      border: 1.5px solid rgba(148, 163, 184, 0.62);
      border-radius: var(--radius-md);
      padding: 12px 14px;
      font-size: 15px;
      color: var(--ink);
      outline: none;
      transition: .2s ease;
      background: #fff;
      box-sizing: border-box;
    }

    .form-control:focus,
    textarea.form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px var(--ring);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      padding: 10px 14px;
      font-weight: 700;
      text-decoration: none;
      border: 1px solid transparent;
      cursor: pointer;
      transition: .2s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
    }

    .btn-warning {
      background: linear-gradient(135deg, #d97706, #b45309);
      color: #fff;
    }

    .btn-danger {
      background: linear-gradient(135deg, #dc2626, #b91c1c);
      color: #fff;
    }

    .btn-secondary {
      background: #fff;
      border-color: #cbd5e1;
      color: #0f172a;
    }

    .info-box {
      border-radius: 12px;
      padding: 12px 14px;
      margin-bottom: 10px;
      border: 1px solid #dbe3ee;
    }

    .info-box.success {
      background: #f0fdf4;
      color: #166534;
      border-color: #bbf7d0;
    }

    .info-box.error {
      background: #fef2f2;
      color: #991b1b;
      border-color: #fecaca;
    }

    @media (max-width: 768px) {
      .audit-wrapper {
        padding: 0 8px;
        margin-top: 14px;
      }

      .hero-header,
      .section-card {
        padding: 14px;
        border-radius: 12px;
      }

      .hero-header h1 {
        font-size: 24px;
      }

      .tab-menu {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 4px;
      }

      .tab-btn {
        white-space: nowrap;
        flex: 0 0 auto;
      }
    }
  </style>
</head>
<body class="audit-page">
  <div class="layout">
    <?php include_once '../sidebar.php'; ?>
    <main>
      <?php include_once '../topbar.php'; ?>
      <div class="audit-wrapper">
        <section class="hero-header">
          <h1>Audit Pelayanan Gizi</h1>
          <p class="subtitle">Form checklist audit gizi D01-D09, data audit, rekap, dan grafik.</p>
        </section>

        <?= $message ?>

        <div class="tab-menu">
          <a href="?tab=tab-form" class="tab-btn <?= $activeTab === 'tab-form' ? 'active' : '' ?>">Form</a>
          <a href="?tab=tab-data" class="tab-btn <?= $activeTab === 'tab-data' ? 'active' : '' ?>">Data</a>
          <a href="?tab=tab-rekap" class="tab-btn <?= $activeTab === 'tab-rekap' ? 'active' : '' ?>">Rekap</a>
          <a href="?tab=tab-grafik" class="tab-btn <?= $activeTab === 'tab-grafik' ? 'active' : '' ?>">Grafik</a>
        </div>

        <?php
        switch ($activeTab) {
          case 'tab-data':
            include __DIR__ . '/tabs_gizi/tab_data_audit.php';
            break;
          case 'tab-rekap':
            include __DIR__ . '/tabs_gizi/tab_rekap_audit.php';
            break;
          case 'tab-grafik':
            include __DIR__ . '/tabs_gizi/tab_grafik_audit.php';
            break;
          case 'tab-form':
          default:
            include __DIR__ . '/tabs_gizi/tab_form_audit.php';
            break;
        }
        ?>
      </div>
    </main>
  </div>
  <script src="<?= asset('assets/js/utama.js') ?>"></script>
</body>
</html>
