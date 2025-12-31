<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the request is for a real file (css/js/img etc.), serve it normally
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Else, redirect everything to index.php (our front controller)
require_once __DIR__ . '/index.php';
