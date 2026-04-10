<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_service   = new WPSG_UsersService();
// $person_service = new WPSG_PersonsService();

$genders = fe_get_app_genders();

$current_user = wp_get_current_user();

$user_data = $current_user->data ?? [];

$person = $user_service->get_person_by_user_id( $current_user->ID );

if( is_null($person) ) {
    $blank_person = $user_service->get_blank_person();
    $person = $blank_person;
    $person['user_id'] = $current_user->ID;
    $person['name']    = $current_user->display_name  ?? $blank_person['name'];
    $person['slug']    = $current_user->user_nicename ?? $blank_person['slug'];
    $person['email']   = $current_user->user_email    ?? $blank_person['email'];
    $person['phone']   = '';
    $person['gender']  = '';
    $person['status']  = 'active';
    $person['birth_place'] = '';
    $person['birth_date']  = null;
    $person['occupation']  = '';
    $person['user_data']   = [];
    $person['roles']       = ['guardian'];
    $person['description'] = '';
    // $person = [
    //     'id' => $blank_person['id'] ?? 0,
    //     'user_id' => $current_user->ID,
    //     'name' => $blank_person['name'] ?? $user_data->display_name,
    //     'email' => $blank_person['email'] ?? $user_data->user_email,
    //     'phone' => $blank_person['phone'] ?? '',
    // ];
}

/*
?>Person: <xmp><?php
print_r( $person ?? 'person data is null' );
?></xmp><br/>Gender: <xmp><?php
print_r( $genders );
?></xmp><?php
*/

// print_r($person);
// die('person data');

$person_id = $person['id'] ?? 0;

?><div class="wpsg-page">
    <div class="wpsg-page-content">
        <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

            <div class="d-none">
                <input type="hidden" name="app" id="app" value="<?php echo fe_get_app_url(); ?>">
                <input type="hidden" name="sid" id="sid" value="<?php echo esc_attr($_GET['sid']); ?>">
                <input type="hidden" id="person_id" name="person_id" value="<?php echo esc_attr( $person_id ); ?>"/>
                <input type="hidden" id="user_id"   name="user_id"   value="<?php echo esc_attr( $current_user->ID ); ?>"/>
                <input type="hidden" name="action" value="wpsg_save_user_profile">
            </div>

            <?php wp_nonce_field('wpsg_save_user_profile','wpsg_person_nonce'); ?>

            <!-- INIT FORM -->
            <?php
            // print_r( $person );
            ?>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="name">Nama</label>
                    <input class="form-control" type="text" id="name" name="name" value="<?php echo esc_html( $person['name'] ); ?>"/>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="text" id="email" name="email" value="<?php echo esc_html( $person['email'] ); ?>"/>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-control" type="text" id="phone" name="phone" value="<?php echo esc_html( $person['phone'] ); ?>"/>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <?php
                        
                        if( $person['status']!='active' ){
                            $user_class="bg-danger";
                            $user_icon = 'fas fa-user-slash fa-fw';
                        } else {
                            $user_class="bg-success";
                            $user_icon = 'fas fa-user-check fa-fw';
                        }
                    ?>
                    <div class="input-group text-center">
                            <span class="input-group-text <?php echo $user_class; ?>"><i class="<?php echo $user_icon; ?> text-white"></i></span>
                            <input class="form-control" value="<?php echo $person['status']; ?>"/>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="birth_place">Tempat Lahir</label>
                    <input class="form-control" type="text" id="birth_place" name="birth_place" value="<?php echo esc_html( $person['birth_place'] ); ?>"/>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="birth_date">Tanggal Lahir</label>
                    <input class="form-control" type="date" id="birth_date" name="birth_date" value="<?php echo esc_html( $person['birth_date'] ); ?>"/>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label" for="gender">Jenis Kelamin</label>
                    <select class="form-select" id="gender" name="gender"><?php
                        foreach( $genders as $key=>$val ){
                            ?><option value="<?php echo esc_html( $key ); ?>"<?php echo ( $key == $person['gender'] ) ? ' selected' : ''; ?>><?php echo $val; ?></option><?php
                        }
                    ?></select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label" for="blood_type">Gol. Darah</label>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="occupation">Pekerjaan</label>
                    <input class="form-control" type="text" id="occupation" name="occupation" value="<?php echo esc_html( $person['occupation'] ); ?>"/>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="description">Informasi/Keterangan Tambahan</label>
                    <textarea class="form-control" id="description" name="description"><?php
                        echo esc_html( $person['description'] );
                    ?></textarea>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6 text-start">
                    <button type="button" class="btn btn-danger" id="btn-delete" data-url="<?php echo esc_url( remove_query_arg( ['action'] ) . '&action=guardian-delete' ); ?>">
                        <i class="fas fa-trash-alt fa-fw"></i>
                        <span class="d-none d-md-inline">Hapus</span>
                    </button>
                </div>
                <div class="col-6 text-end">
                    <button type="submit" class="btn btn-primary">
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
                
        </form>
    </div>
</div><?php
