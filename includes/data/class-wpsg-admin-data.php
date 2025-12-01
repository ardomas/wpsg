<?php
if (!defined('ABSPATH')) exit;

class WPSG_AdminData {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Data dari JSON
     */
    private static $data = [];

    private function __construct() {
        self::load_json();
    }

    /**
     * Ambil instance singleton
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load JSON admin
     */
    private static function load_json() {
        $json_file = WPSG_DIR . 'assets/json/admin.json';
        if (file_exists($json_file)) {
            $content = file_get_contents($json_file);
            self::$data = json_decode($content, true);
        }
    }

    /**
     * Ambil semua data JSON
     */
    public static function get_all() {
        return self::$data;
    }

    /**
     * Ambil value berdasarkan key
     */
    public static function get($key, $default = null) {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }

    /**
     * Proses data menjadi menu (internal)
     */
    private static function cast_as_menu($raw_data) {
        $clean_data = [];

        foreach ($raw_data as $key => $item) {
            if (!isset($item['dashboard'])) $item['dashboard'] = true;
            if (!isset($item['view'])) $item['view'] = true;
            if (!isset($item['site'])) $item['site'] = 'main';

            if (($item['dashboard'] || $item['view']) && ($item['site'] === 'all' || is_super_admin())) {
                $clean_data[$key] = $item;
            }
        }

        return $clean_data;
    }

    /**
     * Ambil menu sidebar
     */
    public static function get_sidebar_menu() {
        $raw_sidebar = self::get('sidebar', []);
        return isset($raw_sidebar['data']) ? self::cast_as_menu($raw_sidebar['data']) : [];
    }

}
