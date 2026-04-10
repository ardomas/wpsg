<?php

if( !defined('ABSPATH') ) exit;

class WPSG_ActivityFactory {

    public function __construct() {
        // do nothing
    }
    public function save_master_daily_activity( $raw_data ){
        $service = new WPSG_DailyActivitiesService();
        $data = [];

        // 1. sanitize data
        $excluded_fields = [ 'sid', 's1', 's2', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_master_daily_activity_data_nonce', '_wp_http_referer' ];
        $new_data = wpsg_retransform_array( $raw_data, [ 'exclude_keys' => $excluded_fields ] );
        foreach( $new_data['data'] as $key=>$val ){
            if( $key == 'activity_id' ){
                $data['id'] = $val;
            } else {
                $data[$key] = $val;
            }
        }

        $service->save( $data );
    }
}

function wpsg_activity_factory(): WPSG_ActivityFactory {

    static $instance = null;

    if ( $instance !== null ) {
        return $instance;
    }

    // Activity Factory (orchestrator)
    $instance = new WPSG_ActivityFactory();

    return $instance;
}
