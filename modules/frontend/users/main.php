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

$user = wp_get_current_user();

// Tentukan action
$action = $_GET['act'] ?? wpsg_encode_keys([$user->ID,'list']);

switch ( $action ) {

    case wpsg_encode_keys([$user->ID,'add']):
    case wpsg_encode_keys([$user->ID,'edit']):
        require __DIR__ . '/users-form.php';
        break;

    case wpsg_encode_keys([$user->ID,'delete']):
        break;

    case wpsg_encode_keys([$user->ID,'list']):
    default:
        require __DIR__ . '/users-list.php';
        break;

}