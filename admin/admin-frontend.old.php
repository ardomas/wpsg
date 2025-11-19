<?php
// admin/admin-frontend.php

if ( ! defined('ABSPATH') ) exit;

class WPSG_Admin_Frontend {

    public function __construct() {
        // add_action('admin_menu', array($this, 'register_admin_menu'));
    }

    public function register_admin_menu() {
        add_menu_page(
            'WPSG Dashboard',        // Page title
            'WPSG Admin',                  // Menu title
            'manage_options',        // Capability
            'wpsg-admin',            // Menu slug
            array($this, 'load_dashboard_page'), // Callback
            'dashicons-admin-generic', // Icon
            3                        // Position
        );
    }

    public function load_dashboard_page() {
        // Include the dashboard page layout
        // require_once plugin_dir_path(__FILE__) . 'views/dashboard.php';
    }
}
