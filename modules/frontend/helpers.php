<?php

if (! defined('ABSPATH')) {
    exit;
}

if( !isset( $app_json_data ) ){
    global $app_json_data;
}

if( !function_exists('fe_get_app_url') ){ function fe_get_app_url(){ return '/app'; } }

if( !function_exists('fe_load_main_json_data') ){
    function fe_load_main_json_data(){

        $init_json = [
            'menu' => [],
            'roles' => [],
            'genders' => []
        ];
        /* function wpsg_get_json_data is defined in this file - includes/helpers.php */
        if( $test_json = wpsg_get_json_data( WPSG_DIR . 'modules/frontend/assets/json/data.json' ) ){
            $init_json = json_decode( json_encode( array_merge( $init_json, $test_json ) ), true );
        }
        return $init_json;

    }
}

$app_json_data  = fe_load_main_json_data();

if(  !function_exists('fe_get_app_json_data')){ 
    function fe_get_app_json_data(string $key) : ?array {
        global $app_json_data;
        return $app_json_data[$key];
    }
}

if( !function_exists('fe_get_app_roles') ){ function fe_get_app_roles(){ return fe_get_app_json_data( 'roles' ); } }
if( !function_exists('fe_get_app_status') ){ function fe_get_app_status(){ return fe_get_app_json_data( 'status' ); } }
if( !function_exists('fe_get_app_genders') ){ function fe_get_app_genders(){ return fe_get_app_json_data( 'genders' ); } }
if( !function_exists('fe_get_app_raw_menu') ){ function fe_get_app_raw_menu(){ return fe_get_app_json_data( 'menu' ); } }

if( !function_exists('fe_generate_menu_data') ){
    function fe_generate_menu_data( array $menu ){

        $user   = wp_get_current_user();
        $person = get_person_by_user_id( $user->ID );
        $result = [];
        // $all_menu = [];
        foreach( $menu as $key=>$item ){
            if( $item ){
                $is_access = false;
                if( isset($item['role']) ){
                    if( $user->roles && $user->roles[0] != 'subscriber' ){
                        $is_access = true;
                    } else if( $person && in_array( $person['role'], $item['role'] ) ){
                        $is_access = true;
                    }
                } else {
                    $is_access = true;
                }
                if( $is_access ){
                    $enc_key = wpsg_encode_keys([$user->ID,$key]);
                    $item['key'] = $enc_key;
                    $item['pos'] = $item['pos']   ?? 'left';
                    $result[$enc_key] = $item;
                    // $all_menu[$enc_key] = $item;
                }
            }
        }
        return $result;

    }
}

if( !function_exists('fe_get_app_menu') ){
    function fe_get_app_menu(){

        $user   = wp_get_current_user();
        $person = get_person_by_user_id( $user->ID );

        $app_separator = wpsg_get_separator();
        $raw_menu      = fe_get_app_raw_menu();

        $app_menu = [];

        if( !is_null($user) && ($user != []) ){

            $app_menu = fe_generate_menu_data( $raw_menu );

        }

        return $app_menu;

    }
}

if( !function_exists('fe_generate_menu_navbar') ){
    function fe_generate_menu_navbar( array $menu ){
        $html = '';

        foreach( $menu as $key=>$item ){
            $item['show'] = is_bool( $item['show'] ) ? $item['show'] : true;
            if( $item['show'] ){
                $html   .=  '<a class="navbar-menu-item text-nowrap" href="' . esc_url( home_url( fe_get_app_url() . '?sid=' .  $key ) ) . '">'
                        .       '<i class="' . esc_attr( $item['icon'] ) . ' fa-fw"></i>'
                        .       ' '
                        .       '<span class="d-none d-md-inline">' . esc_html( $item['title'] ) . '</span>'
                        .   '</a>';
            }
        }

        return $html;
    }
}

if( !function_exists('fe_generate_url_params') ){
    function fe_generate_url_params( array $url_params, array $exclude_keys=[] ){

        $new_params = [];
        foreach( $url_params as $key=>$value ){
            if( !in_array( $key, $exclude_keys ) ){
                $new_params[$key] = $value;
            }
        }
        return implode( '&', array_map( function($key) use ($new_params){ return $key . '=' . $new_params[$key]; }, array_keys($new_params) ) );

    }
}

if( !function_exists('fe_generate_href_button') ){
    function fe_generate_href_button( array $args ){
        $args = [
            'url_params'    => $args['url_params']   ?? [],
            'exclude_keys'  => $args['exclude_keys'] ?? [],
            'class'         => $args['class']        ?? 'btn-light',
            'text'          => $args['text']         ?? 'Back',
            'icon'          => $args['icon']         ?? 'fas fa-reply fa-fw'
        ];
        $header_param = fe_generate_url_params( $args['url_params'], $args['exclude_keys'] );
        $html = '<a class="btn ' . $args['class'] . ' text-nowrap" href="' . esc_url( home_url( fe_get_app_url() . '?' . $header_param ) ) . '">'
                .   '<i class="' . $args['icon'] . '"></i> '
                .   '<span class="d-none d-md-inline">' . $args['text'] . '</span>'
                .'</a>';
        return $html;
    }
}

if( !function_exists('fe_generate_href_menu_buttons') ){
    function fe_generate_href_menu_buttons( array $args ){
        if( !isset( $args['menu'] ) ) return;
        $args = [
            'menu'          => $args['menu']            ?? [],
            'key_id_name'   => $args['key_id_name']     ?? 'key',
            'url_params'    => $args['url_params']      ?? [],
            'is_back_button'=> $args['is_back_button']  ?? false,
            'exclude_keys'  => $args['exclude_keys']    ?? []
        ];
        $user = wp_get_current_user();
        $app_url    = fe_get_app_url();
        $url_params = $args['url_params'];
        $text_param = implode( '&', array_map( function($key) use ($url_params){ return $key . '=' . $url_params[$key]; }, array_keys( $url_params )) );
        $html   =   '<div class="wpsg-menu">'
                .       '<div class="row">'
                .           '<div class="' . ( $args['is_back_button'] ? 'col-10' : 'col-12' ) . ' text-start">'
                .               '<div class="mb-4 d-flex flex-wrap gap-2">';
        foreach( $args['menu'] as $key=>$item ){
            $item['show'] = $item['show'] ?? true;
            if( $item['show'] ) {
                $html   .=  '<a class="btn btn-process text-nowrap"'
                        .       ' href="' . esc_url( home_url($app_url) . '?' . $text_param . '&' . $args['key_id_name'] . '=' . $key ) . '"'
                        .       ' title="' . esc_attr( $item['title'] ) . '">'
                        .       '<i class="' . esc_attr( $item['icon'] ) . '"></i>'
                        .       ' '
                        .       '<span class="d-none d-md-inline">' . $item['title'] . '</span>'
                        .   '</a>';
            }
        }
        $html   .=              '</div>'
                .           '</div>';
        if( $args['is_back_button'] ) {
            $html   .=      '<div class="col-2 text-end">' 
                    .           fe_generate_href_button([
                                    'url_params'    => $url_params,
                                    'exclude_keys'  => $args['exclude_keys'],
                                    'text' => 'Kembali', 
                                    'class' => 'btn-process'
                                ])
                    .       '</div>';
        }
        $html   .=      '</div>'
                .   '</div>';
        return $html;
    }
}

if( !function_exists('fe_check_current_user_access') ){
    function fe_check_current_user_access(){
        global $app_json_data;
        $app_menu = $app_json_data['menu'];
        $is_valid = false;
        $cur_user = wpsg_get_current_user();
        /*
        ?><xmp><?php
        print_r( $cur_user );
        ?></xmp><?php
        /* */
        if( $cur_user ){
            if( isset( $_GET['sid'] ) ){
                $sid_menu = $_GET['sid'];
                if( $app_menu = $app_json_data['menu'] ){
                    foreach( $app_menu as $key=>$item ){
                        if( $sid_menu == wpsg_encode_keys([$cur_user->ID, $key]) ){
                            $is_valid = true;
                        }
                    }
                }
            }
        }
        return $is_valid;
    }
}

if( !function_exists('fe_check_default_requirement') ) {
    function fe_check_default_requirement(){
        if( !defined('ABSPATH') ){
            exit;
        }
        if( !is_user_logged_in() ){
            return false;
        }
        if( !defined('WPSG_DIR') ){
            return false;
        }
        if( !isset( $_GET['sid'] ) ){
            return false;
        }
        if( fe_check_current_user_access() === false ){
            return false;
        }
        return true;
    }
}