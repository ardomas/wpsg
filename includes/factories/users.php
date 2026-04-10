<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Factory: Users Service
 *
 * Centralized creator for WPSG_UsersService
 */

class WPSG_UsersFactory {
    private $user_service;

    public function __construct() {
        $this->user_service = new WPSG_UsersService();
    }

    public function change_user_password( array $data ) {
        $this->user_service->change_user_password( $data );
    }

    public function save_user_profile( array $raw_data ):int {

        // 1. sanitize data
        $excluded_fields = ['app','sid','cid','act','action','wpsg_person_nonce','nonce','submit','_wp_http_referer'];
        $init_data = wpsg_retransform_array( $raw_data, ['exclude_keys' => $excluded_fields] );

        // 2. save data using service
        return $this->user_service->save_basic_user_person( $init_data );

    }

    public function save_person_biodata( array $raw_data ):int {

        $data = [];

        // 1. sanitize data
        $excluded_fields = [ 'app', 'sid', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_person_nonce', 'wpsg_save_person_bio', '_wp_http_referer' ];
        $init_data = wpsg_retransform_array( $raw_data, [ 'exclude_keys' => $excluded_fields ] );
        foreach( $init_data['data'] as $key => $value ) {
            if( $key=='person_id' ){
                $data['id'] = absint( $value );
            } else {
                $data[$key] = sanitize_text_field( $value );
            }
        }

        return $this->user_service->save_basic_user_person( $init_data );

    }

}

function wpsg_users_factories(): WPSG_UsersFactory {

    static $instance = null;

    if ( $instance !== null ) {
        return $instance;
    }

    // Core services
    // $persons_service = new WPSG_PersonsService();
    // $relations_service = new WPSG_PersonRelationsService( $persons_service );
    // $site_persons_repo = new WPSG_SitePersonsRepository();

    // Children service (orchestrator)
    $instance = new WPSG_UsersFactory();

    return $instance;
}
