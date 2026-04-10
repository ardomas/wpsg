<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user = wp_get_current_user();
$param_ok = false;
if( isset( $_GET['sid'] ) ){
    if( isset( $_GET['act'] ) ){
        if( isset( $_GET['id'] ) ){
            $c_cid = wpsg_encode_keys( [$user->ID,$_GET['id']] );
            $c_act = wpsg_encode_keys( [$user->ID,'delete'] );
            if( ($c_act==$_GET['act']) && ($c_cid==$_GET['cid']) ){
                $param_ok = true;
            }
        }
    }
}

if( $param_ok ){
    //
    $children_service = new WPSG_ChildrenService();

    // ambil ID jika edit
    $person_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    if( $children_service->delete_person( $person_id ) ){
        wp_safe_redirect( remove_query_arg( ['act','id','cid','vid','action'] ) );
    }

} else {
    wp_die('ada yang salah nih');
}

?>