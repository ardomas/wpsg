<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPSG_MembershipsData
 *
 * Data layer untuk WPSG Memberships.
 * Tabel:
 *  - wp_wpsg_sites      (main membership entity)
 *  - wp_wpsg_sitemeta   (meta key-value per site)
 */
class WPSG_MembershipsData {

    protected $wpdb;
    protected $table_main;
    protected $table_meta;

    public function __construct() {
        global $wpdb;
        $this->wpdb       = $wpdb;
        $this->table_main = $wpdb->base_prefix . 'wpsg_sites';
        $this->table_meta = $wpdb->base_prefix . 'wpsg_sitemeta';
    }

    /**
     * ============================================================
     * TABLE CREATION
     * ============================================================
     */
    private function generate_create_tables(): array {
        $charset = $this->wpdb->get_charset_collate();
        $main   = $this->table_main;
        $meta   = $this->table_meta;

        return [
            $main => "CREATE TABLE {$main} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                site_id BIGINT UNSIGNED NULL,
                person_id BIGINT UNSIGNED NULL,
                member_number VARCHAR(50) NULL,
                name VARCHAR(255) NOT NULL,
                member_type VARCHAR(50) NOT NULL,
                membership_level VARCHAR(50) NOT NULL,
                status ENUM('active','inactive','suspended') DEFAULT 'active',
                start_date DATETIME NOT NULL,
                end_date DATETIME DEFAULT NULL,
                address TEXT,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_site_id (site_id),
                KEY idx_person_id (person_id),
                KEY idx_status (status),
                KEY idx_member_type (member_type),
                KEY idx_start_date (start_date)
            ) {$charset};",

            $meta => "CREATE TABLE {$meta} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                site_id BIGINT UNSIGNED NOT NULL,
                meta_key VARCHAR(255) NOT NULL,
                meta_value LONGTEXT,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_site_meta (site_id, meta_key),
                KEY idx_site_id (site_id),
                KEY idx_meta_key (meta_key)
            ) {$charset};"
        ];
    }

    public function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ( $this->generate_create_tables() as $sql ) {
            dbDelta( $sql );
        }
    }

    /**
     * Helper formats: int => %d, else => %s
     */
    private function generate_formats_from_values(array $values): array {
        return array_map(
            fn($v) => is_int($v) ? '%d' : '%s',
            array_values($values)
        );
    }

    /**
     * ============================================================
     * BASIC GETTERS
     * ============================================================
     */
    public function get($id) {
        $id = intval($id);
        if ($id <= 0) return null;

        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_main} WHERE id = %d AND deleted_at IS NULL",
                $id
            ),
            ARRAY_A
        );
    }

    public function get_by_site_id($site_id) {
        $site_id = intval($site_id);
        if ($site_id <= 0) return null;

        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_main} WHERE site_id = %d AND deleted_at IS NULL",
                $site_id
            ),
            ARRAY_A
        );
    }

    public function get_by_person_id($person_id) {
        $person_id = intval($person_id);
        if ($person_id <= 0) return [];

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_main} WHERE person_id = %d AND deleted_at IS NULL",
                $person_id
            ),
            ARRAY_A
        );
    }

    /**
     * ============================================================
     * GET ALL WITH FILTERS
     * ============================================================
     */
    public function get_all($args = []) {
        $conditions = [];
        $values     = [];

        if (empty($args['include_deleted'])) {
            $conditions[] = "deleted_at IS NULL";
        }

        foreach (['status','member_type','membership_level'] as $key) {
            if (!empty($args[$key])) {
                $conditions[] = "{$key} = %s";
                $values[]     = $args[$key];
            }
        }

        if (!empty($args['site_id'])) {
            $conditions[] = "site_id = %d";
            $values[]     = intval($args['site_id']);
        }
        if (!empty($args['person_id'])) {
            $conditions[] = "person_id = %d";
            $values[]     = intval($args['person_id']);
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $allowed_orderby = ['id','start_date','created_at','updated_at','membership_level'];
        if( isset( $args['orderby'] ) ){
            $orderby = in_array($args['orderby'] ?? 'id', $allowed_orderby, true)
                ? $args['orderby']
                : 'id';

            $order = strtoupper($args['orderby'] ?? 'ASC');
            $order = $order === 'DESC' ? 'DESC' : 'ASC';
        } else {
            $orderby = 'id';
            $order   = 'ASC';
        }

        $sql = "SELECT * FROM {$this->table_main} {$where} ORDER BY {$orderby} {$order}";

        if (!empty($args['limit'])) {
            $limit  = intval($args['limit']);
            $offset = max(0, intval($args['offset'] ?? 0));
            $sql   .= " LIMIT {$offset}, {$limit}";
        }

        return !empty($values)
            ? $this->wpdb->get_results($this->wpdb->prepare($sql, $values), ARRAY_A)
            : $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * ============================================================
     * INSERT / UPDATE
     * ============================================================
     */
    public function set($data) {
        $id = intval($data['id'] ?? 0);

        $fields = [
            'site_id'          => $data['site_id']          !== '' ? intval($data['site_id']) : null,
            'person_id'        => $data['person_id']        !== '' ? intval($data['person_id']) : null,
            'name'             => (string)($data['name'] ?? ''),
            'member_number'    => $data['member_number']    !== '' ? (string)$data['member_number'] : null,
            'member_type'      => (string)($data['member_type'] ?? ''),
            'membership_level' => (string)($data['membership_level'] ?? ''),
            'status'           => (string)($data['status'] ?? 'active'),
            'start_date'       => $data['start_date'] ?? current_time('mysql'),
            'end_date'         => $data['end_date'] !== '' ? $data['end_date'] : null,
            'address'          => (string)($data['address'] ?? ''),
            'updated_at'       => current_time('mysql'),
        ];

        if ($id > 0) {
            return $this->wpdb->update(
                $this->table_main,
                $fields,
                ['id' => $id],
                $this->generate_formats_from_values($fields),
                ['%d']
            );
        }

        $fields['created_at'] = current_time('mysql');

        $inserted = $this->wpdb->insert(
            $this->table_main,
            $fields,
            $this->generate_formats_from_values($fields)
        );

        return $inserted ? $this->wpdb->insert_id : false;
    }

    /**
     * Soft delete
     */
    public function delete($id) {
        $id = intval($id);
        if ($id <= 0) return false;

        return $this->wpdb->update(
            $this->table_main,
            [
                'deleted_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $id],
            ['%s','%s'],
            ['%d']
        );
    }

    /**
     * ============================================================
     * META METHODS
     * ============================================================
     */

    public function get_all_meta($site_id) {
        $site_id = intval($site_id);
        if ($site_id <= 0) return [];

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$this->table_meta} WHERE site_id = %d",
                $site_id
            ),
            ARRAY_A
        );
    }

    public function get_meta($site_id, $meta_key) {
        $site_id  = intval($site_id);
        $meta_key = (string)$meta_key;

        if ($site_id <= 0 || $meta_key === '') return null;

        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT meta_value FROM {$this->table_meta} WHERE site_id = %d AND meta_key = %s",
                $site_id,
                $meta_key
            )
        );
    }

    public function set_meta($site_id, $meta_key, $meta_value) {
        $site_id  = intval($site_id);
        $meta_key = (string)$meta_key;

        if ($site_id <= 0 || $meta_key === '') return false;

        $existing = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->table_meta} WHERE site_id = %d AND meta_key = %s",
                $site_id,
                $meta_key
            )
        );

        $data = [
            'meta_value' => $meta_value,
            'updated_at' => current_time('mysql'),
        ];

        if ($existing) {
            return $this->wpdb->update(
                $this->table_meta,
                $data,
                ['id' => intval($existing)],
                ['%s','%s'],
                ['%d']
            );
        }

        $data['site_id']    = $site_id;
        $data['meta_key']   = $meta_key;
        $data['created_at'] = current_time('mysql');

        return $this->wpdb->insert(
            $this->table_meta,
            $data,
            ['%d','%s','%s','%s','%s']
        );
    }

    public function delete_meta($site_id, $meta_key) {
        return $this->wpdb->delete(
            $this->table_meta,
            [
                'site_id'  => intval($site_id),
                'meta_key' => (string)$meta_key
            ],
            ['%d','%s']
        );
    }

    public function delete_all_meta($site_id) {
        return $this->wpdb->delete(
            $this->table_meta,
            ['site_id' => intval($site_id)],
            ['%d']
        );
    }
}
