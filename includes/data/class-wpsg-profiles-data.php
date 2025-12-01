<?php
if (!defined('ABSPATH')) exit;

class WPSG_ProfilesData {

    /**
     * Singleton instance
     */
    private static $instance = null;
    private $wpsg_settings = null;

    /**
     * Constructor private untuk singleton
     */
    private function __construct() {
        // Bisa ditambahkan inisialisasi khusus jika perlu
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
     * Generate SQL untuk semua tabel WPSG
     * -------------------------------
     */
    private static function _create_tables() {
        global $wpdb;

        // Charset dan collate
        $charset_collate = $wpdb->get_charset_collate();

        $tables = [
            // Tabel data multi-site
            'wpsg_data' => "CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}wpsg_data (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                site_id BIGINT UNSIGNED NOT NULL,
                data_key VARCHAR(191) NOT NULL,
                data_value LONGTEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY site_key_unique (site_id, data_key)
            ) $charset_collate;"
        ];

        return $tables;
    }

    // Ambil data platform-private, pakai default jika belum ada
    public static function get_platform_private() {
        $option_key = 'wpsg_platform_private';
        $data = WPSG_SettingsData::get_setting($option_key, null);

        if ($data === null) {
            $data = WPSG_SettingsData::get('platform-private-default', []);
            WPSG_SettingsData::set_setting($option_key, $data);
        }

        return $data;
    }

    // Simpan data platform-private, pakai default jika belum ada
    public static function set_platform_private($data) {
        if (!is_array($data)) return false;
        return WPSG_SettingsData::set_setting('wpsg_platform_private', $data);
    }

    // Ambil data platform-public, pakai default jika belum ada
    public static function get_platform_public() {
        $option_key = 'wpsg_platform_public';
        $data = WPSG_SettingsData::get_setting($option_key, null);

        if ($data === null) {
            $data = WPSG_SettingsData::get('platform-public-default', []);
            WPSG_SettingsData::set_setting($option_key, $data);
        }

        return $data;
    }

    // Simpan data platform-public, pakai default jika belum ada
    public static function set_platform_public($data) {
        if (!is_array($data)) return false;
        return WPSG_SettingsData::set_setting('wpsg_platform_public', $data);
    }

    /**
     * Ambil data dari wp_wpsg_data
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $site_id
     * @return mixed
     */
    public static function get_data($key, $default = null, $site_id = null) {
        global $wpdb;

        if ($site_id === null) {
            $site_id = wpsg_get_network_id();
        }

        if ($site_id === "*") {
            // Ambil data untuk seluruh site
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT site_id, data_value FROM {$wpdb->base_prefix}wpsg_data WHERE data_key = '%s' AND deleted_at IS NULL",
                    $key
                ),
                ARRAY_A
            );

            $result = [];
            foreach ($rows as $row) {
                $result[$row['site_id']] = maybe_unserialize($row['data_value']);
            }
            return $result;
        }

        $sql = $wpdb->prepare( "SELECT data_value FROM {$wpdb->base_prefix}wpsg_data WHERE site_id = %d AND data_key = %s AND deleted_at IS NULL", $site_id, $key );
        $row = $wpdb->get_row( $sql );

        return $row ? maybe_unserialize($row->data_value) : $default;

    }

    /**
     * Simpan data ke wp_wpsg_data
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $site_id
     * @return void
     */
    public static function set_data($key, $value, $site_id = null) {
        global $wpdb;

        if ($site_id === null) {
            $site_id = wpsg_get_network_id();
        }

        $value = maybe_serialize($value);

        if ($site_id === "*") {
            // Update untuk semua site
            $sites = wpsg_get_networks(['fields' => 'ids']);
            foreach ($sites as $id) {
                $wpdb->replace(
                    "{$wpdb->base_prefix}wpsg_data",
                    [
                        'site_id'    => $id,
                        'data_key'   => $key,
                        'data_value' => $value,
                        'deleted_at' => null,
                    ],
                    ['%d', '%s', '%s', '%s']
                );
            }
            return;
        }

        // Update untuk site tunggal
        $wpdb->replace(
            "{$wpdb->base_prefix}wpsg_data",
            [
                'site_id'    => $site_id,
                'data_key'   => $key,
                'data_value' => $value,
                'deleted_at' => null,
            ],
            ['%d', '%s', '%s', '%s']
        );
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
