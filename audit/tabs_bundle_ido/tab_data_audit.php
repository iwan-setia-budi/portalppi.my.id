<?php
require_once __DIR__ . '/../../include/audit_delete_auth.php';
$ppiAuditCanDelete = ppi_audit_delete_allowed();
?>
<style>
  #tab-data .data-title { margin: 0 0 12px; font-size: 20px; font-weight: 900; letter-spacing: -0.2px; }
  #tab-data .card-title { margin: 0 0 12px; font-size: 20px; font-weight: 900; letter-spacing: -0.2px; }
  #tab-data .filter-grid { display:grid; grid-template-columns:minmax(160px, 2fr) minmax(140px, 1fr) minmax(90px, 1fr) minmax(90px, 1fr) auto; gap:12px; align-items: center; }
  #tab-data .search-wrap { position: relative; }
  #tab-data .search-wrap .search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.65; font-size:14px; }
  #tab-data .search-wrap .form-control { padding-left:34px; }
  #tab-data .sort-link { color: inherit; text-decoration: none; display:inline-flex; align-items:center; gap:4px; transition: all .2s ease; }
  #tab-data .sort-link .sort-arrow { font-size: 11px; opacity: .6; }
  #tab-data .table-shell { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; background: var(--card); }
  #tab-data .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  #tab-data .mobile-list { display: none; }
  #tab-data .mobile-item {
    border:1px solid var(--line); border-radius: 12px; background: var(--card); padding: 12px; margin-bottom: 10px;
    box-shadow: 0 6px 12px rgba(15,23,42,.06);
  }
  #tab-data .mobile-item-head { display:flex; justify-content:space-between; gap:10px; font-weight:800; margin-bottom: 8px; }
  #tab-data .mobile-meta { color: var(--muted, #64748b); font-size: 12px; margin-bottom: 8px; }
  #tab-data .mobile-actions { display:flex; gap:8px; margin-top:10px; }
  #tab-data table { width:100%; border-collapse:separate; border-spacing:0 2px; padding: 0 6px 4px; }
  #tab-data thead th {
    font-size: 12px; text-transform: uppercase; letter-spacing: .6px; color: #64748b;
    text-align:left; padding: 6px 8px 5px; font-weight: 900;
    background: linear-gradient(180deg, #eaf2ff, #dbeafe);
    border-bottom: 1px solid #bfdbfe;
  }
  #tab-data tbody tr { transition: all .2s ease; cursor: pointer; }
  #tab-data tbody tr:hover { transform: translateY(-1px); }
  #tab-data td {
    background: var(--card); border-top:1px solid var(--line); border-bottom:1px solid var(--line);
    padding: 8px 8px; vertical-align: middle; transition: all .2s ease;
  }
  #tab-data td:first-child { border-left:1px solid var(--line); border-top-left-radius: 9px; border-bottom-left-radius: 9px; }
  #tab-data td:last-child { border-right:1px solid var(--line); border-top-right-radius: 9px; border-bottom-right-radius: 9px; }
  #tab-data tbody tr:nth-child(even) td { background: color-mix(in srgb, var(--card) 94%, #e2e8f0 6%); }
  #tab-data tbody tr:hover td { box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08); background: #f8fafc; }
  #tab-data .center { text-align:center; }
  #tab-data .score-wrap { min-width: 180px; }
  #tab-data .score-head { display:flex; align-items:center; justify-content:flex-start; gap:6px; margin-bottom:5px; font-size:12px; font-weight:800; }
  #tab-data .score-main { font-size: 15px; font-weight: 900; color: #0f172a; letter-spacing: -.2px; line-height: 1; }
  #tab-data .score-pill {
    display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:3px 9px;
    font-size:12px; font-weight:800; border:1px solid transparent; box-shadow: inset 0 -1px 0 rgba(15,23,42,.08);
  }
  #tab-data .score-pill.good { background:#dcfce7; color:#166534; border-color:#86efac; }
  #tab-data .score-pill.warn { background:#fef9c3; color:#854d0e; border-color:#fde047; }
  #tab-data .score-pill.bad { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
  #tab-data .score-bar { height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; border:1px solid #cbd5e1; width: 100%; }
  #tab-data .score-fill { height: 100%; border-radius:999px; background: linear-gradient(135deg, #22c55e, #16a34a); }
  #tab-data .score-fill.warn { background: linear-gradient(135deg, #facc15, #eab308); }
  #tab-data .score-fill.bad { background: linear-gradient(135deg, #f87171, #ef4444); }
  #tab-data .action-group { display:flex; gap:6px; justify-content:center; }
  #tab-data .icon-btn {
    width:30px; height:30px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center;
    font-size:13px; text-decoration:none; border:1px solid transparent; transition:all .2s ease; color:#fff; cursor: pointer;
  }
  #tab-data .icon-btn:hover { transform: translateY(-1px) scale(1.04); box-shadow: 0 8px 15px rgba(15,23,42,.16); }
  #tab-data .icon-btn.view { background:#2563eb; border-color:#1d4ed8; box-shadow: 0 4px 10px rgba(37,99,235,.35); }
  #tab-data .icon-btn.view:hover { background:#1d4ed8; }
  #tab-data .icon-btn.edit { background:#f59e0b; border-color:#d97706; box-shadow: 0 4px 10px rgba(245,158,11,.35); }
  #tab-data .icon-btn.edit:hover { background:#d97706; }
  #tab-data .icon-btn.del { background:#ef4444; border-color:#dc2626; box-shadow: 0 4px 10px rgba(239,68,68,.35); }
  #tab-data .icon-btn.del:hover { background:#dc2626; }
  #tab-data .empty-state {
    border:1px dashed var(--line); border-radius:14px; padding:34px 18px; text-align:center; color:#64748b;
  }
  #tab-data .empty-state .icon { font-size: 34px; display:block; margin-bottom:10px; }
  #tab-data .pagination-wrap { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:12px; flex-wrap:wrap; }
  #tab-data .pagination { display:flex; gap:6px; flex-wrap:wrap; }
  #tab-data .page-btn {
    min-width:36px; height:36px; border:1px solid var(--line); border-radius:10px; display:inline-flex; align-items:center; justify-content:center;
    text-decoration:none; color:var(--ink); font-weight:800; background:var(--card);
  }
  #tab-data .page-btn.active { background: linear-gradient(135deg, var(--primary), var(--primary-2)); color:#fff; border-color:transparent; }
  @media (max-width: 900px) {
    #tab-data .filter-grid { grid-template-columns:1fr; }
    #tab-data .desktop-table { display: none; }
    #tab-data .mobile-list { display: block; }
    #tab-data table { min-width: 640px; }
    #tab-data td { padding: 7px 6px; }
    #tab-data thead th { padding: 6px 6px 5px; font-size: 11px; }
    #tab-data .icon-btn { width: 32px; height: 32px; font-size: 14px; }
    #tab-data .score-main { font-size: 14px; }
    #tab-data .score-pill { font-size: 12px; padding: 3px 8px; }
  }
  body.dark-mode #tab-data thead th { color:#cbd5e1; background: linear-gradient(180deg, #1e293b, #0f172a); border-bottom-color:#475569; }
  body.dark-mode #tab-data tbody tr:hover td { background: #0f172a; box-shadow: 0 6px 14px rgba(2,6,23,.34); }
  body.dark-mode #tab-data tbody tr:nth-child(even) td { background: color-mix(in srgb, var(--card) 94%, #0f172a 6%); }
  body.dark-mode #tab-data .score-bar { background:#1f2937; border-color:#334155; }
  body.dark-mode #tab-data .score-main { color:#e2e8f0; }
</style>

<?php
$mkSortDir = static function ($col) use ($sortBy, $sortDir) {
  return ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc';
};
$mkSortArrow = static function ($col) use ($sortBy, $sortDir) {
  if ($sortBy !== $col) return '⬍';
  return $sortDir === 'asc' ? '↑' : '↓';
};
$qsBase = $_GET;
$qsBase['tab'] = 'tab-data';
$startRow = $totalData > 0 ? (($page - 1) * $limit) + 1 : 0;
$endRow = min($totalData, $page * $limit);
$rowsData = [];
while ($tmp = mysqli_fetch_assoc($qData)) {
  $rowsData[] = $tmp;
}
?>

<div id="tab-data" class="tab-pane active">
  <div class="section-card">
    <h3 class="card-title">Filter Data Audit</h3>
    <form method="get">
      <input type="hidden" name="tab" value="tab-data">
      <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy) ?>">
      <input type="hidden" name="sort_dir" value="<?= htmlspecialchars($sortDir) ?>">
      <div class="filter-grid">
        <div class="search-wrap">
          <span class="search-icon">🔎</span>
          <input type="text" name="keyword_data" class="form-control" placeholder="Cari pasien, petugas, catatan, atau ruangan" value="<?= htmlspecialchars($keywordData) ?>">
        </div>
        <select name="ruangan" class="form-control" title="Filter ruangan">
          <option value="">Semua Ruangan</option>
          <?php foreach ($ruanganDiauditOptions as $optRu): ?>
            <option value="<?= htmlspecialchars($optRu) ?>" <?= (string) $filterRuangan === $optRu ? 'selected' : '' ?>><?= htmlspecialchars($optRu) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="bulan" class="form-control">
          <option value="">Semua Bulan</option>
          <?php for ($b = 1; $b <= 12; $b++): ?>
            <option value="<?= $b ?>" <?= (string) $filterBulan === (string) $b ? 'selected' : '' ?>><?= $b ?></option>
          <?php endfor; ?>
        </select>
        <select name="tahun" class="form-control">
          <option value="">Semua Tahun</option>
          <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
            <option value="<?= $t ?>" <?= (string) $filterTahun === (string) $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn btn-primary" type="submit">Cari</button>
      </div>
    </form>
  </div>

  <div class="section-card">
    <h3 class="card-title">Data Audit Bundle IDO</h3>
    <?php if (count($rowsData) > 0): ?>
      <div class="table-shell">
        <div class="table-scroll desktop-table">
          <table>
            <thead>
            <tr>
              <?php $q = $qsBase; $q['sort_by'] = 'tanggal'; $q['sort_dir'] = $mkSortDir('tanggal'); $q['page'] = 1; ?>
              <th><a class="sort-link" href="?<?= htmlspecialchars(http_build_query($q)) ?>">Tanggal <span class="sort-arrow"><?= $mkSortArrow('tanggal') ?></span></a></th>
              <?php $q = $qsBase; $q['sort_by'] = 'ruangan'; $q['sort_dir'] = $mkSortDir('ruangan'); $q['page'] = 1; ?>
              <th><a class="sort-link" href="?<?= htmlspecialchars(http_build_query($q)) ?>">Ruangan <span class="sort-arrow"><?= $mkSortArrow('ruangan') ?></span></a></th>
              <th>Nama Pasien</th>
              <?php $q = $qsBase; $q['sort_by'] = 'petugas'; $q['sort_dir'] = $mkSortDir('petugas'); $q['page'] = 1; ?>
              <th><a class="sort-link" href="?<?= htmlspecialchars(http_build_query($q)) ?>">Nama Petugas Unit <span class="sort-arrow"><?= $mkSortArrow('petugas') ?></span></a></th>
              <?php $q = $qsBase; $q['sort_by'] = 'num'; $q['sort_dir'] = $mkSortDir('num'); $q['page'] = 1; ?>
              <th class="center"><a class="sort-link" href="?<?= htmlspecialchars(http_build_query($q)) ?>">Skor <span class="sort-arrow"><?= $mkSortArrow('num') ?></span></a></th>
              <th class="center">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rowsData as $row): ?>
              <?php
              $num = (int) $row['num'];
              $den = max(0, (int) $row['denum']);
              $pct = $den > 0 ? round(($num / $den) * 100, 1) : 0;
              $scoreClass = $pct >= 95 ? 'good' : ($pct >= 80 ? 'warn' : 'bad');
              ?>
              <tr>
                <td><?= htmlspecialchars($row['tanggal_audit']) ?></td>
                <td><?= htmlspecialchars($row['ruangan_diaudit'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_petugas_unit']) ?></td>
                <td class="center">
                  <div class="score-wrap">
                    <div class="score-head">
                      <span class="score-main"><?= $pct ?>%</span>
                      <span class="score-pill <?= $scoreClass ?>"><?= $num ?> / <?= $den ?></span>
                    </div>
                    <div class="score-bar">
                      <div class="score-fill <?= $scoreClass === 'good' ? '' : ($scoreClass === 'warn' ? 'warn' : 'bad') ?>" style="width: <?= $pct ?>%;"></div>
                    </div>
                  </div>
                </td>
                <td class="center">
                  <div class="action-group">
                    <a class="icon-btn view" href="crud_bundle_ido/detail_audit.php?id=<?= (int) $row['id'] ?>" title="Lihat detail" aria-label="Lihat detail">👁</a>
                    <a class="icon-btn edit" href="crud_bundle_ido/edit_audit.php?id=<?= (int) $row['id'] ?>" title="Edit data" aria-label="Edit data">✏</a>
                    <?php if ($ppiAuditCanDelete): ?>
                    <a class="icon-btn del" href="crud_bundle_ido/hapus_audit.php?id=<?= (int) $row['id'] ?>" title="Hapus data" aria-label="Hapus data" onclick="return confirm('Yakin hapus data ini?')">🗑</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mobile-list">
          <?php foreach ($rowsData as $row): ?>
            <?php
            $num = (int) $row['num'];
            $den = max(0, (int) $row['denum']);
            $pct = $den > 0 ? round(($num / $den) * 100, 1) : 0;
            $scoreClass = $pct >= 95 ? 'good' : ($pct >= 80 ? 'warn' : 'bad');
            ?>
            <div class="mobile-item">
              <div class="mobile-item-head">
                <span><?= htmlspecialchars($row['tanggal_audit']) ?></span>
                <span class="score-pill <?= $scoreClass ?>"><?= $pct ?>%</span>
              </div>
              <div class="mobile-meta"><?= htmlspecialchars($row['ruangan_diaudit'] ?? '') ?> · <?= htmlspecialchars($row['nama_pasien'] ?? '-') ?> · <?= htmlspecialchars($row['nama_petugas_unit']) ?></div>
              <div class="score-wrap">
                <div class="score-head">
                  <span class="score-main">Skor</span>
                  <span class="score-pill <?= $scoreClass ?>"><?= $num ?> / <?= $den ?></span>
                </div>
                <div class="score-bar">
                  <div class="score-fill <?= $scoreClass === 'good' ? '' : ($scoreClass === 'warn' ? 'warn' : 'bad') ?>" style="width: <?= $pct ?>%;"></div>
                </div>
              </div>
              <div class="mobile-actions">
                <a class="icon-btn view" href="crud_bundle_ido/detail_audit.php?id=<?= (int) $row['id'] ?>" title="Lihat detail">👁</a>
                <a class="icon-btn edit" href="crud_bundle_ido/edit_audit.php?id=<?= (int) $row['id'] ?>" title="Edit data">✏</a>
                <?php if ($ppiAuditCanDelete): ?>
                <a class="icon-btn del" href="crud_bundle_ido/hapus_audit.php?id=<?= (int) $row['id'] ?>" title="Hapus data" onclick="return confirm('Yakin hapus data ini?')">🗑</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="pagination-wrap">
        <div>Menampilkan <strong><?= $startRow ?></strong>-<strong><?= $endRow ?></strong> dari <strong><?= (int) $totalData ?></strong> data</div>
        <div class="pagination">
          <?php
          $prev = max(1, $page - 1);
          $next = min($totalPages, $page + 1);
          $q = $qsBase; $q['page'] = $prev;
          ?>
          <a class="page-btn" href="?<?= htmlspecialchars(http_build_query($q)) ?>">‹</a>
          <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
            <?php $q = $qsBase; $q['page'] = $p; ?>
            <a class="page-btn <?= $p === $page ? 'active' : '' ?>" href="?<?= htmlspecialchars(http_build_query($q)) ?>"><?= $p ?></a>
          <?php endfor; ?>
          <?php $q = $qsBase; $q['page'] = $next; ?>
          <a class="page-btn" href="?<?= htmlspecialchars(http_build_query($q)) ?>">›</a>
        </div>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <span class="icon">📄</span>
        <strong>Belum ada data audit Bundle IDO.</strong>
        <div style="margin-top:8px;">Mulai isi form audit untuk menampilkan data pada tabel ini.</div>
        <div style="margin-top:14px;">
          <a class="btn btn-primary" href="?tab=tab-form">Tambah Audit</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
