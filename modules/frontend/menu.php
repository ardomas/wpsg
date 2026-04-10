<?php
/**
 * modules/frontend/menu.php
 */

if (! defined('ABSPATH')) {
    exit;
}

$chk_key = null;
if( isset($_GET['sid']) ){
    $chk_key = $_GET['sid'];
}

$app_menu = fe_get_app_menu();
$str_menu = fe_generate_menu_navbar( $app_menu );

?>

                        <div class="col-10 text-start">
                            <div class="row">
                                <div class="col-12 text-left">
                                    <div class="row d-inline">
                                        <?php echo $str_menu; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-2 text-end">

                            <div class="btn-group dropdown">
                                <div class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    <span class="d-md-inline">
                                        <i class="fas fa-user-circle fa-fw"></i>
                                        <span class="d-none d-md-inline"><?php echo esc_html( $user->display_name ); ?></span>
                                    </span>
                                </div>
                                <ul class="dropdown-menu">

                                    <li class="dropdown-item">
                                        <a class="navbar-menu-item text-nowrap d-block" href="<?php 
                                            echo esc_url( home_url( fe_get_app_url() . '/' . 
                                                    '?sid=' .  wpsg_encode_keys( [$user->ID, 'profile'] )
                                                ) ); ?>">
                                            <i class="fas fa-user-gear fa-fw"></i>
                                            Profile
                                        </a>
                                    </li>

                                    <li class="dropdown-item">
                                        <a class="navbar-menu-item text-nowrap d-block" href="<?php 
                                            echo esc_url( home_url( fe_get_app_url() . '/' . 
                                                    '?sid=' .  wpsg_encode_keys( [$user->ID, 'password'] )
                                                ) ); ?>">
                                            <i class="fas fa-key fa-fw"></i>
                                            Change Password
                                        </a>
                                    </li>

                                    <li class="dropdown-item">
                                        <a class="navbar-menu-item text-nowrap d-block" href="<?php echo esc_url( wp_logout_url( home_url( fe_get_app_url() ) ) ); ?>">
                                            <i class="fas fa-sign-out-alt fa-fw"></i>
                                            Logout
                                        </a>
                                    </li>

                                </ul>
                            </div>

                        </div>
