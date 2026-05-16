<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_BaseConfigData extends WPSG_DataBase {

    protected $table_config;

    public function __construct(){
        parent::__construct();
        $this->generate_table_structure();
    }

    private function _table_structure() {
        global $wpdb;
        $this->table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_base_config',
            'columns' => [
                ['name'=>'id'        , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED', 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'   , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED' ],
                ['name'=>'meta_key'  , 'type'=>'VARCHAR(191)', 'null'=>false],
                ['name'=>'meta_value', 'type'=>'TEXT'        , 'null'=>false],
            ],
            'indexed' => [ 
                ['name'=>'main_idx'  ,                   'field'=>'site_id, meta_key, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'site_id, meta_key, deleted_at' ]
            ]
        ];
    }

    protected function generate_table_structure(): bool {
        $this->_table_structure();
        // global $wpdb;
        // $this->table_config = $this->table_structure();
        return parent::_generate_table_structure( $this->table_config );
    }

    public function get_table_structure(): array {
        return $this->table_config;
    }

    public function create_table(){
        parent::_create_table();
    }

}

?>