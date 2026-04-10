<?php
/**
 * modules/frontend/content.php
 * Partial content used by shortcode and optional front-page override.
 */

if (! defined('ABSPATH')) {
    exit;
}

if( isset( $_POST ) ){

    $is_signed = false;
    // checking $_POST
    if( isset( $_POST['log'] ) ){
        $creds['user_login'] = $_POST['log'];
        if( isset( $_POST['pwd'] ) ){
            $creds['user_password'] = $_POST['pwd'];
            $is_signed = true;
            if( isset( $_POST['remember'] ) ){
                $creds['remember'] = $_POST['remember']=='on';
            }
        }
    }

    if( $is_signed ) {
        wp_signon( $creds, false );
        header('location: /');
    }

}

/** @var array $data */
if (! isset($data) || ! is_array($data)) {
    $data = [];
}

$user = isset($data['user']) ? $data['user'] : wp_get_current_user();

if( !empty($_GET) ){
    $param_gets = $_GET;
    ?><section_code class="d-none"><?php

        foreach( $param_gets as $key=>$val ){
            ?><code type="hidden" name='data-from-get-param' key-id="<?php echo $key; ?>" key-value="<?php echo $val; ?>"><?php echo $val; ?></code><?php
        }

    ?></section_code><?php
    unset( $param_gets );
}

?>
<div class="container">
    <div id="wpsg-front-page" class="wpsg-front-page-wrapper">
<?php

    if( isset( $user->data->user_login ) ){
        require WPSG_DIR . 'modules/frontend/content.php';
    } else {
        require WPSG_DIR . 'modules/frontend/signin.php';
    }
?>
    </div>

</div>
