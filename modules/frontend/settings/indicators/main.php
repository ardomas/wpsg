<?php
/**
 * modules/frontend/settings/main.php
 */

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$sid = $_GET['sid'];
$s1  = $_GET['s1'] ?? null;

$url_params = $_GET;

$user = wp_get_current_user();
$json_file = __DIR__ . '/assets/json/data.json';
$json_data = wpsg_get_json_data( $json_file );

$raw_menu = $json_data['menu'] ?? [];

$menu = [];
foreach( $raw_menu as $key=>$item ){
    $sub = wpsg_encode_keys( [$user->ID, $key] );
    // $item['url'] = fe_get_app_url() . '/settings?sid=' . $sid . '&s1=' . $sub;
    $menu[$sub] = $item;
}

if( !isset( $_GET['s2'] ) ){

    echo fe_generate_href_menu_buttons([ 
        'menu'=>$menu, 
        'key_id_name'=>'s2', 
        'url_params'=> [ 'sid' => $sid, 's1' => $s1 ], 
        'is_back_button'=>true, 
        'exclude_keys'=> ['s1','s2'] 
    ]);

} else {

    $subkey = $_GET['s2'];
    $sub_menu = $menu[$subkey] ?? null;
    $path = $sub_menu['path'];
    $file = $sub_menu['file'];

    $str_file = WPSG_DIR . '/modules/' . $path . '/' . $file;

    if( file_exists( $str_file ) ){

        require $str_file;

    } else {
        $base_filename = basename( $str_file );
        ?><div class="alert alert-danger py-5">
            Problem:
            <ul class="text-left mb-3">
                <li>File name: <code><strong><?php echo esc_html( $base_filename ); ?></strong></code></li>
                <li>Status: File not found!</li>
            </ul>
            Solution:
            <p class="mb-3">Check the file path or contact the administrator.</p>
        </div><?php

    }

}
