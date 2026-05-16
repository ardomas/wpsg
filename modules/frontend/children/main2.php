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

$test = get_current_site();

/*
?><xmp><?php
print_r( $test );
?></xmp><?php
/* */

// set default action
$action = $_GET['act'] ?? $def_action;

// next process switched by action
switch ( $action ) {

    case wpsg_encode_keys([$user->ID,'form']):
        require __DIR__ . '/form.php';
        break;
    case $def_action:
    default:
        require __DIR__ . '/list.php';
        break;

}