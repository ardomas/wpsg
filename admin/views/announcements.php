<?php
if (!defined('ABSPATH')) exit;

function wpsg_announcement_data_page() {
    ?>
    <div class="wrap">
        <h1>Manage Announcements</h1>
        <form method="post" action="">
            <?php wp_nonce_field('wpsg_announcement_data_action', 'wpsg_announcement_data_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="announcement_title">Title</label></th>
                    <td><input type="text" name="announcement_title" id="announcement_title" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="announcement_content">Content</label></th>
                    <td><?php wp_editor('', 'announcement_content', ['textarea_name' => 'announcement_content']); ?></td>
                </tr>
            </table>
            <?php submit_button('Save Announcement'); ?>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['wpsg_announcement_data_nonce']) && wp_verify_nonce($_POST['wpsg_announcement_data_nonce'], 'wpsg_announcement_data_action')) {
                $post_data = [
                    'post_title'    => sanitize_text_field($_POST['announcement_title']),
                    'post_content'  => wp_kses_post($_POST['announcement_content']),
                    'post_status'   => 'publish',
                    'post_type'     => 'post', // bisa ganti custom post type announcement
                ];
                $post_id = wp_insert_post($post_data);
                if ($post_id) {
                    echo '<div class="updated"><p>Announcement saved successfully!</p></div>';
                } else {
                    echo '<div class="error"><p>Failed to save announcement.</p></div>';
                }
            }
        }
        ?>
    </div>
    <?php
}
