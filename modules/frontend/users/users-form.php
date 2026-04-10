<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $guardian_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$user_service = new WPSG_UsersService();

$user = wp_get_current_user();
$person_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$user_id   = $user->ID;
$code_key  = wpsg_encode_keys( [$user_id, $person_id] );

$params_ok = false;
if( isset( $_GET['sid'] ) && isset( $_GET['cid'] ) ){
    $cid = $_GET['cid'];
    if( $cid == $code_key ){
        $params_ok = true;
    }
}

if( !$params_ok ){
    //
    // wp_safe_redirect( esc_url( remove_query_arg(['id','cid','act'] ) ) );
    // wp_safe_redirect( fe_get_app_url() );
    //
}

$is_edit   = $person_id > 0;

/*
$default_person = [
    'id' => 0,
    'user_id' => null,
    'name' => '',
    'email' => '',
    'phone' => '',
    'gender' => '',
    'status' => 'active',
    'birth_place' => '',
    'birth_date' => '',
    'occupation' => '',
    'user_data' => [],
    'roles' => ['guardian'],
    'description' => '',
];
// 
$default_person = $user_service->get_blank_person();
$default_person['roles'] = ['guardian'];
*/

$init_user = $user_service->get_person($person_id);
if( $person_id==0 ){
    $init_user = $user_service->get_blank_person();
    $init_user['phone'] = '';
    $init_user['gender'] = '';
    $init_user['status'] = 'active';
    $init_user['birth_place'] = '';
    $init_user['birth_date'] = '';
    $init_user['occupation'] = '';
    $init_user['roles'] = ['guardian'];
}

$genders = fe_get_app_genders();
$person_status = fe_get_app_status();

?>

<div class="wpsg-page">

    <div class="wpsg-page-header">
        <div class="row">
            <div class="col-10 text-start">
                <h3 class="wpsg-page-title">
                    <?php echo $is_edit ? 'Edit Data' : 'Tambah Data'; ?> User
                </h3>
            </div>
            <div class="col-2 mt-2 text-end">
                <a class="btn btn-process" href="<?php echo esc_url( remove_query_arg( ['act','id','cid'] ) ); ?>">
                    <i class="fa fa-reply fa-fw"></i>
                    <span class="d-none d-md-inline">Kembali</span>                            
                </a>
            </div>
        </div>
    </div>

<?php
/*
?>
    <div class="wpsg-page-content">
        <xmp><?php
        print_r( $init_user );
        ?></xmp>
    </div>
<?php
/* */
?>

    <div class="wpsg-page-content">
        <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

            <div class="d-none">
                <input type="hidden" name="app" id="app" value="<?php echo fe_get_app_url(); ?>"/>
                <input type="hidden" name="sid" id="sid" value="<?php echo esc_attr( $_GET['sid'] ); ?>"/>
                <input type="hidden" name="cid" id="cid" value="<?php echo esc_attr( $_GET['cid'] ); ?>"/>
                <input type="hidden" name="vid" id="vid" value="<?php echo esc_attr( $code_key ); ?>"/>
                <input type="hidden" name="action" value="wpsg_save_person_biodata"/>
                <input type="hidden" id="person_id" name="person_id" value="<?php echo esc_attr( $person_id ); ?>"/>
            </div>

<?php
        if( $params_ok ) {
?>

            <?php wp_nonce_field('wpsg_save_person_biodata','wpsg_person_nonce'); ?>

            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="name">Nama</label>
                    <input class="form-control" type="text" id="name" name="name" value="<?php echo esc_html( $init_user['name'] ); ?>"/>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="text" id="email" name="email" value="<?php echo esc_html( $init_user['email'] ); ?>"/>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-control" type="text" id="phone" name="phone" value="<?php echo esc_html( $init_user['phone'] ); ?>"/>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="status">Guardian</label>
                    <select class="form-select" id="status" name="status"><?php
                        foreach( $person_status as $pers_stat ){
                            ?><option value="<?php echo $pers_stat; ?>"<?php echo ( $pers_stat==$init_user['status'] ) ? ' selected':''; ?>><?php
                                echo ucfirst( $pers_stat );
                            ?></option><?php
                        }
                    ?></select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="user_status">User Status</label>
                    <select class="form-select" id="user_status" name="user_status">
                    <?php
                    $user_stat = $init_user['status'] ?? [];
                    ?>
                        <option value="1"<?php echo ($user_stat=='active' && $init_user['user_data']==[]) ? '' : ' selected'; ?>>Active</option>
                        <option value="2"<?php echo ($user_stat=='active' && $init_user['user_data']==[]) ? ' selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="birth_place">Tempat Lahir</label>
                    <input class="form-control" type="text" id="birth_place" name="birth_place" value="<?php echo esc_html( $init_user['birth_place'] ); ?>"/>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="birth_date">Tanggal Lahir</label>
                    <input class="form-control" type="date" id="birth_date" name="birth_date" value="<?php echo esc_html( $init_user['birth_date'] ); ?>"/>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="gender">Jenis Kelamin</label>
                    <select class="form-select" id="gender" name="gender"><?php
                        foreach( $genders as $key=>$val ){
                            ?><option value="<?php echo esc_html( $key ); ?>"<?php echo ( $key == $init_user['gender'] ) ? ' selected' : ''; ?>><?php echo $val; ?></option><?php
                        }
                    ?></select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="occupation">Pekerjaan</label>
                    <input class="form-control" type="text" id="occupation" name="occupation" value="<?php echo esc_html( $init_user['occupation'] ); ?>"/>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="description">Informasi/Keterangan Tambahan</label>
                    <textarea class="form-control" id="description" name="description"><?php
                        echo esc_html( $init_user['description'] );
                    ?></textarea>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6 text-start">
                    <button type="button" class="btn btn-cancel" id="btn-delete" data-url="<?php echo esc_url( remove_query_arg( ['action'] ) . '&action=guardian-delete' ); ?>">
                        <i class="fas fa-trash-alt fa-fw"></i>
                        <span class="d-none d-md-inline">Hapus</span>
                    </button>
                </div>
                <div class="col-6 text-end">
                    <button type="submit" class="btn btn-process">
                        <i class="fas fa-floppy-disk fa-fw"></i>
                        <span class="d-none d-md-inline">Simpan</span>                            
                    </button>
<?php
/*
?>
                    <a class="btn btn-secondary" href="<?php echo esc_url( remove_query_arg( ['act','id'] ) ); ?>">
                        <i class="fas fa-reply fa-fw"></i>
                        <span class="d-none d-md-inline">Kembali</span>                            
                    </a>
<?php
/* */
?>
                </div>
            </div>

<?php

        }

?>

            <script type="text/javascript" lang="javascript">
                document.addEventListener('DOMContentLoaded',function(){
                    //
                    var params_ok = false;
                    var safe_url  = window.origin;
                    // console.log(safe_url);
                    if( document.getElementById('app') ){
                        safe_url  = safe_url + document.getElementById('app').value;
                        if( document.getElementById('sid') ){
                            let sid = document.getElementById('sid').value;
                            safe_url = safe_url + '?sid=' + sid;
                            // safe_url = window.origin + '?sid=' + sid;
                            if( document.getElementById('cid') &&
                                document.getElementById('vid') ) {
                                let cid = document.getElementById('cid').value;
                                let vid = document.getElementById('vid').value;
                                if( cid==vid ){
                                    params_ok = true;
                                }
                            }
                        }
                    }
                    //
                    if( !params_ok ){
                        window.alert('something wrong...!!!');
                        window.location = safe_url;
                    }
                    //
                    
                });
            </script>
<?php

?>
        </form>
    </div>

</div>