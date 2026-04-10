<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$status = '';
$err = 0;
$msg = '';
if( isset( $_GET['msg'] ) ){
    $string_json_msg = base64_decode( base64_decode( $_GET['msg'] ) );
    $json_message = json_decode( $string_json_msg );
    $status = $json_message->status;
    $err = $json_message->err_code;
    $msg = $json_message->message;
}


?><div class="wpsg-page">
    <div class="wpsg-page-content">
        <div  class="wpsg-text-center" style="width: 60%; max-width: 540px; min-width: 320px;">
            <div class="wpsg-boxed my-5 p-5 text-center">
                <div class="row my-5" text-center><?php
                    echo '<p>';
                    echo $status . '<br/>';
                    echo $msg . '</p>';
                    if( $err = 1 ) {
                        echo '<a href="/app">Back to main page</a>';
                        wp_destroy_current_session();
                        wp_clear_auth_cookie();
                        wp_set_current_user( 0 );
                    }
                ?></div>
            </div>
        </div>
    </div>
</div>