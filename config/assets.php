<?php

if (!defined('BASE_URL')) {
    $hostName = $_SERVER['HTTP_HOST'] ?? '';
    $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';

    $isLocal = (
        $hostName === 'localhost' ||
        strpos($hostName, 'localhost') !== false ||
        $serverAddr === '127.0.0.1' ||
        $serverAddr === '::1'
    );

    define('BASE_URL', $isLocal ? '/portalppi.my.id' : '');
}

if (!defined('ASSET_VERSION')) {
    define('ASSET_VERSION', '1a');
}

if (!function_exists('asset')) {
    function asset($path)
    {
        $normalizedPath = ltrim((string) $path, '/');
        $base = rtrim((string) BASE_URL, '/');
        return ($base !== '' ? $base : '') . '/' . $normalizedPath . '?v=' . ASSET_VERSION;
    }
}
