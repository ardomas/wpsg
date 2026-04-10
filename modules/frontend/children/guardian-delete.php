<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user = wp_get_current_user();

$vid = '';

$params_ok = false;
if( isset( $_GET['sid'] ) && isset( $_GET['act'] ) ){
    $sid = $_GET['sid'];
    $act = $_GET['act'];
    echo '<br/>pass 1';
    if( $act == wpsg_encode_keys( [ $user->ID, 'guardian-delete' ] ) ){
        echo '<br/>pass 2';
        if( isset( $_GET['cid'] ) ){
            $cid = $_GET['cid'];
            echo '<br/>pass 3 - cid : ' . $cid;
            if( isset( $_GET['child_id'] ) && isset( $_GET['parent_id'] ) && isset( $_GET['relation_type'] ) ){
                $child_id = $_GET['child_id'];
                $vid = wpsg_encode_keys( [$user->ID, $child_id] );
                if( $cid == $vid ){
                    $params_ok = true;
                    echo '<br/>pass 4';
                }
            }
        }
    }
}

if( $params_ok ){

} else {
    wp_die( 'something wrong!!!' );

}

$children_service = new WPSG_ChildrenService();
$person_relation  = new WPSG_PersonRelationsService();

// ambil ID jika edit
$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$child_id = isset( $_GET['child_id'] ) ? absint( $_GET['child_id'] ) : 0;
$parent_id = isset( $_GET['parent_id'] ) ? absint( $_GET['parent_id'] ) : 0;
$relation_type = isset( $_GET['relation_type'] ) ? ( $_GET['relation_type'] ) : '';

echo( '<br/>' . $id );
echo( '<br/>' . $child_id );
echo( '<br/>' . $parent_id );
echo( '<br/>' . $relation_type );

if( $child_id!=0 && $parent_id!=0 && trim($relation_type)!='' ){
    $del_process = $person_relation->remove_relation( $child_id, $parent_id, $relation_type );
    if( $del_process ){
        $action = wpsg_encode_keys([$user->ID,'guardian-edit']);
        wp_safe_redirect( remove_query_arg( ['action','act','child_id','parent_id','relation_type'] ) . '&act=' . $action );
    } else {
        // wp_safe_redirect( remove_query_arg( ['action','child_id','parent_id','relation_type'] ) . '&action=guardian-edit' );
        print_r( $del_process );
        wp_die( $del_process );
        // wp_die( remove_query_arg( ['action','child_id','parent_id','relation_type'] ) . '&action=guardian-edit' );
    }
} else {
    wp_die( 'something wrong!!!' );
    // wp_safe_redirect( remove_query_arg( ['action','id'] ) );
}

?>