<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load helper functions.
require_once WPSG_DIR . 'includes/helpers.php';

// Load all modules automatically.
$modules_dir = WPSG_DIR . 'modules/';

foreach ( glob( $modules_dir . '*', GLOB_ONLYDIR ) as $module_path ) {
    $module_init = $module_path . '/init.php';

    if ( file_exists( $module_init ) ) {
        require_once $module_init;
    }
}

wpsg_enqueue_fontawesome();
