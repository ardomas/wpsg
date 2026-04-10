<?php

if (!defined('ABSPATH')) exit;

class WPSG_BasicData {

    private $is_ready;

    protected $wpdb;

    public $table_name;
    public $columns;
    public $indexed;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->is_ready = false;
    }

    protected function set_table_name( $table_name ): void {
        $this->table_name = $table_name;
    }

    public function get_table_name(): string {
        return $this->table_name;
    }

    protected function _generate_columns( array $columns ): array {
        $result = [];
        if( $columns == [] ){
            return $result;
        }
        $result = $columns;
        $created_at_skip = false;
        $updated_at_skip = false;
        $deleted_at_skip = false;
        foreach( $result as $idx => $column ){
            if( isset( $column['created_at'] ) ){ $created_at_skip = true; }
            if( isset( $column['updated_at'] ) ){ $updated_at_skip = true; }
            if( isset( $column['deleted_at'] ) ){ $deleted_at_skip = true; }
            if( isset( $column['default'] )){
                $column['defdata'] = $column['default'];
            }
            if( in_array( strtolower(trim($column['type'])), ['timestamp','datetime','date','time'] ) ){
                $column['is_datetime'] = true;
                $column['defdata'] = "'" . $column['default'] . "'";
            }
            $result[$idx] = $column;
        }
        // auto generate for timestamps
        // created_at
        if( ! $created_at_skip ){
            $result[] = [
                'name' => 'created_at',
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ];
        }
        // updated_at
        if( ! $updated_at_skip ){
            $result[] = [
                'name' => 'updated_at',
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ];
        }
        // deleted_at
        if( ! $deleted_at_skip ){
            $result[] = [
                'name' => 'deleted_at',
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => 'NULL',
            ];
        }

        return $result;
    }

    protected function _generate_table_structure( array $args ): bool {
        if( $args==[] ){
            return false;
        }
        if( !isset( $args['table_name'] ) ){
            return false;
        }
        if( !isset( $args['columns'] ) ){
            return false;
        }
        $this->table_name = $args['table_name'];
        $this->columns = $this->_generate_columns( $args['columns'] );
        if( isset( $args['indexed'] ) ){
            $this->indexed = $args['indexed'];
        }
        $this->is_ready = true;
        return true;
    }

    protected function _generate_sql_create_table(): string {

        $query = '';
        if( $this->is_ready){
            $str_column  = '';
            foreach( $this->columns as $idx => $column ){
                $str_column .= ( $str_column=='' ? '' : ', ' )
                            .  $column['name']
                            .  ' ' . $column['type']
                            .  ' ' . ( isset( $column['null'] ) ? ( ( $column['null'] ) ? 'NULL' : 'NOT NULL' ) : 'NULL' )
                            .  ( isset( $column['auto_increment'] ) ? ' AUTO_INCREMENT' : '' )
                            .  ( isset( $column['primary_key']) ? ' PRIMARY KEY' : '' );
                if( isset( $column['default'] ) && !empty( $column['default'] ) ){
                    if( isset( $column['defdata'] ) && !empty( $column['defdata'] ) ){
                        $str_column .= " DEFAULT " . $column['defdata'];
                    } else {
                        $str_column .= " DEFAULT " . $column['default'];
                    }
                }
            }
            foreach( $this->indexed as $item ){
                if( isset( $item['type'] ) ){
                    $str_column   .= ( $str_column=='' ? '' : ', ' )
                                .  ( isset( $item['type'] ) ? $item['type'] : '' ) . ' KEY ' . $item['name'] . '( ' . $item['field'] . ' )';
                } else {
                    $str_column   .= ( $str_column=='' ? '' : ', ' )
                                .  'KEY ' . $item['name'] . '( ' . $item['field'] . ' )';
                }
            }

            $charset = ( isset( $args['charset'] ) && !empty( $args['charset'] )  ) ? $args['charset'] : $this->wpdb->get_charset_collate();
            $query = "CREATE TABLE {$this->table_name} ({$str_column}) {$charset};";            
        }
        return $query;
    }

    public function get_last_order_number(string $field_name = 'sort_order'){
        $wpdb  = $this->wpdb;
        $max_num = $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX({$field_name}) FROM {$this->table_name} WHERE site_id=%d", 
            wpsg_get_network_id()
        ));
        $max_num = $max_num ? (int) $max_num : 0;
        $max_num++;
        return $max_num;
    }

    protected function _create_table() {
        if( ! $this->is_ready ){
            return false;
        }
        $query = $this->_generate_sql_create_table();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        return dbDelta( $query );
    }

    public function blank_data(): array {
        $result = [];
        if( $this->is_ready ){
            foreach( $this->columns as $column ){
                if( strtolower(trim($column['name']))=='site_id' ){
                    $result[$column['name']] = wpsg_get_network_id();
                } else if( isset( $column['default'] ) ) {
                    $result[$column['name']] = $column['default'];
                } else {
                    switch( strtolower(trim($column['type'])) ){
                        case 'int':
                            $result[$column['name']] = 0;
                            break;
                        case 'varchar':
                            $result[$column['name']] = '';
                            break;
                        default:
                            $result[$column['name']] = null;
                            break;
                    }
                }
            }
        }
        return $result;
    }

    public function get( $id, $include_deleted = false ) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = %d";
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
        $query  = "SELECT * FROM {$this->table_name}";
        $where  = [];
        $values = [];
        if( !$include_deleted ){
            $where[] = "deleted_at IS NULL";
        }
        // if( ! isset($args['site_id']) ){
        //     $args['site_id'] = wpsg_get_network_id();
        // }

        $special_treatment_keys = ['site_id'];
        /* special filter - site_id */
        if( !isset( $this->columns['site_id'] ) ){
            if( empty($args['site_id']) || (isset( $args['site_id'] ) && trim($args['site_id']) === '' ) ){
                $args['site_id'] = wpsg_get_network_id();
            }
            $where[] = "site_id = %d";
            $values[] = $args['site_id'];
        }
        /* ------------------------ */

        foreach( $args as $key => $value ){
            if( !in_array( $key, $special_treatment_keys ) ){
                if( str_contains( $key, '_min_' ) ){
                    $where[] = "{$key} >= %d";
                    $values[] = $value;
                }
                if( str_contains( $key, '_max_' ) ){
                    $where[] = "{$key} <= %d";
                    $values[] = $value;
                }
                if( substr( strtolower( trim( $this->columns[$key]['type'] ) ), 0, 6 ) == 'varchar' ){
                    $words = array_filter(array_map('trim', explode(' ', $value)));
                    if( !empty($words) ){
                        $like = '%' . implode('%', $words) . '%';
                        $where[] = "{$key} LIKE %s";
                        $values[] = $like;
                    }
                }
            }
        }
 
        if( !empty( $args['id'] ) ){
            $where[] = "id = %d";
            $values[] = $args['id'];
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
        $this->wpdb->insert( $this->table_name, $data );
        return $this->wpdb->insert_id;
    }

    public function update( $id, $data ) {
        return $this->wpdb->update(
            $this->table_name,
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
            $this->table_name,
            [ 'deleted_at' => current_time( 'Y-m-d H:i:s' ) ],
            [ 'id' => $id ]
        );
    }
    public function soft_delete_by_site( $site_id ) {
        return $this->wpdb->update(
            $this->table_name,
            [ 'deleted_at' => current_time( 'Y-m-d H:i:s' ) ],
            [ 'site_id' => $site_id ]
        );
    }
    public function soft_delete_by_ids( array $ids ) {
        /* 1. Proteksi terhadap data array kosong */
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        /* 2. Pastikan semua ID adalah integer */
        $ids = array_map( 'intval', $ids );
        /* 3. Buat placeholder */
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        /* 4. Penyusunan Query */
        $query = "UPDATE {$this->table_name} SET deleted_at = %s WHERE id IN ($placeholders)";
        /* 5. Gabungan Parameter (waktu dan daftar ID) */
        $params = array_merge( [ current_time( 'Y-m-d H:i:s' ) ], $ids );
        /* 6. Eksekusi Query */
        return $this->wpdb->query( $this->wpdb->prepare( $query, $params ) );
    }

    /* ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * --------------------------------------------------------- */
    public function restore( $id ) {
        return $this->wpdb->update(
            $this->table_name,
            [ 'deleted_at' => null ],
            [ 'id' => $id ]
        );
    }
    public function restore_by_site( $site_id ) {
        return $this->wpdb->update(
            $this->table_name,
            [ 'deleted_at' => null ],
            [ 'site_id' => $site_id ]
        );
    }
    public function restore_by_ids( array $ids ) {
        /* 1. Proteksi terhadap data array kosong */
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        /* 2. Pastikan semua ID adalah integer */
        $ids = array_map( 'intval', $ids );
        /* 3. Buat placeholder */
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        /* 4. Penyusunan Query */
        $query = "UPDATE {$this->table_name} SET deleted_at = null WHERE id IN ($placeholders)";
        /* 5. Eksekusi Query */
        return $this->wpdb->query( $this->wpdb->prepare( $query, $ids ) );
    }

    /* ---------------------------------------------------------
     * AREA BERBAHAYA, 
     * PASTIKAN UNTUK MENGGUNAKAN METODE INI DENGAN HATI-HATI YANG TERDAFTAR DI BAWAH INI,
     * KARENA PROSES INI MENGHAPUS DATA SECARA PERMANEN TANPA BISA DIKEMBALIKAN
     * --------------------------------------------------------- */

    /* ---------------------------------------------------------
     * DELETE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena bentar saja bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete( $id ) {
        return $this->wpdb->delete(
            $this->table_name,
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
            $this->table_name,
            [ 'site_id' => $site_id ]
        );
    }
    /* ---------------------------------------------------------
     * DELETE BY IDS METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete_by_ids( array $ids ) {
        /* 1. Proteksi terhadap data array kosong */
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        /* 2. Pastikan semua ID adalah integer */
        $ids = array_map( 'intval', $ids );
        /* 3. Buat placeholder */
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        /* 4. Penyusunan Query */
        $query = "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)";
        /* 5. Eksekusi Query */
        return $this->wpdb->query( $this->wpdb->prepare( $query, $ids ) );
    }

}