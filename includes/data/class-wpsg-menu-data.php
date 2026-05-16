<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_MenuData extends WPSG_DataBase {

    protected $table_config;

    public function __construct(){
        parent::__construct();
        $this->generate_table_structure();
    }

    private function _table_structure() {
        global $wpdb;
        $this->table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_cal_year',
            'columns' => [
                ['name'=>'id'         , 'type'=>'BIGINT'        , 'null'=>false , 'subtype'=>'UNSIGNED' , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'    , 'type'=>'BIGINT'        , 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'parent_id'  , 'type'=>'BIGINT'        , 'null'=>true  , 'subtype'=>'UNSIGNED'],   
                ['name'=>'title'      , 'type'=>'VARCHAR(191)'  , 'null'=>false],
                ['name'=>'path'       , 'type'=>'VARCHAR(191)'  , 'null'=>false],
                ['name'=>'file'       , 'type'=>'VARCHAR(191)'  , 'null'=>false],
                ['name'=>'icon'       , 'type'=>'VARCHAR(191)'  , 'null'=>false],
                ['name'=>'description', 'type'=>'TEXT'          , 'null'=>true ],
                ['name'=>'sort_order' , 'type'=>'INT'           , 'null'=>false , 'subtype'=>'UNSIGNED' , 'default'=>0],
                ['name'=>'is_active'  , 'type'=>'INT'           , 'null'=>false , 'subtype'=>'UNSIGNED' , 'default'=>1],
                ['name'=>'role'       , 'type'=>'VARCHAR(191)'  , 'null'=>false],
            ],
            'indexed' => [ 
                ['name'=>'title_idx'  , 'field'=>'site_id, parent_id, title, path, file, deleted_at' ]
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