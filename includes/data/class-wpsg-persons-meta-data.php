<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_PersonsBaseData extends WPSG_DataBase {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): bool {
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_personmeta',
            'columns' => [
                ['name'=>'id'         , 'type'=>'BIGINT'      , 'null'=>false , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'person_id'  , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'meta_key'   , 'type'=>'VARCHAR(191)', 'null'=>false],
                ['name'=>'meta_value' , 'type'=>'LONGTEXT'    , 'null'=>true ],
            ],
            'indexed' => [ 
                ['name'=>'person_unique', 'type'=>'UNIQUE', 'field'=>'person_id, meta_key, deleted_at' ],
                ['name'=>'idx_person'   , 'type'=>'NORMAL', 'field'=>'person_id, meta_key' ],
            ]
        ];
        return parent::_generate_table_structure($table_config);
    }

    public function create_table(){
        $this->generate_table_structure();
        $this->_create_table();
    }

}
