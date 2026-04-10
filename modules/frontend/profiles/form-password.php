<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_debug = false;
$class_code = $is_debug ? 'd-block' : 'd-none';
$class_text = $is_debug ? 'text'    : 'hidden';

$msg = '';
if( isset( $_GET['msg'] ) ){
    $msg = base64_decode( $_GET['msg'] );
}

$user_service   = new WPSG_UsersService();

$current_user = wp_get_current_user();

$user_data = $current_user->data ?? [];

?><div class="wpsg-page">
    <div class="wpsg-page-content">
        <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

            <div class="<?php echo $class_code; ?>">
                <input type="hidden" id="main_base_url" value="https://l.ptpai.org"/>
                <input type="<?php echo $class_text; ?>" name="app" id="app" value="<?php echo fe_get_app_url(); ?>">
                <input type="<?php echo $class_text; ?>" name="sid" id="sid" value="<?php echo esc_attr($_GET['sid']); ?>">
                <input type="<?php echo $class_text; ?>" id="user_id"   name="user_id"   value="<?php echo esc_attr( $current_user->ID ); ?>"/>
                <input type="<?php echo $class_text; ?>" name="action" value="wpsg_change_password">
                <input type="<?php echo $class_text; ?>" name="msg" value="<?php echo $msg; ?>"/>
            </div>
            <?php wp_nonce_field('wpsg_change_password','wpsg_password_nonce'); ?>

<?php
/*
?>
                TEST<br/><?php
                print_r( $user_data );
                ?>
<?php
/* */
?>
            <div  class="wpsg-text-center" style="width: 60%; max-width: 540px; min-width: 320px;">
                <div class="wpsg-boxed wpsg-text-left">
                    <div class="wpsg-panel">
                        <div class="row mb-5">
                            <div class="col-12">
                                <label class="control-label" for="pass-old">Input Old Password</label>
                                <div class="input-group">
                                    <input type="password" name="pass_old" id="pass-old" class="form-control" value="" required="required">
                                    <span type="button" name="btn-pwd-eye" data-trigger="pass-old" class="group-input-text btn btn-secondary border">
                                        <i id="icon-pass-old" class="fa fa-eye fa-fw"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="control-label" for="pass-new">Input New Password</label>
                                <div class="input-group">
                                    <input type="password" name="pass_new" id="pass-new" class="form-control" value="" required="required">
                                    <span type="button" name="btn-pwd-eye" data-trigger="pass-new" class="group-input-text btn btn-secondary border">
                                        <i id="icon-pass-new" class="fa fa-eye fa-fw"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="control-label" for="pass-chk">Confirmation Password</label>
                                <div class="input-group">
                                    <input type="password" name="pass_chk" id="pass-chk" class="form-control" value="" required="required">
                                    <span type="button" name="btn-pwd-eye" data-trigger="pass-chk" class="group-input-text btn btn-secondary border">
                                        <i id="icon-pass-chk" class="fa fa-eye fa-fw"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12 text-center">
                                <label id="change_password_message" class="control-label"><?php
                                if( isset( $msg ) && trim( $msg )!='' ){
                                    echo $msg;
                                }
                                ?></label>
                            </div>
                        </div>
                        <div class="row mt-5 mb-3">
                            <div class="col-6 text-start">
                                <button type="button" id="btn-page-id-to-home-base" class="btn btn-warning">
                                    <i class="fas fa-reply fa-lg fa-fw"></i>
                                    <span class="d-none d-md-inline px-2">Cancel</span>
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="submit" id="btn-page-id-login-submit" class="btn btn-primary">
                                    <i class="fas fa-check fa-lg fa-fw"></i>
                                    <span class="d-none d-md-inline px-2">Submit</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script type="text/javascript" lang="javascript">
                (()=>{

                    let elms = document.getElementsByName('btn-pwd-eye');
                    elms.forEach((elm)=>{
                        elm.addEventListener('click',()=>{
                            src_elm_id = elm.getAttribute('data-trigger');
                            src_elm_ob = document.getElementById( src_elm_id );
                            ico_elm_ob = document.getElementById( 'icon-' + src_elm_id );
                            init_type  = src_elm_ob.getAttribute('type');
                            init_icon  = init_type == 'password' ? 'fa-eye-slash' : 'fa-eye';
                            src_elm_ob.setAttribute('type', init_type=='password' ? 'text' : 'password' );
                            ico_elm_ob.classList.remove('fa-eye');
                            ico_elm_ob.classList.remove('fa-eye-slash');
                            ico_elm_ob.classList.add( init_icon );
                        });
                    });

                    let check_form_data = ()=>{
                        let obj_pass_old = document.getElementById('pass-old');
                        let obj_pass_new = document.getElementById('pass-new');
                        let obj_pass_chk = document.getElementById('pass-chk');
                        let text_old = String( obj_pass_old.value ).trim();
                        let text_new = String( obj_pass_new.value ).trim();
                        let text_chk = String( obj_pass_chk.value ).trim();
                        if( text_old=='' || text_new=='' || text_chk=='' ){
                            if( text_old=='' && text_new=='' && text_chk=='' ){
                                document.getElementById('change_password_message').textContent = '';
                            } else {
                                if( text_old=='' ){
                                    document.getElementById('change_password_message').textContent = 'password lama harus diisi';
                                } else if( text_new=='' ) {
                                    document.getElementById('change_password_message').textContent = 'password baru harus diisi';
                                } else if( text_chk=='' ) {
                                    document.getElementById('change_password_message').textContent = 'konfirmasi password harus diisi';
                                } else {
                                    document.getElementById('change_password_message').textContent = '';
                                }
                            }
                            document.getElementById('btn-page-id-login-submit').setAttribute('disabled','disabled');
                        } else {
                            document.getElementById('change_password_message').textContent = '';
                            if( text_new != text_chk ){
                                document.getElementById('change_password_message').textContent = 'password baru dan konfirmasi password tidak sama';
                                document.getElementById('btn-page-id-login-submit').setAttribute('disabled','disabled');
                            } else {
                                if( text_old==text_new ){
                                    document.getElementById('change_password_message').textContent = 'password baru masih sama dengan password lama';
                                    document.getElementById('btn-page-id-login-submit').setAttribute('disabled','disabled');
                                } else {
                                    document.getElementById('btn-page-id-login-submit').removeAttribute('disabled');
                                }
                            }
                        }
                    }

                    if( '<?php echo trim($msg); ?>' == '' ) {
                        check_form_data();
                    }
                    document.getElementById('btn-page-id-login-submit').setAttribute('disabled','disabled');

                    document.getElementById('pass-old').addEventListener('keyup',()=>{ check_form_data(); });
                    document.getElementById('pass-new').addEventListener('keyup',()=>{ check_form_data(); });
                    document.getElementById('pass-chk').addEventListener('keyup',()=>{ check_form_data(); });

                    document.getElementById('btn-page-id-to-home-base').addEventListener('click',()=>{ 
                        window.location= document.getElementById('main_base_url').value ;
                    });

                })();
            </script>

        </form>
    </div>
</div>