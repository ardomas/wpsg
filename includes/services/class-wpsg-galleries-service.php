<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPSG_GalleriesService {

    private $repo;

    public function __construct() {
        $this->repo = new WPSG_GalleriesRepository();
    }

    /* ---------------------------------------------
     * ALBUM
     * --------------------------------------------- */
    
    public function set_album($data) {
        // Bisa tambah validasi atau manipulasi data
        if( !isset( $data['site_id'] ) ){
            $data['site_id'] = wpsg_get_network_id();
        }
        if (empty($data['title'])) {
            return new WP_Error('empty_title', 'Title album tidak boleh kosong.');
        }

        // Panggil repository
        return $this->repo->set($data);
    }

    public function delete_album($id) {
        // Bisa tambah validasi sebelum hapus
        return $this->repo->delete($id);
    }

    public function get_album($id) {
        return $this->repo->get($id);
    }

    public function get_album_list($args = []) {
        return $this->repo->get_list($args);
    }

    /* ---------------------------------------------
     * ITEMS
     * --------------------------------------------- */

    public function set_item($data) {
        // Bisa tambah validasi post_id misal
        if (empty($data['post_id'])) {
            return new WP_Error('empty_post', 'Post ID item tidak boleh kosong.');
        }

        return $this->repo->set_item($data);
    }

    public function delete_item($id) {
        return $this->repo->delete_item($id);
    }

    public function get_item($id) {
        return $this->repo->get_item_by_id($id);
    }

    public function get_items_by_album($album_id) {
        return $this->repo->get_items_by_album($album_id);
    }
}
