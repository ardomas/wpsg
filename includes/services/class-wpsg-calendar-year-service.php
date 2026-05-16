<?php

if (!defined('ABSPATH')) exit;

class WPSG_CalendarYearService extends WPSG_ServiceBase {

    public function __construct() {
        parent::__construct();
        $this->repo = new WPSG_CalendarYearRepository();
    }

    public function get_list($args = [], $include_deleted = false) {
        $temp_1 = $this->repo->get_list( $args, $include_deleted );
        $temp_2 = [];
        $data_list = [];
        foreach( $temp_1 as $item ){
            $key = $item['date_record'];
            $temp_2[$key] = $item;
        }
        foreach( $temp_2 as $item ){
            $data_list[] = $item;
        }
        return $data_list;
    }

}