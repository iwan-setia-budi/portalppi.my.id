<?php
$path = trim($_SERVER['REQUEST_URI'], "/");
$segments = explode("/", $path);

function cleanName($text) {
    return ucwords(str_replace(["-", "_"], " ", $text));
}
?>
<!-- ================== STYLES (paste ke file ini agar berdiri sendiri) ================== -->
<style>
/* Jika layout Anda memiliki sidebar, override --content-offset di root (mis. :root { --content-offset: 280px; }) */
:root {
    --content-offset: 0px; /* ubah di satu tempat jika ada sidebar (mis. 260px / 280px) */
}

/* Wrapper kecil agar breadcrumb tidak melebar ke tepi layar */
.breadcrumb-wrapper {
    box-sizing: border-box;
    margin-left: auto;
    padding: 0px;               /* jarak kiri/kanan dalam area konten */
    max-width: 1280px;
    margin-right: auto;
    margin-top: 14px;
}

/* Breadcrumb card */
.breadcrumb {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #f6f9ff;
    border: 1px solid #e2e9fb;
    color: #6b7c93;
    padding: 10px 14px;
    border-radius: 10px;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    font-size: 14px;
    line-height: 1;
    box-shadow: 0 6px 18px rgba(31, 64, 128, 0.04);
}

/* Home icon */
.breadcrumb .home {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: transparent;
    border-radius: 12px;
    font-weight: 600;
    color: #1a73e8;
}

/* Link style */
.breadcrumb a {
    color: #1a73e8;
    text-decoration: none;
    font-weight: 600;
}

/* separator */
.breadcrumb .sep {
    color: #98a6bf;
    margin: 0 6px;
}

/* long breadcrumbs wrap gracefully */
.breadcrumb .crumbs {
    display: inline-flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}

/* small screens: full width and smaller padding */
@media (max-width: 768px) {
    .breadcrumb-wrapper {
        margin-left: 0;
        padding: 0 12px;
    }
    .breadcrumb {
        width: 100%;
        padding: 10px;
        font-size: 13px;
        border-radius: 8px;
    }
}

/* DARK MODE */
body.dark-mode .breadcrumb {
    background: rgba(15, 23, 42, 0.85);
    border-color: rgba(56, 189, 248, .18);
    color: #94a3b8;
    box-shadow: 0 4px 16px rgba(0,0,0,.35);
}
body.dark-mode .breadcrumb a {
    color: #38bdf8;
}
body.dark-mode .breadcrumb .home {
    color: #38bdf8;
}
body.dark-mode .breadcrumb .sep {
    color: #334155;
}
</style>

<!-- ================== HTML / PHP BREADCRUMB ================== -->
<div class="breadcrumb-wrapper">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <div class="home" title="Home">🏠</div>

        <div class="crumbs">
            <a href="<?= base_url('dashboard.php') ?>">Home</a>

            <?php
            $pathAcc = "";
            foreach ($segments as $seg) {
                if ($seg === "" || strpos($seg, ".php") !== false) continue;
                $pathAcc .= "/$seg";
                echo '<span class="sep">/</span><a href="' . htmlspecialchars($pathAcc) . '">' . cleanName($seg) . '</a>';
            }
            ?>
        </div>
    </nav>
</div>
