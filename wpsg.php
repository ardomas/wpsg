<?php
/**
 * Plugin Name: WPSG Core — Wonder Pieces in Simple Gear
 * Plugin URI:  https://ardomas.com/
 * Description: Modular utility plugin containing small tools for WordPress. Developed by Sam & Gepeto.
 * Version:     0.9.1
 * Author:      Samodra & Gepeto
 * Text Domain: wpsg
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

// --------------------------------------------------
// Core Constants
// --------------------------------------------------
define('WPSG_VERSION', '0.1.0');
define('WPSG_DIR', plugin_dir_path(__FILE__));
define('WPSG_URL', plugin_dir_url(__FILE__));
define('WPSG_CORE_LOADED', true);            // <-- Add-on detector
define('WPSG_CORE_MIN_VERSION', '0.1.0');    // <-- Add-on version check

// --------------------------------------------------
// Load Core Class
// --------------------------------------------------
require_once WPSG_DIR . 'includes/class-wpsg-core.php';

// Boot Core
WPSG_Core::instance();

// --------------------------------------------------
// Old Autoloader + Includes (untouched)
// --------------------------------------------------
$mapping_class_files = [

    /* Helpers */
    WPSG_DIR . '/includes/helpers.php',

    /* Tools - locales */
    WPSG_DIR . '/includes/tools/locales/class-locale-base.php',
    WPSG_DIR . '/includes/tools/locales/class-locale-date.php',
    WPSG_DIR . '/includes/tools/locales/class-locale-currency.php',
];

// Load mapped class files
foreach ($mapping_class_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

require_once WPSG_DIR . 'includes/autoloader.php';
WPSG_Autoloader::register();

// Initialize locales module
add_action('plugins_loaded', function() {
    if (class_exists('WPSG_LocalesBase')) {
        WPSG_LocalesBase::init();
    }
}, 20);

// --------------------------------------------------
// Activation Hook (unchanged)
// --------------------------------------------------
WPSG_AdminData::get_instance();
register_activation_hook(__FILE__, function() {

    ob_start();

    WPSG_PostsData::get_instance();

    WPSG_SettingsData::get_instance();
    WPSG_SettingsData::create_tables();

    WPSG_PersonsData::get_instance();
    WPSG_PersonsData::create_tables();

    WPSG_ProfilesRepository::init();

    WPSG_GalleriesData::init();
    WPSG_GalleriesData::create_tables();

    $rep_memberships = new WPSG_MembershipsRepository();
    $rep_memberships->create_tables();

    $leak = ob_get_clean();
    file_put_contents(WPSG_DIR . 'activation_output.log', $leak);

});

// Load singleton instances

// --------------------------------------------------
// Admin Frontend
// --------------------------------------------------
if (is_admin()) {
    require_once WPSG_DIR . 'modules/admin-frontend.php';
    add_action('plugins_loaded', function () {
        if (class_exists('WPSG_AdminFrontend')) {
            WPSG_AdminFrontend::get_instance();
        }
    });
}

// --------------------------------------------------
// Announcements
// --------------------------------------------------
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

// --------------------------------------------------
// Galleries
// --------------------------------------------------
// add_action('plugins_loaded', function() {
//     $albummedia = new WPSG_AlbumMediaRepository();
// });

require_once WPSG_DIR . 'modules/galleries/main.php';
add_action('plugins_loaded', function(){
    do_action('wp_wpsg_galleries');
});

/*
add_filter('template_include', function($template) {
    if (is_front_page()) {
        return WPSG_DIR . '/modules/frontend/front-page.php';
    }
    return $template;
});
*/

add_filter('template_include', 'wpsg_load_front_page_template');

function wpsg_load_front_page_template($template) {

    if (is_front_page()) {

        // Tetap pakai template theme (front-page.php atau page.php)
        // tetapi kita ganti bagian content-nya nanti.
        add_action('wp', function() {
            remove_all_actions('the_content');
            add_filter('the_content', function() {
                ob_start();
                include WPSG_DIR . 'modules/frontend/front-page.php';
                return ob_get_clean();
            });
        }, 20);
    }

    return $template;
}

function wpsg_enqueue_frontend_styles() {
    $css_files = [
        'wpsg-core_layout' => 'assets/css/core-layout.css',
        'wpsg-frontend'    => 'modules/frontend/assets/css/frontend.css',
    ];
    foreach( $css_files as $css_key => $css_file ){
        wp_enqueue_style(
            $css_key, // handle
            plugins_url( $css_file, __FILE__), // path ke file CSS
            [],     // dependencies
            filemtime( plugin_dir_path(__FILE__) . $css_file ), // versi cache-busting
            'all'   // media
        );

    }
}

add_action('wp_enqueue_scripts', 'wpsg_enqueue_frontend_styles');

// load frontend module
$frontend_loader = WPSG_DIR . 'modules/frontend/frontend-loader.php';
if ( file_exists($frontend_loader) ) {
    require_once $frontend_loader;
}

// Load shortcode module
require_once WPSG_DIR . 'modules/shortcodes/shortcodes-loader.php';
