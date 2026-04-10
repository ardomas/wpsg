<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_PersonActivitiesData extends WPSG_BasicData {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): void{
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_person_activities',
            'columns' => [
                ['name'=>'id'       , 'type'=>'BIGINT', 'null'=>false , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'  , 'type'=>'BIGINT', 'null'=>false],
                ['name'=>'person_id', 'type'=>'BIGINT', 'null'=>false],
                ['name'=>'date'     , 'type'=>'DATE'  , 'null'=>false],
                ['name'=>'notes_dc' , 'type'=>'TEXT'  , 'null'=>true],
                ['name'=>'notes_pa' , 'type'=>'TEXT'  , 'null'=>true],
            ],
            'indexed' => [ 
                ['name'=>'person_idx',                   'field'=>'site_id, person_id, date, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'site_id, person_id, date, deleted_at' ]
            ]
        ];
        parent::_generate_table_structure( $table_config );
    }

    public function create_table(){
        $this->generate_table_structure();
        $this->_create_table();
    }

}
