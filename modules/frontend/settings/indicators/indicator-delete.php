<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

// ?><xmp><?php

// print_r( $_GET );

// ?></xmp><?php

if( isset( $_GET['id'] ) && isset( $_GET['act'] ) ){
    // echo '<br/>masuk 1';
    if( wpsg_decrypt( $_GET['act'] )=='delete' ){
        // echo '<br/>masuk 2';
        $id_for_delete = wpsg_decrypt( $_GET['id'] );
        $service = new WPSG_IndicatorsService();
        if( !$service->delete( $id_for_delete ) ){
            die('gagal hapus nih');
        }
    }
}

$new_param = $_GET;
unset( $new_param['id'] );
$new_param['act'] = wpsg_encrypt('list');

$str_param = http_build_query( $new_param );

$new_urlpath = '/app?' . $str_param;

// die( $new_urlpath );

wpsg_safe_redirect( $new_urlpath );