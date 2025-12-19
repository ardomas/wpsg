<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// wpsg/includes/ajax/class-wpsg-galleries-ajax.php

class WPSG_GalleriesAjax {

    protected $service;

    public function __construct() {
        $this->service = new WPSG_GalleriesService();

        add_action('wp_ajax_wpsg_delete_gallery_item', [$this, 'delete_item']);
        add_action('wp_ajax_wpsg_get_gallery_items', [$this, 'get_items']);

    }

    public function delete_item() {

        check_ajax_referer('wpsg_delete_gallery_item', 'nonce');

        $item_id = absint($_POST['item_id'] ?? 0);

        if ( ! $item_id ) {
            wp_send_json_error(['message' => 'Invalid item ID']);
        }

        if ( ! $this->service ) {
            wp_send_json_error(['message' => 'Service not available']);
        }

        $deleted = $this->service->delete_item($item_id);

        if ( $deleted ) {
            wp_send_json_success(['item_id' => $item_id]);
        }

        wp_send_json_error(['message' => 'Failed to delete item']);
    }

    public function get_items() {
        check_ajax_referer('wpsg_gallery_nonce', 'nonce');

        $album_id = intval($_POST['album_id']);

        $gallery_items = new WPSG_GalleryItems();
        $html = $gallery_items->render_reload( $album_id );

        wp_send_json_success([
            'html' => $html
        ]);
    }

}

?>