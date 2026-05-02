<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/include/sidebar_access.php';
?>
<aside class="sidebar" id="sb">
    <div class="brand"><span class="dot"></span>
        <div class="name">PORTAL PPI</div>
    </div>
    <nav class="nav">
        <div class="section">Menu Utama</div>
        <a class="root-link active" href="<?= base_url('dashboard.php') ?>">
          <span class="menu-left">
            <span class="icon">🏠</span>
            <span>Dashboard</span>
          </span>
        </a>

        <?php if (ppi_sidebar_show_module('regulasi')): ?>
        <details data-type="reg">
            <summary>
                <span class="menu-left">
                    <span class="icon">📄</span>
                    <span>Regulasi</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('regulasi/referensi.php') ?>">Referensi</a></li>
                <li><a href="<?= base_url('regulasi/regulasi.php') ?>">SPO, Pedoman, Panduan</a></li>
                <li><a href="<?= base_url('regulasi/mou.php') ?>">MOU & Perizinan</a></li>
            </ul>
        </details>
        <?php endif; ?>

        <?php if (ppi_sidebar_show_module('komite')): ?>
        <details data-type="komite">
            <summary>
                <span class="menu-left">
                    <span class="icon">👥</span>
                    <span>Komite PPI</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('komite/kalender.php') ?>">Kalender PPI</a></li>
                <li><a href="<?= base_url('komite/sk.php') ?>">SK Komite</a></li>
                <li><a href="<?= base_url('komite/struktur.php') ?>">Struktur Komite</a></li>
                <li><a href="<?= base_url('komite/program.php') ?>">Program Komite</a></li>
                <li><a href="<?= base_url('komite/umanf.php') ?>">Uman Rapat</a></li>
            </ul>
        </details>
        <?php endif; ?>

        <?php if (ppi_sidebar_show_module('surveilance')): ?>
        <details data-type="surv">
            <summary>
                <span class="menu-left">
                    <span class="icon">🧪</span>
                    <span>Surveilans</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('surveilance/surveilancehais.php') ?>">HAIs (VAP, ISAK, IADP, IDO)</a></li>
                <li><a href="<?= base_url('surveilance/antibiotik.php') ?>">Antibiotik & MDRO</a></li>
                <li><a href="<?= base_url('surveilance/emerging.php') ?>">Infeksi Emerging</a></li>
            </ul>
        </details>
        <?php endif; ?>

        <?php if (ppi_sidebar_show_module('audit')): ?>
        <details data-type="audit">
            <summary>
                <span class="menu-left">
                    <span class="icon">📊</span>
                    <span>Audit dan Supervisi</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('audit/supervisi_ipcn.php') ?>">Supervisi</a></li>
                <li><a href="<?= base_url('audit/temuan_supervisi.php') ?>">Temuan Supervisi</a></li>
                <li><a href="<?= base_url('audit/audit_internal.php') ?>">Audit Internal</a></li>
                <li><a href="<?= base_url('audit/audit_eksternal.php') ?>">Audit Eksternal</a></li>
            </ul>
        </details>
        <?php endif; ?>

        <?php if (ppi_sidebar_show_module('diklat')): ?>
        <details data-type="diklat">
            <summary>
                <span class="menu-left">
                    <span class="icon">🎓</span>
                    <span>Diklat / Pelatihan</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('diklat/jadwalpelatihan.php') ?>">Jadwal</a></li>
                <li><a href="<?= base_url('diklat/pelatihan_terlaksana.php') ?>">Hasil Pelaksanaan</a></li>
                <li><a href="<?= base_url('diklat/sertifikat.php') ?>">Semua Sertifikat</a></li>
                <li><a href="<?= base_url('diklat/materi.php') ?>">Semua Materi</a></li>
            </ul>
        </details>
        <?php endif; ?>

        <?php if (ppi_sidebar_show_module('dokumen')): ?>
        <details data-type="doc">
            <summary>
                <span class="menu-left">
                    <span class="icon">📁</span>
                    <span>Dokumen / Media</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('dokumen/atk-formulir.php') ?>">Formulir & Brosur</a></li>
                <li><a href="<?= base_url('dokumen/foto_video.php') ?>">Foto & Video</a></li>
            </ul>
        </details>
        <?php endif; ?>

        <?php if (ppi_sidebar_show_module('laporan')): ?>
        <details data-type="lap">
            <summary>
                <span class="menu-left">
                    <span class="icon">📋</span>
                    <span>Laporan PPI</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('laporan/lap_bulanan.php') ?>">Bulanan</a></li>
                <li><a href="<?= base_url('laporan/lap_tahuanan.php') ?>">Quartal</a></li>
                                <li><a href="<?= base_url('laporan/lap_tahuanan.php') ?>">Tahunan</a></li>
                <li><a href="<?= base_url('laporan/lap_icraprogram.php') ?>">ICRA Program</a></li>
                <li><a href="<?= base_url('laporan/hasilkultur.php') ?>">Hasil Kultur</a></li>
            </ul>
        </details>
        <?php endif; ?>

        <?php if (ppi_sidebar_show_module('drive')): ?>
        <details data-type="drive">
            <summary>
                <span class="menu-left">
                    <span class="icon">☁️</span>
                    <span>Drive PPI</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="<?= base_url('drive/drive.php') ?>">DRIVE PPI</a></li>

            </ul>
        </details>
        <?php endif; ?>



        <div class="section">Lainnya</div>
        <?php if (ppi_sidebar_show_module('master')): ?>
        <a class="root-link" href="<?= base_url('master/master-app.php') ?>">
            <span class="menu-left">
                <span class="icon">🏥</span>
                <span>Master Aplikasi</span>
            </span>
        </a>
        <?php endif; ?>
        <ul style="list-style:none; padding:0; margin:0;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li>
                <a class="root-link" href="<?= base_url('users.php') ?>">
                    <span class="menu-left">
                        <span class="icon">👥</span>
                        <span>Manajemen User</span>
                    </span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a class="root-link" href="<?= base_url('logout.php') ?>">
                    <span class="menu-left">
                        <span class="icon">🚪</span>
                        <span>Logout</span>
                    </span>
                </a>
            </li>
        </ul>

    </nav>

    <div class="profile">
        <div class="avatar">A</div>
        <div><b>Administrator</b><br><small>ppi.bwp@primayahospital.com</small></div>
    </div>
</aside>