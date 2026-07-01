<?php
/**
 * Router for PHP's built-in development server.
 * Serve existing files from webroot, route everything else to CakePHP front controller.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

if (strpos($path, '..') === false) {
    $file = __DIR__ . '/webroot' . $path;
    if (is_file($file)) {
        return false;
    }
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require __DIR__ . '/webroot/index.php';
