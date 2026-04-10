<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$obj_children = new WPSG_ChildrenService();
$obj_person   = new WPSG_PersonsRepository();

$user = wp_get_current_user();
$cur_person  = get_person_by_user_id( $user->ID );

// print_r( $cur_person );

/*
$url_new_data = esc_url( 
    add_query_arg( 
        [
            'sid'=>$_GET['sid'],
            'act'=>wpsg_encode_keys( [$user->ID,'add']), 
            'cid'=>wpsg_encode_keys( [$user->ID], '0' )
        ] 
    )
);
*/

$url_new_data = esc_url( fe_get_app_url() )
              . '?' . 'sid=' . $_GET['sid']
              . '&' . 'act=' . wpsg_encode_keys( [$user->ID, 'add'] )
              . '&' . 'cid=' . wpsg_encode_keys( [$user->ID, '0']   );

// echo '<br/>url: ' . $url_new_data;

?>

<div class="wpsg-page">

    <!-- Page Header -->
    <div class="wpsg-page-header">
        <div class="row">
            <div class="col-9 text-start">
                <h3 class="wpsg-page-title">Daftar Anak</h3>
            </div>
            <div class="col-3 text-end">
                <?php
                if( $user->roles && $user->roles[0] != 'subscriber' ){
                    ?>
                        <a href="<?php echo $url_new_data; ?>"
                        class="btn btn-process">
                            <i class="fas fa-plus"></i>
                            <span class="d-none d-md-inline">
                                &nbsp;Tambah Anak
                            </span>
                        </a>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Content -->
    <div class="wpsg-page-content">

        <div id="div-children-area" class="wpsg-page-content wpsg-grid-border wpsg-grid-hover py-2 px-2"><?php
            $children = $obj_children->get_children();
            if( empty( $children ) ){
                ?><?php
            } else {
                foreach( $children as $child ){

                    $person_id  = $child['id'];
                    $name       = $child['name'] ?? '(noname)';
                    $birth_date = $child['birth_date'] ?? '-';
                    $age        = '-';
                    $gender     = '-'; 
                    if( $child['gender'] == 'M' ) {
                        $gender = 'Laki-laki';
                    } elseif( $child['gender'] == 'F' ) {
                        $gender = 'Perempuan';
                    }
                    if( !empty($child['birth_date']) ){
                        try {
                            $birth = new DateTime( $birth_date );
                            $now   = new DateTime();
                            $diff  = $birth->diff( $now );
                            $age   = $diff->y . ' tahun';
                        } catch( Exception $e ){
                            $age = '-';
                        }
                    }
                    $status = $child['status'] ?? 'Active';
                    // data of guardians
                    $related_guardians = $obj_children->get_related_persons_by_types( $child['id'], ['father','mother','guardian'] );
                    // $guardians = $obj_children->get_related_persons_by_types( $child['id'], ['father','mother','guardian'] );
                    $parent = $child['parent_name'] ?? '(noname)';
                    $code_id= wpsg_encode_keys( [$user->ID,$child['id']] );

                    ?><div class="row wpsg-grid-data wpsg-child-row my-0 px-2 mb-1 pt-3 pb-2"
                        data-id="<?php echo esc_attr( $child['id'] ); ?>"
                        code-id="<?php echo $code_id; ?>"
                        data-edit-url="<?php
                            echo esc_url(
                                add_query_arg(
                                    [
                                        'id'     => $child['id'],
                                    ]
                                )
                            );
                        ?>">
                        <div class="col-12 col-md-8">
                            <div class="row">
                                <div class="col-12 fw-bold"><?php  echo esc_html( $name   ); ?></div>
                                <div class="col-12 col-md-5"><?php echo esc_html( $age    ); ?> ( <?php echo $birth_date; ?> )</div>
                                <div class="col-12 col-md-4"><?php echo esc_html( $gender ); ?></div>
                                <div class="col-12 col-md-3"><?php echo esc_html( $status ); ?></div>
                            </div>
                        </div>
                        <div class="col12 col-md-4"><?php
                        foreach( $related_guardians as $relation => $guardian_item ){
/*
                            echo '<p><xmp>';
                            print_r( $guardian_item );
                            echo '</xmp></p>';
/* */
                            $guardian_row = $guardian_item[0] ?? [];
                            if( $guardian_row != [] ){
                                $guardian = $obj_person->get( $guardian_row['related_person_id'] );
                                ?><div class="row">
                                    <div class="d-none d-md-none d-lg-block col-4 text-end"><?php
                                        switch( $relation ){
                                            case 'father': echo 'Ayah :'; break;
                                            case 'mother': echo 'Ibu :'; break;
                                            default: echo 'Wali :'; break;
                                        }
                                    ?></div>
                                    <div class="col text-start"><?php 
                                        // print_r( $guardian );
                                        echo $guardian['name']; 
                                    ?></div>
                                </div><?php
                            }

                        }
                        ?></div>
                    </div><?php
                }
            }
        ?></div>

        <div id="wpsg-row-menu" class="wpsg-row-menu" style="display:none;">
            <ul>
                <?php if( $cur_person && $cur_person['role']=='guardian' ){
                    $child_act = 'view';
                    $guard_act = 'guardian-view';
                } else {
                    $child_act = 'edit';
                    $guard_act = 'guardian-edit';
                }
                ?>
                <li><a href="#" data-action="<?php echo wpsg_encode_keys( [$user->ID, $child_act ] ); ?>">
                    <!-- <i class="fas fa-fw fa-edit" data-action="edit"></i> -->
                    <!-- &nbsp; -->
                    Biodata Anak
                </a></li>
                <li><a href="#" data-action="<?php echo wpsg_encode_keys([$user->ID, $guard_act ] ); ?>">
                    <!-- <i class="fas fa-fw fa-user-friends" data-action="guardian-edit"></i> -->
                    <!-- &nbsp; -->
                    Orang Tua/Wali
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
                const sid = '<?php echo esc_js( $_GET['sid'] ?? '' ); ?>';
                const child_id = '<?php echo esc_js( $_GET['id'] ?? '' ); ?>';

                let currentRow = null;

                document.querySelectorAll('.wpsg-child-row').forEach(row => {
                    row.addEventListener('click', function (e) {

                        let body_rect = document.getElementById('div-children-area').getBoundingClientRect();
                        e.preventDefault();

                        pos_y = e.clientY - 20;
                        pos_x = e.clientX + 10;
                        if( pos_x > body_rect.right - menu.offsetWidth ){
                            pos_x = body_rect.right - menu.offsetWidth - 10;
                        }
                        if( pos_x > body_rect.right - 120 ){
                            pos_x = e.clientX - menu.offsetWidth - 10;
                            pos_y = e.clientY + 10;
                        }

                        currentRow = row;

                        menu.style.display = 'block';
                        menu.style.position = 'fixed';
                        menu.style.top  = pos_y + 'px';
                        menu.style.left = pos_x + 'px';
                        // menu.style.top  = ( 1 * e.pageY ) + 'px';
                        // menu.style.left = ((document.body.offsetWidth - menu.offsetWidth)/2) + 'px';

                    });
                });

                // klik menu
                menu.addEventListener('click', function (e) {
                    e.preventDefault();

                    // console.log( e.target.dataset );

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
                    if (!menu.contains(e.target) && !e.target.closest('.wpsg-child-row')) {
                        menu.style.display = 'none';
                    }
                });

            });
        </script>

    </div>
</div>
