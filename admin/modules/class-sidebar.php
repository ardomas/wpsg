<?php
// admin/modules/sidebar-assets.php
if (!defined('ABSPATH')) exit;

class WPSG_Sidebar {

    public static function init() {
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_wpsg-admin' && strpos($hook, 'wpsg-admin') === false) {
            return;
        }
        wp_enqueue_style(
            'wpsg-sidebar-css',
            WPSG_DIR_URL . 'admin/assets/css/sidebar.css',
            [],
            WPSG_VERSION
        );
    }

}
