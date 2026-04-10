<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$children_service = new WPSG_PersonsService();

$user      = wp_get_current_user();
// echo '<p>User:<br/>';
// print_r( $user->roles );
// echo '</p>';

$p_user    = get_person_by_user_id( $user->ID );
// echo '<p>Person:<br/>';
// print_r( $p_user );
// echo '</p>';

$person_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$user_id   = $user->ID;
$code_key  = wpsg_encode_keys( [$user_id, $person_id] );

$param_ok = false;
if( isset( $_GET['cid'] ) ){
    $cid = $_GET['cid'];
    if( $cid == $code_key ){
        $param_ok = true;
    }
}

$can_edit_data = ( ($user && $user->roles && ($user->roles[0] != 'subscriber')) || ( $p_user && !in_array( $p_user['role'], ['guardian','child'] ) ) );

if( !$param_ok ){
    wp_safe_redirect( wp_get_referer() );
} else {

// ambil ID jika edit
$is_edit   = $person_id > 0;

// default data
$data = [
    'name'        => '',
    'status'      => 'active',
    'birth_place' => '',
    'birth_date'  => '',
    'gender'      => '',
    'blood_type'  => '',
    'address'     => '',
];

?>

    <div class="wpsg-page wpsg-children-edit">

        <div class="row">
            <div class="col-10">
                <div class="wpsg-page-header">
                    <h3 class="wpsg-page-title">
                        <?php echo $is_edit ? 'Edit Data Anak' : 'Tambah Data Anak'; ?>
                    </h3>
                </div>
            </div>
            <div class="col-2 mt-3 text-end">
                <a class="btn btn-process" href="<?php echo esc_url( remove_query_arg( ['act','id','cid'] ) ); ?>">
                    <i class="fa fa-reply fa-fw"></i>
                    <span class="d-none d-md-inline">Kembali</span>                            
                </a>
            </div>
        </div>

        <div class="wpsg-page-content">
    <?php
            $person_id = $_GET['id'] ?? '';
            if( $person_id!='' ){
                $person = $children_service->get_person( absint($person_id) );
                if( $person ){
                    $data['name']        = $person['name'] ?? '';
                    $data['status']      = $person['status'] ?? 'active';
                    $data['birth_place'] = $person['birth_place'] ?? '';
                    $data['birth_date']  = $person['birth_date'] ?? '';
                    $data['gender']      = $person['gender'] ?? '';
                    $data['blood_type']  = $person['blood_type'] ?? '';
                    $data['address']     = $person['address'] ?? '';
                }
            }
            // print_r($data);
            /*
            <form method="post" action="<?php echo esc_url( add_query_arg( ['sid'=> $_GET['sid'] ?? '','action' => 'add',], home_url('/app/') ) ); ?>">
            */
    ?>
            <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

                <input type="hidden" name="sid" id="sid" value="<?php echo esc_attr( $_GET['sid'] ); ?>">
                <input type="hidden" name="cid" id="cid" value="<?php echo esc_attr( $_GET['cid'] ); ?>"/>
                <input type="hidden" name="vid" id="vid" value="<?php echo esc_attr( $code_key ); ?>"/>
                <input type="hidden" name="action" value="wpsg_save_child_as_person_data">
                <input type="hidden" name="person_id" value="<?php echo $person_id; ?>"/>

                <?php wp_nonce_field('wpsg_save_child_as_person_data','wpsg_children_nonce'); ?>

                <?php 

                    $flag_can_edit = $can_edit_data ?? false;

                    require __DIR__ . '/child-dataform.php';

                ?>

                <div class="mt-4">
                    <div class="row">
                        <div class="col-6 text-start">
                            <button type="button" 
                                class="btn btn-cancel"
                                id="btn-delete"
                                data-url="<?php echo esc_url( remove_query_arg( ['act'] ) . '&act=' . wpsg_encode_keys( [$user->ID, 'delete'] ) ); ?>">
                                <i class="fas fa-trash-alt fa-fw"></i>
                                <span class="d-none d-md-inline">Hapus</span>
                            </button>
                        </div>
                        <div class="col-6 text-end">
                            <button type="submit" class="btn btn-process">
                                <i class="fas fa-floppy-disk fa-fw"></i>
                                <span class="d-none d-md-inline">Simpan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <script type="text/javascript" lang="javascript">
                    document.addEventListener('DOMContentLoaded',()=>{
                        //
                        let sid = document.getElementById('sid').value;
                        let cid = document.getElementById('cid').value;
                        let vid = document.getElementById('vid').value;
                        //
                        if( cid!=vid ){
                            window.alert('something wrong...!!!');
                            window.location = window.origin + '/<?php echo fe_get_app_url(); ?>?sid='+sid;
                        }

                        //
                        const btn_delete = document.getElementById('btn-delete');
                        btn_delete.addEventListener('click',(e)=>{
                            if( window.confirm('Are you sure, You want to delete this data') ){
                                let data_url = btn_delete.getAttribute('data-url');
                                // console.log( data_url );
                                window.location = data_url;
                                // e.preventDefault();
                                // console.log('button delete clicked');
                                // console.log(e);
                                // console.log(data_url);
                            }
                        });
                    });
                </script>

            </form>

        </div>
    </div>

<?php

}

?>