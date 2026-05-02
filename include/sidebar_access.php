<?php
declare(strict_types=1);

/**
 * Cached list of allowed halaman keys for the current user (non-admin only).
 *
 * @return list<string>
 */
function ppi_sidebar_allowed_halaman(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    if (empty($_SESSION['user_id'])) {
        return $cache;
    }
    if (isset($_SESSION['role']) && strtolower((string) $_SESSION['role']) === 'admin') {
        return $cache;
    }
    global $koneksi;
    if (!isset($koneksi)) {
        include_once __DIR__ . '/../koneksi.php';
    }
    $uid = (int) $_SESSION['user_id'];
    $st = mysqli_prepare($koneksi, 'SELECT halaman FROM user_access WHERE user_id = ? AND diizinkan = 1');
    if (!$st) {
        return $cache;
    }
    mysqli_stmt_bind_param($st, 'i', $uid);
    mysqli_stmt_execute($st);
    $r = mysqli_stmt_get_result($st);
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $cache[] = (string) $row['halaman'];
        }
    }
    mysqli_stmt_close($st);
    return $cache;
}

function ppi_sidebar_show_module(string $halaman): bool
{
    if (empty($_SESSION['user_id'])) {
        return false;
    }
    if (isset($_SESSION['role']) && strtolower((string) $_SESSION['role']) === 'admin') {
        return true;
    }
    $allowed = ppi_sidebar_allowed_halaman();
    return in_array($halaman, $allowed, true);
}
