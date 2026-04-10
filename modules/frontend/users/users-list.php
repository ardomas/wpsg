<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_service = new WPSG_UsersService;

$user = wp_get_current_user();

$url_new_data = esc_url( fe_get_app_url() )
              . '?' . 'sid=' . $_GET['sid']
              . '&' . 'act=' . wpsg_encode_keys( [$user->ID, 'add'] )
              . '&' . 'cid=' . wpsg_encode_keys( [$user->ID, '0'] );

$data_genders = $app_json_data['genders'];
$data_roles   = $app_json_data['roles'];

?>
<code id="__menu_test" class="d-none"></code>

<div class="wpsg-page">

    <!-- Page Header -->
    <div class="wpsg-page-header">
        <div class="row">
            <div class="col-8 text-start">
                <h3 class="wpsg-page-title">Daftar User</h3>
            </div>
            <div class="col-4 text-end">
                <a href="<?php echo $url_new_data; ?>" class="btn btn-process">
                    <i class="fas fa-plus"></i>
                    <span class="d-none d-md-inline">
                        &nbsp;Tambah Data
                    </span>
                </a>
            </div>
        </div>
    </div>
    <!-- Content -->
    <div id="div-person-area" class="wpsg-page-content wpsg-grid-border wpsg-grid-hover py-2 px-2"><?php

            $user_persons = $user_service->get_persons_by_roles(['teacher', 'staff', 'guardian']);

            foreach( $user_persons as $person ) {

                if( !isset( $person['id'] ) ){
                    break;
                }

                $sex_icon  = 'fas fa-circle-question fa-fw';
                switch( strtolower(trim( $person['gender'] )) ){
                    case 'm': $sex_icon = 'fas fa-mars  fa-lg fa-fw text-primary'; break;
                    case 'f': $sex_icon = 'fas fa-venus fa-lg fa-fw text-danger'; break;
                    default : $sex_icon = 'fas fa-circle-question fa-fw text-secondary'; break;
                }
                
                $is_active = ( isset( $person['user_id'] ) && !empty( $person['user_id'] ) ) ? 1 : 0;
                $reg_icon  = $is_active ? 'fas fa-user-check fa-lg fa-fw text-success' 
                                        : 'fas fa-user-times fa-lg fa-fw text-secondary';

                $code_id = wpsg_encode_keys( [$user->ID, $person['id']] );

                ?><div class="row wpsg-grid-data mt-0 mb-1 px-2 pt-3 pb-2"
                        data-id="<?php echo esc_attr( $person['id'] ); ?>"
                        code-id="<?php echo $code_id; ?>"
                        data-edit-url="<?php
                            echo esc_url(
                                add_query_arg(
                                    [
                                        'id'     => $person['id'],
                                    ]
                                )
                            );
                        ?>">
                        <?php
                        // ($person['roles']);
                        ?>
                        <div class="row">
                            <div class="col-12 fw-bold"><?php echo esc_html( $person['name'] ?? '' ); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-9 col-md-10">
                                <div class="row">
                                    <div class="col-12 col-md-3"><?php echo esc_html( $person['occupation'] ?? '' ); ?></div>
                                    <div class="col-12 col-md-3"><?php echo esc_html( $person['email'] ?? '' ); ?></div>
                                    <div class="col-12 col-md-2"><?php echo esc_html( $person['phone'] ?? '' ); ?></div>
                                    <div class="col-12 col-md-4"><?php echo esc_html( implode(', ', $person['roles'] ) ?? '' ); ?></div>
                                </div>
                            </div>
                            <div class="col-3 col-md-2 text-center">
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <i class="<?php echo $sex_icon; ?>"></i>
                                    </div>
                                    <div class="col-6 text-center">
                                        <i icon-tag="person-user-icon-register" class="<?php echo $reg_icon; ?>"></i>
                                    </div>
                                </div>
                            </div>                        
                        </div>

                        <!-- <xmp><?php print_r( $person ); ?></xmp> -->
                </div><?php 

            }
    ?></div>

    <div id="wpsg-row-menu" class="wpsg-row-menu" style="display:none;">
        <ul>
            <li><a href="#" data-action="<?php echo wpsg_encode_keys([$user->ID,'edit']); ?>">
                <!-- <i class="fas fa-fw fa-edit" data-action="edit"></i> -->
                <!-- &nbsp; -->
                Form Data
            </a></li>
        </ul>
    </div>

    <style>

        .wpsg-child-row {
            cursor: pointer;
        }

        .wpsg-row-menu {
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 6px;
            z-index: 9999;
        }

        .wpsg-row-menu ul {
            list-style: none;
            margin: 0;
            padding: 6px 0;
        }

        .wpsg-row-menu li a {
            display: block;
            padding: 6px 14px;
            text-decoration: none;
            color: #333;
        }

        .wpsg-row-menu li a:hover {
            background: #f0f0f0;
        }

    </style>

    <script type="text/javascript" lang="javascript">
        document.addEventListener('DOMContentLoaded', function () {

            const menu = document.getElementById('wpsg-row-menu');
            const sid  = '<?php echo esc_js( $_GET['sid'] ?? '' ); ?>';

            let currentRow = null;

            document.querySelectorAll('.wpsg-grid-data').forEach(row=>{

                row.addEventListener('click',(e)=>{

                    let body_rect = document.getElementById('div-person-area').getBoundingClientRect();
                    e.preventDefault();

                    currentRow = row;

                    pos_y = e.clientY - 20;
                    pos_x = e.clientX + 10;
                    if( pos_x > body_rect.right - menu.offsetWidth ){
                        pos_x = body_rect.right - menu.offsetWidth - 10;
                    }
                    if( pos_x > body_rect.right - 120 ){
                        pos_x = e.clientX - menu.offsetWidth - 10;
                        pos_y = e.clientY + 10;
                    }

                    menu.style.display = 'block';
                    menu.style.position = 'fixed';
                    menu.style.top  = pos_y + 'px';
                    menu.style.left = pos_x + 'px';

                });

            });

            // klik menu
            menu.addEventListener('click', function (e) {


                e.preventDefault();

                // console.log( currentRow.dataset );
                let code_id = currentRow.getAttribute('code-id');

                const action = e.target.dataset.action;

                if (!action || !currentRow) return;

                const url = new URL( currentRow.dataset.editUrl, window.location.origin );
                url.searchParams.set( 'cid', code_id );
                url.searchParams.set( 'act', action  );
                url.searchParams.set( 'id' , currentRow.dataset.id );

                // console.log( url.toString() );

                window.location.href = url.toString();

            });

            // klik di luar → tutup
            document.addEventListener('click', function (e) {
                if (!menu.contains(e.target) && !e.target.closest('.wpsg-grid-data')) {
                    menu.style.display = 'none';
                }
            });

        });
    </script>

</div>
