<?php
// admin/modules/announcements/form.php
if (!defined('ABSPATH')) exit;

class WPSG_AnnouncementsForm {

    public function __construct(){
        add_action('admin_enqueue_scripts', 'wpsg_enqueue_announcements_scripts', 20);
    }

    // in form.php or admin enqueue area
    function wpsg_enqueue_announcements_scripts() {
        // enqueue WP media (required for wp.media)
        wp_enqueue_media();

        // enqueue our script
        wp_enqueue_script(
            'wpsg-announcements',
            WPSG_URL . 'admin/js/announcements.js',
            array('jquery'),
            WPSG_VERSION,
            true
        );

        // prepare existing data for JS (if editing)
        $existing = array();
        if (!empty($data)) {
            // sanitize $data as needed and only pass the fields relevant to JS
            $existing = array(
                'speakers'   => $data['speakers'] ?? array(),
                'organizers' => $data['organizers'] ?? array(),
                'image'      => $data['image'] ?? '',
            );
        }

        wp_localize_script('wpsg-announcements', 'WPSG_ANN_DATA', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id'  => intval($data['id'] ?? 0),
            'nonce'    => wp_create_nonce('wpsg_ann_nonce'),
            'existing' => $existing,
            'slug_check_action' => 'wpsg_check_slug'
        ));
    }

    public function render() {
        $action = $_GET['action'] ?? 'add';
        $id     = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $data = [
            'title'           => '',
            'slug'            => '',
            'tagline'         => '',
            'subtitle'        => '',
            'start_date'      => '',
            'end_date'        => '',
            'start_time'      => '',
            'end_time'        => '',
            'content'         => '',
            'image'           => '',
            'speakers'        => [],
            'location'        => [ 'address' => '', 'map_url' => '' ],
            'organizers'      => [],
            'publish_status'  => 'draft',
            'publish_date'    => '',
            'expiry_date'     => '',
            'contact'         => '',
            'pricing_label'   => '',
            'pricing_values'  => []
        ];

        ?><style><?php

        require WPSG_DIR . 'modules/announcements/form-left.css';

        ?></style><?php

        echo '<form class="wpsg-form" method="post" action="">';
        echo    '<div class="wpsg wpsg-boxed">';
        echo        '<div class="wrap">';
        echo            '<h1>' . ($action === 'edit' ? 'Edit Announcement' : 'Add New Announcement') . '</h1>';


        echo            '<div id="poststuff">';
        echo                '<div id="post-body" class="metabox-holder columns-2">';

        // LEFT COLUMN
        echo                    '<div id="post-body-content">';

        require WPSG_DIR . 'modules/announcements/form-left.php';

        echo                    '</div>'; // post-body-content

        // RIGHT COLUMN
        echo                    '<div id="postbox-container-1" class="postbox-container">';
        // require __DIR__ . '/views/form-right.php';
        echo                    '</div>'; // postbox-container-1

        echo                '</div>'; // post-body
        echo            '</div>';   // poststuff

        echo        '</div>'; // wrap
        echo    '</div>'; // wrapper
        echo '</form>';
    }
}

new WPSG_AnnouncementsForm();