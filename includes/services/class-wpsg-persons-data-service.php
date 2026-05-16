<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Service layer for Persons.
 *
 * Orchestrates domain rules around person entities.
 */
class WPSG_PersonsDataService 
    //   extends WPSG_ServiceBase 
{

    protected WPSG_PersonsBaseRepository $base_repo;
    protected WPSG_PersonsMetaRepository $meta_repo;
    protected WPSG_SitePersonsRepository $site_repo;

    public function __construct() {
        $this->base_repo = new WPSG_PersonsBaseRepository();
        $this->meta_repo = new WPSG_PersonsMetaRepository();
        $this->site_repo = new WPSG_SitePersonsRepository();
        // throw new \Exception('Not implemented');
    }

    public function set_default_data(){
        $def_data = $this->base_repo->blank_data();
        // $def_meta = $this->meta_repo->blank_data();
        // $def_site = $this->site_repo->blank_data();
        return $def_data;
    }

    protected function get_meta_by_person(int $id){
        $meta_data = [];
        $meta_temp = $this->meta_repo->get_list(['person_id'=>$id]);
        foreach( $meta_temp as $item ){
            $meta_data[$item->meta_key] = $item->meta_value;
        }
        return $meta_data;
    }
    protected function get_site_by_person(int $id){
        return $this->site_repo->get_sites_by_person($id);
    }

    public function get(int $id){
        $base_data = $this->base_repo->get($id);
        $meta_data = $this->get_meta_by_person($id);
        foreach( $meta_data as $key=>$value ){
            if( !key_exists($key, $base_data) ){
                $base_data[$key] = $value;
            }
        }
        $base_data['sites'] = $this->site_repo->get_sites_by_person($id);
        return $base_data;
    }
    public function get_list(array $args){
        $rows = $this->base_repo->get_list($args);
        foreach( $rows as $key=>$row ){
            $meta_data = $this->meta_repo->get_list(['person_id'=>$row['id']]);
            foreach( $meta_data as $item ){
                if( !key_exists( $item['meta_key'], $row ) ){ $row[$item['meta_key']] = $item['meta_value']; }
            }
            $site_data = $this->site_repo->get_sites_by_person($row['id']);
            $row['sites'] = $site_data;
            $rows[$key] = $row;
        }
        return $rows;
    }
    public function get_person(int $id){
        $data = $this->get($id);
        $data['site_id'] = wpsg_get_network_id();
        $roles_temp = $this->get_site_by_person( $id );
        $roles_data = [];
        foreach( $roles_temp as $item ){
            if( $item['site_id'] == $data['site_id'] ){
                $roles_data[] = $item;
            }
        }
        $data['roles'] = wp_list_pluck( $roles_data, 'role' );
        return $data;
    }
    public function get_by_user_id(int $user_id){
        $data = $this->get_list(['user_id'=>$user_id]);
        if( !empty( $data ) ){ 
            return $data[0];
        } else {
            return [];
        }
    }

}