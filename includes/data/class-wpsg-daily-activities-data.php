<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_DailyActivitiesData extends WPSG_BasicData {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): void{
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_daily_activities',
            'columns' => [
                ['name'=>'id'         , 'type'=>'BIGINT', 'null'=>false , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'    , 'type'=>'BIGINT', 'null'=>false],
                ['name'=>'title'      , 'type'=>'VARCHAR(255)', 'null'=>false],
                ['name'=>'time_start' , 'type'=>'TIME'  , 'null'=>false , 'default'=>"08:00:00" ],
                ['name'=>'time_end'   , 'type'=>'TIME'  , 'null'=>false , 'default'=>"17:00:00" ],
                ['name'=>'sort_order' , 'type'=>'INT'   , 'null'=>false , 'default'=>0],
                ['name'=>'description', 'type'=>'TEXT'  , 'null'=>true],
            ],
            'indexed' => [ 
                ['name'=>'title_idx',                    'field'=>'site_id, title, sort_order, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'site_id, title, deleted_at' ]
            ]
        ];
        parent::_generate_table_structure( $table_config );
    }

    public function create_table(){
        $this->generate_table_structure();
        $this->_create_table();
    }

}
