<?php
if (!defined('ABSPATH')) exit;

// Tentukan action (list/add/edit)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Default Messages
$message = '';

// Load CSS/JS jika diperlukan
wp_enqueue_style('wpsg-admin-css', plugin_dir_url(__FILE__) . '../assets/admin.css');
wp_enqueue_script('wpsg-admin-js', plugin_dir_url(__FILE__) . '../assets/admin.js', ['jquery'], false, true);

// Fungsi untuk menampilkan daftar artikel
function wpsg_articles_list() {

    $args = [
        'post_type' => 'post',
        'posts_per_page' => 10,
        'orderby' => 'date',
        'order' => 'DESC'
    ];

    $posts = get_posts($args);
    ?>
    <h2>Articles <a href="?page=wpsg-articles&action=add" class="wpsg-button">Add New Article</a></h2>
    <table class="wpsg-table">
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>Title / Info</th>
                <th>Excerpt</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($posts as $post): setup_postdata($post); ?>
            <tr>
                <td><?php echo get_the_post_thumbnail($post->ID, [80,80]); ?></td>
                <td>
                    <strong><?php echo esc_html($post->post_title); ?></strong><br>
                    Author: <?php echo get_the_author_meta('display_name', $post->post_author); ?><br>
                    Created: <?php echo get_the_date('Y-m-d H:i:s', $post); ?><br>
                    Modified: <?php echo get_the_modified_date('Y-m-d H:i:s', $post); ?>
                </td>
                <td><?php echo wp_trim_words($post->post_content, 5, '...'); ?></td>
            </tr>
        <?php endforeach; wp_reset_postdata(); ?>
        </tbody>
    </table>
    <?php
}

// Fungsi untuk menampilkan form add/edit artikel
function wpsg_articles_form($post_id = 0) {
    $post = $post_id ? get_post($post_id) : null;
    $title = $post ? $post->post_title : '';
    $content = $post ? $post->post_content : '';
    ?>
    <h2><?php echo $post ? 'Edit Article' : 'Add New Article'; ?></h2>
    <form method="post" enctype="multipart/form-data">
        <label>Title</label><br>
        <input type="text" name="wpsg_title" value="<?php echo esc_attr($title); ?>" style="width:100%; font-size:18px; padding:5px;"><br><br>
        
        <label>Content</label><br>
        <?php
        wp_editor($content, 'wpsg_content', [
            'textarea_name' => 'wpsg_content',
            'textarea_rows' => 10
        ]);
        ?><br>
        
        <label>Featured Image</label><br>
        <input type="file" name="wpsg_featured_image"><br><br>
        
        <label>Category</label><br>
        <?php wp_dropdown_categories(['hide_empty' => 0, 'name' => 'wpsg_category']); ?><br><br>
        
        <label>Status</label><br>
        <select name="wpsg_status">
            <option value="publish">Publish</option>
            <option value="draft">Draft</option>
        </select><br><br>
        
        <input type="submit" value="Save Article" class="wpsg-button">
    </form>
    <?php
}

// Render sesuai action
if ($action == 'add' || $action == 'edit') {
    $post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    wpsg_articles_form($post_id);
} else {
    wpsg_articles_list();
}
