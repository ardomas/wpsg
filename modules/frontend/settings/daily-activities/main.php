<?php
/**
 * modules/frontend/settings/indicators/indicators.php
 */

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$act = $_GET['act'] ?? wpsg_encrypt( 'list' );

$is_continue = true;
switch( wpsg_decrypt( $act ) ){

    case 'delete':
        $file_name = __DIR__ . '/activity-delete.php';
        break;

    case 'add' :
    case 'edit':
        $file_name = __DIR__ . '/activity-edit.php';
        break;

    case 'list':
        $file_name = __DIR__ . '/activity-list.php';
        break;

    default:
        $is_continue = false;
        ?><div class="alert alert-danger py-5">
            Problem:
            <ul class="text-left mb-3">
                <li>Action: <code><strong><?php echo esc_html( $_GET['act'] ?? 'null' ); ?></strong></code></li>
                <li>Status: Action not found!</li>
            </ul>
            Solution:
            <p class="mb-3">Check the action parameter or contact the administrator.</p>
        </div><?php

        break;
}

if( $is_continue ){
    if( file_exists( $file_name ) ){
        require $file_name;
    } else {
        ?><div class="alert alert-danger py-5">
            Problem:
            <ul class="text-left mb-3">
                <li>File name: <code><strong><?php echo esc_html( basename( $file_name ) ); ?></strong></code></li>
                <li>Status: File not found!</li>
            </ul>
            Solution:
            <p class="mb-3">Check the action parameter or contact the administrator.</p>
        </div><?php

    }
}

?>