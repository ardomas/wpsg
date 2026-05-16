<?php

if (!defined('ABSPATH')) exit;

class WPSG_PersonActivitiesService
{
    private $repo_data;
    private $repo_master;
    private $repo_detail;

    public function __construct()
    {
        $this->repo_data   = new WPSG_DailyActivitiesRepository();
        $this->repo_master = new WPSG_PersonActivitiesRepository();
        $this->repo_detail = new WPSG_PersonActivityDetailRepository();
    }

    public function create_tables(){
        $this->repo_detail->data->create_table();
        $this->repo_master->data->create_table();
    }

    public function blank_data(){
        return $this->repo_master->blank_data();
    }
    public function blank_data_detail(){
        return $this->repo_detail->blank_data();
    }

    public function get($id, $include_deleted = false) {
        if( $id==0 ){
            $data = $this->repo_master->blank_data();
        } else {
            $data = $this->repo_master->get($id, $include_deleted);
        }
        return $data;
    }
    public function get_list($args = [], $include_deleted = false) {
        $data_list = $this->repo_master->get_list($args, $include_deleted);
        return $data_list;
    }
    public function get_detail( $id ){
        if( $id==0 ){
            $data = $this->repo_detail->blank_data();
        } else {
            $data = $this->repo_detail->get( $id );
        }
        return $data;
    }
    public function get_detail_list( $master_id, $args=[], $include_deleted = false ){
        $new_args = $args;
        $new_args['person_activity_id'] = $master_id;
        $data_list = $this->repo_detail->get_list( $new_args, $include_deleted );
        return $data_list;
    }

    public function publish_data($id) {
        $this->repo_master->publish_data($id);
    }

    public function unpublish_data($id) {
        $this->repo_master->unpublish_data($id);
    }

    public function ensure_data_master($data){
        // $post_data = $data;
        $master_id = $this->repo_master->ensure_data( $data );
        // $detail_ids = [];
        if( $master_id ){
            $list   = $this->repo_data->get_list();
            foreach( $list as $item ){
                $detail_id = $this->ensure_data_detail([
                    'person_activity_id' => $master_id,
                    'daily_activity_id' => $item['id'],
                ]);
                // $detail_ids[] = $detail_id;
            }
        }
        return $master_id;
    }
    public function ensure_data_detail($data){
        // $post_data = $data;
        return $this->repo_detail->ensure_data( $data );
        // return $post_data;
    }

    public function save( $data ) {
        return $this->repo_master->save( $data );
    }

    public function delete( $id ) {
        return $this->repo_master->delete( $id );
    }

    public function restore( $id ) {
        return $this->repo_master->restore( $id );
    }

    public function destroy( int $id ){
        return $this->repo_master->destroy( $id );
    }

    public function save_detail( $data ){
        return $this->repo_detail->save( $data );
    }

    public function delete_detail( $id ){
        return $this->repo_detail->delete( $id );
    }

    public function restore_detail( $id ){
        return $this->repo_detail->restore( $id );
    }

    public function destroy_detail( int $id ){
        return $this->repo_detail->destroy( $id );
    }

}