<?php
/**
 * modules/frontend/menu.php
 */

if (! defined('ABSPATH')) {
    exit;
}

if ( !defined('WPSG_DIR') ){
    return;
}

$continue = fe_check_current_user_access();

$user = wpsg_get_current_user();
$def_action = wpsg_encode_keys([$user->ID,'list']);

/*
?><xmp><?php
print_r( $user );
?></xmp><?php
/* */

// set default action
$action = $_GET['act'] ?? $def_action;

// next process switched by action
switch ( $action ) {

    case wpsg_encode_keys([$user->ID,'guardian-edit']):
        require __DIR__ . '/guardian-edit.php';
        break;
    case wpsg_encode_keys([$user->ID,'guardian-view']):
        require __DIR__ . '/guardian-view.php';
        break;
    case wpsg_encode_keys([$user->ID,'guardian-delete']):
        require __DIR__ . '/guardian-delete.php';
        break;
    case wpsg_encode_keys([$user->ID,'add']):
    case wpsg_encode_keys([$user->ID,'edit']):
        require __DIR__ . '/child-edit.php';
        break;
    case wpsg_encode_keys([$user->ID,'view']):
        require __DIR__ . '/child-view.php';
        break;
    case wpsg_encode_keys([$user->ID,'delete']):
        require __DIR__ . '/child-delete.php';
        break;

    case $def_action:
    default:
        require __DIR__ . '/child-list.php';
        break;

}