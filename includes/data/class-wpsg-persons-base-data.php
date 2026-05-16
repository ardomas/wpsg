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
            'table_name' => $wpdb->base_prefix . 'wpsg_persons',
            'columns' => [
                ['name'=>'id'         , 'type'=>'BIGINT'      , 'null'=>false , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'user_id'    , 'type'=>'BIGINT'      , 'null'=>true ],
                ['name'=>'name'       , 'type'=>'VARCHAR(191)', 'null'=>false],
                ['name'=>'nickname'   , 'type'=>'VARCHAR(64)' , 'null'=>true ],
                ['name'=>'email'      , 'type'=>'VARCHAR(191)', 'null'=>false],
                ['name'=>'phone'      , 'type'=>'VARCHAR(191)', 'null'=>true ],
                ['name'=>'slug'       , 'type'=>'VARCHAR(191)', 'null'=>false],
                ['name'=>'status'     , 'type'=>'VARCHAR(50)' , 'null'=>false , 'default'=>'active'],
                ['name'=>'description', 'type'=>'LONGTEXT'    , 'null'=>true ],
            ],
            'indexed' => [ 
                ['name'=>'slug_unique'  , 'type'=>'UNIQUE', 'field'=>'slug, deleted_at'    ],
                ['name'=>'user_unique'  , 'type'=>'UNIQUE', 'field'=>'user_id, deleted_at' ],
                ['name'=>'email_unique' , 'type'=>'UNIQUE', 'field'=>'email, deleted_at'   ],
                ['name'=>'person_status', 'type'=>'NORMAL', 'field'=>'status' ],
            ]
        ];
        return parent::_generate_table_structure($table_config);
    }

    public function create_table(){
        $this->generate_table_structure();
        $this->_create_table();
    }

}
