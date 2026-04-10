<?php
/**
 * modules/frontend/settings/indicators/indicators.php
 */

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$act = $_GET['act'] ?? wpsg_encode_keys( [$user->ID, 'act'=>'list'] );

switch( $act ){

    case wpsg_encode_keys( [$user->ID, 'act'=>'list'] ):
        require __DIR__ . '/attribute-list.php';
        break;

    case wpsg_encode_keys( [$user->ID, 'act'=>'edit'] ):
        require __DIR__ . '/attribute-edit.php';
        break;

    default:
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

?>