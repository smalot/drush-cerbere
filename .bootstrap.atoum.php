<?php

// composer
require __DIR__ . '/vendor/autoload.php';

/**
 * Creates a temporary directory and return its path.
 */
function drush_tempdir()
{
    $tmp_dir = sys_get_temp_dir();
    $tmp_dir .= '/' . 'drush_tmp_' . uniqid(time() . '_');

    return $tmp_dir;
}
