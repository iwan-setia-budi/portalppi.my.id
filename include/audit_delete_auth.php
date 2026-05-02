<?php
declare(strict_types=1);

/**
 * Hanya role admin yang boleh menghapus data audit / supervisi / temuan supervisi.
 */

function ppi_audit_delete_allowed(): bool
{
    return isset($_SESSION['role']) && (string) $_SESSION['role'] === 'admin';
}

/**
 * Untuk handler hapus (mis. hapus_audit.php): redirect jika bukan admin.
 */
function ppi_require_admin_delete_redirect(string $redirectRelativeUrl): void
{
    if (ppi_audit_delete_allowed()) {
        return;
    }
    $sep = str_contains($redirectRelativeUrl, '?') ? '&' : '?';
    header('Location: ' . $redirectRelativeUrl . $sep . 'delete_denied=1');
    exit;
}
