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
        $this->repo_category  = new WPSG_IndicatorCategoriesRepository();
        $temp_data_categories = $this->get_data_indicators();
        $this->data_categories_output = $temp_data_categories['output'];
        $this->data_categories_input  = $temp_data_categories['input'];
    }

    private function get_data_indicators(){
        $temp_input  = $this->repo_category->get_list([], false);
        $temp_output = $this->repo_category->get_list([], true );
        $data_input  = [];
        foreach( $temp_input as $item ){
            $data_input[$item['id']] = $item;
        }
        foreach( $temp_output as $item ){
            $data_output[$item['id']] = $item;
        }
        return [
            'input'  => $data_input,
            'output' => $data_output
        ];
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
        // print_r( $this->repo_indicator->data->columns );
        // die('test');
        $data['categories'] = '';
        if( isset( $this->data_categories_output[ $data['category_id'] ] ) ){
            $data['categories'] = $this->data_categories_output[ $data['category_id'] ]['name'];
        }
        return $data;
    }

    public function get_categories(){
        return $this->data_categories_input;
    }

    public function get_categories_output(){
        return $this->data_categories_output;
    }

    public function get_list($args = [], $include_deleted = false) {
        $data_temp = $this->repo_indicator->get_list($args, $include_deleted);
        $data_list = [];
        foreach( $data_temp as $item ){
            $item['category'] = '';
            if( isset( $this->data_categories_output[ $item['category_id'] ] ) ){
                if( $this->data_categories_output[ $item['category_id'] ] ){
                    $item['category'] = $this->data_categories_output[ $item['category_id'] ]['name'];
                }
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