<?php

define('BASE_URL', '/portalppi.my.id');
define('ASSET_VERSION', '1a');

function asset($path)
{
    $normalizedPath = ltrim($path, '/');
    return BASE_URL . '/' . $normalizedPath . '?v=' . ASSET_VERSION;
}
