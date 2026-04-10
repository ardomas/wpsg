<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !isset( $wpsg_app_global_separator ) ){
    global $wpsg_app_global_separator;
    $wpsg_app_global_separator = '-$-';
}

/**
 * Load stylesheet with integrity, crossorigin, and referrerpolicy attributes.
 */
if( !function_exists('wpsg_enqueue_cdn_style') ){
    function wpsg_enqueue_cdn_style( $handle, $attributes ) {

        $src = $attributes['src'] ?? null;
        $ver = $attributes['ver'] ?? 'N/A';

        if( is_null( $src ) ){
            return;
        }
        $integrity = $attributes['integrity'] ?? '';
        $origin    = $attributes['origin'   ] ?? 'anonymous';
        $policy    = $attributes['policy'   ] ?? 'no-referrer';

        wp_enqueue_style( $handle, $src, [], $ver );

        add_filter('style_loader_tag', function($html, $tag_handle) use ($handle, $integrity, $origin, $policy) {

            if ($tag_handle !== $handle) {
                return $html;
            }

            return preg_replace(
                '/rel=[\'"]stylesheet[\'"]/',
                "rel='stylesheet' integrity='{$integrity}' crossorigin='{$origin}' referrerpolicy='{$policy}'",
                $html
            );

        }, 10, 2);
    }
}

if( !function_exists('wpsg_get_separator') ){ function wpsg_get_separator() { return '-$-'; } }

if( !function_exists('wpsg_retransform_array') ){
    function wpsg_retransform_array( $array, $filters ) {

        $result = [ 'data' => [], 'id' => null, 'relation' => [] ];

        $is_key = false;
        $key_id = null;
        $exclude_keys = [];
        $relation_key = [];

        foreach ( $filters as $key => $val ) {
            if( $key === 'is_key' ){ 
                $is_key = $val;
                if(  $is_key && isset( $filters['key_id'] ) ){
                    $key_id = $filters['key_id'];
                } else {
                    die( 'Invalid key_id in filters array.' );
                }
            }
            if( $key === 'exclude_keys' && is_array( $val ) ){
                $exclude_keys = $val;
            }
            if( $key === 'relation_key' && is_array( $val ) ){
                $relation_key = $val;
            }
        }

        if( $is_key && !is_null( $key_id ) ){
            if( !in_array( $key_id, $exclude_keys ) ){
                $result['id'] = $array[$key_id] ?? null;
            } else {
                die( 'key_id cannot be in exclude_keys.' );
            }
        }

        foreach ( $array as $key => $item ) {
            if( $is_key && $key === $key_id ){
                continue; // skip key_id if it's used as id
            } else {
                if( !in_array( $key, $exclude_keys ) ){
                    if( in_array( $key, $relation_key ) ){
                        $result['relation'][$key] = $item;
                    } else {
                        $result['data'][$key] = $item;
                    }
                }
            }
        }
        return $result;
    }
}

/**
 * Load script with integrity, crossorigin, and referrerpolicy attributes.
 */
if( !function_exists('wpsg_enqueue_cdn_script') ){
    function wpsg_enqueue_cdn_script( $handle, $attributes, $in_footer = true ) {

        $src = $attributes['src'] ?? null;
        $ver = $attributes['ver'] ?? 'N/A';

        if ( is_null( $src ) ) {
            return;
        }

        $integrity = $attributes['integrity'] ?? '';
        $origin    = $attributes['origin']    ?? 'anonymous';
        $policy    = $attributes['policy']    ?? 'no-referrer';

        wp_enqueue_script( $handle, $src, [], $ver, $in_footer );

        add_filter('script_loader_tag', function( $tag, $tag_handle ) use ( $handle, $integrity, $origin, $policy ) {

            if ( $tag_handle !== $handle ) {
                return $tag;
            }

            // Inject integrity, crossorigin, and referrerpolicy
            $tag = str_replace(
                '<script ',
                "<script integrity='{$integrity}' crossorigin='{$origin}' referrerpolicy='{$policy}' ",
                $tag
            );

            return $tag;

        }, 10, 2 );
    }
}

function wpsg_enqueue_picocss(){
    $attr_style = [
        'src' => 'https://cdnjs.cloudflare.com/ajax/libs/picocss/2.1.1/pico.min.css',
        'ver' => '2.1.1',
        'integrity' =>'sha512-+4kjFgVD0n6H3xt19Ox84B56MoS7srFn60tgdWFuO4hemtjhySKyW4LnftYZn46k3THUEiTTsbVjrHai+0MOFw==',
        'origin'    => 'anonymous',
        'policy'    => 'no-referrer',
    ];
    wpsg_enqueue_cdn_style( 'wpsg-pico-css', $attr_style  );
}

/**
 * Load Font Awesome via CDN with SRI.
 */
function wpsg_enqueue_fontawesome() {

    $attr_style = [
        'src'=>'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
        'ver'=>'6.7.2',
        'integrity' => 'sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==',
        'origin'    => 'anonymous',
        'policy'    => 'no-referrer',
    ];
    $attr_script = [
        'src'   => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js',
        'ver'   => '6.7.2',
        'integrity' => 'sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==',
        'origin'    => 'anonymous',
        'policy'    => 'no-referrer',
    ];

    wpsg_enqueue_cdn_style( 'wpsg-fontawesome-css', $attr_style  );
    wpsg_enqueue_cdn_script( 'wpsg-fontawesome-js', $attr_script, false );

}

if( !function_exists('wpsg_encrypt') ){
    function wpsg_encrypt( string $string_data='' ){
        $user = wp_get_current_user();
        $chip_code  = 'AES-128-CTR';
        $iv_length  = openssl_cipher_iv_length( $chip_code );
        $options    = 0;
        $site_name  = get_current_site()->site_name;
        $encrypt_iv  = mb_substr( str_replace( ' ', '', $site_name ) . '0123456789ABCDEF', 0, 16 );
        $encrypt_key = $user->ID . '-$-' . $user->name;
        return openssl_encrypt( $string_data, $chip_code, $encrypt_key, $options, $encrypt_iv );
    }
}

if( !function_exists('wpsg_decrypt') ){
    function wpsg_decrypt( string $string_code=null ){
        if( $string_code==null ) return false;
        $user = wp_get_current_user();
        $chip_code = 'AES-128-CTR';
        $iv_length  = openssl_cipher_iv_length( $chip_code );
        $options    = 0;
        $site_name  = get_current_site()->site_name;
        $encrypt_iv  = mb_substr( str_replace( ' ', '', $site_name ) . '0123456789ABCDEF', 0, 16 );
        $encrypt_key = $user->ID . '-$-' . $user->name;
        return openssl_decrypt( $string_code, $chip_code, $encrypt_key, $options, $encrypt_iv );
    }
}

function wpsg_get_json_data( $file_path ){
    if (file_exists($file_path)) {
        $content   = file_get_contents($file_path);
        $json_data = json_decode( $content, true );
        return $json_data;
     } else {
         die('File not found: ' . $file_path);
     }
}

function wpsg_dbDelta($args = []){
    global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    if( is_array( $args ) ){
        foreach ($args as $tbkey => $sql) {
            dbDelta($sql);
        }
    }

}

function wpsg_safe_redirect( $new_url ){
    $validated = wp_validate_redirect($new_url, false);
    if( $validated === $new_url ){
        wp_safe_redirect( $new_url );
        // exit;
    } else {
        return new WP_Error(
            'wpsg_redirect_blocked',
            'Redirect blocked. Domain not in allowed hosts: ' . esc_url( $new_url )
        );
    }
}

function wpsg_get_last_slug( $table, $base_slug, $last_num = 0 ){
    global $wpdb;
    $slug = $base_slug;
    $i = $last_num;
    $slug = $base_slug . ( $i == 0 ? '' : ( '-' . $i ) );
    $check = true;
    while( $check ){
        $check = $wpdb->get_var($wpdb->prepare("SELECT * FROM $table WHERE slug = %s", $slug ) );
        if( $check ){
            $i++; 
            $slug = $base_slug . ( $i == 0 ? '' : ( '-' . $i ) );
        }
    }
    return $slug;
}

function wpsg_generate_unique_slug( $table, $title ){
    global $wpdb;
    $base_slug = sanitize_title( $title );
    $related_slugs = $wpdb->get_col($wpdb->prepare(
        "SELECT slug FROM $table WHERE slug REGEXP %s",
        '^' . preg_quote($base_slug) . '(-[0-9]+)?$'
    ));
    $last_num = 0;
    foreach( $related_slugs as $item ){
        $temp_num = explode( $base_slug, $item )[1];
        if( is_null( $temp_num ) ) break;
        if( is_numeric( $temp_num ) ){
            if( $temp_num > $last_num ){
                $last_num = ( $temp_num ?? 0 ) + 1;
            }
        }
    }
    return wpsg_get_last_slug( $table, $base_slug, $last_num );
}

function wpsg_get_networks($args = []) {
    $defaults = [
        'fields' => 'objects', // objects|ids|domains|custom
    ];
    $args = wp_parse_args($args, $defaults);

    $nets = get_networks();

    switch ($args['fields']) {
        case 'ids':
            return wp_list_pluck($nets, 'id');

        case 'domains':
            return wp_list_pluck($nets, 'domain');

        case 'objects':
        default:
            return $nets;
    }
}

function wpsg_get_network_id() {
    if ( function_exists( 'get_current_network_id' ) ) {
        return get_current_network_id();
    }

    // fallback (WordPress 4.6 ke bawah)
    $network = get_network();
    return $network ? $network->id : 1;
}

function wpsg_get_profile_data( $site_id=null ){
    if( $site_id==null ) $site_id = wpsg_get_network_id();
    return WPSG_ProfilesRepository::get_all_data($site_id);
}

if( !file_exists('wpsg_get_current_user') ){
    function wpsg_get_current_user() {
        $person_repo = new WPSG_PersonsRepository();
        $site_person = new WPSG_SitePersonsRepository();
        $cur_user = wp_get_current_user();
        $cur_data = $cur_user->data;
        //
        $cur_repo = $person_repo->get_by_user_id( $cur_user->ID );
        $cur_data->person_id = $cur_repo ? $cur_repo['id'] : null;
        $cur_data->site_id = wpsg_get_network_id();
        $site_roles = [];
        if( !is_null( $cur_data->person_id ) ){
            $temp_site_roles = $site_person->get_by_site_person( $cur_data->site_id, $cur_data->person_id );
            foreach( $temp_site_roles as $role ){
                // if( $role->status ){
                    $site_roles[$role['site_id']] = $role['role'];
                // }
            }
            $cur_data->site_roles = $site_roles;
        }
        $cur_user->data = $cur_data;
        return $cur_user;
    }
}

function wpsg_site_abbreviation() {
    $profile_data = wpsg_get_profile_data();
    return $profile_data['profile_identity']['short_name'];
}

if( !function_exists('wpsg_encode_keys') ){
    function wpsg_encode_keys( array $args ){
        global $wpsg_app_global_separator;
        $str_main = '';
        $new_args = [];
        foreach( $args as $arg ){
            $item = trim( $arg );
            $new_args[] = $item;
        }
        return md5( base64_encode( implode( $wpsg_app_global_separator, $new_args ) ) );
    }
}

// helper => get person by user id
if( !function_exists('get_person_by_user_id') ){
    function get_person_by_user_id( int $user_id ) : ?array {
        $init = new WPSG_UsersService();
        // wpsg_users_service();
        return $init->get_person_by_user_id( $user_id );
    }
}

if( !function_exists('get_site_person') ){
    function get_site_person(int $person_id, int $site_id){
        $init_user = new WPSG_UsersService();
        // wpsg_users_service();
        return $init_user->get_by_site_person( $site_id, $person_id );
    }
}
