<?php
/**
 * modules/frontend/content.php
 * Partial content used by shortcode and optional front-page override.
 */

if (! defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/helpers.php';

?>
    <div class="wp-block-template-part">
        <div class="text-start g-0 m-0 p-0">
            <div class="navbar-menu">
                <div class="container">
                    <div class="row">

<?php

require_once( WPSG_DIR . '/modules/frontend/menu.php' );

?>

                    </div>
                </div>
            </div>
        </div>
    </div>

<section id="wpsg-content" class="wpsg-content"><?php 

    if( isset( $_GET['sid'] ) ){

        $sid = $_GET['sid'];

        switch( $sid ){
            case wpsg_encode_keys( [$user->ID, 'profile'] ):
                require WPSG_DIR . '/modules/frontend/profiles/page-userdata.php';
                // return;
                break;
            case wpsg_encode_keys( [$user->ID, 'password'] ):
                require WPSG_DIR . '/modules/frontend/profiles/page-password.php';
                // return;
                break;
            default:
                if( isset( $app_menu[$sid] ) ){

                    $menu_item = $app_menu[$sid];

                    if( $menu_item['key']=='logout' ) {
                        wp_logout();
                        wp_safe_redirect( home_url('/') );
                        exit;
                    } else {

                        $path = $menu_item['path'] ?? '';

                        if( isset( $path ) ){

                            $str_main = 'main.php';
                            $str_file = WPSG_DIR . '/modules/' . $path . '/' . $str_main;

                            $chk_file = file_exists( $str_file );
                            if( $chk_file ){

                                require $str_file;

                            } else {
                                ?>
                                    <div class="row my-5 wpsg-main wpsg-form">
                                        <div class="wpsg-text-center" style="width: 80%; max-width: 800px; min-width: 360px;">
                                            <div class="wpsg-boxed p-0">
                                                <div class="text-center p-5 m-0">
                                                    <h2 class="mb-5">Please, contact Administrator...</h2>
                                                    <p>Main file <code><big>&lt;<?php echo $str_main; ?>&gt;</big></code> is missing.</p>
                                                </div>
                                                <hr/>
                                                <div class="text-start p-5 m-0">
                                                    <h4>Additional menu info:</h4><p><?php

                                                        echo 'Title: <b>' . $menu_item['title'] . '</b><xmp>';
                                                        print_r( $menu_item );
                                                        echo '</xmp>';

                                                    ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            }

                        } else {
                            echo 'something wrong: Path not found!!';
                        }

                    }

                } else {
                    echo 'Something wrong: Menu not found!!';
                }

                break;
        }

    } else {

        require WPSG_DIR . '/modules/frontend/home/main.php';

    }
    
?></section>
