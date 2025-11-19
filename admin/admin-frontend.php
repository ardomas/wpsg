<?php
// admin/admin-frontend.php

if ( ! defined('ABSPATH') ) exit;

class WPSG_Admin_Frontend {

    private static $instance = null;

    private function __construct() {
        // if (!has_action('admin_menu', array($this, 'register_admin_menu'))) {
        //     add_action('admin_menu', array($this, 'register_admin_menu'));
        // }
    }

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_admin_menu() {
        add_menu_page(
            'WPSG Dashboard',        // Page title
            'WPSG Admin',            // Menu title
            'manage_options',        // Capability
            'wpsg-admin',            // Menu slug
            array($this, 'load_admin_page'), // Callback
            'dashicons-admin-generic', // Icon
            3                        // Position
        );
    }

    public function load_admin_page() {
        $page = $_GET['subpage'] ?? 'dashboard';
        $GLOBALS['wpsg_current_page'] = $page;

        switch ($page) {
            case 'profile':
                $GLOBALS['wpsg_view_file'] = plugin_dir_path(__FILE__) . 'views/profile.php';
                break;
            case 'dashboard':
            default:
                $GLOBALS['wpsg_view_file'] = plugin_dir_path(__FILE__) . 'views/dashboard.php';
                $GLOBALS['wpsg_view_file'] = null;
        }

        require_once plugin_dir_path(__FILE__) . 'views/layout.php';
    }
}

// Inisialisasi hanya sekali
WPSG_Admin_Frontend::get_instance();
