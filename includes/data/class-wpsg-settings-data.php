<?php
if (!defined('ABSPATH')) exit;

class WPSG_SettingsData {

    /**
     * Singleton instance
     */
    private static $instance = null;

    private function __construct() {
        // do nothing
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
     * -------------------------------
     * Ambil value settings berdasarkan option_key
     * -------------------------------
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = '') {
        global $wpdb;
        $table = $wpdb->base_prefix . 'wpsg_settings';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT option_value FROM $table WHERE option_key = %s LIMIT 1",
                $key
            )
        );

        return $row ? maybe_unserialize($row->option_value) : $default;
    }

    /**
     * -------------------------------
     * Simpan/update value settings
     * -------------------------------
     *
     * @param string $key
     * @param mixed $value
     * @return int|false ID inserted/updated or false on failure
     */
    public static function set($key, $value) {
        global $wpdb;
        $table = $wpdb->base_prefix . 'wpsg_settings';
        $serialized_value = maybe_serialize($value);

        // Cek apakah key sudah ada
        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $table WHERE option_key = %s", $key)
        );

        if ($exists) {
            $updated = $wpdb->update(
                $table,
                [
                    'option_value' => $serialized_value,
                    'updated_at'   => current_time('mysql')
                ],
                ['id' => $exists],
                ['%s', '%s'],
                ['%d']
            );
            return $updated !== false ? $exists : false;
        } else {
            $inserted = $wpdb->insert(
                $table,
                [
                    'option_key'   => $key,
                    'option_value' => $serialized_value,
                    'created_at'   => current_time('mysql')
                ],
                ['%s', '%s', '%s']
            );
            return $inserted !== false ? intval($wpdb->insert_id) : false;
        }
    }

    // Ambil value settings berdasarkan option_key
    public static function get_setting($key, $default = '') {
        global $wpdb;
        $table = $wpdb->base_prefix . 'wpsg_settings';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT option_value FROM $table WHERE option_key = %s LIMIT 1", 
            $key
        ));
        return $row ? maybe_unserialize($row->option_value) : $default;
    }

    /**
     * -------------------------------
     * Ambil semua business types
     * -------------------------------
     *
     * @return array
     */
    public static function get_business_types() {
        return self::get('wpsg_business_types', []);
    }

    /**
     * -------------------------------
     * Simpan/update business types
     * -------------------------------
     *
     * @param array $data
     * @return int|false
     */
    public static function set_business_types(array $data) {
        return self::set('wpsg_business_types', $data);
    }

    /**
     * -------------------------------
     * Ambil data platform-public
     * -------------------------------
     *
     * @return array
     */
    public static function get_platform_public() {
        $key = 'wpsg_platform_public';
        $data = self::get($key, null);
        if ($data === null) {
            // default fallback
            $data = []; // bisa diganti sesuai default JSON jika perlu
            self::set($key, $data);
        }
        return $data;
    }

    /**
     * -------------------------------
     * Simpan data platform-public
     * -------------------------------
     *
     * @param array $data
     * @return int|false
     */
    public static function set_platform_public($data) {
        if (!is_array($data)) return false;
        return self::set('wpsg_platform_public', $data);
    }

    /**
     * -------------------------------
     * Ambil data platform-private
     * -------------------------------
     *
     * @return array
     */
    public static function get_platform_private() {
        $key = 'wpsg_platform_private';
        $data = self::get($key, null);
        if ($data === null) {
            $data = []; // fallback default
            self::set($key, $data);
        }
        return $data;
    }

    /**
     * -------------------------------
     * Simpan data platform-private
     * -------------------------------
     *
     * @param array $data
     * @return int|false
     */
    public static function set_platform_private($data) {
        if (!is_array($data)) return false;
        return self::set('wpsg_platform_private', $data);
    }

    /**
     * -------------------------------
     * Create settings table
     * -------------------------------
     */
    private static function _create_tables() {

        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        $sqls = ["CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}wpsg_settings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            option_key VARCHAR(191) NOT NULL,
            option_value LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY option_key_unique (option_key)
        ) $charset_collate;"];

        return $sqls;

    }

    /**
     * -------------------------------
     * Method baru: Create settings tables
     * -------------------------------
     */
    public static function create_tables() {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Ambil semua SQL create table dari method create_table
        $sqls = self::_create_tables();

        foreach ($sqls as $table_name => $sql) {
            dbDelta($sql);
        }
    }

}
