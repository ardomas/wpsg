<?php

if (!defined('ABSPATH')) exit;

class WPSG_DataBase {

    private bool $is_ready;

    private object $builder;

    protected object $wpdb;

    public string $table_name;
    public array $columns;
    public array $indexed;
    
    public array $columns_assoc;
    public array $registered_fields;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->is_ready = false;
        $this->builder  = new WPSG_BuilderBase();
    }

    protected function set_table_name( string $table_name ): void {
        $this->table_name = $table_name;
    }

    public function get_table_name(): string {
        return $this->table_name;
    }

    protected function _generate_columns( array $columns ): array {
        return $this->builder->generate_columns( $columns );
    }

    protected function _generate_table_structure( array $args ): bool {
        // $builder = $this->builder;
        $this->is_ready = $this->builder->generate_table_structure( $args );
        if( $this->is_ready ){
            $this->table_name        = $this->builder->table_name;
            $this->columns           = $this->builder->columns;
            $this->indexed           = $this->builder->indexed;
            $this->columns_assoc     = $this->builder->columns_assoc;
            $this->registered_fields = $this->builder->registered_fields;
        }
        return $this->is_ready;
    }

    /*
    protected function _generate_sql_create_table(): string {
        return $this->builder->generate_sql_create_table();
    }
    */

    protected function _create_table() {
        return $this->builder->_create_table();
        /*
        if( ! $this->is_ready ){
            return false;
        }
        $query = $this->_generate_sql_create_table();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        return dbDelta( $query );
        */
    }

    public function get_last_order_number(string $field_name = 'sort_order'){
        if( !isset( $this->registered_fields[$field_name] ) ) {
            return 0;
        }
        $max_num = $this->wpdb->get_var( $this->wpdb->prepare(
            "SELECT MAX({$field_name}) FROM {$this->table_name} WHERE site_id=%d", 
            wpsg_get_network_id()
        ));
        $max_num = $max_num ? (int) $max_num : 0;
        $max_num++;
        return $max_num;
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

    public function get( int $id, $include_deleted = false ) {
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
        // $str_where = '';
        $where  = [];
        $values = [];
        if( !$include_deleted ){
            $where[] = "deleted_at IS NULL";
        }

        // $special_treatment_keys = ['site_id'];
        /* special filter - site_id */
        if( !isset( $args['site_id'] ) ){
            if( isset( $this->columns_assoc['site_id'] ) ){
                // if( empty($args['site_id']) || (isset( $args['site_id'] ) && trim($args['site_id']) === '' ) ){
                    $args['site_id'] = wpsg_get_network_id();
                // }
                // $where[] = "site_id = %d";
                // $values[] = $args['site_id'];
            }
        }

        /* ------------------------ */
        /*
        ?><br/><?php echo $this->table_name . '<br/>';
        ?><xmp><?php
        print_r( $args );
        ?></xmp><?php
        /* */
        /*
        ?><xmp><?php
        print_r( $this->columns );
        ?></xmp><?php
        /* */

        foreach( $args as $key => $value ){
            if( isset( $this->columns_assoc[$key] ) ){
                $field_name = $this->columns_assoc[$key]['name'];
                // if( !in_array( $field_name, $special_treatment_keys ) ){
                    // echo $field_name . '<br/>';
                    if( str_contains( strtolower(trim( $value )), 'null' ) ){
                        $where[] = "{$field_name} IS NULL";
                    } else if( str_contains( $field_name, '_min_' ) ){
                        $where[] = "{$field_name} >= %d";
                        $values[] = $value;
                    } else if( str_contains( $field_name, '_max_' ) ){
                        $where[] = "{$field_name} <= %d";
                        $values[] = $value;
                    } else if( 
                        substr( strtolower( trim( $this->columns_assoc[$key]['type'] ) ), 0, 6 ) == 'string' ||
                        substr( strtolower( trim( $this->columns_assoc[$key]['type'] ) ), 0, 7 ) == 'varchar' ){
                        $words = array_filter(array_map('trim', explode(' ', $value)));
                        if( !empty($words) ){
                            // $like = '%' . implode('%', $words) . '%';
                            // $where[] = "{$field_name} LIKE %s";
                            // $values[] = $like;

                            // 2. Rakit string di PHP: "kata1%kata2"
                            // Kita gunakan esc_like agar karakter % asli dari user tidak merusak query
                            $inner_string = implode('%', array_map([$this->wpdb, 'esc_like'], $words));

                            // 3. Tambahkan pembungkus % di awal dan akhir
                            $final_value = '%' . $inner_string . '%';

                            // 4. MASALAH UTAMA ANDA DI SINI:
                            // JANGAN gunakan tanda kutip di string query. Cukup %s saja.
                            $where[] = "{$field_name} LIKE %s"; 

                            // 5. Masukkan nilainya ke array values
                            $values[] = $final_value;
                        }
                    } else if(  substr( strtolower( trim( $this->columns_assoc[$key]['type'] ) ), strlen( trim( $this->columns_assoc[$key]['type'] ) ) - 3, 3 ) == 'int' ){
                        $where[] = "{$field_name} = %d";
                        $values[] = $value;
                    } else {
                        $where[] = "{$field_name} = %s";
                        $values[] = $value;
                    }
                // }
            }
        }
 
        /*
        ?>table name: <?php echo $this->table_name; ?><?php
        ?><xmp><?php
        print_r( $args );
        ?></xmp><xmp><?php
        print_r( $where );
        ?></xmp><?php
        die('test');
        /* */

        if( !empty( $args['id'] ) ){
            $where[] = "id = %d";
            $values[] = $args['id'];
        }

        if( !empty($where) ){
            $query .= " WHERE " . implode( ' AND ', $where );
            // if( trim($str_where) != '' ){
            //     $query .= ' AND ' . $str_where;
            // }
        // } else {
        //     if( trim($str_where) != '' ){
        //         $query .= ' WHERE ' . $str_where;
        //     }
        }

        if( isset( $this->columns_assoc['sort_order'] ) ){
            $query .= " ORDER BY sort_order ASC, created_at DESC";
        }

        if( !empty($values) ){
            $query = $this->wpdb->prepare( $query, $values );
        }

        /*
        ?><xmp><?php
        print_r( $this->columns_assoc );
        ?></xmp><xmp><?php
        print_r( $where );
        ?></xmp><xmp><?php
        print_r( $values );
        ?></xmp><xmp><?php
        print_r( $query );
        ?></xmp><?php
        /* */

        $test_result = $this->wpdb->get_results( $query, ARRAY_A );

        // echo '<pre>';
        // echo "1. Query Mentah (dengan hash): " . $query . "\n\n";
        // echo "2. Data di Array Values: "; print_r($values);
        // echo "\n3. Query Final (yang dikirim ke MySQL): " . $this->wpdb->last_query . "\n";
        // echo "4. Error DB (jika ada): " . $this->wpdb->last_error . "\n";
        // echo '</pre>';
        // die();

        return $test_result;

    }

    public function get_by_fields( array $args=[], $include_deleted = false ) {
        $result = $this->get_list( $args, $include_deleted );
        return $result[0];
    }

    public function get_by_ids( array $ids ) {
        /* 1. Proteksi terhadap data array kosong */
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        /* 2. Pastikan semua ID adalah integer */
        $ids = array_map( 'intval', $ids );
        /* 3. Buat placeholder */
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        /* 4. Penyusunan Query */
        $query = "SELECT * FROM {$this->table_name} WHERE id IN ($placeholders)";
        /* 5. Eksekusi Query */
        return $this->wpdb->get_result( $this->wpdb->prepare( $query, $ids ) );
    }

    public function get_count( array $args=[], $include_deleted = false ) {
        $query  = "SELECT COUNT(*) FROM {$this->table_name}";
        $where  = [];
        $values = [];

        if( !isset( $this->columns['site_id'] ) || ( (  isset( $args['site_id'] ) && empty( $args['site_id'] ) ) ) ){
            $args['site_id'] = wpsg_get_network_id();
        }

        if( !$include_deleted ){
            $where[] = "deleted_at IS NULL";
        }
        foreach( $args as $key => $value ){
            $where[] = "{$key} = %d";
            $values[] = $value;
        }
        if( !empty($where) ){
            $query .= " WHERE " . implode( ' AND ', $where );
        }
        if( !empty($values) ){
            $query = $this->wpdb->prepare( $query, $values );
        }
        return $this->wpdb->get_var( $query );
    }

    public function insert( array $data ) {
        $this->wpdb->insert( $this->table_name, $data );
        return $this->wpdb->insert_id;
    }

    public function update( int $id, array $data ) {
        return $this->wpdb->update(
            $this->table_name,
            $data,
            [ 'id' => $id ]
        );
    }

    public function _ensure_data( array $data=[]): int{
        if( $data==[] ){
            return 0;
        }
        if( in_array( 'site_id', $this->registered_fields ) ){
            if( !isset( $data['site_id'] ) || empty( $data['site_id'] ) ){
                $data['site_id'] = wpsg_get_network_id();
            }
        }
        $where = [];
        $values = [];
        foreach( $data as $key=>$val ){
            $where[]  = "{$key} = %s";
            $values[] = $val;
        }
        $query = $this->wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE " . implode( ' AND ', $where ), $values );
        $id = $this->wpdb->get_var( $query );
        if( $id > 0 ){
            return $id;
        } else {
            return $this->insert( $data );
        }
    }

    /* ---------------------------------------------------------
     * SOFT DELETE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * --------------------------------------------------------- */
    public function soft_delete( int $id ) {
        return $this->wpdb->update(
            $this->table_name,
            [ 'deleted_at' => current_time( 'Y-m-d H:i:s' ) ],
            [ 'id' => $id ]
        );
    }
    public function soft_delete_by_site( int $site_id ) {
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
    public function restore( int $id ) {
        return $this->wpdb->update(
            $this->table_name,
            [ 'deleted_at' => null ],
            [ 'id' => $id ]
        );
    }
    public function restore_by_site( int $site_id ) {
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
    public function delete( int $id ) {
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
    public function delete_by_site( int $site_id ) {
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