<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorAttributeRelationsData {

    private $table;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->base_prefix . 'wpsg_indicator_attribute_relations';
    }

    protected function generate_sql_create_table(): string {
        $charset = $this->wpdb->get_charset_collate();
        $table_name = $this->table;
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_id BIGINT UNSIGNED NOT NULL,
            indicator_id BIGINT UNSIGNED NOT NULL,
            attribute_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_relation (site_id, indicator_id, attribute_id, deleted_at),
            KEY idx_site (site_id, deleted_at),
            KEY idx_indicator (indicator_id, deleted_at),
            KEY idx_attribute (attribute_id, deleted_at)
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

        $result = $this->wpdb->get_row(
            $this->wpdb->prepare( $query, $params ),
            ARRAY_A
        );
        return $result ?: null;
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
        if( isset($args['indicator_id']) && !empty(trim($args['indicator_id'])) ){
             $where[] = "indicator_id = %d";
             $values[] = $args['indicator_id'];
        }
        if( isset($args['attribute_id']) && !empty(trim($args['attribute_id']) ) ){
             $where[] = "attribute_id = %d";
             $values[] = $args['attribute_id'];
        }

        if( !empty($where) ){
            $query .= " WHERE " . implode(" AND ", $where);
        }
        return $this->wpdb->get_results(
            $this->wpdb->prepare( $query, $values ),
            ARRAY_A
        );
    }

    public function insert($data) {
        if (empty($data['site_id']) || empty($data['indicator_id']) || empty($data['attribute_id'])) {
            return false;
        }
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        return $this->wpdb->insert($this->table, $data);
    }
    
    public function update($id, $data) {
        if( empty($id) || empty($data) ){
            return false;
        }
        $data['updated_at'] = current_time('mysql');
        return $this->wpdb->update($this->table, $data, ['id' => $id]);
    }

    /* ---------------------------------------------------------
     * SOFT DELETE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * --------------------------------------------------------- */
    public function soft_delete($id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => current_time('mysql')],
            ['id' => $id]
        );
    }
    public function soft_delete_by_ids($ids) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        $ids = array_map('intval', $ids);
        $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));
        $query = "UPDATE {$this->table} SET deleted_at = %s WHERE id IN ($ids_placeholder)";
        $params = array_merge([current_time('mysql')], $ids);
        return $this->wpdb->query(
            $this->wpdb->prepare($query, $params)
        );
    }
    public function soft_delete_by_site($site_id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => current_time('mysql')],
            ['site_id' => $site_id]
        );
    }
    public function soft_delete_by_indicator($indicator_id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => current_time('mysql')],
            ['indicator_id' => $indicator_id]
        );
    }
    public function soft_delete_by_attribute($attribute_id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => current_time('mysql')],
            ['attribute_id' => $attribute_id]
        );
    }

    /* ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore($id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => null],
            ['id' => $id]
        );
    }
    public function restore_by_ids($ids) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));
        $query = "UPDATE {$this->table} SET deleted_at = NULL WHERE id IN ($ids_placeholder)";
        return $this->wpdb->query(
            $this->wpdb->prepare($query, $ids)
        );
    }
    public function restore_by_site($site_id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => null],
            ['site_id' => $site_id]
        );
    }
    public function restore_by_indicator($indicator_id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => null],
            ['indicator_id' => $indicator_id]
        );
    }
    public function restore_by_attribute($attribute_id) {
        return $this->wpdb->update(
            $this->table,
            ['deleted_at' => null],
            ['attribute_id' => $attribute_id]
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
    public function delete($id) {
        return $this->wpdb->delete($this->table, ['id' => $id]);
    }
    public function delete_by_ids($ids) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));
        $query = "DELETE FROM {$this->table} WHERE id IN ($ids_placeholder)";
        return $this->wpdb->query(
            $this->wpdb->prepare($query, $ids)
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

    /* ---------------------------------------------------------
     * DELETE BY INDICATOR METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete_by_indicator( $indicator_id ) {
        return $this->wpdb->delete(
            $this->table,
            [ 'indicator_id' => $indicator_id ]
        );
    }

    /* ---------------------------------------------------------
     * DELETE BY ATTRIBUTE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete_by_attribute( $attribute_id ) {
        return $this->wpdb->delete(
            $this->table,
            [ 'attribute_id' => $attribute_id ]
        );
    }

}