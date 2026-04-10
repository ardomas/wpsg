<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorsData extends WPSG_BasicData {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }


    protected function generate_table_structure(): void{
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_indicators',
            'columns' => [
                ['name'=>'id'             , 'type'=>'BIGINT'      , 'null'=>false , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'        , 'type'=>'BIGINT'      , 'null'=>false],
                ['name'=>'title'          , 'type'=>'VARCHAR(255)', 'null'=>false],
                ['name'=>'description'    , 'type'=>'LONGTEXT'    , 'null'=>true],
                ['name'=>'age_min_month'  , 'type'=>'INT'         , 'null'=>false , 'default'=>0],
                ['name'=>'age_max_month'  , 'type'=>'INT'         , 'null'=>false , 'default'=>60],
                ['name'=>'milestone_label', 'type'=>'VARCHAR(100)', 'null'=>true],
                ['name'=>'milestone_key'  , 'type'=>'VARCHAR(100)', 'null'=>true],
                ['name'=>'category_id'    , 'type'=>'BIGINT'      , 'null'=>false],
            ],
            'indexed' => [ 
                ['name'=>'site_key_unique', 'type'=>'UNIQUE', 'field'=>'site_id, title, age_min_month, age_max_month, deleted_at' ],
                ['name'=>'idx_site_id'    ,                   'field'=>'site_id, deleted_at' ],
            ]
        ];
        parent::_generate_table_structure( $table_config );
    }

    protected function generate_sql_create_table(): string {
        $charset = $this->wpdb->get_charset_collate();
        $table_name = $this->table_name;
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            age_min_month INT UNSIGNED NOT NULL DEFAULT 0,
            age_max_month INT UNSIGNED NOT NULL DEFAULT 60,
            milestone_label VARCHAR(100) NULL,
            milestone_key VARCHAR(100) NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY site_key_unique (site_id, title, age_min_month, age_max_month),
            KEY idx_site_id (site_id,deleted_at)
        ) {$charset};";
        return $sql;
    }


    public function create_table(){
        $this->generate_table_structure();
        $this->_create_table();
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