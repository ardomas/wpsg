<?php

function wpsg_login_form( $atts ) {

    // Parameter: redirect URL setelah login
    $atts = shortcode_atts( [
        'redirect' => home_url(), // default ke homepage
    ], $atts );

    // Jika user sudah login, tampilkan pesan
    if ( is_user_logged_in() ) {
        return '<p>You are already logged in.</p>';
    }

    // Form login sederhana
    ob_start();
    ?>
    <form method="post" action="<?php echo esc_url( wp_login_url() ); ?>">
        <p>
            <label for="wpsg_username">Phone Number or Email</label><br>
            <input type="text" name="log" id="wpsg_username" required>
        </p>
        <p>
            <label for="wpsg_password">Password</label><br>
            <input type="password" name="pwd" id="wpsg_password" required>
        </p>
        <p>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url( $atts['redirect'] ); ?>">
            <input type="submit" value="Login">
        </p>
    </form>
    <?php
    return ob_get_clean();

}