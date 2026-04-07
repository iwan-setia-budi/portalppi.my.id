<aside class="sidebar" id="sb">
    <div class="brand"><span class="dot"></span>
        <div class="name">PORTAL PPI</div>
    </div>
    <nav class="nav">
        <div class="section">Menu Utama</div>
        <a class="root-link active" href="/dashboard.php">
          <span class="menu-left">
            <span class="icon">🏠</span>
            <span>Dashboard</span>
          </span>
        </a>

        <details data-type="reg">
            <summary>
                <span class="menu-left">
                    <span class="icon">📄</span>
                    <span>Regulasi</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/regulasi/referensi.php">Referensi</a></li>
                <li><a href="/regulasi/regulasi.php">SPO, Pedoman, Panduan</a></li>
                <li><a href="/regulasi/mou.php">MOU & Perizinan</a></li>
            </ul>
        </details>

        <details data-type="komite">
            <summary>
                <span class="menu-left">
                    <span class="icon">👥</span>
                    <span>Komite PPI</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/komite/kalender.php">Kalender PPI</a></li>
                <li><a href="/komite/sk.php">SK Komite</a></li>
                <li><a href="/komite/struktur.php">Struktur Komite</a></li>
                <li><a href="/komite/program.php">Program Komite</a></li>
                <li><a href="/komite/umanf.php">Uman Rapat</a></li>
            </ul>
        </details>

        <details data-type="surv">
            <summary>
                <span class="menu-left">
                    <span class="icon">🧪</span>
                    <span>Surveilans</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/surveilance/surveilancehais.php">HAIs (VAP, ISAK, IADP, IDO)</a></li>
                <li><a href="/surveilance/antibiotik.php">Antibiotik & MDRO</a></li>
                <li><a href="/surveilance/emerging.php">Infeksi Emerging</a></li>
            </ul>
        </details>

        <details data-type="audit">
            <summary>
                <span class="menu-left">
                    <span class="icon">📊</span>
                    <span>Audit dan Supervisi</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/audit/supervisi_ipcn.php">Supervisi</a></li>
                <li><a href="/audit/temuan_supervisi.php">Temuan Supervisi</a></li>
                <li><a href="/audit/audit_internal.php">Audit Internal</a></li>
                <li><a href="/audit/audit_eksternal.php">Audit Eksternal</a></li>
            </ul>
        </details>

        <details data-type="diklat">
            <summary>
                <span class="menu-left">
                    <span class="icon">🎓</span>
                    <span>Diklat / Pelatihan</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/diklat/jadwalpelatihan.php">Jadwal</a></li>
                <li><a href="/diklat/pelatihan_terlaksana.php">Hasil Pelaksanaan</a></li>
                <li><a href="/diklat/sertifikat.php">Semua Sertifikat</a></li>
                <li><a href="/diklat/materi.php">Semua Materi</a></li>
            </ul>
        </details>

        <details data-type="doc">
            <summary>
                <span class="menu-left">
                    <span class="icon">📁</span>
                    <span>Dokumen / Media</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/dokumen/atk-formulir.php">Formulir & Brosur</a></li>
                <li><a href="/dokumen/foto_video.php">Foto & Video</a></li>
            </ul>
        </details>

        <details data-type="lap">
            <summary>
                <span class="menu-left">
                    <span class="icon">📋</span>
                    <span>Laporan PPI</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/laporan/lap_bulanan.php">Bulanan</a></li>
                <li><a href="/laporan/lap_tahuanan.php">Quartal</a></li>
                                <li><a href="/laporan/lap_tahuanan.php">Tahunan</a></li>
                <li><a href="/laporan/lap_icraprogram.php">ICRA Program</a></li>
                <li><a href="/laporan/hasilkultur.php">Hasil Kultur</a></li>
            </ul>
        </details>

        <details data-type="drive">
            <summary>
                <span class="menu-left">
                    <span class="icon">☁️</span>
                    <span>Drive PPI</span>
                </span>
                <span class="chev">▶</span>
            </summary>
            <ul>
                <li><a href="/drive/drive.php">DRIVE PPI</a></li>

            </ul>
        </details>



        <div class="section">Lainnya</div>
        <a class="root-link" href="/master/master-app.php">
            <span class="menu-left">
                <span class="icon">🏥</span>
                <span>Master Aplikasi</span>
            </span>
        </a>
        <ul style="list-style:none; padding:0; margin:0;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li>
                <a class="root-link" href="/users.php">
                    <span class="menu-left">
                        <span class="icon">👥</span>
                        <span>Manajemen User</span>
                    </span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a class="root-link" href="/logout.php">
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