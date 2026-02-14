<?php
// router.php for PHP built-in server

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Verify if the requested file exists in public/
$file = __DIR__ . '/public' . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // Serve static file directly
}

// Otherwise, route to index.php
require __DIR__ . '/public/index.php';
