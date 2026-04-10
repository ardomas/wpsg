<?php
/**
 * Plugin Name: WPSG Core — Wonder Pieces in Simple Gear
 * Plugin URI:  https://wordpress.ardomas.com/
 * Description: Modular utility plugin containing small tools for WordPress. Developed by Sam (assisted by Gepeto - an OpenAI Persona).
 * Version:     0.9.1
 * Author:      Samodra
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

    /* Factory Handlers */
    WPSG_DIR . '/includes/factories/users.php',
    WPSG_DIR . '/includes/factories/activities.php',
    WPSG_DIR . '/includes/factories/indicators.php',
    WPSG_DIR . '/includes/factories/children.php',

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
    // WPSG_AppFormHandler::register();
}, 20);

// --------------------------------------------------
// Activation Hook (unchanged)
// --------------------------------------------------
WPSG_AdminData::get_instance();
register_activation_hook(__FILE__, function() {

    ob_start();

    WPSG_PostsData::get_instance();
    // $svc_contents = WPSG_ContentsService();
    // WPSG_ContentsService::get_instance();

    WPSG_SettingsData::get_instance();
    WPSG_SettingsData::create_tables();

    WPSG_PersonsData::get_instance();
    WPSG_PersonsData::create_tables();

    WPSG_SitePersonsData::create_table();

    $person_rel = new WPSG_PersonRelationsRepository();
    $person_rel->activate();
    $person_rec = new WPSG_PersonRecordsRepository();
    $person_rec->activate();

    $rep_memberships = new WPSG_MembershipsRepository();
    $rep_memberships->create_tables();

    WPSG_ProfilesRepository::init();

    WPSG_GalleriesData::init();
    WPSG_GalleriesData::create_tables();

    // Indicators
    // First - create tables for categories, attributes, and indicators
    $mst_indicator_categories = new WPSG_IndicatorCategoriesData();
    $mst_indicator_categories->create_tables();
    $mst_indicator_attributes = new WPSG_IndicatorAttributesData();
    $mst_indicator_attributes->create_tables();
    $mst_indicators = new WPSG_IndicatorsData();
    $mst_indicators->create_tables();
    // Then - create relation tables
    $mst_indicator_attribute_relations = new WPSG_IndicatorAttributeRelationsData();
    $mst_indicator_attribute_relations->create_tables();
    //

    // master table => Daily Activity
    $daily_activities = new WPSG_DailyActivitiesData();
    $daily_activities->create_table();
    // Person Activities
    $person_activities = new WPSG_PersonActivitiesData();
    $person_activities->create_table();
    //

    $leak = ob_get_clean();
    file_put_contents(WPSG_DIR . 'activation_output.log', $leak);

});

require_once WPSG_DIR . 'modules/class-postcontent-modules.php';
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

require_once WPSG_DIR . 'modules/galleries/main.php';
add_action('plugins_loaded', function(){
    do_action('wp_wpsg_galleries');
});

require_once WPSG_DIR . 'includes/ajax/class-wpsg-galleries-ajax.php';
add_action('plugins_loaded', function(){
    new WPSG_GalleriesAjax();
});

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
        "wpsg-buttons"     => 'assets/css/wpsg-buttons.css',
        'wpsg-frontend'    => 'modules/frontend/assets/css/frontend.css',
         // Tambahkan file CSS lainnya di sini
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
$frontend_loader = WPSG_DIR . 'modules/frontend/loader.php';
if ( file_exists($frontend_loader) ) {
    require_once $frontend_loader;
}

// Load shortcode module
require_once WPSG_DIR . 'modules/shortcodes/shortcodes-loader.php';

add_action('wp_ajax_wpsg_add_gallery_item', function () {

    $album_id = intval($_POST['album_id']);
    $post_id  = intval($_POST['post_id']);

    $service = new WPSG_GalleriesService();
    $service->save_item([
        'album_id' => $album_id,
        'post_id'  => $post_id,
        'position' => 0
    ]);

    wp_send_json_success();
});

add_action('init', function () {
    WPSG_AppFormHandler::register();
});

// Load singleton instances

// --------------------------------------------------
// Admin Frontend - Main plugin module here
// --------------------------------------------------
if (is_admin()) {
    require_once WPSG_DIR . 'modules/admin-frontend.php';
    add_action('plugins_loaded', function () {
        if (class_exists('WPSG_AdminFrontend')) {
            WPSG_AdminFrontend::get_instance();
        }
    });
}

/*
add_action( 'rest_api_init', function () {

    require_once( WPSG_DIR . '/includes/rest/class-wpsg-children-rest-controller.php' );
    $children_service = new WPSG_ChildrenService(
        new WPSG_PersonsService(),
        new WPSG_PersonRelationsService(
            new WPSG_PersonsService()
        ),
        new WPSG_SitePersonsRepository()
    );

    $children_controller = new WPSG_ChildrenRESTController( $children_service );
    $children_controller->register_routes();

});
*/

/*
function wpsg_enqueue_children_api_client(){

    // Optional: batasi hanya di halaman children
    // if ( $hook !== 'wpsg_page_children' ) return;

    wp_enqueue_script(
        'wpsg-children-list',
        WPSG_URL . '/modules/children/assets/js/children-list.js',
        [ 'wp-api-fetch' ],
        '1.0',
        true
    );

    wp_localize_script(
        'wpsg-children-list',
        'WPSG_CHILDREN',
        [
            'api_url' => rest_url( 'wpsg/v1/children' ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ]
    );

}

add_action( 'wp_enqueue_scripts', 'wpsg_enqueue_children_api_client');
add_action( 'admin_enqueue_scripts', 'wpsg_enqueue_children_api_client');
*/