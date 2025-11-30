<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_PersonsData {

    private static $instance = null;
    private $users;
    private $table;
    private $meta_table;
    private $user_person_key;

    private function __construct() {
        global $wpdb;
        //
        $this->table       = $wpdb->base_prefix . 'wpsg_persons';
        $this->meta_table  = $wpdb->base_prefix . 'wpsg_personmeta';
        $this->users       = $wpdb->base_prefix . 'users';
        //
        $this->user_person_key = 'wpsg_user_person_link_key';
    }

    /**
     * Singleton instance
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Called on plugin activation
     */
    public static function activate() {
        self::create_tables();
    }

    /**
     * Create both persons & meta tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $person_table = $wpdb->base_prefix . 'wpsg_persons';
        $meta_table   = $wpdb->base_prefix . 'wpsg_personmeta';

        $sql_person = "CREATE TABLE $person_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            meta LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB $charset;";

        $sql_meta = "CREATE TABLE $meta_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            person_id BIGINT UNSIGNED NOT NULL,
            meta_key VARCHAR(191) NOT NULL,
            meta_value LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY person_id_key (person_id),
            KEY meta_key_key (meta_key)
        ) ENGINE=InnoDB $charset;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_person);
        dbDelta($sql_meta);
    }

    /* -----------------------------------------
     * CREATE
     * ----------------------------------------- */
    public function create($data) {
        global $wpdb;
        $now = current_time('mysql');

        $insert_data = [
            'name'       => isset($data['name']) ? sanitize_text_field($data['name']) : null,
            'email'      => isset($data['email']) ? sanitize_email($data['email']) : null,
            'phone'      => isset($data['phone']) ? sanitize_text_field($data['phone']) : null,
            'meta'       => isset($data['meta']) ? wp_json_encode($data['meta']) : null,
            'created_at' => $now,
            'updated_at' => $now
        ];

        $wpdb->insert($this->table, $insert_data);
        return $wpdb->insert_id;
    }

    /* -----------------------------------------
     * READ
     * ----------------------------------------- */
    public function get($id) {
        global $wpdb;

        $person = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if ($person && $person['meta']) {
            $person['meta'] = $this->decode_json($person['meta']);
        }

        return $person;
    }

    public function get_by_email($email) {
        global $wpdb;

        $person = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE email = %s", $email),
            ARRAY_A
        );

        if ($person && $person['meta']) {
            $person['meta'] = $this->decode_json($person['meta']);
        }

        return $person;
    }

    public function get_multiple($ids = []) {
        global $wpdb;

        if (empty($ids)) return [];

        $ids = implode(',', array_map('intval', $ids));

        $query = "SELECT * FROM {$this->table} WHERE id IN ($ids)";
        $rows  = $wpdb->get_results($query, ARRAY_A);

        foreach ($rows as &$row) {
            if ($row['meta']) $row['meta'] = $this->decode_json($row['meta']);
        }

        return $rows;
    }

    /* -----------------------------------------
     * UPDATE
     * ----------------------------------------- */
    public function update($id, $data) {
        global $wpdb;

        $update_data = [];

        if (isset($data['name']))  $update_data['name']  = sanitize_text_field($data['name']);
        if (isset($data['email'])) $update_data['email'] = sanitize_email($data['email']);
        if (isset($data['phone'])) $update_data['phone'] = sanitize_text_field($data['phone']);
        if (isset($data['meta']))  $update_data['meta']  = wp_json_encode($data['meta']);

        $update_data['updated_at'] = current_time('mysql');

        return $wpdb->update($this->table, $update_data, ['id' => $id]);
    }

    /* -----------------------------------------
     * DELETE
     * ----------------------------------------- */
    public function delete($id) {
        global $wpdb;
        $this->delete_all_meta($id);
        return $wpdb->delete($this->table, ['id' => $id]);
    }

    public function bulk_delete($ids) {
        global $wpdb;

        if (empty($ids)) return false;

        $ids_list = implode(',', array_map('intval', $ids));

        // Delete metas first
        $wpdb->query("DELETE FROM {$this->meta_table} WHERE person_id IN ($ids_list)");

        // Delete persons
        return $wpdb->query("DELETE FROM {$this->table} WHERE id IN ($ids_list)");
    }

    /* -----------------------------------------
     * LIST / SEARCH
     * ----------------------------------------- */
    public function list($args = []) {
        global $wpdb;

        $defaults = [
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 50,
            'offset'  => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM {$this->table} ORDER BY {$args['orderby']} {$args['order']}";

        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        $results = $wpdb->get_results($query, ARRAY_A);

        foreach ($results as &$r) {
            if ($r['meta']) {
                $r['meta'] = $this->decode_json($r['meta']);
            }
        }

        return $results;
    }

    public function search($keyword, $limit = 50) {
        global $wpdb;

        $like = '%' . $wpdb->esc_like($keyword) . '%';

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table}
             WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s
             LIMIT %d",
            $like, $like, $like, $limit
        );

        $rows = $wpdb->get_results($query, ARRAY_A);

        foreach ($rows as &$r) {
            if ($r['meta']) $r['meta'] = $this->decode_json($r['meta']);
        }

        return $rows;
    }

    public function count() {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    public function exists($id) {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = %d", $id)
        );

        return ($count > 0);
    }

    /* -----------------------------------------
     * META HANDLING
     * ----------------------------------------- */
    public function add_meta($person_id, $key, $value) {
        global $wpdb;
        $now = current_time('mysql');

        return $wpdb->insert($this->meta_table, [
            'person_id'  => $person_id,
            'meta_key'   => sanitize_text_field($key),
            'meta_value' => wp_json_encode($value),
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    public function get_meta($person_id, $key, $single = true) {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, meta_value FROM {$this->meta_table}
                 WHERE person_id = %d AND meta_key = %s",
                $person_id, $key
            ),
            ARRAY_A
        );

        if ($results) {
            $values = [];

            foreach ($results as $row) {
                $decoded = $this->decode_json($row['meta_value']);
                $values[] = $decoded;
            }

            return $single ? $values[0] : $values;
        }

        return null;
    }

    public function get_all_meta($person_id) {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$this->meta_table}
                 WHERE person_id = %d",
                $person_id
            ),
            ARRAY_A
        );

        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['meta_key']] = $this->decode_json($row['meta_value']);
        }

        return $meta;
    }

    public function update_meta($person_id, $key, $value) {
        global $wpdb;

        return $wpdb->update(
            $this->meta_table,
            [
                'meta_value' => wp_json_encode($value),
                'updated_at' => current_time('mysql')
            ],
            [
                'person_id' => $person_id,
                'meta_key'  => $key
            ]
        );
    }

    public function delete_meta($person_id, $key) {
        global $wpdb;
        return $wpdb->delete($this->meta_table, [
            'person_id' => $person_id,
            'meta_key'  => $key
        ]);
    }

    public function delete_meta_by_id($meta_id) {
        global $wpdb;
        return $wpdb->delete($this->meta_table, ['id' => $meta_id]);
    }

    public function delete_all_meta($person_id) {
        global $wpdb;
        return $wpdb->delete($this->meta_table, ['person_id' => $person_id]);
    }

    /* -----------------------------------------
     * USER - PERSON HANDLING
     * ----------------------------------------- */
    public function get_user( $person_id ){
        return $this->get_meta( $person_id, $this->user_person_key, true );
    }
    public function set_user( $person_id, $user_id ){
        return $this->update_meta( $person_id, $this->user_person_key, $user_id );
    }
    public function unset_user( $person_id ){
        return $this->delete_meta( $person_id, $this->user_person_key );
    }

    /* -----------------------------------------
     * JSON Helper
     * ----------------------------------------- */
    private function decode_json($value) {
        if (!is_string($value)) return $value;

        $decoded = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE)
            ? $decoded
            : $value;
    }
}
