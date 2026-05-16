<?php

if (!defined('ABSPATH')) exit;

class WPSG_DailyActivitiesService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new WPSG_DailyActivitiesRepository();
    }

    public function blank_data(){
        return $this->repo->blank_data();
    }
    public function get($id, $include_deleted = false) {
        if( $id==0 ){
            $data = $this->repo->blank_data();
        } else {
            $data = $this->repo->get($id, $include_deleted);
        }
        return $data;
    }
    public function get_list($args = [], $include_deleted = false) {
        $temp_1 = $this->repo->get_list($args, $include_deleted);
        $temp_2 = [];
        $data_list = [];
        foreach( $temp_1 as $item ){
            $key = $item['time_start'] . '-' . $item['time_end'] . '-' . str_pad( $item['sort_order'], 6, '0', STR_PAD_LEFT ); 
            $item['original_sort_key'] = $key;
            $item['activity_id'] = $item['id'];
            $temp_2[$key] = $item;
        }
        foreach( $temp_2 as $item ){
            $data_list[] = $item;
        }
        return $data_list;
    }

    public function save( $data ) {
        return $this->repo->save( $data );
    }

    public function delete( $id ) {
        return $this->repo->delete( $id );
    }

    public function restore( $id ) {
        return $this->repo->restore( $id );
    }

    public function destroy( int $id ){
        return $this->repo->destroy( $id );
    }

}