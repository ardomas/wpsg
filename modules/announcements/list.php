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

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Announcements</h1>';
        echo '<a href="' . admin_url('admin.php?page=' . $page . '&view=' . $view . '&action=add') . '" class="page-title-action">Add New</a>';
        echo '<hr class="wp-header-end">';

        echo '<table class="wp-list-table widefat fixed striped posts">';
        echo '<thead>
                <tr>
                    <th class="manage-column">Title</th>
                    <th class="manage-column">Author</th>
                    <th class="manage-column">Status</th>
                    <th class="manage-column">Date</th>
                </tr>
              </thead>';
        echo '<tbody>';

        if (!empty($announcements)) {
            foreach ($announcements as $ann) {
                $edit_link = admin_url('admin.php?page=wpsg_announcements&action=edit&id=' . $ann->id);
                $delete_link = wp_nonce_url(admin_url('admin.php?page=wpsg_announcements&action=delete&id=' . $ann->id), 'wpsg_ann_delete_' . $ann->id);
                
                echo '<tr>';
                echo '<td><strong><a href="'. $edit_link .'">'. esc_html($ann->title) .'</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="'. $edit_link .'">Edit</a> | </span>
                            <span class="trash"><a href="'. $delete_link .'">Trash</a></span>
                        </div>
                      </td>';
                echo '<td>'. get_userdata($ann->author_id)->display_name .'</td>';
                echo '<td>'. ucfirst($ann->status) .'</td>';
                echo '<td>'. date('Y-m-d H:i', strtotime($ann->publish_date)) .'</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4">No announcements found.</td></tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}

// Instantiate
// new WPSG_AnnouncementsList();
