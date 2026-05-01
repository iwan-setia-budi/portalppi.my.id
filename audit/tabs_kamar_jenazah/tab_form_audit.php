<style>
  #tab-form .section-title {
    margin: 0 0 12px;
    font-size: 20px;
    font-weight: 900;
    letter-spacing: -0.2px;
  }

  #tab-form .field-label {
    display: block;
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 8px;
    color: var(--ink);
  }

  #tab-form .required {
    color: #e11d48;
  }

  #tab-form .opportunity-card {
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 14px;
    background: var(--card);
    padding: 14px;
    margin-bottom: 14px;
  }

  #tab-form .section-toggle {
    width: 100%;
    border: none;
    background: transparent;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    text-align: left;
  }

  #tab-form .opportunity-title {
    margin: 0;
    font-size: 19px;
    font-weight: 800;
    color: var(--ink);
  }

  #tab-form .opportunity-meta {
    display: inline-flex;
    align-items: flex-start;
    gap: 10px;
  }

  #tab-form .opportunity-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  #tab-form .section-code {
    display: inline-flex;
    width: fit-content;
    padding: 2px 8px;
    border-radius: 999px;
    border: 1px solid var(--line);
    color: var(--muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .3px;
    background: var(--card-2);
  }

  #tab-form .section-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--card-2);
    border: 1px solid var(--line);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }

  #tab-form .section-chevron {
    font-size: 14px;
    color: var(--muted);
    transition: transform .2s ease;
  }

  #tab-form .opportunity-card.is-collapsed .section-chevron {
    transform: rotate(-90deg);
  }

  #tab-form .section-body {
    margin-top: 10px;
  }

  #tab-form .opportunity-card.is-collapsed .section-body {
    display: none;
  }

  #tab-form .table-responsive {
    width: 100%;
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid var(--line);
    max-height: 420px;
  }

  #tab-form .audit-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
    min-width: 860px;
    border-radius: 10px;
    overflow: hidden;
    padding: 0 8px 8px;
  }

  #tab-form .audit-table thead th {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
    color: #fff;
    font-weight: 800;
    font-size: 14px;
    padding: 10px 8px;
    border: none;
    text-align: center;
    position: sticky;
    top: 0;
    z-index: 3;
  }

  #tab-form .audit-table thead th:first-child {
    text-align: center;
    padding-left: 8px;
  }

  #tab-form .audit-table tbody td {
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    padding: 10px 8px;
    font-size: 14px;
    background: var(--card);
  }

  #tab-form .audit-table tbody tr:nth-child(odd) td {
    background: var(--card-2);
  }

  #tab-form .audit-table tbody td:first-child {
    border-left: 1px solid var(--line);
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
  }

  #tab-form .audit-table tbody td:last-child {
    border-right: 1px solid var(--line);
    border-top-right-radius: 10px;
    border-bottom-right-radius: 10px;
  }

  #tab-form .audit-table td:nth-child(2) {
    font-weight: 600;
    color: var(--ink);
    width: 62%;
    min-width: 420px;
    text-align: left;
  }

  #tab-form .audit-table td:first-child {
    width: 8%;
    min-width: 64px;
    text-align: center;
    font-weight: 700;
    color: var(--ink);
    white-space: nowrap;
  }

  #tab-form .audit-table td:nth-child(n+3) {
    width: 10%;
    text-align: center;
  }

  #tab-form .choice-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-width: 74px;
    padding: 6px 8px;
    border-radius: 999px;
    border: 1px solid var(--line);
    background: var(--card);
    color: var(--muted);
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    user-select: none;
    transition: .18s ease;
  }

  #tab-form .choice-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  #tab-form .choice-pill:has(.choice-input:checked) {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.14);
  }

  #tab-form .choice-pill.is-selected {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.14);
  }

  #tab-form .choice-pill.choice-ya.is-selected {
    background: linear-gradient(135deg, #bbf7d0, #86efac);
    border-color: #16a34a;
    border-width: 1.6px;
    color: #166534;
  }

  #tab-form .choice-pill.choice-tidak.is-selected {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    border-color: #f87171;
    color: #991b1b;
  }

  #tab-form .choice-pill.choice-na.is-selected {
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    border-color: #94a3b8;
    color: #334155;
  }

  #tab-form .choice-pill.choice-ya:has(.choice-input:checked) {
    background: linear-gradient(135deg, #bbf7d0, #86efac);
    border-color: #16a34a;
    border-width: 1.6px;
    color: #166534;
  }

  #tab-form .choice-pill.choice-tidak:has(.choice-input:checked) {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    border-color: #f87171;
    color: #991b1b;
  }

  #tab-form .choice-pill.choice-na:has(.choice-input:checked) {
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    border-color: #94a3b8;
    color: #334155;
  }

  #tab-form .audit-row.state-ya td {
    background: #f0fdf4 !important;
  }

  #tab-form .audit-row.state-tidak td {
    background: #fef2f2 !important;
  }

  #tab-form .audit-row.state-na td {
    background: #f8fafc !important;
  }

  #tab-form .audit-row.state-missing td {
    border-color: #fbbf24 !important;
    background: #fffbeb !important;
  }

  #tab-form .mobile-card {
    display: none;
  }

  #tab-form .mobile-item {
    border-bottom: 1px solid var(--line);
    padding: 10px 0;
  }

  #tab-form .mobile-item:last-child {
    border-bottom: none;
  }

  #tab-form .mobile-item-title {
    font-size: 13px;
    line-height: 1.45;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 8px;
  }

  #tab-form .mobile-option-list {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  #tab-form .mobile-option {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 7px 10px;
    background: var(--card);
    font-size: 12px;
    cursor: pointer;
    transition: .18s ease;
  }

  #tab-form .mobile-option .choice-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  #tab-form .mobile-option:has(.choice-input:checked) {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.14);
  }

  #tab-form .mobile-option.is-selected {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.14);
  }

  #tab-form .mobile-option.option-ya.is-selected {
    background: linear-gradient(135deg, #bbf7d0, #86efac);
    border-color: #4ade80;
    color: #166534;
  }

  #tab-form .mobile-option.option-tidak.is-selected {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    border-color: #f87171;
    color: #991b1b;
  }

  #tab-form .mobile-option.option-na.is-selected {
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    border-color: #94a3b8;
    color: #334155;
  }

  #tab-form .mobile-option.option-ya:has(.choice-input:checked) {
    background: linear-gradient(135deg, #bbf7d0, #86efac);
    border-color: #4ade80;
    color: #166534;
  }

  #tab-form .mobile-option.option-tidak:has(.choice-input:checked) {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    border-color: #f87171;
    color: #991b1b;
  }

  #tab-form .mobile-option.option-na:has(.choice-input:checked) {
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    border-color: #94a3b8;
    color: #334155;
  }

  #tab-form .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  #tab-form .mt-16 {
    margin-top: 16px;
  }

  #tab-form .bulk-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 12px;
  }

  #tab-form .bulk-actions-wrap {
    display: none;
  }

  #tab-form.show-bulk-actions .bulk-actions-wrap {
    display: block;
  }

  #tab-form .bulk-actions .btn {
    min-width: 120px;
  }

  #tab-form .small-note {
    margin-top: 8px;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
  }

  #tab-form .label-optional {
    font-size: 12px;
    color: var(--muted);
    font-weight: 700;
    margin-left: 6px;
  }

  #tab-form .progress-wrap {
    margin-top: 12px;
  }

  #tab-form .progress-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--muted);
  }

  #tab-form .progress-bar {
    width: 100%;
    height: 10px;
    border-radius: 999px;
    background: var(--card-2);
    border: 1px solid var(--line);
    overflow: hidden;
  }

  #tab-form .progress-fill {
    width: 0%;
    height: 100%;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    transition: width .2s ease;
  }

  #tab-form .signature-pad-wrap {
    border: 1.7px dashed var(--line-strong);
    border-radius: 14px;
    padding: 10px;
    background: var(--card-2);
  }

  #tab-form .signature-canvas {
    width: 100%;
    height: 190px;
    background: var(--card);
    border: 1.7px solid var(--line-strong);
    border-radius: 10px;
    touch-action: none;
    cursor: crosshair;
    display: block;
  }

  #tab-form .signature-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 10px;
  }

  #tab-form .signature-hint {
    margin-top: 6px;
    color: var(--muted);
    font-size: 12px;
  }

  #tab-form .upload-box {
    border: 1.7px dashed var(--line-strong);
    border-radius: 12px;
    background: var(--card-2);
    padding: 12px;
  }

  #tab-form .upload-head {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: var(--muted);
    margin-bottom: 8px;
  }

  #tab-form .upload-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 10px;
    margin-top: 10px;
  }

  #tab-form .upload-preview img {
    width: 100%;
    height: 90px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid var(--line);
    background: var(--card);
  }

  #tab-form .progress-warning {
    margin-top: 8px;
    color: #b45309;
    font-weight: 700;
    font-size: 12px;
  }

  #tab-form .sticky-submit-wrap {
    position: sticky;
    bottom: 10px;
    z-index: 8;
    margin-top: 12px;
    padding: 10px;
    border-radius: 12px;
    border: 1px solid var(--line);
    background: color-mix(in srgb, var(--card) 90%, transparent);
    backdrop-filter: blur(4px);
  }

  #tab-form {
    --card: #ffffff;
    --card-2: #f8fafc;
    --ink: #0f172a;
    --muted: #64748b;
    --line: #dbe3ee;
    --line-strong: #94a3b8;
  }

  body.dark-mode #tab-form {
    --card: #0f172a;
    --card-2: #111827;
    --ink: #e5e7eb;
    --muted: #94a3b8;
    --line: #334155;
    --line-strong: #475569;
  }

  body.dark-mode #tab-form .choice-pill.choice-ya:has(.choice-input:checked),
  body.dark-mode #tab-form .mobile-option.option-ya:has(.choice-input:checked) {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.35), rgba(74, 222, 128, 0.28));
    border-color: rgba(74, 222, 128, 0.62);
    color: #dcfce7;
  }

  body.dark-mode #tab-form .choice-pill.choice-ya.is-selected,
  body.dark-mode #tab-form .mobile-option.option-ya.is-selected {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.35), rgba(74, 222, 128, 0.28));
    border-color: rgba(74, 222, 128, 0.62);
    color: #dcfce7;
  }

  body.dark-mode #tab-form .choice-pill.choice-tidak:has(.choice-input:checked),
  body.dark-mode #tab-form .mobile-option.option-tidak:has(.choice-input:checked) {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.34), rgba(248, 113, 113, 0.25));
    border-color: rgba(248, 113, 113, 0.62);
    color: #fee2e2;
  }

  body.dark-mode #tab-form .choice-pill.choice-tidak.is-selected,
  body.dark-mode #tab-form .mobile-option.option-tidak.is-selected {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.34), rgba(248, 113, 113, 0.25));
    border-color: rgba(248, 113, 113, 0.62);
    color: #fee2e2;
  }

  body.dark-mode #tab-form .choice-pill.choice-na:has(.choice-input:checked),
  body.dark-mode #tab-form .mobile-option.option-na:has(.choice-input:checked) {
    background: linear-gradient(135deg, rgba(148, 163, 184, 0.34), rgba(100, 116, 139, 0.25));
    border-color: rgba(148, 163, 184, 0.58);
    color: #e2e8f0;
  }

  body.dark-mode #tab-form .choice-pill.choice-na.is-selected,
  body.dark-mode #tab-form .mobile-option.option-na.is-selected {
    background: linear-gradient(135deg, rgba(148, 163, 184, 0.34), rgba(100, 116, 139, 0.25));
    border-color: rgba(148, 163, 184, 0.58);
    color: #e2e8f0;
  }

  body.dark-mode #tab-form .audit-row.state-ya td {
    background: rgba(22, 163, 74, 0.2) !important;
  }

  body.dark-mode #tab-form .audit-row.state-tidak td {
    background: rgba(220, 38, 38, 0.22) !important;
  }

  body.dark-mode #tab-form .audit-row.state-na td {
    background: rgba(71, 85, 105, 0.24) !important;
  }

  body.dark-mode #tab-form .audit-row.state-missing td {
    background: rgba(251, 191, 36, 0.12) !important;
    border-color: rgba(251, 191, 36, 0.5) !important;
  }

  @media (max-width: 768px) {
    #tab-form .section-title {
      font-size: 17px;
    }

    #tab-form .opportunity-title {
      font-size: 15px;
      margin-bottom: 8px;
    }

    #tab-form .opportunity-card {
      padding: 12px;
    }

    #tab-form .table-responsive {
      display: none;
    }

    #tab-form .mobile-card {
      display: block;
    }

    #tab-form .grid-2 {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    #tab-form .bulk-actions .btn {
      width: 100%;
    }

    #tab-form .signature-canvas {
      height: 150px;
    }
  }

  @media print {
    #tab-form,
    #tab-form * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    #tab-form .table-responsive {
      display: block !important;
      max-height: none !important;
      overflow: visible !important;
      border: 1px solid #cbd5e1 !important;
    }

    #tab-form .audit-table {
      width: 100% !important;
      min-width: 0 !important;
      table-layout: fixed !important;
      border-collapse: collapse !important;
    }

    #tab-form .audit-table thead th,
    #tab-form .audit-table tbody td {
      font-size: 11px !important;
      padding: 6px 6px !important;
      word-break: break-word !important;
      white-space: normal !important;
    }

    #tab-form .audit-table th:first-child,
    #tab-form .audit-table td:first-child {
      width: 9% !important;
      min-width: 0 !important;
    }

    #tab-form .audit-table th:nth-child(2),
    #tab-form .audit-table td:nth-child(2) {
      width: 63% !important;
      min-width: 0 !important;
    }

    #tab-form .audit-table th:nth-child(n+3),
    #tab-form .audit-table td:nth-child(n+3) {
      width: 9.33% !important;
    }

    #tab-form .mobile-card,
    #tab-form .section-toggle .section-chevron,
    #tab-form .bulk-actions-wrap,
    #tab-form .sticky-submit-wrap {
      display: none !important;
    }

    #tab-form .section-body {
      display: block !important;
      margin-top: 6px;
    }

    #tab-form .opportunity-card {
      break-inside: avoid;
      page-break-inside: avoid;
    }

    #tab-form .audit-row.state-ya td,
    #tab-form .choice-pill.choice-ya.is-selected {
      background: linear-gradient(135deg, #bbf7d0, #86efac) !important;
      border-color: #16a34a !important;
      color: #14532d !important;
    }

    #tab-form .audit-row.state-tidak td,
    #tab-form .choice-pill.choice-tidak.is-selected {
      background: linear-gradient(135deg, #fecaca, #fca5a5) !important;
      border-color: #dc2626 !important;
      color: #7f1d1d !important;
    }

    #tab-form .audit-row.state-na td,
    #tab-form .choice-pill.choice-na.is-selected {
      background: linear-gradient(135deg, #e2e8f0, #cbd5e1) !important;
      border-color: #64748b !important;
      color: #1e293b !important;
    }

    /* Print: keep row/item backgrounds neutral, color only choice buttons */
    #tab-form .audit-row.state-ya td,
    #tab-form .audit-row.state-tidak td,
    #tab-form .audit-row.state-na td {
      background: #ffffff !important;
      color: #111827 !important;
      border-color: #e5e7eb !important;
    }

    #tab-form .choice-pill {
      min-width: 0 !important;
      width: 100% !important;
      padding: 4px 6px !important;
      border-width: 1px !important;
      box-shadow: none !important;
      font-size: 10px !important;
      font-weight: 800 !important;
    }
  }
</style>

<div id="tab-form" class="tab-pane active">
  <form method="post" enctype="multipart/form-data">
    <div class="section-card">
      <h2 class="section-title">Form Audit Kamar Jenazah</h2>
      <label class="field-label">Tanggal Audit <span class="required">*</span></label>
      <input type="date" name="tanggal_audit" class="form-control" value="<?= htmlspecialchars($_POST['tanggal_audit'] ?? '') ?>" required>
      <div class="bulk-actions-wrap">
        <div class="bulk-actions">
          <button type="button" class="btn btn-primary" data-bulk-jawaban="ya">Semua Ya</button>
          <button type="button" class="btn btn-warning" data-bulk-jawaban="tidak">Semua Tidak</button>
          <button type="button" class="btn btn-secondary" data-bulk-jawaban="na">Semua NA</button>
        </div>
        <div class="small-note">Klik salah satu tombol untuk isi semua item sekaligus.</div>
      </div>
      <div class="progress-wrap">
        <div class="progress-head">
          <span>Progress Pengisian</span>
          <strong id="auditProgressText">0/0 item</strong>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" id="auditProgressFill"></div>
        </div>
        <div class="progress-warning" id="auditProgressWarning"></div>
      </div>
    </div>

    <?php
    $sectionIcons = [
      'E01' => '🧹',
      'E02' => '🛡️',
      'E03' => '🏥',
    ];
    ?>
    <?php foreach ($checklistSections as $kode => $section): ?>
      <div class="section-card opportunity-card" data-section-card>
        <button type="button" class="section-toggle" data-section-toggle>
          <span class="opportunity-meta">
            <span class="section-icon"><?= htmlspecialchars($sectionIcons[$kode] ?? '📋') ?></span>
            <span class="opportunity-text">
              <h3 class="opportunity-title"><?= htmlspecialchars($section['title']) ?></h3>
              <span class="section-code"><?= htmlspecialchars($kode) ?></span>
            </span>
          </span>
          <span class="section-chevron">▼</span>
        </button>
        <div class="section-body">
          <div class="table-responsive">
            <table class="audit-table">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Item Indikator</th>
                  <?php foreach ($opsiJawaban as $opsiLabel): ?>
                    <th><?= htmlspecialchars($opsiLabel) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($section['items'] as $idx => $item): ?>
                  <?php $urutan = $idx + 1; ?>
                  <?php $kodeItem = $kode . str_pad((string) $urutan, 2, '0', STR_PAD_LEFT); ?>
                  <tr class="audit-row" data-audit-row>
                    <td><?= htmlspecialchars($kodeItem) ?></td>
                    <td><?= htmlspecialchars($item) ?></td>
                    <?php foreach ($opsiJawaban as $opsiKey => $opsiLabel): ?>
                      <td>
                        <label class="choice-pill choice-<?= htmlspecialchars($opsiKey) ?>">
                          <input
                            class="choice-input"
                            type="radio"
                            name="jawaban[<?= htmlspecialchars($kode) ?>][<?= $urutan ?>]"
                            value="<?= htmlspecialchars($opsiKey) ?>"
                            <?= (($_POST['jawaban'][$kode][$urutan] ?? '') === $opsiKey) ? 'checked' : '' ?>
                            required>
                          <span><?= htmlspecialchars($opsiLabel) ?></span>
                        </label>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="mobile-card">
            <?php foreach ($section['items'] as $idx => $item): ?>
              <?php $urutan = $idx + 1; ?>
              <?php $kodeItem = $kode . str_pad((string) $urutan, 2, '0', STR_PAD_LEFT); ?>
              <div class="mobile-item">
                <div class="mobile-item-title"><strong><?= htmlspecialchars($kodeItem) ?> -</strong> <?= htmlspecialchars($item) ?></div>
                <div class="mobile-option-list">
                  <?php foreach ($opsiJawaban as $opsiKey => $opsiLabel): ?>
                    <label class="mobile-option option-<?= htmlspecialchars($opsiKey) ?>">
                      <input
                        class="choice-input"
                        type="radio"
                        name="jawaban[<?= htmlspecialchars($kode) ?>][<?= $urutan ?>]"
                        value="<?= htmlspecialchars($opsiKey) ?>"
                        <?= (($_POST['jawaban'][$kode][$urutan] ?? '') === $opsiKey) ? 'checked' : '' ?>
                        required>
                      <span><?= htmlspecialchars($opsiLabel) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="section-card">
      <label class="field-label">Catatan Audit <span class="label-optional">(opsional)</span></label>
      <textarea name="catatan_audit" class="form-control" rows="4" placeholder="Contoh: kendala lapangan, temuan khusus, atau tindak lanjut yang perlu dicatat."><?= htmlspecialchars($_POST['catatan_audit'] ?? '') ?></textarea>

      <div class="grid-2 mt-16">
        <div>
          <label class="field-label">Nama Petugas Unit <span class="required">*</span></label>
          <input type="text" name="nama_petugas_unit" class="form-control" value="<?= htmlspecialchars($_POST['nama_petugas_unit'] ?? '') ?>" required>
        </div>
        <div>
          <label class="field-label">Tanda Tangan Petugas <span class="required">*</span></label>
          <div class="signature-pad-wrap">
            <canvas id="signatureCanvas" class="signature-canvas"></canvas>
            <input type="hidden" name="signature_data" id="signatureData" required>
            <div class="signature-hint">Tulis tanda tangan di sini.</div>
            <div class="signature-actions">
              <button type="button" class="btn btn-danger" id="btnClearSignature">Hapus Tanda Tangan</button>
            </div>
          </div>
          <div class="small-note">Tanda tangan langsung di area atas (presisi desktop & HP).</div>
        </div>
      </div>

      <div class="mt-16">
        <label class="field-label">Dokumentasi Foto</label>
        <div class="upload-box">
          <div class="upload-head"><span>📤</span><span>Upload foto dokumentasi</span></div>
          <input type="file" id="fotoInput" name="dokumentasi_foto[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple>
          <div class="small-note">Upload maksimum 5 file yang didukung. Maks 10 MB per file.</div>
          <div id="fotoPreview" class="upload-preview" aria-live="polite"></div>
        </div>
      </div>

      <div class="mt-16 sticky-submit-wrap">
        <button type="submit" name="simpan" class="btn btn-primary">Simpan Audit</button>
      </div>
    </div>
  </form>
</div>
<script>
  (function () {
    const canvas = document.getElementById('signatureCanvas');
    const hidden = document.getElementById('signatureData');
    const clearBtn = document.getElementById('btnClearSignature');
    if (!canvas || !hidden || !clearBtn) return;

    const ctx = canvas.getContext('2d');
    let drawing = false;
    let hasStroke = false;

    function initCtx() {
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.strokeStyle = '#0f172a';
      ctx.lineWidth = 2.2;
    }

    function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const rect = canvas.getBoundingClientRect();
      const prev = hasStroke ? canvas.toDataURL('image/png') : '';
      canvas.width = Math.floor(rect.width * ratio);
      canvas.height = Math.floor(rect.height * ratio);
      ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
      initCtx();
      ctx.clearRect(0, 0, rect.width, rect.height);
      if (prev) {
        const img = new Image();
        img.onload = function () {
          ctx.drawImage(img, 0, 0, rect.width, rect.height);
          hidden.value = canvas.toDataURL('image/png');
        };
        img.src = prev;
      }
    }

    function getPoint(event) {
      const rect = canvas.getBoundingClientRect();
      if (event.touches && event.touches[0]) {
        return { x: event.touches[0].clientX - rect.left, y: event.touches[0].clientY - rect.top };
      }
      return { x: event.clientX - rect.left, y: event.clientY - rect.top };
    }

    function startDraw(event) {
      event.preventDefault();
      drawing = true;
      const p = getPoint(event);
      ctx.beginPath();
      ctx.moveTo(p.x, p.y);
    }

    function moveDraw(event) {
      if (!drawing) return;
      event.preventDefault();
      const p = getPoint(event);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
      hasStroke = true;
    }

    function endDraw(event) {
      if (!drawing) return;
      event.preventDefault();
      drawing = false;
      ctx.closePath();
      hidden.value = hasStroke ? canvas.toDataURL('image/png') : '';
    }

    function clearSignature() {
      const rect = canvas.getBoundingClientRect();
      ctx.clearRect(0, 0, rect.width, rect.height);
      hasStroke = false;
      hidden.value = '';
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', moveDraw);
    window.addEventListener('mouseup', endDraw);
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', moveDraw, { passive: false });
    window.addEventListener('touchend', endDraw, { passive: false });
    clearBtn.addEventListener('click', clearSignature);
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
  })();

  (function () {
    const tabForm = document.getElementById('tab-form');
    const bulkButtons = document.querySelectorAll('#tab-form [data-bulk-jawaban]');
    if (!bulkButtons.length || !tabForm) return;

    const STORAGE_KEY = 'auditCssdShowBulkActions';

    function setAllJawaban(targetValue) {
      const radios = document.querySelectorAll('#tab-form input[type="radio"][name^="jawaban["]');
      const groupMap = new Map();

      radios.forEach((radio) => {
        if (!groupMap.has(radio.name)) {
          groupMap.set(radio.name, []);
        }
        groupMap.get(radio.name).push(radio);
      });

      groupMap.forEach((groupRadios) => {
        // Each question appears twice (desktop + mobile) with same name.
        // Browser only allows one checked radio per name, so pick visible input.
        const candidates = groupRadios.filter((radio) => radio.value === targetValue);
        if (!candidates.length) return;

        const visibleCandidate = candidates.find((radio) => {
          const style = window.getComputedStyle(radio);
          return style.display !== 'none' &&
            style.visibility !== 'hidden' &&
            radio.offsetParent !== null;
        });

        const targetRadio = visibleCandidate || candidates[0];
        if (targetRadio) {
          targetRadio.checked = true;
        }
      });
    }

    function setBulkVisibility(isVisible) {
      tabForm.classList.toggle('show-bulk-actions', isVisible);
      try {
        localStorage.setItem(STORAGE_KEY, isVisible ? '1' : '0');
      } catch (e) {
        // Ignore storage errors in restricted browser mode.
      }
    }

    function toggleBulkVisibility() {
      const isVisible = !tabForm.classList.contains('show-bulk-actions');
      setBulkVisibility(isVisible);
    }

    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved === '1') {
        setBulkVisibility(true);
      }
    } catch (e) {
      // Ignore storage errors in restricted browser mode.
    }

    bulkButtons.forEach((button) => {
      button.addEventListener('click', function () {
        const targetValue = this.getAttribute('data-bulk-jawaban');
        if (!targetValue) return;
        setAllJawaban(targetValue);
      });
    });

    document.addEventListener('keydown', function (event) {
      const target = event.target;
      const isTypingField = target && (
        target.tagName === 'INPUT' ||
        target.tagName === 'TEXTAREA' ||
        target.tagName === 'SELECT' ||
        target.isContentEditable
      );
      if (isTypingField) return;

      const key = String(event.key || '').toLowerCase();
      if (event.ctrlKey && event.altKey && key === 'm') {
        event.preventDefault();
        toggleBulkVisibility();
      }
    });
  })();

  (function () {
    const tabForm = document.getElementById('tab-form');
    if (!tabForm) return;

    const rows = tabForm.querySelectorAll('[data-audit-row]');
    const progressText = document.getElementById('auditProgressText');
    const progressFill = document.getElementById('auditProgressFill');
    const progressWarning = document.getElementById('auditProgressWarning');
    const formEl = tabForm.querySelector('form');
    const fotoInput = document.getElementById('fotoInput');
    const fotoPreview = document.getElementById('fotoPreview');

    function updateRowStates() {
      rows.forEach((row) => {
        row.classList.remove('state-ya', 'state-tidak', 'state-na', 'state-missing');
        const checked = row.querySelector('.choice-input:checked');
        if (checked && checked.value) {
          row.classList.add('state-' + checked.value);
        } else {
          row.classList.add('state-missing');
        }
      });

      tabForm.querySelectorAll('.choice-pill, .mobile-option').forEach((el) => {
        el.classList.remove('is-selected');
      });

      tabForm.querySelectorAll('.choice-input:checked').forEach((input) => {
        const holder = input.closest('.choice-pill, .mobile-option');
        if (holder) {
          holder.classList.add('is-selected');
        }
      });
    }

    function updateProgress() {
      const radios = tabForm.querySelectorAll('.choice-input[name^="jawaban["]');
      const names = new Set();
      radios.forEach((radio) => names.add(radio.name));
      const total = names.size;
      let completed = 0;

      names.forEach((name) => {
        if (tabForm.querySelector('.choice-input[name="' + name + '"]:checked')) {
          completed++;
        }
      });

      const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
      const remaining = Math.max(0, total - completed);
      if (progressText) progressText.textContent = completed + ' / ' + total + ' item sudah diisi';
      if (progressFill) progressFill.style.width = percent + '%';
      if (progressWarning) {
        progressWarning.textContent = remaining > 0
          ? 'Masih ada ' + remaining + ' item yang belum dipilih.'
          : 'Semua item sudah terisi, siap simpan audit.';
      }
    }

    function previewFoto() {
      if (!fotoInput || !fotoPreview) return;
      fotoPreview.innerHTML = '';
      const files = Array.from(fotoInput.files || []).slice(0, 5);
      files.forEach((file) => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function (e) {
          const img = document.createElement('img');
          img.src = String(e.target?.result || '');
          img.alt = file.name;
          fotoPreview.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    }

    tabForm.addEventListener('change', function (event) {
      if (event.target.classList.contains('choice-input')) {
        updateRowStates();
        updateProgress();
      }
      if (event.target === fotoInput) {
        previewFoto();
      }
    });

    tabForm.querySelectorAll('[data-section-toggle]').forEach((btn, idx) => {
      const card = btn.closest('[data-section-card]');
      if (!card) return;
      if (idx > 0) card.classList.add('is-collapsed');
      btn.addEventListener('click', () => card.classList.toggle('is-collapsed'));
    });

    if (formEl) {
      formEl.addEventListener('submit', function () {
        const firstMissing = tabForm.querySelector('.audit-row.state-missing');
        if (firstMissing) {
          const sectionCard = firstMissing.closest('[data-section-card]');
          if (sectionCard) {
            sectionCard.classList.remove('is-collapsed');
          }
        }
      });
    }

    updateRowStates();
    updateProgress();
    previewFoto();
  })();
</script>
