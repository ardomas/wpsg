<?php
/**
 * Plugin Name: WPSG — Wonder Pieces Simple Gear
 * Plugin URI:  https://ardomas.com/
 * Description: Modular utility plugin containing small tools for WordPress. Developed by Sam & Gepeto.
 * Version:     0.1.0
 * Author:      Sam & Gepeto
 * Text Domain: wpsg
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WPSG_VERSION', '0.1.0' );
define( 'WPSG_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPSG_URL', plugin_dir_url( __FILE__ ) );

// Load the plugin loader.
// Load Locales Tool
require_once WPSG_DIR . 'includes/tools/locales/locales-loader.php';

require_once WPSG_DIR . 'includes/loader.php';

$locales_loader = WPSG_DIR . 'includes/tools/locales/locales-loader.php';
if ( file_exists( $locales_loader ) ) {
    require_once $locales_loader;
}
