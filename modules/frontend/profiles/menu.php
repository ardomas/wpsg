<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
$current_url = esc_url( add_query_arg( 'sid', $_GET['sid'] ), get_permalink() );

?><div class="row mb-4">
    <div class="col-12">
        <div class="btn-group">
            <a class="btn btn-outline-primary" href="<?php echo $current_url . '&pid=' . wpsg_encode_keys( [$current_user->ID, 'dataform'] ); ?>">
                User Profile
            </a>
            <a class="btn btn-outline-primary" href="<?php echo $current_url . '&pid=' . wpsg_encode_keys( [$current_user->ID, 'password'] ); ?>">
                Change Password
            </a>
        </div>
    </div>
</div>