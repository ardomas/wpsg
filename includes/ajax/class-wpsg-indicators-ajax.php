<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// wpsg/includes/ajax/class-wpsg-galleries-ajax.php

class WPSG_IndicatorsAjax {

    public function __construct() {

        /* Get Indicators List */
        add_action('wp_ajax_wpsg_get_indicators', [$this, 'get_indicators']);

        /* Get Indicators List - for output*/
        add_action('wp_ajax_wpsg_get_indicators_output', [$this, 'get_indicators_output']);

    }

    public function get_indicators(){

        check_ajax_referer('get_indicators', 'nonce');
    
        $service = new WPSG_IndicatorsService();
        $data_temp = $service->get_list();
        $data_list = [];
        foreach( $data_temp as $item ){
            $key =  str_pad( $item['age_min_month'], 4, '0', STR_PAD_LEFT ) 
            . '-' . str_pad( $item['age_max_month'], 4, '0', STR_PAD_LEFT )
            . '-' . str_pad( $item['sort_order'   ], 4, '0', STR_PAD_LEFT )
            . '-' . str_pad( $item['id'          ], 11, '0', STR_PAD_LEFT );
            $data_list[$key] = $item;
        }
        ksort($data_list);
        wp_send_json_success($data_list);
    }

    public function get_indicators_output(){

        check_ajax_referer('get_indicators_output', 'nonce');
    
        $service = new WPSG_IndicatorsService();
        $data_temp = $service->get_list([], true);
        $data_list = [];
        foreach( $data_temp as $item ){
            $key =  str_pad( $item['age_min_month'], 4, '0', STR_PAD_LEFT ) 
            . '-' . str_pad( $item['age_max_month'], 4, '0', STR_PAD_LEFT )
            . '-' . str_pad( $item['sort_order'   ], 4, '0', STR_PAD_LEFT )
            . '-' . str_pad( $item['id'          ], 11, '0', STR_PAD_LEFT );
            $data_list[$key] = $item;
        }
        ksort($data_list);
        wp_send_json_success($data_list);
    }
}