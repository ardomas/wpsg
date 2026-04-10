<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$obj_children = new WPSG_ChildrenService();

$user = wp_get_current_user();
$child_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$user_id   = $user->ID;

$user_id   = $user->ID;
$code_key  = wpsg_encode_keys( [$user_id, $child_id] );

$param_ok = false;
if( isset( $_GET['cid'] ) ){
    $cid = $_GET['cid'];
    if( $cid == $code_key ){
        $param_ok = true;
    }
}

if( !$param_ok ){
    wp_safe_redirect( wp_get_referer() );
}

$_guardians = $obj_children->get_related_persons_by_types( $child_id, ['father','mother','guardian'] );
$guardians  = [];
$persons   = $obj_children->get_guardians();

// print_r($_guardians);

foreach( $_guardians as $reltype=>$guardian_row ){
    // echo '<p>reltype: ' . $reltype . '</p>';
    // $reltype = $guardian['relation_type'];
    // $guardian = $guardian_row[0] ?? [];
    $g = null;
    foreach( $guardian_row as $idx=>$item ){
        // echo '<p>idx: ' . $idx . '</p>';
        $item['chkid'] = wpsg_encode_keys([$child_id,$item['id'],$reltype]);
        // print_r( $item );
        $g[] = $item ?? [];
    }
    $guardians[$reltype] = $g ? $g[0] : [];
}

if( isset( $_GET['rid'] )){
    $rid = $_GET['rid'];
} else {
    $rid = wpsg_encode_keys( [$user_id, 'father'] );
}

switch( $rid ){
    case wpsg_encode_keys( [$user_id, 'mother'] ):
        break;
    case wpsg_encode_keys( [$user_id, 'guardian'] ):
        break;
    case wpsg_encode_keys( [$user_id, 'father'] ):
    default:
        $rid = wpsg_encode_keys( [$user_id, 'father'] );
        break;
}

?><div class="wpsg-page wpsg-children-edit">
    <div class="row">
        <div class="col-10 text-start">
            <div class="wpsg-page-header">
                <h3 class="wpsg-page-title">
                    Data Orang Tua/Wali Anak
                </h3>
            </div>
        </div>
        <div class="col-2 mt-2 text-end">
            <a class="btn btn-secondary" href="<?php echo esc_url( remove_query_arg( ['act','id','cid'] ) ); ?>">
                <i class="fas fa-reply fa-fw"></i>
                <span class="d-none d-md-inline">Kembali</span>
            </a>
        </div>
    </div>

    <div class="wpsg-page-content">

            <?php

                $can_edit_data = ( ($user && $user->roles && ($user->roles[0] != 'subscriber')) || ( $p_user && !in_array( $p_user['role'], ['guardian','child'] ) ) );
                require __DIR__ . '/guardian-dataform.php';

            ?>

    </div>
</div>