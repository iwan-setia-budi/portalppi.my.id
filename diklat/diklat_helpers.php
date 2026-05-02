<?php

declare(strict_types=1);

/**
 * Helper khusus modul Uman Diklat (tanggal, folder upload, format tampilan).
 */

function ppi_diklat_bulan_nama(int $bulan): string
{
    $nama = [
        1 => 'januari', 2 => 'februari', 3 => 'maret', 4 => 'april',
        5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'agustus',
        9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'desember',
    ];
    return $nama[max(1, min(12, $bulan))] ?? 'bulan';
}

function ppi_diklat_slug_folder(string $nama): string
{
    $nama = trim(mb_strtolower($nama, 'UTF-8'));
    $nama = (string) preg_replace('/[^\p{L}\p{N}]+/u', '-', $nama);
    $nama = trim((string) preg_replace('/-+/', '-', $nama), '-');
    if ($nama === '' || $nama === null) {
        return 'diklat';
    }
    return mb_substr($nama, 0, 80, 'UTF-8');
}

/**
 * Path relatif dari folder uploads/, tanpa slash depan/belakang.
 * Contoh: diklat/2026/05-mei/02-05-2026_12_pelatihan-ppi-dasar
 */
function ppi_diklat_upload_rel_dir(string $tanggalYmd, string $namaDiklat, int $id): string
{
    $ts = strtotime($tanggalYmd);
    if ($ts === false) {
        $ts = time();
    }
    $y = date('Y', $ts);
    $mm = date('m', $ts);
    $dd = date('d', $ts);
    $slug = ppi_diklat_slug_folder($namaDiklat);
    $monthFolder = $mm . '-' . ppi_diklat_bulan_nama((int) $mm);
    $leaf = sprintf('%s-%s-%s_%d_%s', $dd, $mm, $y, $id, $slug);
    return 'diklat/' . $y . '/' . $monthFolder . '/' . $leaf;
}

function ppi_diklat_abs_upload_dir(string $projectParent, string $relDir): string
{
    $relDir = trim(str_replace('\\', '/', $relDir), '/');
    return str_replace('\\', '/', rtrim($projectParent, '/\\')) . '/uploads/' . $relDir;
}

/**
 * Folder upload untuk baris DB (legacy: uploads/uman_diklat).
 */
function ppi_diklat_resolve_target_dir(array $row, string $projectParent): string
{
    if (!empty($row['upload_rel_dir'])) {
        return ppi_diklat_abs_upload_dir($projectParent, (string) $row['upload_rel_dir']);
    }
    foreach (['file_undangan', 'file_materi', 'file_absensi', 'file_pretest', 'file_posttest', 'file_sertifikat'] as $k) {
        if (!empty($row[$k]) && is_string($row[$k])) {
            $dir = dirname(str_replace('\\', '/', $row[$k]));
            if (is_dir($dir)) {
                return $dir;
            }
        }
    }
    return str_replace('\\', '/', rtrim($projectParent, '/\\')) . '/uploads/uman_diklat';
}

function ppi_diklat_sort_date_ymd(array $row): string
{
    if (!empty($row['tanggal_diklat'])) {
        return (string) $row['tanggal_diklat'];
    }
    $ca = $row['created_at'] ?? '';
    if (is_string($ca) && preg_match('/^(\d{4}-\d{2}-\d{2})/', $ca, $m)) {
        return $m[1];
    }
    return date('Y-m-d');
}

function ppi_diklat_label_bulan_tahun(int $bulan, int $tahun): string
{
    $nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return ($nama[max(1, min(12, $bulan))] ?? '') . ' ' . $tahun;
}

function ppi_diklat_format_tanggal_id(?string $ymd): string
{
    if ($ymd === null || $ymd === '') {
        return '—';
    }
    $ts = strtotime($ymd);
    if ($ts === false) {
        return '—';
    }
    $b = (int) date('n', $ts);
    $nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return sprintf('%d %s %d', (int) date('j', $ts), $nama[$b] ?? '', (int) date('Y', $ts));
}

/** @return array{jumlahFoto:int,jumlahDok:int,jumlahTotal:int,pct:int,fillExtra:string,docPill:string,photoPillExtra:string,statusLineClass:string,statusText:string,statusLabel:string} */
function ppi_diklat_row_metrics(array $r): array
{
    $fotos = !empty($r['file_foto']) ? json_decode($r['file_foto'], true) : [];
    $jumlahFoto = is_array($fotos) ? count($fotos) : 0;
    $jumlahDok = (int) !empty($r['file_undangan']) + (int) !empty($r['file_materi'])
        + (int) !empty($r['file_absensi']) + (int) !empty($r['file_pretest'])
        + (int) !empty($r['file_posttest']) + (int) !empty($r['file_sertifikat']);
    $jumlahTotal = $jumlahDok + ($jumlahFoto > 0 ? 1 : 0);
    $pct = (int) round(min(100, max(0, ($jumlahTotal / 7) * 100)));

    $fillExtra = '';
    if ($jumlahTotal === 0) {
        $fillExtra = ' mc-progress-fill--warn';
    } elseif ($jumlahTotal < 7) {
        $fillExtra = ' mc-progress-fill--muted';
    }

    $docPill = 'mc-pill-doc--mid';
    if ($jumlahDok === 6) {
        $docPill = 'mc-pill-doc--full';
    } elseif ($jumlahDok === 0) {
        $docPill = 'mc-pill-doc--empty';
    }
    $photoPillExtra = $jumlahFoto > 0 ? '' : ' mc-pill-photo--none';

    if ($jumlahTotal === 7) {
        $statusLineClass = 'mc-status-line--full';
        $statusText = '✓ Lengkap — 6/6 modul & dokumentasi foto';
        $statusLabel = 'Lengkap';
    } elseif ($jumlahTotal === 0) {
        $statusLineClass = 'mc-status-line--empty';
        $statusText = 'Belum ada berkas — mulai unggah dari Edit atau Detail';
        $statusLabel = 'Kosong';
    } else {
        $statusLineClass = 'mc-status-line--partial';
        $statusText = 'Berlangsung · kelengkapan ' . $jumlahTotal . '/7 (detail untuk tiap berkas)';
        $statusLabel = 'Berlangsung';
    }

    return [
        'jumlahFoto' => $jumlahFoto,
        'jumlahDok' => $jumlahDok,
        'jumlahTotal' => $jumlahTotal,
        'pct' => $pct,
        'fillExtra' => $fillExtra,
        'docPill' => $docPill,
        'photoPillExtra' => $photoPillExtra,
        'statusLineClass' => $statusLineClass,
        'statusText' => $statusText,
        'statusLabel' => $statusLabel,
    ];
}

function ppi_diklat_hapus_query(array $r, string $csrfToken, string $persistQuery): string
{
    $q = 'hapus=' . (int) $r['id'] . '&csrf=' . rawurlencode($csrfToken);
    if ($persistQuery !== '') {
        $q .= '&' . $persistQuery;
    }
    return $q;
}

/** HTML satu kartu diklat (grid). */
function ppi_diklat_render_card(array $r, string $csrfToken, string $persistQuery): string
{
    $m = ppi_diklat_row_metrics($r);
    $tgl = ppi_diklat_format_tanggal_id(ppi_diklat_sort_date_ymd($r));
    $titleEsc = htmlspecialchars($r['nama_diklat'] ?? '', ENT_QUOTES, 'UTF-8');
    $namaAttr = htmlspecialchars($r['nama_diklat'] ?? '', ENT_QUOTES, 'UTF-8');
    $tanggalIso = htmlspecialchars(ppi_diklat_sort_date_ymd($r), ENT_QUOTES, 'UTF-8');
    $statusEsc = htmlspecialchars($m['statusText'], ENT_QUOTES, 'UTF-8');
    $hapusHref = htmlspecialchars('?' . ppi_diklat_hapus_query($r, $csrfToken, $persistQuery), ENT_QUOTES, 'UTF-8');
    $dataTitleEsc = htmlspecialchars(strtolower((string) ($r['nama_diklat'] ?? '')), ENT_QUOTES, 'UTF-8');

    ob_start();
    ?>
                <div class="meeting-card card-diklat" data-title="<?= $dataTitleEsc ?>">
                    <div class="mc-top">
                        <div class="mc-avatar" aria-hidden="true">🎓</div>
                        <div class="mc-head-text">
                            <h3 class="mc-title"><?= $titleEsc ?></h3>
                            <p class="mc-num"><?= htmlspecialchars($tgl, ENT_QUOTES, 'UTF-8') ?> · Diklat #<?= (int) $r['id'] ?></p>
                        </div>
                    </div>

                    <div class="mc-status-block">
                        <div class="mc-status-pills">
                            <span class="mc-pill mc-pill-doc <?= htmlspecialchars($m['docPill'], ENT_QUOTES, 'UTF-8') ?>"><?= (int) $m['jumlahDok'] ?>/6 modul</span>
                            <span class="mc-pill mc-pill-photo<?= htmlspecialchars($m['photoPillExtra'], ENT_QUOTES, 'UTF-8') ?>"><?= (int) $m['jumlahFoto'] ?> foto</span>
                        </div>
                        <div class="mc-progress-row">
                            <div class="mc-progress-track" role="progressbar" aria-valuenow="<?= (int) $m['pct'] ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Kelengkapan berkas">
                                <div class="mc-progress-fill<?= htmlspecialchars($m['fillExtra'], ENT_QUOTES, 'UTF-8') ?>" style="width: <?= (int) $m['pct'] ?>%;"></div>
                            </div>
                            <span class="mc-progress-pct"><?= (int) $m['pct'] ?>%</span>
                        </div>
                        <p class="mc-status-line <?= htmlspecialchars($m['statusLineClass'], ENT_QUOTES, 'UTF-8') ?>"><?= $statusEsc ?></p>
                    </div>

                    <div class="mc-actions">
                        <a href="uman_diklat_view.php?id=<?= (int) $r['id'] ?>" class="btn btn-primary btn-sm mc-btn-detail">Lihat detail</a>
                        <div class="mc-actions-icons">
                            <button
                                type="button"
                                class="btn-icon-only"
                                onclick="openEditModal(this)"
                                data-id="<?= (int) $r['id'] ?>"
                                data-nama="<?= $namaAttr ?>"
                                data-tanggal="<?= $tanggalIso ?>"
                                title="Edit">&#9998;</button>
                            <a href="<?= $hapusHref ?>"
                               class="btn-icon-only btn-icon-del"
                               onclick="return confirm('Hapus data diklat ini beserta semua file?')"
                               title="Hapus">🗑️</a>
                        </div>
                    </div>
                </div>
    <?php
    return (string) ob_get_clean();
}
