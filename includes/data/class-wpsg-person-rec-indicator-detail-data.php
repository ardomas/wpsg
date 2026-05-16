<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_PersonRecIndicatorDetailData extends WPSG_DataBase {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): void{
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_person_rec_indicator_detail',
            'columns' => [
                ['name'=>'id'                     , 'type'=>'BIGINT', 'null'=>false , 'subtype'=>'UNSIGNED' , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'person_rec_indicator_id', 'type'=>'BIGINT', 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'indicator_id'           , 'type'=>'BIGINT', 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'score'                  , 'type'=>'INT   ', 'null'=>false , 'subtype'=>'UNSIGNED' , 'default'=>0],
            ],
            'indexed' => [ 
                ['name'=>'person_idx',                   'field'=>'person_rec_indicator_id, indicator_id, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'person_rec_indicator_id, indicator_id, deleted_at' ]
            ]
        ];
        parent::_generate_table_structure( $table_config );
    }

    public function create_table(){
        // $this->generate_table_structure();
        $this->_create_table();
    }

}
