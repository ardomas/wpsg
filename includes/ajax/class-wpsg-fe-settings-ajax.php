<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// wpsg/includes/ajax/class-wpsg-galleries-ajax.php

/* Front End Base Config */

class WPSG_FESettingsAjax {

    public function __construct() {
        //
        add_action( 'wp_ajax_wpsg.fe-settings.fetch_data'         , array( $this, 'get_data'    ) );
        add_action( 'wp_ajax_nopriv_wpsg.fe-settings.fetch_data'  , array( $this, 'get_data'    ) );
        //
        add_action( 'wp_ajax_wpsg.fe-settings.submit_data'        , array( $this, 'save_data'   ) );
        add_action( 'wp_ajax_nopriv_wpsg.fe-settings.submit_data' , array( $this, 'save_data'   ) );
        //
    }

    public function get_data() {
        check_ajax_referer( 'fe-settings.fetch_data', 'nonce' );

        $post = $_POST['data'];
        $meta_key = $post['meta_key'];

        $service = new WPSG_BaseConfigService();
        $data = $service->get_meta_value( $meta_key );
        wp_send_json_success( $data );
    }

    public function save_data() {
        check_ajax_referer( 'fe-settings.submit_data', 'nonce' );

        $post = $_POST['data'];
        $meta_key   = $post['meta_key'];
        $meta_value = stripslashes( $post['meta_value'] );

        $service = new WPSG_BaseConfigService();
        $data = $service->get_by_meta_key( $meta_key );
        $data_id = $data['id'];
        $data    = [ 'id' => $data_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ];

        $data = $service->save( $data );
        wp_send_json_success( $data );
    }
}