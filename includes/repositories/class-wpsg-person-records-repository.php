<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_PersonRecordsRepository {

    /**
     * @var WPSG_PersonRecordsData
     */
    protected $data;

    public function __construct() {
        $this->data = WPSG_PersonRecordsData::get_instance();
    }

    /**
     * Called on plugin activation
     */
    public function activate() {
        $this->data->activate();
    }

    /* ---------------------------------------------------------
     * BASIC RECORD OPERATIONS
     * --------------------------------------------------------- */

    public function get( $record_id ) {
        return $this->data->get( $record_id );
    }

    public function create( array $record_data ) {
        return $this->data->insert( $record_data );
    }

    public function update( $record_id, array $record_data ) {
        return $this->data->update( $record_id, $record_data );
    }

    public function delete( $record_id ) {
        return $this->data->delete( $record_id );
    }

    /* ---------------------------------------------------------
     * PERSON-BASED QUERIES
     * --------------------------------------------------------- */

    public function get_by_person( $person_id, array $args = [] ) {
        return $this->data->get_by_person( $person_id, $args );
    }

    public function get_by_person_and_type( $person_id, $record_type, $record_subtype = null ) {

        $args = [
            'record_type' => $record_type,
        ];

        if ( $record_subtype ) {
            $args['record_subtype'] = $record_subtype;
        }

        return $this->data->get_by_person( $person_id, $args );
    }

    /* ---------------------------------------------------------
     * RECORD + META (COMPOSED)
     * --------------------------------------------------------- */

    /**
     * Create record and attach meta in one flow
     *
     * @param array $record_data
     * @param array $meta_data
     * @return int|false record_id
     */
    public function create_with_meta( array $record_data, array $meta_data = [] ) {

        $record_id = $this->data->insert( $record_data );

        if ( ! $record_id ) {
            return false;
        }

        foreach ( $meta_data as $key => $value ) {
            $this->data->set_meta( $record_id, $key, $value );
        }

        return $record_id;
    }

    /**
     * Replace all meta for a record
     * (useful for calculated or snapshot data)
     */
    public function replace_meta( $record_id, array $meta_data ) {

        foreach ( $meta_data as $key => $value ) {
            $this->data->set_meta( $record_id, $key, $value );
        }

        return true;
    }

    /* ---------------------------------------------------------
     * META SHORTCUTS
     * --------------------------------------------------------- */

    public function get_meta( $record_id, $key = '', $single = true ) {
        return $this->data->get_meta( $record_id, $key, $single );
    }

    public function set_meta( $record_id, $key, $value ) {
        return $this->data->set_meta( $record_id, $key, $value );
    }

    public function delete_meta( $record_id, $key ) {
        return $this->data->delete_meta( $record_id, $key );
    }
}
