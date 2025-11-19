<?php
// admin/admin-frontend.php

if ( ! defined('ABSPATH') ) exit;

class WPSG_Admin_Frontend {

    private static $instance = null;

    private function __construct() {
        // Register admin menu
        // add_action('admin_menu', [$this, 'register_admin_menu']);

        // Enqueue scripts/styles untuk admin page WPSG
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_admin_menu() {
        add_menu_page(
            'WPSG Dashboard',          // Page title
            'WPSG Admin',              // Menu title
            'manage_options',          // Capability
            'wpsg-admin',              // Menu slug
            [$this, 'load_admin_page'],// Callback
            'dashicons-admin-generic', // Icon
            3                          // Position
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wpsg-admin') !== false) {
            // WP Editor & Media
            wp_enqueue_editor();
            wp_enqueue_media();

            // CSS & JS custom
            wp_enqueue_script(
                'wpsg-profile-js',
                plugin_dir_url(__FILE__) . 'views/profile.js',
                ['jquery', 'wp-mediaelement', 'wp-editor'],
                '1.0',
                true
            );
            wp_enqueue_style(
                'wpsg-admin-css',
                plugin_dir_url(__FILE__) . 'views/profile.css',
                [],
                '1.0'
            );
        }
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
        }

        require_once plugin_dir_path(__FILE__) . 'views/layout.php';
    }
}

// Inisialisasi hanya sekali
WPSG_Admin_Frontend::get_instance();
