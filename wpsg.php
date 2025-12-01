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

// Constants
define('WPSG_VERSION', '0.1.0');
define('WPSG_DIR', plugin_dir_path(__FILE__));
define('WPSG_URL', plugin_dir_url(__FILE__));

$mapping_class_files = [

    /* Helpers */
    WPSG_DIR . '/includes/helpers.php',

    /* Tools - locales */
    WPSG_DIR . '/includes/tools/locales/class-locale-base.php',
    WPSG_DIR . '/includes/tools/locales/class-locale-date.php',
    WPSG_DIR . '/includes/tools/locales/class-locale-currency.php',

    /* Handling - admin.json */
    WPSG_DIR . '/includes/data/class-wpsg-admin-data.php',    // class WPSG_AdminData
    WPSG_DIR . '/includes/data/class-wpsg-settings-data.php', // class WPSG_SettingsData
    WPSG_DIR . '/includes/data/class-wpsg-profiles-data.php', // class WPSG_ProfilesData

    /* Handling - wpsg tables => wp_wpsg_posts, wp_wpsg_postmeta, wp_wpsg_comments */
    WPSG_DIR . '/includes/data/class-wpsg-posts-data.php',
    WPSG_DIR . '/includes/data/class-wpsg-persons-data.php',

    /* Repositories */
    WPSG_DIR . '/includes/repositories/class-wpsg-persons-repository.php',
    WPSG_DIR . '/includes/repositories/class-wpsg-memberships-repository.php',

    /* Services */
    WPSG_DIR . '/includes/services/class-wpsg-memberships-service.php',
];

// Load all mapped class files
foreach ($mapping_class_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Initialize locales module
add_action('plugins_loaded', function() {
    if (class_exists('WPSG_LocalesBase')) {
        WPSG_LocalesBase::init();
    }
}, 20);

// Activation hook
register_activation_hook(__FILE__, function() {
    // Pastikan instance dan tabel settings dibuat
    WPSG_AdminData::get_instance();
    // WPSG_AdminData::create_settings_table();

    WPSG_SettingsData::get_instance(); // pastikan WPSG_SettingsData terload
    WPSG_SettingsData::create_tables();

    WPSG_ProfilesData::get_instance(); // pastikan WPSG_ProfilesData terload
    WPSG_ProfilesData::create_tables();

    // WPSG_PostsData::get_instance(); // bila diperlukan
});

// Load singleton instances
WPSG_AdminData::get_instance();
WPSG_PostsData::get_instance();
// WPSG_SettingsData::get_instance();
// WPSG_ProfilesData::get_instance();

// Admin frontend
if (is_admin()) {
    require_once WPSG_DIR . 'modules/admin-frontend.php';
    add_action('plugins_loaded', function () {
        if (class_exists('WPSG_AdminFrontend')) {
            WPSG_AdminFrontend::get_instance();
        }
    });
}

// Announcements module
require_once WPSG_DIR . 'modules/announcements.php';
add_action('wp_ajax_wpsg_save_announcement', function() {
    $form = new WPSG_Announcements();
    $post_id = $form->save_announcement();

    if ($post_id) {
        wp_send_json_success(['post_id' => $post_id]);
    } else {
        wp_send_json_error(['message' => 'Unable to save announcement.']);
    }
});
