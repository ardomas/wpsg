<?php
// File: wpsg/modules/announcements/list.php

if (!defined('ABSPATH')) exit;

class WPSG_AnnouncementsList {

    protected $posts_handler;

    public function __construct() {
        require_once WPSG_DIR . '/includes/class-wpsg-posts.php';
        $this->posts_handler = new WPSG_Posts();

        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu() {
        add_menu_page(
            'Announcements',
            'Announcements',
            'manage_options',
            'wpsg_announcements',
            [$this, 'render'],
            'dashicons-megaphone',
            6
        );
    }

    public function render() {
        $page = 'wpsg-admin';
        $view = 'announcements';
        $announcements = $this->posts_handler->get_posts([
            'post_type' => 'announcement'
        ]);

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Announcements</h1>
            <a href="<?php echo admin_url('admin.php?page=' . $page . '&view=' . $view . '&action=add'); ?>" class="page-title-action">Add New</a>
            <a href="<?php echo admin_url('admin.php?page=' . $page . '&view=' . $view); ?>" class="page-title-action">Back to List (All Announcements)</a>
            <hr class="wp-header-end">
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th class="manage-column" colspan="2">Title</th>
                        <th class="manage-column">Status</th>
                        <th class="manage-column">Date</th>
                        <th class="manage-column">Author</th>
                    </tr>
                </thead>
                <tbody>
        <?php

                if (!empty($announcements)) {
                    foreach ($announcements as $ann) {
                        $edit_link = admin_url('admin.php?page=wpsg_announcements&action=edit&id=' . $ann->id);
                        $delete_link = wp_nonce_url(admin_url('admin.php?page=wpsg_announcements&action=delete&id=' . $ann->id), 'wpsg_ann_delete_' . $ann->id);

                        ?><tr>
                            <td>img</td>
                            <td>
                                <strong></strong><a href="<?php echo esc_url($edit_link); ?>"><?php echo esc_html($ann->title); ?></a></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="<?php echo esc_url($edit_link); ?>">Edit</a> | </span>
                                    <span class="trash"><a href="<?php echo esc_url($delete_link); ?>">Trash</a></span>
                                </div>
                            </td>
                            <td><?php echo esc_html(ucfirst($ann->status)); ?></td>
                            <td><?php echo esc_html(date('Y-m-d H:i', strtotime($ann->publish_date))); ?></td>
                            <td><?php echo esc_html(get_userdata($ann->author_id)->display_name); ?></td>
                        </tr><?php

                    }
                } else {
                    echo '<tr><td colspan="5">No announcements found.</td></tr>';
                }
        ?>

                </tbody>
            </table>
        </div>
        <?php

    }
}

// Instantiate
// new WPSG_AnnouncementsList();
