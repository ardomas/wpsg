<?php
if (!defined('ABSPATH')) exit;

function wpsg_membership_page() {
    ?>
    <div class="wrap">
        <h1>Manage Membership</h1>
        <form method="post" action="">
            <?php wp_nonce_field('wpsg_membership_action', 'wpsg_membership_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="membership_name">Membership Name</label></th>
                    <td><input type="text" name="membership_name" id="membership_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="membership_description">Description</label></th>
                    <td><textarea name="membership_description" id="membership_description" rows="5" class="large-text"></textarea></td>
                </tr>
                <tr>
                    <th><label for="membership_price">Price</label></th>
                    <td><input type="number" name="membership_price" id="membership_price" step="0.01"></td>
                </tr>
            </table>
            <?php submit_button('Save Membership'); ?>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['wpsg_membership_nonce']) && wp_verify_nonce($_POST['wpsg_membership_nonce'], 'wpsg_membership_action')) {
                update_option('wpsg_membership_name', sanitize_text_field($_POST['membership_name']));
                update_option('wpsg_membership_description', sanitize_textarea_field($_POST['membership_description']));
                update_option('wpsg_membership_price', floatval($_POST['membership_price']));
                echo '<div class="updated"><p>Membership saved successfully!</p></div>';
            }
        }
        ?>
    </div>
    <?php
}
