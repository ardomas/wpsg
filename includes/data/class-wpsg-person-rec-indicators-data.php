<?php

if(  !defined( 'ABSPATH' ) ) exit;

class WPSG_PersonRecIndicatorsData extends WPSG_DataBase {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): void{
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_person_rec_indicators',
            'columns' => [
                ['name'=>'id'             , 'type'=>'BIGINT'  , 'null'=>false , 'subtype'=>'UNSIGNED' , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'        , 'type'=>'BIGINT'  , 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'person_id'      , 'type'=>'BIGINT'  , 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'date_record'    , 'type'=>'DATE'    , 'null'=>false],
                ['name'=>'date_publish'   , 'type'=>'DATETIME', 'null'=>true ],
                ['name'=>'note_by_teacher', 'type'=>'TEXT'    , 'null'=>true ],
                ['name'=>'note_by_parent' , 'type'=>'TEXT'    , 'null'=>true ],
            ],
            'indexed' => [ 
                ['name'=>'person_idx',                   'field'=>'site_id, person_id, date_record, date_publish, deleted_at' ],
                ['name'=>'unique_idx', 'type'=>'UNIQUE', 'field'=>'site_id, person_id, date_record, deleted_at' ]
            ]
        ];
        parent::_generate_table_structure( $table_config );
    }

    public function create_table(){
        // $this->generate_table_structure();
        $this->_create_table();
    }

    public function get_list_published( $args=[], $include_deleted = false ){
        $args['date_publish'] = 'NOT NULL';
        return parent::get_list( $args, $include_deleted );
    }

    public function publish_data($id){
        global $wpdb;
        $sql = "UPDATE {$this->table_name} SET date_publish = NOW() WHERE id = %d";
        $wpdb->query( $wpdb->prepare( $sql, $id ) );
    }

    public function unpublish_data($id){
        global $wpdb;
        $sql = "UPDATE {$this->table_name} SET date_publish = NULL WHERE id = %d";
        $wpdb->query( $wpdb->prepare( $sql, $id ) );
    }

}
