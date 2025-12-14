<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPSG_GalleriesData {

    private static $table_main;
    private static $table_item;

    public static function init() {
        global $wpdb;

        self::$table_main = $wpdb->base_prefix . 'wpsg_albummedia';
        self::$table_item = $wpdb->base_prefix . 'wpsg_albummedia_items';
    }

    /* ---------------------------------------------
     * CREATE TABLE
     * --------------------------------------------- */
    public static function create_tables() {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        $sql1 = "CREATE TABLE " . self::$table_main . " (
            id BIGINT UNSIGNED AUTO_INCREMENT,
            site_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            thumbnail_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset;";

        $sql2 = "CREATE TABLE " . self::$table_item . " (
            id BIGINT UNSIGNED AUTO_INCREMENT,
            album_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            position INT DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (album_id) REFERENCES " . self::$table_main . "(id) ON DELETE CASCADE
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql1);
        dbDelta($sql2);
    }

    /* ---------------------------------------------
     * BASIC CRUD - MAIN (ALBUM)
     * --------------------------------------------- */

    public static function insert_main($data) {
        global $wpdb;
        $wpdb->insert(self::$table_main, $data);
        return $wpdb->insert_id;
    }

    public static function update_main($data, $id) {
        global $wpdb;
        return $wpdb->update(self::$table_main, $data, ['id' => $id]);
    }

    public static function delete_main($id) {
        global $wpdb;
        return $wpdb->delete(self::$table_main, ['id' => $id]);
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . self::$table_main . " WHERE id = %d", $id)
        );
    }

    public static function get_data($args = []) {
        global $wpdb;

        $table = self::$table_main; // atau self::$table_item
        $conditions = [];
        $key_values = [];

        $filterable = [
            'id'            => 'int',
            'site_id'       => 'int',
            'title'         => 'string',
            'thumbnail_id'  => 'int',
            'created_at'    => 'string',
            'updated_at'    => 'string'
        ];

        foreach ( $filterable as $key=>$type) { 
            if (!empty($args[$key])) { 
                switch( $type ){
                    case 'int' :
                        $conditions[] = "{$key} = %d"; 
                        $key_values[] = intval( $args[$key] ); 
                        break;
                    case 'string' :
                    default:
                        $conditions[] = "{$key} = %s"; 
                        $key_values[] = strval( $args[$key] ); 
                        break;
                }
            } 
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $order_by = "created_at DESC, updated_at DESC";
        $sql = "SELECT * FROM {$table} {$where} ORDER BY {$order_by}";

        if (!empty($args['limit'])) {
            $limit  = max(1, intval($args['limit']));
            $offset = max(0, intval($args['offset'] ?? 0));
            $sql   .= " LIMIT {$offset}, {$limit}";
        }

        return !empty($key_values)
            ? $wpdb->get_results($wpdb->prepare($sql, $key_values), ARRAY_A)
            : $wpdb->get_results($sql, ARRAY_A);
    }

    /* ---------------------------------------------
     * BASIC CRUD - ITEMS
     * --------------------------------------------- */

    public static function insert_item($data) {
        global $wpdb;
        $wpdb->insert(self::$table_item, $data);
        return $wpdb->insert_id;
    }

    public static function update_item( $data, $id ){
        global $wpdb;
        return $wpdb->update(self::$table_item, $data, ['id' => $id]);
    }

    public static function delete_item($id) {
        global $wpdb;
        return $wpdb->delete(self::$table_item, ['id' => $id]);
    }

    public static function get_item_by_id($item_id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_item . " WHERE id = %d",
                $item_id
            )
        );
    }

    public static function get_items_by_album($album_id) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_item . " WHERE album_id = %d ORDER BY position ASC, id ASC",
                $album_id
            )
        );
    }
}
