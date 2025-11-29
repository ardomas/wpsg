<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Repository layer for Persons.
 * Acts as an abstraction between business logic and data access.
 */
class WPSG_PersonsRepository {

    /** @var WPSG_PersonsData */
    private $data;

    /**
     * Constructor
     */
    public function __construct() {
        $this->data = WPSG_PersonsData::get_instance();
    }

    /* ---------------------------------------------------------
     * BASIC WRAPPERS
     * (Forward calls to Data layer, but this is the extension point)
     * --------------------------------------------------------- */

    public function create_person( $data ) {
        return $this->data->create( $data );
    }

    public function get_person( $id ) {
        return $this->data->get( $id );
    }

    public function get_person_by_email( $email ) {
        return $this->data->get_by_email( $email );
    }

    public function update_person( $id, $data ) {
        return $this->data->update( $id, $data );
    }

    public function delete_person( $id ) {
        return $this->data->delete( $id );
    }

    public function list_persons( $args = [] ) {
        return $this->data->list( $args );
    }

    /* ---------------------------------------------------------
     * META WRAPPERS
     * --------------------------------------------------------- */

    public function add_meta( $person_id, $key, $value ) {
        return $this->data->add_meta( $person_id, $key, $value );
    }

    public function get_meta( $person_id, $key, $single = true ) {
        return $this->data->get_meta( $person_id, $key, $single );
    }

    public function update_meta( $person_id, $key, $value ) {
        return $this->data->update_meta( $person_id, $key, $value );
    }

    public function delete_meta( $person_id, $key ) {
        return $this->data->delete_meta( $person_id, $key );
    }

    /* ---------------------------------------------------------
     * BUSINESS LOGIC HELPERS (can grow later)
     * --------------------------------------------------------- */

    /**
     * Find person by email. If not found, create new person.
     * Useful for Membership module.
     */
    public function find_or_create_by_email( $email, $data = [] ) {

        $person = $this->get_person_by_email( $email );

        if ( $person ) {
            return $person['id'];
        }

        // Ensure email included in creation data
        $data['email'] = $email;

        return $this->create_person( $data );
    }

    /**
     * Check if a person exists (by ID)
     */
    public function exists( $person_id ) : bool {
        return (bool) $this->get_person( $person_id );
    }

    /**
     * Update only meta without touching main table.
     */
    public function update_single_meta( $person_id, $key, $value ) {
        $current = $this->get_meta( $person_id, $key, true );

        if ( $current === null ) {
            return $this->add_meta( $person_id, $key, $value );
        }

        return $this->update_meta( $person_id, $key, $value );
    }

}
