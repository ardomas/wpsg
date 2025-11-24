<?php
/**
 * Plugin Name: WPSG — Wonder Pieces in Simple Gear
 * Plugin URI:  https://ardomas.com/
 * Description: Modular utility plugin containing small tools for WordPress. Developed by Sam & Gepeto.
 * Version:     0.1.0
 * Author:      Samodra & Gepeto
 * Text Domain: wpsg
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

// require_once __DIR__ . '/includes/autoload.php';

// Constants
define('WPSG_VERSION', '0.1.0');
define('WPSG_DIR', plugin_dir_path(__FILE__));
define('WPSG_URL', plugin_dir_url(__FILE__));

require_once WPSG_DIR . 'includes/class-wpsg-config.php';

register_activation_hook(__FILE__, function() {
    WPSG_AdminData::get_instance(); // pastikan instance terload
    WPSG_AdminData::create_settings_table(); // buat tabel
});

// Load Admin Frontend class
if( is_admin() ){
    require_once WPSG_DIR . 'admin/admin-frontend.php';
}

// Init main admin class
add_action('plugins_loaded', function () {
    if (class_exists('WPSG_AdminFrontend')) {
        WPSG_AdminFrontend::get_instance();
    }
});

require_once WPSG_DIR . 'includes/class-admin-data.php';
WPSG_AdminData::get_instance();

require_once WPSG_DIR . 'includes/class-admin-profile.php';
new \WPSG\WPSG_AdminProfile();