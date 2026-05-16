<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorCategoriesData extends WPSG_DataBase {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): void{
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_indicator_categories',
            'columns' => [
                ['name'=>'id'             , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED' , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'        , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'name'           , 'type'=>'VARCHAR(150)', 'null'=>false , 'length' =>150 ],
                ['name'=>'slug'           , 'type'=>'VARCHAR(150)', 'null'=>false , 'length' =>150 ],
                ['name'=>'description'    , 'type'=>'LONGTEXT'    , 'null'=>true ],
                ['name'=>'parent_id'      , 'type'=>'BIGINT'      , 'null'=>true  , 'subtype'=>'UNSIGNED'],
                ['name'=>'sort_order'     , 'type'=>'INT'         , 'null'=>false , 'subtype'=>'UNSIGNED' , 'default'=>0],
            ],
            'indexed' => [ 
                ['name'=>'site_key_unique', 'type'=>'UNIQUE', 'field'=>'site_id, slug, deleted_at' ],
                ['name'=>'idx_normal'     ,                   'field'=>'site_id, name, slug, parent_id, sort_order, deleted_at' ],
            ],
        ];
        parent::_generate_table_structure( $table_config );
    } 

    public function create_table(){
        parent::_create_table();
    }

}