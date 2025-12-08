<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Repository layer for MembershipsData.
 * Acts as an abstraction between business logic and data access,
 * without containing business rules itself.
 */
class WPSG_MembershipsRepository {

    /** @var WPSG_MembershipsData */
    protected $data;

    public function __construct() {
        $this->data = new WPSG_MembershipsData();
    }

    public function create_tables() {
        return $this->data->create_tables();
    }

    /* ========================================================================
     * MAIN TABLE ACCESSORS
     * ===================================================================== */

    /**
     * Get all membership rows with simple argument normalization.
     * $args can include: site_id, person_id, status, limit, offset, orderby, order
     */
    public function get_all( $args = [] ) {

        // Normalisasi ringkas
        $allowed = [
            'site_id',
            'person_id',
            'status',
            'limit',
            'offset',
            'orderby',
            'order',
        ];

        $filtered = [];
        foreach ( $allowed as $key ) {
            if ( isset( $args[ $key ] ) ) {
                $filtered[ $key ] = $args[ $key ];
            }
        }

        return $this->data->get_all( $filtered );
    }

    /**
     * Get a single membership by ID.
     */
    public function get( $id ) {
        $id = intval( $id );
        if ( $id <= 0 ) return null;

        return $this->data->get( $id );
    }

    /**
     * Create or update membership.
     */
    public function set( $data ) {
        if ( ! is_array( $data ) ) return false;

        return $this->data->set( $data );
    }

    /**
     * Soft delete membership.
     */
    public function delete( $id ) {
        $id = intval( $id );
        if ( $id <= 0 ) return false;

        return $this->data->delete( $id );
    }


    /* ========================================================================
     * META TABLE ACCESSORS (wp_postmeta-like)
     * ===================================================================== */

    public function get_all_meta( $site_id ) {
        $site_id = intval( $site_id );
        if ( $site_id <= 0 ) return [];

        return $this->data->get_all_meta( $site_id );
    }

    public function get_meta( $site_id, $meta_key ) {
        $site_id  = intval( $site_id );
        $meta_key = (string) $meta_key;

        if ( $site_id <= 0 || $meta_key === '' ) return null;

        return $this->data->get_meta( $site_id, $meta_key );
    }

    public function set_meta( $site_id, $meta_key, $meta_value ) {
        $site_id  = intval( $site_id );
        $meta_key = (string) $meta_key;

        if ( $site_id <= 0 || $meta_key === '' ) return false;

        return $this->data->set_meta( $site_id, $meta_key, $meta_value );
    }

    /**
     * Upsert meta: if meta exists update, otherwise insert.
     * (Sebenarnya sama dengan set_meta, tapi naming semantic lebih jelas
     * untuk dipakai di layer service nanti.)
     */
    public function upsert_meta( $site_id, $meta_key, $meta_value ) {
        return $this->set_meta( $site_id, $meta_key, $meta_value );
    }

    public function delete_meta( $site_id, $meta_key ) {
        $site_id  = intval( $site_id );
        $meta_key = (string) $meta_key;

        if ( $site_id <= 0 || $meta_key === '' ) return false;

        return $this->data->delete_meta( $site_id, $meta_key );
    }

    public function delete_all_meta( $site_id ) {
        $site_id = intval( $site_id );
        if ( $site_id <= 0 ) return false;

        return $this->data->delete_all_meta( $site_id );
    }

}
