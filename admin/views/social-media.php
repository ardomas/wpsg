<?php
if (!defined('ABSPATH')) exit;

function wpsg_social_media_page() {
    ?>
    <div class="wrap">
        <h1>Manage Social Media Links</h1>
        <form method="post" action="">
            <?php wp_nonce_field('wpsg_social_media_action', 'wpsg_social_media_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="facebook_url">Facebook</label></th>
                    <td><input type="url" name="facebook_url" id="facebook_url" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="twitter_url">Twitter</label></th>
                    <td><input type="url" name="twitter_url" id="twitter_url" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="instagram_url">Instagram</label></th>
                    <td><input type="url" name="instagram_url" id="instagram_url" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Save Social Media Links'); ?>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['wpsg_social_media_nonce']) && wp_verify_nonce($_POST['wpsg_social_media_nonce'], 'wpsg_social_media_action')) {
                update_option('wpsg_facebook_url', esc_url_raw($_POST['facebook_url']));
                update_option('wpsg_twitter_url', esc_url_raw($_POST['twitter_url']));
                update_option('wpsg_instagram_url', esc_url_raw($_POST['instagram_url']));
                echo '<div class="updated"><p>Social media links saved successfully!</p></div>';
            }
        }
        ?>
    </div>
    <?php
}
