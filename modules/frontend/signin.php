<?php
/**
 * modules/frontend/signin.php
 * sign on page modul frontend WPSG
 */

if (! defined('ABSPATH')) {
    exit;
}

$site_url = get_site_url();

?>
<form name="loginform" id="loginform" action="<?php echo $site_url; ?>/app" method="post">
    <input type="hidden" id="main_base_url" value="<?php echo $site_url; ?>/"/>
    <div class="wpsg">
        <div class="wpsg-main wpsg-form">
            <div  class="wpsg-text-center" style="width: 60%; max-width: 540px; min-width: 320px;">
                <div class="wpsg-boxed wpsg-text-left">
                    <div class="wpsg-panel">
                        <div class="row">
                            <div class="col-12">
                                <div class="wpsg-form-field">
                                    <label class="control-label" for="user_login">Username or Email Address</label>
                                    <input type="text" name="log" id="user_login" class="form-control" value="" autocapitalize="off" autocomplete="username" required="required">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="wpsg-form-field">
                                    <label class="control-label" for="user_pass">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="pwd" id="user_pass" class="form-control" value="" autocomplete="current-password" spellcheck="false" required="required">
                                        <button type="button" id="btn-front-page-pass-eye" class="group-input-text btn btn-secondary">
                                            <i id="ico-front-page-pass-eye" class="fa-regular fa-eye fa-fw"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php
/*
?>
<!--
                        <div class="row">
                            <div class="col-12">
                                <div class="wpsg-form-field">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="remember" id="user_remember" class="form-check-input"/>
                                        </div>
                                        <div class="form-control">
                                            <label for="user_remember">Remember me!</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
-->
<?php
*/
?>
                    </div>                        
                    <div class="wpsg-panel mt-5">
                        <div class="row">
                            <div class="col-6 text-start">
                                <button type="button" id="btn-page-id-to-home-base" class="btn btn-secondary">
                                    <i class="fas fa-home fa-lg fa-fw"></i>
                                    <span class="d-none d-md-inline px-2">Back to Web Home</span>
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="submit" id="btn-page-id-login-submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt fa-lg fa-fw"></i>
                                    <span class="d-none d-md-inline px-2">Submit</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" lang="javascript">
        (()=>{

            document.getElementById('btn-front-page-pass-eye').addEventListener('click',()=>{ 
                let obj_pass = document.getElementById('user_pass');
                let obj_icon = document.getElementById('ico-front-page-pass-eye');
                if( obj_icon.classList.contains('fa-eye') ){
                    obj_pass.setAttribute('type','text');
                    obj_icon.classList.remove('fa-eye');
                    obj_icon.classList.add('fa-eye-slash');
                } else {
                    obj_pass.setAttribute('type','password');
                    obj_icon.classList.add('fa-eye');
                    obj_icon.classList.remove('fa-eye-slash');
                }                
            });

            document.getElementById('btn-page-id-to-home-base').addEventListener('click',()=>{ 
                window.location= document.getElementById('main_base_url').value ;
            });

        })();
    </script>
</form>
<?php

?>