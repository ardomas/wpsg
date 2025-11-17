<?php
// File: includes/tools/locales/locales-loader.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Locales module loader for WPSG
 */

// Require class files
require_once WPSG_DIR . 'includes/tools/locales/class-locale-base.php';
require_once WPSG_DIR . 'includes/tools/locales/class-locale-date.php';
require_once WPSG_DIR . 'includes/tools/locales/class-locale-currency.php';

// Initialize module
add_action( 'plugins_loaded', function() {
    if ( class_exists( 'WPSG_Locales_Base' ) ) {
        WPSG_Locales_Base::init();
    }
}, 20 );
