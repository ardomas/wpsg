<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !isset( $_GET['msg'] ) ){
    require WPSG_DIR . '/modules/frontend/profiles/form-password.php';
} else {
    require WPSG_DIR . '/modules/frontend/profiles/page-password-message.php';
}

?>