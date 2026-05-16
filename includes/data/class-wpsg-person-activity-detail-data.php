<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_PersonActivityDetailData extends WPSG_DataBase {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): void{
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_person_activity_detail',
            'columns' => [
                ['name'=>'id'                , 'type'=>'BIGINT', 'null'=>false , 'subtype'=>'UNSIGNED' , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'person_activity_id', 'type'=>'BIGINT', 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'daily_activity_id' , 'type'=>'BIGINT', 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'score'             , 'type'=>'INT'   , 'null'=>false , 'subtype'=>'UNSIGNED'],
            ],
            'indexed' => [ 
                ['name'=>'person_idx',                   'field'=>'person_activity_id, daily_activity_id, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'person_activity_id, daily_activity_id, deleted_at' ]
            ]
        ];
        parent::_generate_table_structure( $table_config );
    }

    public function create_table(){
        global $wpdb;
        $this->_create_table();
        $wpdb->query( "ALTER TABLE {$this->table_name} ALTER COLUMN score SET DEFAULT 0" );
    }

}
