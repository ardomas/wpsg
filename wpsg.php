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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define('WPSG_VERSION', '0.1.0');
define('WPSG_DIR', plugin_dir_path(__FILE__));
define('WPSG_URL', plugin_dir_url(__FILE__));

// Include admin frontend class (optional, bisa ditambah nanti)
require_once WPSG_DIR . 'admin/admin-frontend.php';

// ---------------------------
// 1️⃣ Menu WP Admin Dummy
// ---------------------------
add_action('admin_menu', function() {
    add_menu_page(
        'WPSG Admin',
        'WPSG Admin',
        'manage_options',
        'wpsg-admin',
        function() {
            wp_redirect(site_url('/wpsg-admin'));
            exit;
        },
        'dashicons-admin-generic',
        2
    );
});

// ---------------------------
// 2️⃣ Rewrite Rules untuk Pretty URL
// ---------------------------
add_action('init', function() {
    // /wpsg-admin → dashboard
    add_rewrite_rule('^wpsg-admin/?$', 'index.php?wpsg_subpage=dashboard', 'top');
    // /wpsg-admin/<subpage>
    add_rewrite_rule('^wpsg-admin/([^/]+)/?$', 'index.php?wpsg_subpage=$matches[1]', 'top');
});

add_action('admin_init', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'wpsg-admin') {
        wp_safe_redirect(site_url('/wpsg-admin'));
        exit;
    }
});

// Register query_var
add_filter('query_vars', function($vars){
    $vars[] = 'wpsg_subpage';
    return $vars;
});

// ---------------------------
// 3️⃣ Template redirect untuk load page
// ---------------------------
add_action('template_redirect', function() {

    $subpage = get_query_var('wpsg_subpage');
    if (!$subpage) return; // bukan route WPSG Admin

    // Default ke dashboard
    $subpage = sanitize_key($subpage);
    if (empty($subpage)) $subpage = 'dashboard';

    $GLOBALS['wpsg_current_page'] = $subpage;

    // Tentukan view file
    switch($subpage) {
        case 'dashboard':
            $GLOBALS['wpsg_view_file'] = WPSG_DIR . 'admin/views/dashboard.php';
            break;

        case 'profile':
            $GLOBALS['wpsg_view_file'] = WPSG_DIR . 'admin/views/profile.php';
            break;

        default:
            $GLOBALS['wpsg_view_file'] = WPSG_DIR . 'admin/views/404.php';
            break;
    }

    // Load layout
    include WPSG_DIR . 'admin/views/layout.php';
    exit;
});

// ---------------------------
// 4️⃣ Enqueue admin styles & scripts
// ---------------------------
add_action('wp_enqueue_scripts', function() {
    $subpage = get_query_var('wpsg_subpage');
    if (!$subpage) return;

    wp_enqueue_style('dashicons');

    wp_enqueue_style('wpsg-admin-wp-admin', admin_url('css/wp-admin.css'), [], null);
    wp_enqueue_style('wpsg-admin-admin-menu', admin_url('css/admin-menu.css'), [], null);
    wp_enqueue_style('wpsg-admin-common', admin_url('css/common.css'), [], null);
    wp_enqueue_style('wpsg-admin-forms', admin_url('css/forms.css'), [], null);

    wp_enqueue_script('wpsg-admin-common-js', admin_url('js/common.js'), ['jquery'], null, true);
    wp_enqueue_script('wpsg-admin-list-tables', admin_url('js/list-tables.js'), ['jquery'], null, true);
});

// ---------------------------
// Flush rewrite rules saat aktivasi plugin
// ---------------------------
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// ---------------------------
// Optional: flush rewrite rules saat deactivation
// ---------------------------
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
