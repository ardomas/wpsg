<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_CalendarYearData extends WPSG_DataBase {

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
                ['name'=>'id'         , 'type'=>'BIGINT'        , 'null'=>false , 'subtype'=>'UNSIGNED', 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'    , 'type'=>'BIGINT'        , 'null'=>false , 'subtype'=>'UNSIGNED' ],
                ['name'=>'date_record', 'type'=>'DATE'          , 'null'=>false],
                ['name'=>'meta_data'  , 'type'=>'TEXT'          , 'null'=>true ],
                ['name'=>'sign'       , 'type'=>'TINYINT'       , 'null'=>false , 'subtype'=>'UNSIGNED', 'default'=>0 ],
            ],
            'indexed' => [ 
                ['name'=>'title_idx' ,                   'field'=>'site_id, date_record, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'site_id, date_record, deleted_at' ]
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