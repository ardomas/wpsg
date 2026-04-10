<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Service layer for WordPress Users.
 * Acts as an orchestrator between wp_users and WPSG Persons domain.
 */
class WPSG_UsersService {

    /** @var WPSG_PersonsService */
    protected $user_data;
    protected $person_repo;
    protected $site_person;

    protected $site_id;

    public function __construct() {
        $this->user_data = null;
        $this->person_repo = new WPSG_PersonsRepository();
        $this->site_person = new WPSG_SitePersonsRepository();
        $this->site_id = get_current_network_id();
    }

    /* ---------------------------------------------------------
     * READ
     * --------------------------------------------------------- */

    public function get_person( int $person_id ) {
        $data = $this->person_repo->get($person_id);
        $data['user_data'] = [];
        $data['roles'] = $this->get_person_roles( $person_id );
        if( isset( $data['user_id'] ) ){
            if( !is_null($data['user_id']) ){
                if( trim($data['user_id'])!='' ){
                    $user_data = get_userdata( $data['user_id'] );
                    $data['user_data'] = $user_data;
                }
            }
        }
        return $data;
    }

    public function get_blank_person() {
        return $this->person_repo->set_default_data();
    }

    public function get_person_by_user_id( int $user_id ) {
        $person = $this->person_repo->get_by_user_id( $user_id );
        if( $person ){
            $rel_repo = new WPSG_PersonRelationsRepository();
            $p_meta = $this->person_repo->get_all_meta( $person['id'] );
            foreach((array)$p_meta as $key => $value){
                if( !isset( $person[$key] ) ) {
                    $person[$key] = $value;
                }
            }
            $temp_sites = $this->site_person->get_sites_by_person( $person['id'] );
            foreach( $temp_sites as $site_role ){
                if( $site_role['site_id'] == $this->site_id ){
                    $person['role'] = $site_role['role'];
                }
            }
        }
        return $person;
    }

    public function get_persons_by_roles( array $args=[] ) : ?array {
        $site_id = $this->site_id;
        $ids = [];
        $results = [];
        if( $args!=[] ){
            foreach( $args as $val ){
                $person_ids = $this->_get_persons_ids_by_role($val);
                foreach( $person_ids as $person_id ){
                    if( !in_array( $person_id, $ids ) ){
                        $ids[] = $person_id;
                    }
                }
            }
        }
        if( $ids!=[] ){
            foreach( $ids as $id ){
                $person = $this->get_person($id);
                $results[] = $person;
            }
        }

        return $results;

    }

    public function get_person_roles($person_id) : ?array {
        $roles = [];
        $site_roles = $this->site_person->get_sites_by_person( $person_id );
        foreach( $site_roles as $site_role ){
            if( $site_role['site_id'] == $this->site_id ){
                $roles[] = $site_role['role'];
            }
        }
        return $roles;
    }

    protected function _get_persons_ids_by_role( $role=null ) : ?array {
        $site_id = $this->site_id;
        $results = [];
        if( !is_null( $role ) && (trim($role) != '') ){
            $temp = $this->site_person->get_persons_by_site($site_id,['role'=>$role]);
            foreach( $temp as $person ){
                $person_id = $person['person_id'];
                if( !in_array( $person_id, $results ) ){
                    $results[] = $person_id;
                }
            }
        }
        return $results;
    }

    public function get_user( int $user_id ) {
        return get_userdata( $user_id ) ?: null;
    }

    public function get_user_by_email( string $email ) {
        return get_user_by( 'email', $email ) ?: null;
    }

    /* ---------------------------------------------------------
     * WRITE
     * --------------------------------------------------------- */

    /**
     * Create a WordPress user.
     * Wrapper around wp_insert_user.
     */
    public function create_user( array $args ) {

        if ( empty( $args['user_email'] ) ) {
            return new WP_Error(
                'wpsg_missing_email',
                'User email is required.'
            );
        }

        if ( email_exists( $args['user_email'] ) ) {
            return new WP_Error(
                'wpsg_email_exists',
                'Email already exists.'
            );
        }

        $defaults = [
            'user_login' => $args['user_email'],
            'role'       => 'subscriber',
        ];

        $args = wp_parse_args( $args, $defaults );

        return wp_insert_user( $args );
    }

    public function set_user_password( int $user_id, string $password ) {
        $data_user = [
            'ID' => $user_id,
            'user_pass' => $password,
        ];
        return wp_update_user( $data_user );
    }

    public function change_user_password( array $raw_data ) {

        $current_user = wp_get_current_user();

        $href_app = $raw_data['app'];
        $href_sid = $raw_data['sid'];

        $status   = 'Failed';
        $err_code = 0;
        $msg_text = 'Nothing';

        if( $current_user ){
            $pass_old = $raw_data['pass_old'];
            $is_valid = wp_check_password( $pass_old, $current_user->user_pass, $current_user->ID );
            if( $is_valid ){
                $pass_new = $raw_data['pass_new'];
                $pass_chk = $raw_data['pass_chk'];
                $user_data = [
                    'ID' => $current_user->ID,
                    'user_pass' => $pass_new
                ];
                // wp_set_password( $pass_new, $current_user->ID );
                $result = wp_update_user( $user_data );
                if( is_wp_error( $result ) ){
                    $err_code = 4;
                    $msg_text = $result->get_error_message();
                } else {
                    $status   = 'Success';
                    $err_code = 1;
                    $msg_text = 'You have to re-login';
                }
            } else {
                $err_code = 2;
                $msg_text = 'Your current password is invalid';
            }
        } else {
            $err_code = 3;
            $msg_text = 'User not found';
        }
        $sec = [ 'status' => $status, 'err_code' => $err_code, 'message' => $msg_text ];

        $message = base64_encode( base64_encode( json_encode( $sec ) ) );

/*
        ?><xmp><?php
        // print_r( $raw_data );
        ?></xmp><xmp><?php

        echo 'is it valid? => ' . $is_valid;

        ?></xmp><xmp><?php
        // print_r( $current_user );
        ?></xmp><?php
        echo '<br/>'. $is_valid;
        echo '<br/>'. $href_sid;
        echo '<br/>'. $pass_old;
        echo '<br/>'. $pass_new;
        echo '<br/>'. $pass_chk;
        //
/* */
        // die('test dulu');
        wp_redirect( $href_app . '?sid=' . $href_sid . '&msg=' . $message );
        exit;
        //

    }

    public function get_by_site_person( int $site_id, int $person_id ) {
        return $this->site_person->get_by_site_person( $site_id, $person_id );
    }

    public function save_basic_user_person( array $init_data, array $href_data=[] ):int {

        $data = [];

        foreach( $init_data['data'] as $key=>$val ){
            if( $key == 'person_id' ){
                $data['id'] = absint($val);
            } else {
                $data[$key] = sanitize_text_field( $val );
            }
        }

        $person_id = $this->person_repo->set( $data );

        if( is_wp_error( $person_id ) ){
            //
            // return $person_id;
            ?>Error here<xmp><?php
            print_r( $init_data );
            ?></xmp>Source Data:<br/><xmp><?php
            print_r( $person_id );
            ?></xmp><?php
            die( 'Error in saving data' );
            //
        } else {
            //
            $data_load = $this->person_repo->get($person_id);
            foreach( $data_load as $key => $value ) {
                if( !isset( $data[$key] ) || (isset( $data[$key] ) && trim($data[$key])=='' ) ){
                    $data[$key] = $value;
                }
            }
            //
            $data_meta = $this->person_repo->get_all_meta( $person_id );
            foreach( $data_meta as $key => $value ) {
                if( !isset( $data[$key] ) || (isset( $data[$key] ) && trim($data[$key])=='' ) ){
                    if( !in_array( $key, ['created_at', 'updated_at'] ) ){
                        $data[$key] = $value;
                    }
                }
            }
            //
            $data_site = $this->site_person->get_sites_by_person( $person_id );
            foreach( $data_site as $site_role ) {
                if( $site_role['site_id'] == $this->site_id ){
                    if( !isset( $data['role'] ) || (isset( $data['role'] ) && trim($data['role'])=='' ) ){
                        $data['role'] = $site_role['role'];
                    }
                }
            }
            //
        }

        // Update site-person
        $this->site_person->ensure_link(
            $this->site_id,
            $person_id,
            $data['role'] ?? 'guest',
            'active'
        );

        $status   = 'Failed';
        $err_code = 1;
        $msg_text = 'Unknown error!';

        if( isset( $data['user_status'] ) ){
            if( $data['user_status']=='1'){
                $data_user = [
                    'user_login' => $data['email'],
                    'user_nicename' => $data['slug'],
                    'user_email' => $data['email'],
                    'display_name' => $data['name'],
                    'user_url' => '',
                    'user_status' => 0, // active
                ];
                if( !is_null( $data['user_id'] ) && trim($data['user_id'])!='' && $data['user_id']!=0 ){
                    $data_user['ID'] = $data['user_id'];
                }

                if( !isset($data_user['ID']) ){
                    // new data
                    $is_continue = false;
                    //

                    if( trim( $data['phone'] )=='' ){

                        $err_code = 2;
                        $msg_text = 'Gagal membuat user. Nomor telepon belum di isi.';

                    } else {

                        $arr_new_pass = explode(',',$data['phone']);
                        $data_user['user_pass'] = trim( $arr_new_pass[0] );
                        $user_id = wp_insert_user( $data_user );
                        if( !$user_id ){
                            $err_code = 3;
                            $msg_text = 'Gagal membuat user. Hubungi administrator.';
                        } else {
                            $status = 'Success';
                            $err_code = 1;
                            $msg_text = 'Berhasil';
                        }

                        $this->bind_user_to_person( $user_id, $person_id );
 
                    }
                } else {
                    wp_update_user( $data_user );
                }
            } else {
                // die('User status is not active.');
            }
        } else {
            die('User status is not set.');
        }

        if( $href_data!=[] ) {

            // redirect this
            $href_app = $href_data['app'];
            $href_sid = $href_data['sid'];
            $str_href = '';
            foreach( $href_data as $app_key=>$app_val ){
                $str_href .= ( $str_href=='' ? '' : '&' ) . $app_key . '=' . $app_val;
            }
            //
            $sec = [ 'status' => $status, 'err_code' => $err_code, 'message' => $msg_text ];
            $message = base64_encode( base64_encode( json_encode( $sec ) ) );
            //
            wp_redirect( $href_app . '?' . $str_href . '&msg=' . $message );
            exit;
            //
        }

        return $person_id;

    }

    public function save_user_profile( array $raw_data ):int {

        $init_data = wpsg_retransform_array( $raw_data, [] );

        return $this->save_basic_user_person( $init_data );

    }

    /**
     * Ensure a user exists by email.
     * If exists → return ID
     * If not → create
     */
    public function ensure_user_by_email(
        string $email,
        array $args = []
    ) {

        $user = $this->get_user_by_email( $email );

        if ( $user ) {
            return (int) $user->ID;
        }

        $args['user_email'] = $email;

        return $this->create_user( $args );
    }

    /* ---------------------------------------------------------
     * INTEGRATION
     * --------------------------------------------------------- */

    /**
     * Bind WordPress user to WPSG person.
     */
    public function bind_user_to_person(
        int $user_id,
        int $person_id
    ) {
        $data = ['id'=>$person_id, 'user_id'=>$user_id];
        return $this->person_repo->set( $data );
    }

}
