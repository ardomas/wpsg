<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorsData extends WPSG_DataBase {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): void{
        $table_config = [
            'table_name' => $this->wpdb->base_prefix . 'wpsg_indicators',
            'columns' => [
                ['name'=>'id'             , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED' , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'        , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'title'          , 'type'=>'VARCHAR(255)', 'null'=>false , 'length' =>255 ],
                ['name'=>'description'    , 'type'=>'LONGTEXT'    , 'null'=>true ],
                ['name'=>'age_min_month'  , 'type'=>'INT'         , 'null'=>false , 'subtype'=>'UNSIGNED' , 'default'=>0],
                ['name'=>'age_max_month'  , 'type'=>'INT'         , 'null'=>false , 'subtype'=>'UNSIGNED' , 'default'=>60],
                ['name'=>'category_id'    , 'type'=>'BIGINT'      , 'null'=>false , 'subtype'=>'UNSIGNED'],
                ['name'=>'milestone_label', 'type'=>'VARCHAR(100)', 'null'=>true  , 'length' =>100 ],
                ['name'=>'milestone_key'  , 'type'=>'VARCHAR(100)', 'null'=>true  , 'length' =>100 ],
                ['name'=>'sort_order'     , 'type'=>'INT'         , 'null'=>false , 'subtype'=>'UNSIGNED' , 'default'=>0],
            ],
            'indexed' => [ 
                ['name'=>'site_key_unique', 'type'=>'UNIQUE', 'field'=>'site_id, title, age_min_month, age_max_month, deleted_at' ],
                ['name'=>'idx_site_id'    ,                   'field'=>'site_id, title, age_min_month, age_max_month, milestone_key, milestone_label, sort_order, deleted_at' ],
            ]
        ];
        parent::_generate_table_structure( $table_config );
    }

    public function create_table(){
        parent::_create_table();
    }

    /* ---------------------------------------------------------
     * DELETE BY CATEGORY METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function delete_by_category( $category_id ) {
        return $this->wpdb->delete(
            $this->table_name,
            [ 'category_id' => $category_id ]
        );
    }

}