<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PSR-4 Autoloader for WPSG Plugin
 */
spl_autoload_register(function ($class) {

    // Only autoload classes in WPSG namespace
    if (strpos($class, 'WPSG\\') !== 0) {
        return;
    }

    // Remove namespace prefix
    $relative_class = substr($class, strlen('WPSG\\'));

    // Replace namespace separators with directory separators
    $relative_path = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);

    $file = plugin_dir_path(__FILE__) . '../src/' . $relative_path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
