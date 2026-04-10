<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorCategoriesData {

    public $table;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->base_prefix . 'wpsg_indicator_categories';
    }

    protected function generate_sql_create_table(): string {
        $charset = $this->wpdb->get_charset_collate();
        $table_name = $this->table;
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(150) NOT NULL,
            description TEXT NULL,
            parent_id BIGINT UNSIGNED NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_slug_per_site (site_id, slug),
            KEY idx_site (site_id, deleted_at),
            KEY idx_parent (parent_id)
        ) {$charset};";
        return $sql;
    }

    public function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $this->generate_sql_create_table() );
    }

    public function blank_data() {
        return [
            'id'=>null,
            'site_id'=>wpsg_get_network_id(),
            'name'=>'',
            'slug'=>'',
            'description'=>'',
            'parent_id'=>null,
            'sort_order'=>0
        ];
    }
    public function get( $id, $include_deleted = false ) {
        $query = "SELECT * FROM {$this->table} WHERE id = %d";
        $params = [ $id ];

        if (!$include_deleted) {
            $query .= " AND deleted_at IS NULL";
        }

        return $this->wpdb->get_row(
            $this->wpdb->prepare( $query, $params ),
            ARRAY_A
        );
    }

    public function get_list( array $args=[], $include_deleted = false ) {
        $query  = "SELECT * FROM {$this->table}";
        $where  = [];
        $values = [];
        if( !$include_deleted ){
            $where[] = "deleted_at IS NULL";
        }
        /* special filter - site_id */
        if( empty($args['site_id']) || (isset( $args['site_id'] ) && trim($args['site_id']) === '' ) ){
            $args['site_id'] = wpsg_get_network_id();
        }
        $where[] = "site_id = %d";
        $values[] = $args['site_id'];
        /* */
        if( isset( $args['id'] ) && !empty( $args['id'] ) ){
            $where[] = "id = %d";
            $values[] = $args['id'];
        }
        if( isset( $args['site_id'] ) ){
            $where[] = "site_id = %d";
            $values[] = $args['site_id'];
        }
        if( isset( $args['name'] ) && !empty( trim( $args['name'] ) ) ){
            $words = array_filter(array_map('trim', explode(' ', $args['name'])));
            if( !empty($words) ){
                $like = '%' . implode('%', $words) . '%';
                $where[] = "name LIKE %s";
                $values[] = $like;
            }
        }
        if( isset( $args['slug'] ) && !empty( trim( $args['slug'] ) ) ){
            $where[] = "slug = %s";
            $values[] = $args['slug'];
        }

        if (array_key_exists('parent_id', $args)) {
            if (empty(trim($args['parent_id']))) {
                $where[] = "parent_id IS NULL";
            } else {
                $where[] = "parent_id = %d";
                $values[] = $args['parent_id'];
            }
        }

        if( !empty($where) ){
            $query .= " WHERE " . implode( ' AND ', $where );
        }
        $query .= " ORDER BY sort_order ASC, created_at DESC";

        if( !empty($values) ){
            $query = $this->wpdb->prepare( $query, $values );
        }

        return $this->wpdb->get_results( $query, ARRAY_A );

    }

    public function get_last_order_number(){
        $wpdb  = $this->wpdb;
        $table = $this->table;
        $max_num = $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(sort_order) FROM {$table} WHERE site_id=%d", 
            wpsg_get_network_id()
        ));
        $max_num = $max_num ? (int) $max_num : 0;
        $max_num++;
        return $max_num;
    }

    public function insert( $data ) {
        if( $this->wpdb->insert( $this->table, $data )===false ){
            return false;
        }
        return $this->wpdb->insert_id;
    }

    public function update( $id, $data ) {
        return $this->wpdb->update(
            $this->table,
            $data,
            [ 'id' => $id ]
        );
    }

    /* ---------------------------------------------------------
     * SOFT DELETE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * --------------------------------------------------------- */
    public function soft_delete( $id ) {
        return $this->wpdb->update(
            $this->table,
            [ 'deleted_at' => current_time( 'Y-m-d H:i:s' ) ],
            [ 'id' => $id ]
        );
    }
    public function soft_delete_by_ids( $ids ) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $query = "UPDATE {$this->table} SET deleted_at = %s WHERE id IN ($placeholders)";
        $params = array_merge( [ current_time( 'Y-m-d H:i:s' ) ], $ids );
        return $this->wpdb->query( $this->wpdb->prepare( $query, $params ) );
    }
    public function soft_delete_by_site( $site_id ) {
        return $this->wpdb->update(
            $this->table,
            [ 'deleted_at' => current_time( 'Y-m-d H:i:s' ) ],
            [ 'site_id' => $site_id ]
        );
    }

    /* ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * --------------------------------------------------------- */
    public function restore( $id ) {
        return $this->wpdb->update(
            $this->table,
            [ 'deleted_at' => null ],
            [ 'id' => $id ]
        );
    }
    public function restore_by_ids( $ids ) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $query = "UPDATE {$this->table} SET deleted_at = NULL WHERE id IN ($placeholders)";
        $params = array_merge( [], $ids );
        return $this->wpdb->query( $this->wpdb->prepare( $query, $params ) );
    }
    public function restore_by_site( $site_id ) {
        return $this->wpdb->update(
            $this->table,
            [ 'deleted_at' => null ],
            [ 'site_id' => $site_id ]
        );
    }

    /* ---------------------------------------------------------
     * AREA BERBAHAYA, 
     * PASTIKAN UNTUK MENGGUNAKAN METODE INI DENGAN HATI-HATI YANG TERDAFTAR DI BAWAH INI,
     * KARENA PROSES INI MENGHAPUS DATA SECARA PERMANEN TANPA BISA DIKEMBALIKAN
     * --------------------------------------------------------- */

    /* ---------------------------------------------------------
     * DELETE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete( $id ) {
        return $this->wpdb->delete(
            $this->table,
            [ 'id' => $id ]
        );
    }

    /* ---------------------------------------------------------
     * DELETE BY IDS METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete_by_ids( $ids ) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $query = "DELETE FROM {$this->table} WHERE id IN ($placeholders)";
        return $this->wpdb->query( $this->wpdb->prepare( $query, $ids ) );
    }

    /* ---------------------------------------------------------
     * DELETE BY SITE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete_by_site( $site_id ) {
        return $this->wpdb->delete(
            $this->table,
            [ 'site_id' => $site_id ]
        );
    
    }

}