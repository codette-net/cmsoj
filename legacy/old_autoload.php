<?php

// Simple PSR-4 autoloader for CMSOJ\
spl_autoload_register(function ($class) {

    // Only handle classes starting with 'CMSOJ\'
    if (strpos($class, 'CMSOJ\\') !== 0) {
        return;
    }

    // Convert namespace → file path
    // CMSOJ\Controllers\CalendarController
    // → CMSOJ/Controllers/CalendarController.php
    $path = __DIR__ . '/' . str_replace('CMSOJ\\', '', $class);
    $path = str_replace('\\', '/', $path);
    $file = $path . '.php';

    if (file_exists($file)) {
        require_once $file;
    } else {
        // For debugging — you may remove later
        error_log("Autoload: Class $class not found at $file");
    }
});
