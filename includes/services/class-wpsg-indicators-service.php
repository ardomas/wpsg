<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorsService
{
    private $repo_indicator;
    private $repo_category;
    private $data_categories_output;
    private $data_categories_input;

    public function __construct()
    {
        $this->repo_indicator = new WPSG_IndicatorsRepository();
        $this->repo_category = new WPSG_IndicatorCategoriesRepository();
        $this->data_categories_output = $this->repo_category->get_list([], true);
        $this->data_categories_input = $this->repo_category->get_list([], false);
    }

    public function blank_data(){
        return $this->repo_indicator->blank_data();
    }
    public function get($id, $include_deleted = false) {
        if( $id==0 ){
            $data = $this->repo_indicator->blank_data();
        } else {
            $data = $this->repo_indicator->get($id, $include_deleted);
        }
        $data['categories'] = '';
        if( isset( $this->data_categories_output[ $data['category_id'] ] ) ){
            $data['categories'] = $this->data_categories_output[ $data['category_id'] ]['name'];
        }
        return $data;
    }
    public function get_categories(){
        return $this->data_categories_input;
    }
    public function get_list($args = [], $include_deleted = false) {
        $data_temp = $this->repo_indicator->get_list($args, $include_deleted);
        $data_list = [];
        foreach( $data_temp as $item ){
            $item['categories'] = '';
            if( $this->data_categories_output[ $item['category_id'] ] ){
                $item['categories'] = $this->data_categories_output[ $item['category_id'] ]['name'];
            }
            $data_list[] = $item;
        }
        return $data_list;
    }

    public function save( $data ) {
        return $this->repo_indicator->save( $data );
    }

    public function delete( $id ) {
        return $this->repo_indicator->delete( $id );
    }

    public function restore( $id ) {
        return $this->repo_indicator->restore( $id );
    }

    public function destroy( int $id ){
        return $this->repo_indicator->destroy( $id );
    }

}