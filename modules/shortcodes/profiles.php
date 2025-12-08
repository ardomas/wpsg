<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpsg_shortcode_just_test(){

    // $profile = WPSG_ProfilesRepository::init();
    $data = WPSG_ProfilesRepository::get_all_data();

    ob_start();

    // echo 'This Juat A Test.';
    echo '<xmp>';
    print_r( $data );
    echo '</xmp>';

    return ob_get_clean();

}

function wpsg_shortcode_about_us(){
    $main_src = WPSG_ProfilesRepository::get_all_data();
    $init_data = [
        'name'    => $main_src['profile_identity']['full_name'],
        'summary' => $main_src['profile_identity']['profile_summary'],
        'briefs'  => $main_src['profile_identity']['brief_history'],
        'values'  => $main_src['profile_values']
    ];
    ob_start();

    ?><h1><?php echo $init_data['name']; ?></h1><?php
    ?><div><p><?php print_r($init_data['summary']); ?></p></div><?php
    ?><div><h2>Tentang Kami</h2><p><?php echo $init_data['briefs']; ?></p></div><?php
    /*
    ?><div><h2>Values</h2><p><?php print_r( $init_data['values'] ); ?></p></div><?php
    */

    return ob_get_clean();
}

function wpsg_shortcode_values( $atts=[] ){
    $atts = shortcode_atts([
        'key' => '',
    ], $atts, 'wpsg_data');
    $key = sanitize_text_field($atts['key']);

    $main_src = WPSG_ProfilesRepository::get_all_data();

    ob_start();
    echo maybe_unserialize( $main_src['profile_values'][$key] );
    return ob_get_clean();
}
