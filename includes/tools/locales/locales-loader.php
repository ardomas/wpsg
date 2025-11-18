<?php
// File: includes/tools/locales/locales-loader.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Locales module loader for WPSG
 */

// List of required classes for the locales tool.
$locales_class_files = [
    WPSG_DIR . 'includes/tools/locales/class-locale-base.php',
    WPSG_DIR . 'includes/tools/locales/class-locale-date.php',
    WPSG_DIR . 'includes/tools/locales/class-locale-currency.php'
];

foreach( $locales_class_files as $item_file ){
    if ( file_exists( $item_file ) ) {
        require_once $item_file;
    }
}

// Initialize module
add_action( 'plugins_loaded', function() {
    if ( class_exists( 'WPSG_Locales_Base' ) ) {
        WPSG_Locales_Base::init();
    }
}, 20 );
