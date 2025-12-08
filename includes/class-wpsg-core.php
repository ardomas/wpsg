<?php

if (!defined('ABSPATH')) exit;

class WPSG_Core {

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
            do_action('wpsg_core_loaded', self::$instance);
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->load_textdomain();
        $this->register_hooks();
    }

    private function define_constants() {
        // Constants boleh dipindah ke sini di masa depan
    }

    private function load_textdomain() {
        load_plugin_textdomain(
            'wpsg',
            false,
            dirname(plugin_basename(WPSG_DIR)) . '/languages/'
        );
    }

    private function register_hooks() {

        // Hook identifikasi core — digunakan add-on
        add_filter('wpsg/core/version', function() {
            return WPSG_VERSION;
        });

        add_filter('wpsg/core/loaded', function() {
            return true;
        });

        do_action('wpsg/core/ready');
    }

    // Add-on dapat memanggil:
    // if (WPSG_Core::check_min_version('0.1.0'))
    public static function check_min_version($min) {
        return version_compare(WPSG_VERSION, $min, '>=');
    }
}
