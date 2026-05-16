<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_DailyActivitiesData extends WPSG_DataBase {

    private $table_config;

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): bool {
        global $wpdb;
        $this->table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_daily_activities',
            'columns' => [
                ['name'=>'id'         , 'type'=>'BIGINT'        , 'null'=>false , 'subtype'=>'UNSIGNED', 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'    , 'type'=>'BIGINT'        , 'null'=>false , 'subtype'=>'UNSIGNED' ],
                ['name'=>'title'      , 'type'=>'VARCHAR(255)'  , 'null'=>false , 'length' =>255 ],
                ['name'=>'time_start' , 'type'=>'TIME'          , 'null'=>false],
                ['name'=>'time_end'   , 'type'=>'TIME'          , 'null'=>false],
                ['name'=>'sort_order' , 'type'=>'INT'           , 'null'=>false],
                ['name'=>'description', 'type'=>'TEXT'          , 'null'=>true ],
            ],
            'indexed' => [ 
                ['name'=>'title_idx' ,                   'field'=>'site_id, title, sort_order, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'site_id, title, deleted_at' ]
            ]
        ];
        return parent::_generate_table_structure( $this->table_config );
    }

    public function create_table(){
        parent::_create_table();
    }

}
