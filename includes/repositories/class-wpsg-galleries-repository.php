<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPSG_GalleriesRepository {

    public function __construct() {
        WPSG_GalleriesData::init();
    }

    /* ---------------------------------------------
     * ALBUM (MAIN)
     * --------------------------------------------- */

    /**
     * Set album: insert jika $id null, update jika $id ada
     */
    public function set($data) {
        $id = null;
        if( isset( $data['id'] ) ){
            if( !empty( $data['id'] ) && !is_null( $data['id'] ) && $data['id']!='0' ){
                $id = intval( $data['id'] );
            }
        }
        if ($id) {
            return WPSG_GalleriesData::update_main($data, $id);
        } else {
            unset( $data['id'] );
            return WPSG_GalleriesData::insert_main($data);
        }
    }

    /**
     * Delete album
     */
    public function delete($id) {
        return WPSG_GalleriesData::delete_main($id);
    }

    /**
     * Get album by ID
     */
    public function get($id) {
        return WPSG_GalleriesData::get_by_id($id);
    }

    /**
     * Get list of albums dengan filter optional
     * args bisa: id, site_id, title, thumbnail_id, created_at, updated_at, limit, offset
     */
    public function get_list($args = []) {
        return WPSG_GalleriesData::get_data($args);
    }

    /* ---------------------------------------------
     * ITEMS
     * --------------------------------------------- */

    /**
     * Set item: insert jika $id null, update jika $id ada
     */
    public function set_item($data) {
        $id = null;
        if( isset( $data['id'] ) ){
            if( !empty( $data['id'] ) && !is_null( $data['id'] ) && $data['id']!='0' ){
                $id = intval( $data['id'] );
            }
        }
        if ($id) {
            return WPSG_GalleriesData::update_item($data, $id);
        } else {
            unset( $data['id'] );
            return WPSG_GalleriesData::insert_item($data);
        }
    }

    /**
     * Delete item
     */
    public function delete_item($id) {
        return WPSG_GalleriesData::delete_item($id);
    }

    /**
     * Get item by ID
     */
    public function get_item_by_id($id) {
        return WPSG_GalleriesData::get_item_by_id($id);
    }

    /**
     * Get items by album
     */
    public function get_items_by_album($album_id) {
        return WPSG_GalleriesData::get_items_by_album($album_id);
    }

}
