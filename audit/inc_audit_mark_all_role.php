<?php
/**
 * Fitur mark-all (Semua Ya / Tidak / NA + pintasan) hanya untuk role admin.
 * Non-admin: blok tidak di-render (tampilan untuk akreditasi/lembaga eksternal lebih bersih).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$auditMarkAllEnabled = isset($_SESSION['role']) && strtolower((string) $_SESSION['role']) === 'admin';
