<?php

if (!defined('ABSPATH')) exit;

class WPSG_BuilderBase {

    // public $name = 'Basic Builder';
    // public $slug = 'basic-builder';

    private bool $is_ready;

    public string $table_name;
    public array $columns;
    public array $indexed;

    public array $columns_assoc;
    public array $registered_fields;

    public function __construct() {
        // throw new \Exception('Not implemented');
        // do nothing
        $this->is_ready = false;
        return $this;
    }

    // public static function get_instance() {
    //     return new self();
    // }

    public function is_ready(){
        return $this->is_ready;
    }
    public function get_field_names(){
        return $this->registered_fields;
    }

    public function generate_columns( array $columns=[] ): array {
        if( $columns == [] ){
            return [];
        }
        $results = [];
        $skip_created_at = false;
        $skip_updated_at = false;
        $skip_deleted_at = false;
        foreach( $columns as $idx => $column ){
            if( $column['name'] == 'created_at' ){ $skip_created_at = true; }
            if( $column['name'] == 'updated_at' ){ $skip_updated_at = true; }
            if( $column['name'] == 'deleted_at' ){ $skip_deleted_at = true; }
            if( isset( $column['primary_key'] ) && ( $column['primary_key'] === true ) ) {
                $column['null'] = false;
            }
            /*
            if( isset( $column['default'] ) ){ 
                $column['defdata'] = $column['default'];
                if( in_array( strtolower(trim($column['type'])), ['timestamp','datetime','date','time'] ) ){
                    $column['is_datetime'] = true;
                    $column['defdata'] = "'" . $column['default'] . "'";
                }
            }
            */
            $results[] = $column;
        }
        // auto generate for timestamps
        // created_at
        if( ! $skip_created_at ){
            $results[] = [
                'name' => 'created_at',
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ];
        }
        // updated_at
        if( ! $skip_updated_at ){
            $results[] = [
                'name' => 'updated_at',
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ];
        }
        // deleted_at
        if( ! $skip_deleted_at ){
            $results[] = [
                'name' => 'deleted_at',
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => 'NULL',
            ];
        }

        return $results;
    }

    public function generate_table_structure( array $args ): bool {
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
        $this->columns = $this->generate_columns( $args['columns'] );
        $this->registered_fields = array_column( $this->columns, 'name' );
        $columns_assoc = [];
        foreach( $this->columns as $column ){
            $columns_assoc[$column['name']] = $column;
        }
        $this->columns_assoc = $columns_assoc;

        if( isset( $args['indexed'] ) ){
            $this->indexed = $args['indexed'];
        }

        if( ! empty( $this->table_name ) && ! empty( $this->columns ) ){
            $this->is_ready = true;
        }
        return $this->is_ready;
    }

    private function generate_sql_create_table(): string {
        global $wpdb;
        if( !$this->is_ready ){
            return '';
        }

        $table_name = $this->table_name;
        $defaults = [];
        // $queries  = [];
        $query    = '';
        if( $this->is_ready){
            $str_column  = '';
            foreach( $this->columns as $column ){
                $str_column .= ( $str_column=='' ? '' : ', ' )
                            .  $column['name'] . ' ' . $column['type']
                            .  ( ( $column['type']=='ENUM' ) ? '(' . "'" . implode( "', '", $column['enumdata'] ) . "'" . ')' : '' )
                            .  ( ( isset( $column['subtype']        ) &&  !empty( $column['subtype']        ) ) ? ' ' . $column['subtype'] : '' )
                            . ' ' .  ( ( isset( $column['null']     ) && is_bool( $column['null']           ) ) ? ( $column['null']           ? 'NULL'            : 'NOT NULL'  ) : 'NULL' )
                            .  ( ( isset( $column['auto_increment'] ) && is_bool( $column['auto_increment'] ) ) ? ( $column['auto_increment'] ? ' AUTO_INCREMENT' : ''          ) : ''     )
                            .  ( ( isset( $column['primary_key']    ) && is_bool( $column['primary_key']    ) ) ? ( $column['primary_key']    ? ' PRIMARY KEY'    : ''          ) : ''     );

                if( isset( $column['default'] ) ){
                    $defaults[] = 'ALTER TABLE {$table_name} ALTER COLUMN ' . $column['name'] . ' SET DEFAULT ' . $column['default'];
                    $str_column .= " DEFAULT " . $column['default'];
                }
            }
            foreach( $this->indexed as $item ){
                if( isset( $item['type'] ) ){
                    $str_column .= ( $str_column=='' ? '' : ', ' )
                                .  ( isset( $item['type'] ) ? $item['type'] : '' ) . ' KEY ' . $item['name'] . '( ' . $item['field'] . ' )';
                } else {
                    $str_column .= ( $str_column=='' ? '' : ', ' )
                                .  'KEY ' . $item['name'] . '( ' . $item['field'] . ' )';
                }
            }

            $charset = ( isset( $args['charset'] ) && !empty( $args['charset'] )  ) ? $args['charset'] : $wpdb->get_charset_collate();
            $query = "CREATE TABLE {$table_name} ({$str_column}) {$charset}";
            return implode( ";", array_merge( [$query], $defaults ) ) . ';';
        }
        return $query;
    }

    public function _create_table(){
        if( !$this->is_ready ){
            return '';
        }
        $query = $this->generate_sql_create_table();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        return dbDelta( $query );
    }

}