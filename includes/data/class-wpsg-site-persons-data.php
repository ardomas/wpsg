<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_SitePersonsData extends WPSG_DataBase {

    public function __construct() {
        parent::__construct();
        $this->generate_table_structure();
    }

    protected function generate_table_structure(): bool {
        global $wpdb;
        $table_config = [
            'table_name' => $wpdb->base_prefix . 'wpsg_site_persons',
            'columns' => [
                ['name'=>'id'         , 'type'=>'BIGINT'      , 'null'=>false , 'auto_increment'=>true, 'primary_key'=>true ],
                ['name'=>'site_id'    , 'type'=>'BIGINT'      , 'null'=>false],
                ['name'=>'person_id'  , 'type'=>'BIGINT'      , 'null'=>false],
                ['name'=>'role'       , 'type'=>'VARCHAR(32)' , 'null'=>false],
                ['name'=>'status'     , 'type'=>'VARCHAR(16)' , 'null'=>false , 'default'=>'active'],
            ],
            'indexed' => [ 
                ['name'=>'uq_site_person_role'       , 'type'=>'UNIQUE', 'field'=>'site_id, person_id'    ],
                ['name'=>'site_persons_idx_person_id', 'type'=>'NORMAL', 'field'=>'person_id'             ],
                ['name'=>'site_persons_idx_site_id'  , 'type'=>'NORMAL', 'field'=>'site_id, deleted_at'   ],
                ['name'=>'site_persons_status'       , 'type'=>'NORMAL', 'field'=>'status'                ],
            ]
        ];
        return parent::_generate_table_structure($table_config);
    }

    public function create_table(){
        $this->generate_table_structure();
        parent::create_table();
    }

    public function get_by_site( int $site_id, array $args = [] ) {
        $params = ['site_id'=>$site_id];
        if( $args!=[] ){
            $params = array_merge( ['site_id'=>$site_id] , $args);
        }
        $result = $this->get_list( $params );
        /*
        print_r( $params );
        echo('<br/>');
        print_r( $test_result );
        die('get_by_site');
        /* */
        return $result;
    }

    public function get_by_person( int $person_id, array $args = [] ) {
        $params = ['person_id'=>$person_id];
        if( $args!=[] ){
            $params = array_merge($params, $args);
        }
        return $this->get_list( $params );
    }

    public function get_by_site_person( int $site_id, int $person_id ){
        $args = ['site_id'=>$site_id, 'person_id'=>$person_id];
        $result = $this->get_list( $args );
        // echo '<br/>';
        // print_r( $args );
        // echo '<br/>';
        // print_r( $result );
        return $result;
    }
    
    public function get_by_site_person_role( int $site_id, int $person_id, string $role ){
        $params = ['site_id'=>$site_id, 'person_id'=>$person_id, 'role'=>$role];
        $result = $this->get_list( $params );
        return $result;
    }

    public function delete_by_site_person( int $site_id, int $person_id ){
        $init_data = $this->get_by_site_person( $site_id, $person_id );
        foreach( $init_data as $data ){
            $this->delete( $data['id'] );
        }
    }

    public function delete_by_person( int $person_id ){
        $init_data = $this->get_by_person( $person_id );
        foreach( $init_data as $data ){
            $this->delete( $data['id'] );
        }
    }

}