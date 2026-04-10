<?php

if( !defined('ABSPATH') ) exit;

class WPSG_IndicatorsFactory {

    public function save_indicator( array $post_data=[] ){

        $service = new WPSG_IndicatorsService();
        $data = [];

        // 1. sanitize data
        $excluded_fields = [ 'sid', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_indicator_data_nonce', '_wp_http_referer' ];
        $init_data = wpsg_retransform_array( $post_data, [ 'exclude_keys' => $excluded_fields ] );
        $tmp_data  = $init_data['data'] ?? [];
        if( !isset($tmp_data['site_id']) ) {
            $tmp_data['site_id'] = wpsg_get_network_id();
        }
        foreach( $tmp_data as $key=>$val ){
            if( $key=='indicator_id' ){
                if(  !empty( $tmp_data['indicator_id'] ) ){
                    $data['id'] = $val;
                }
            } else {
                $data[$key] = $val;
            }
        }

        // 2. save data using service
        return $service->save( $data );

    }
    public function save_category( array $post_data=[] ) {

        $service = new WPSG_IndicatorCategoriesService();
        $data = [];

        $excluded_fields = [ 'sid', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_indicator_category_data_nonce', '_wp_http_referer' ];
        $init_data = wpsg_retransform_array( $post_data, [ 'exclude_keys' => $excluded_fields ] );
        $tmp_data  = $init_data['data'] ?? [];
        if( !isset($tmp_data['site_id']) ) {
            $tmp_data['site_id'] = wpsg_get_network_id();
        }
        foreach( $tmp_data as $key=>$val ){
            if( $key=='category_id' ){
                if(  !empty( $tmp_data['category_id'] ) ){
                    $data['id'] = $val;
                }
            } else {
                $data[$key] = $val;
            }
        }

        // 2. save data using service
        return $service->save( $data );
    }

}

function wpsg_indicators_factory(): WPSG_IndicatorsFactory {

    static $instance = null;

    if ( $instance !== null ) {
        return $instance;
    }

    $instance = new WPSG_IndicatorsFactory();

    return $instance;

}