<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// wpsg/includes/ajax/class-wpsg-galleries-ajax.php

/* Front End Base Config */

class WPSG_FECalendarsAjax {

    public function __construct() {
        //
        // Year
        // fetch data
        add_action( 'wp_ajax_wpsg.fe-calendars-year.fetch_data'         , array( $this, 'get_data_year' ) );
        add_action( 'wp_ajax_nopriv_wpsg.fe-calendars-year.fetch_data'  , array( $this, 'get_data_year' ) );
        // submit data
        add_action( 'wp_ajax_wpsg.fe-calendars-year.submit_data'        , array( $this, 'save_data_year') );
        add_action( 'wp_ajax_nopriv_wpsg.fe-calendars-year.submit_data' , array( $this, 'save_data_year') );
        //
    }

    private function get_calendar_year_by_date( $date ){
        $service = new WPSG_CalendarYearService();
        $args = [ 'date_record'=>$date ];
        $data = $service->get_list( $args );
        if(  count($data) > 0 ) {
            return $data[0];
        } else {
            return $service->blank_data();
        }
    }

    public function get_data_year() {
        check_ajax_referer( 'fe-calendars-year.fetch_data', 'nonce' );

        $post = $_POST['data'];
        $date = $post['date'];

        $data = $this->get_calendar_year_by_date( $date );
        wp_send_json_success( $data );
    }

    public function save_data_year() {
        $service = new WPSG_CalendarYearService();
        check_ajax_referer( 'fe-calendars-year.submit_data', 'nonce' );

        $post = $_POST['data'];
        $date   = $post['date'];
        $meta_data = stripslashes( $post['meta_data'] );

        $data = $this->get_calendar_year_by_date( $date );
        $data['meta_data'] = $meta_data;

        $data = $service->save( $data );
        wp_send_json_success( $data );
    }
}