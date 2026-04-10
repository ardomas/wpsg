<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPSG_AppFormHandler {

    // protected WPSG_ChildrenService $children_service;

    // public function __construct( WPSG_ChildrenService $children_service ){
    //     $this->children_service = $children_service;
    // }

    public static function register() {
        // change password
        add_action('admin_post_wpsg_change_password', [ self::class, 'change_user_password' ]);
        add_action('admin_post_nopriv_wpsg_change_password', [ self::class, 'change_user_password' ]);
        // save children
        add_action('admin_post_wpsg_save_child_as_person_data', [ self::class, 'save_child_as_person_data' ]);
        add_action('admin_post_nopriv_wpsg_save_child_as_person_data', [ self::class, 'save_child_as_person_data' ]);
        // save guardians
        add_action('admin_post_wpsg_save_guardian_as_person_data', [ self::class, 'save_guardian_as_person_data' ]);
        add_action('admin_post_nopriv_wpsg_save_guardian_as_person_data', [ self::class, 'save_guardian_as_person_data' ]);
        // save person
        add_action('admin_post_wpsg_save_person_biodata',[ self::class, 'save_person_biodata' ]);
        add_action('admin_post_nopriv_wpsg_save_person_biodata',[ self::class, 'save_person_biodata' ]);
        // save user person
        add_action('admin_post_wpsg_save_user_profile', [ self::class, 'save_user_profile' ]);
        add_action('admin_post_nopriv_wpsg_save_user_profile', [ self::class, 'save_user_profile' ]);
        // save indicator
        add_action('admin_post_wpsg_save_indicator_data', [ self::class, 'save_indicator_data' ]);
        add_action('admin_post_nopriv_wpsg_save_indicator_data', [ self::class, 'save_indicator_data' ]);
        // save category of indicator
        add_action('admin_post_wpsg_save_indicator_category_data', [ self::class, 'save_indicator_category_data' ]);
        add_action('admin_post_nopriv_wpsg_save_indicator_category_data', [ self::class, 'save_indicator_category_data' ]);
        // save daily activity
        add_action('admin_post_wpsg_save_master_daily_activity_data', [self::class, 'save_daily_activity_data']);
        add_action('admin_post_nopriv_wpsg_save_master_daily_activity_data', [self::class, 'save_daily_activity_data']);
        //
    }

    public static function change_user_password(){

        if( ! isset( $_POST['wpsg_password_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wpsg_password_nonce'], 'wpsg_change_password' ) ){
            wp_die('Invalid request');
        }
        $user_factory = wpsg_users_factories();
        $user_factory->change_user_password( $_POST );
        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                wp_get_referer()
            )
        );

        exit;

    }

    public static function save_child_as_person_data() {

        if ( ! isset($_POST['wpsg_children_nonce']) ||
             ! wp_verify_nonce($_POST['wpsg_children_nonce'], 'wpsg_save_child_as_person_data') ) {
            wp_die('Invalid request');
        }

        $children_factory = wpsg_children_factory();
        $children_factory->save_child_data($_POST);

        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                wp_get_referer()
            )
        );

        exit;

    }
    public static function save_guardian_as_person_data() {

        if ( ! isset($_POST['wpsg_guardian_nonce']) ||
             ! wp_verify_nonce($_POST['wpsg_guardian_nonce'], 'wpsg_save_guardian_as_person_data') ) {
            wp_die('Invalid request');
        }

        $children_factory = wpsg_children_factory();
        $children_factory->save_guardian($_POST);

        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                wp_get_referer()
            )
        );

        exit;

    }

    public static function save_person_biodata(){

        if( ! isset( $_POST['wpsg_person_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wpsg_person_nonce'], 'wpsg_save_person_biodata' ) ) {
            wp_die('Invalid request');
        }

        $user_factory = wpsg_users_factories();
        $user_factory->save_person_biodata($_POST);

        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                wp_get_referer()
            )
        );

        exit;

    }

    public static function save_user_profile(){

        if( ! isset( $_POST['wpsg_person_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wpsg_person_nonce'], 'wpsg_save_user_profile' ) ) {
            wp_die('Invalid request');
        }

        $user_factory = wpsg_users_factories();
        $user_factory->save_user_profile($_POST);
        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                wp_get_referer()
            )
        );

        exit;

    }

    public static function save_daily_activity_data(){
        if( ! isset( $_POST['wpsg_master_daily_activity_data_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wpsg_master_daily_activity_data_nonce'], 'wpsg_save_master_daily_activity_data' ) ){
            wp_die('Invalid request');
        }
        $activity_factory = wpsg_activity_factory();
        $activity_factory->save_master_daily_activity( $_POST );

        $url_referer = wp_get_referer();
        $queryString = parse_url( $url_referer, PHP_URL_QUERY );

        $new_url = '/app/';
        if( $queryString ) {
            parse_str( $queryString, $data );
            $data['act'] = wpsg_encrypt('list');
            if( isset( $data['id'] ) ){
                unset( $data['id'] );
            }
            $new_url .= '?' . http_build_query( $data );
        }

        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                $new_url
            )
        );
        exit;
    }

    public static function save_indicator_data(){

        if( ! isset( $_POST['wpsg_indicator_data_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wpsg_indicator_data_nonce'], 'wpsg_save_indicator_data' ) ) {
            wp_die('Invalid request');
        }
        $indicator_data_factory = wpsg_indicators_factory();
        $indicator_data_factory->save_indicator($_POST);

        $url_referer = wp_get_referer();
        $queryString = parse_url( $url_referer, PHP_URL_QUERY );
        $new_url = '/app/';
        if( $queryString ) {
            parse_str( $queryString, $data );
            $data['act'] = wpsg_encrypt('list');
            if( isset( $data['id'] ) ){
                unset( $data['id'] );
            }
            $new_url .= '?' . http_build_query( $data );
        }

        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                $new_url
            )
        );

        exit;

    }
    public static function save_indicator_category_data(){

        if( ! isset( $_POST['wpsg_indicator_category_data_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wpsg_indicator_category_data_nonce'], 'wpsg_save_indicator_category_data' ) ) {
            wp_die('Invalid request');
        }
        $indicator_data_factory = wpsg_indicators_factory();
        $indicator_data_factory->save_category($_POST);

        $url_referer = wp_get_referer();
        $queryString = parse_url( $url_referer, PHP_URL_QUERY );
        $new_url = '/app/';
        if( $queryString ) {
            parse_str( $queryString, $data );
            $data['act'] = wpsg_encrypt('list');
            if( isset( $data['id'] ) ){
                unset( $data['id'] );
            }
            $new_url .= '?' . http_build_query( $data );
        }
        wp_safe_redirect(
            add_query_arg(
                [ 'updated' => 1 ],
                $new_url
            )
        );

        exit;

    }

    // protected function save_from_post(array $data){
        // do save children using children service
    // }

}
