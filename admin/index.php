<?php
// /wp-content/plugins/wpsg/admin/index.php

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once dirname(__FILE__, 3) . '/../../../../wp-load.php';
}

// Path ke layout
$layout = __DIR__ . '/views/layout.php';

if (file_exists($layout)) {
    require $layout;
} else {
    echo "<h1>Layout file not found.</h1>";
}
