<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorAttributesData {

    private $table;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->base_prefix . 'wpsg_indicator_attributes';
    }

    protected function generate_sql_create_table(): string {
        $charset = $this->wpdb->get_charset_collate();
        $table_name = $this->table;
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_id BIGINT UNSIGNED NULL,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(150) NOT NULL,
            description TEXT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_slug_scope (site_id, slug),
            KEY idx_site (site_id, deleted_at)
        ) {$charset};";
        return $sql;
    }

    public function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $this->generate_sql_create_table() );
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
        if( isset( $args['id'] ) && !empty( trim( $args['id'] ) ) ){
            $where[] = "id = %d";
            $values[] = $args['id'];
        }
        /* special filter - site_id */
        if( empty($args['site_id']) || (isset( $args['site_id'] ) && trim($args['site_id']) === '' ) ){
            $args['site_id'] = wpsg_get_network_id();
        }
        $where[] = "site_id = %d";
        $values[] = $args['site_id'];
        /* */
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
        if( !empty($where) ){
            $query .= " WHERE " . implode( ' AND ', $where );
        }
        if( !empty($values) ){
            $query = $this->wpdb->prepare( $query, $values );
        }

        return $this->wpdb->get_results( $query, ARRAY_A );

    }

    public function insert( $data ) {
        $this->wpdb->insert( $this->table, $data );
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