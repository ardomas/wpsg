<?php
// wpsg/modules/announcements/form.php
if (!defined('ABSPATH')) exit;

class WPSG_AnnouncementsForm {

    private $action;
    private $id = null;
    private $data = [];

    public function __construct() {
        $this->action = $_GET['action'] ?? 'add';

        if ($this->action === 'edit') {
            $this->id = intval($_GET['id'] ?? 0);
            $this->data = $this->load_data($this->id);
        }
    }

    /**
     * Load existing announcement data for editing
     * (Sam bisa sambungkan ke database nanti)
     */
    private function load_data($id) {
        // Untuk sementara return dummy
        // Kamu bisa ganti dengan real query via WPDB atau class WPSG_AnnouncementsData
        return [
            'title'   => 'Sample Announcement',
            'content' => 'This is sample content.',
            'status'  => 'publish',
        ];
    }

    /**
     * Render Form
     */
    public function render() {
        $title  = $this->data['title']   ?? '';
        $content = $this->data['content'] ?? '';
        $status  = $this->data['status']  ?? 'draft';

        echo '<div class="wrap">';
        echo '<h1>' . ($this->action === 'edit' ? 'Edit Announcement' : 'Add New Announcement') . '</h1>';

        echo '<form method="post" action="">';
        wp_nonce_field('wpsg_announcements_form', 'wpsg_ann_nonce');

        // Title
        echo '<input type="text" name="wpsg_title" value="' . esc_attr($title) . '" class="widefat" placeholder="Enter title here" style="font-size:1.3em;margin-bottom:20px;" />';

        // Content
        wp_editor(
            $content,
            'wpsg_content',
            [
                'textarea_name' => 'wpsg_content',
                'media_buttons' => true,
                'editor_height' => 250,
            ]
        );

        // Status
        echo '<div style="margin-top:20px;">';
        echo '<label>Status: </label>';
        echo '<select name="wpsg_status">';
        echo '<option value="publish"' . selected($status, 'publish', false) . '>Publish</option>';
        echo '<option value="draft"' . selected($status, 'draft', false) . '>Draft</option>';
        echo '</select>';
        echo '</div>';

        // Submit button
        echo '<p class="submit">';
        echo '<button type="submit" class="button button-primary">Save Announcement</button>';
        echo '</p>';

        echo '</form>';
        echo '</div>';
    }
}
