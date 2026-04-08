<?php
require_once __DIR__ . '/config/assets.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: " . base_url('login.php'));
  exit();
}

$username = $_SESSION['username'];
$pageTitle = "Profile";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3.0" />
    <title>Profile PPI PHBW</title>

    <link rel="stylesheet" href="<?= asset('assets/css/utama.css') ?>">

    <style>
        .container {
            padding: 30px;
        }

        /* HEADER */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .avatar-mini {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 20px;
        }

        .profile-header h2 {
            margin: 0;
        }

        .role-badge {
            display: inline-block;
            margin-top: 4px;
            font-size: 13px;
            padding: 4px 10px;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 999px;
        }

        /* GRID */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        /* CARD */
        .card-profile {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .06);
            transition: .2s;
        }

        .card-profile:hover {
            transform: translateY(-3px);
        }

        .card-profile h3 {
            margin-bottom: 20px;
            font-size: 16px;
        }

        /* INFO */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .status-active {
            color: #16a34a;
        }

        /* BUTTON */
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="layout">

        <?php include_once 'sidebar.php'; ?>

        <main>

            <?php include_once 'topbar.php'; ?>

            <div class="container">

                <!-- HEADER MINIMALIS -->
                <div class="profile-header">
                    <div class="avatar-mini">
                        <?= strtoupper(substr($username,0,1)); ?>
                    </div>
                    <div>
                        <h2>
                            <?= htmlspecialchars($username); ?>
                        </h2>
                        <span class="role-badge">Administrator</span>
                    </div>
                </div>


                <!-- GRID -->
                <div class="profile-grid">

                    <div class="card-profile">
                        <h3>Informasi Akun</h3>

                        <div class="info-row">
                            <span>Username</span>
                            <strong>
                                <?= htmlspecialchars($username); ?>
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Status</span>
                            <strong class="status-active">Aktif</strong>
                        </div>

                        <div class="info-row">
                            <span>Login Terakhir</span>
                            <strong>
                                <?= date("d M Y H:i"); ?>
                            </strong>
                        </div>

                    </div>


                    <div class="card-profile">
                        <h3>Keamanan</h3>

                        <button onclick="window.location='ganti_password.php'" class="btn-primary">
                            Ganti Password
                        </button>

                        <button onclick="window.location='logout.php'" class="btn-danger">
                            Logout
                        </button>

                    </div>

                </div>
            </div>


        </main>
    </div>

    <script src="<?= asset('assets/js/utama.js') ?>"></script>

</body>

</html>