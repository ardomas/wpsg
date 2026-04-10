<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Factory: Children Factory
 *
 * Centralized creator for WPSG_ChildrenFactory
 */

class WPSG_ChildrenFactory {

    private $children_service;

    public function __construct(){
        $this->children_service = new WPSG_ChildrenService();
    }

    public function save_child_data( $raw_data = [] ){
        $data = [];

        // 1. sanitize data
        $excluded_fields = [ 'sid', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_children_nonce', '_wp_http_referer' ];
        $new_data = wpsg_retransform_array( $raw_data, [ 'exclude_keys' => $excluded_fields ] );
        $data = $new_data['data'] ?? [];

        $this->children_service->save_child_data( $data );
    }

    public function save_guardian( array $raw_data, int $person_id = 0 ) : int {

        $data = [];

        // 1. sanitize data
        $excluded_fields = [ 'sid', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_guardian_nonce', '_wp_http_referer' ];
        $init_data = wpsg_retransform_array( $raw_data, [ 'exclude_keys' => $excluded_fields ] );
        $new_data  = $init_data['data'] ?? [];

        foreach( $new_data as $key => $value ) {
            if( $key=='relation_type' ){
                $relation_type = sanitize_text_field( $value );
                if( $relation_type=='father' ){
                    $data['gender'] = 'M';
                } else if( $relation_type=='mother' ){
                    $data['gender'] = 'F';
                }
            }
            $data[$key] = sanitize_text_field( $value );
        };

        // 2. simpan ke persons melalui service children
        $person_id = $this->children_service->save_guardian( $data );

        return $person_id;

    }

}

function wpsg_children_factory(): WPSG_ChildrenFactory {

    static $instance = null;

    if ( $instance !== null ) {
        return $instance;
    }

    // Children service (orchestrator)
    $instance = new WPSG_ChildrenFactory();

    return $instance;
}
