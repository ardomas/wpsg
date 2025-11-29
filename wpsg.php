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
    WPSG_DIR . '/includes/helpers.php' ,

    /* tools - locales */
    WPSG_DIR . '/includes/tools/locales/class-locale-base.php',     // class WPSG_LocalesBase
    WPSG_DIR . '/includes/tools/locales/class-locale-date.php',     // class WPSG_DateFormatter
    WPSG_DIR . '/includes/tools/locales/class-locale-currency.php', // class WPSG_CurrencyFormatter

    /* handling - admin.json */
    WPSG_DIR . '/includes/data/class-wpsg-admin-data.php',               // class WPSG_AdminData

    /* handling - wpsg tables => wp_wpsg_posts, wp_wpsg_postmeta, wp_wpsg_cmments */
    WPSG_DIR . '/includes/data/class-wpsg-posts-data.php',          // class WPSG_PostsData

    WPSG_DIR . '/includes/data/class-wpsg-persons-data.php',

    WPSG_DIR . '/includes/repositories/class-wpsg-persons-repository.php',
    WPSG_DIR . '/includes/repositories/class-wpsg-memberships-repository.php',

    WPSG_DIR . '/includes/services/class-wpsg-memberships-service.php',

];

// Initialize module
add_action( 'plugins_loaded', function() {
    if ( class_exists( 'WPSG_LocalesBase' ) ) {
        WPSG_LocalesBase::init();
    }
}, 20 );

foreach( $mapping_class_files as $item_file ){
    if ( file_exists( $item_file ) ) {
        require_once $item_file;
    }
}

register_activation_hook(__FILE__, function() {
    WPSG_AdminData::get_instance();          // pastikan get_instance terload
    WPSG_AdminData::create_settings_table(); // buat tabel
    // WPSG_PostsData::get_instance();
});


WPSG_AdminData::get_instance();
WPSG_PostsData::get_instance();

// Load Admin Frontend class
if( is_admin() ){

    require_once WPSG_DIR . 'modules/admin-frontend.php';
    // Init main admin class
    add_action('plugins_loaded', function () {
        if (class_exists('WPSG_AdminFrontend')) {
            WPSG_AdminFrontend::get_instance();
        }
    });

}

require_once WPSG_DIR . 'modules/announcements.php';
add_action('wp_ajax_wpsg_save_announcement', function(){

    $form = new WPSG_Announcements();
    $post_id = $form->save_announcement();

    if($post_id){
        wp_send_json_success(['post_id' => $post_id]);
    } else {
        wp_send_json_error(['message' => 'Unable to save announcement.']);
    }
});

