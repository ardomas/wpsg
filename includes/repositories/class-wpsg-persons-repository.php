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

    public function activate() {
        $this->data->activate();
    }

    /* ---------------------------------------------------------
     * BASIC WRAPPERS
     * (Forward calls to Data layer, but this is the extension point)
     * --------------------------------------------------------- */

    public function get( $id ) {
        return $this->data->get( $id );
    }

    public function get_by_ids($ids){
        return $this->data->get_by_ids($ids);
    }

    public function set_default_data() {
        return $this->data->set_default_data();
    }

    public function get_by_user_id( $user_id ) {
        return $this->data->get_by_user_id( $user_id );
    }

    public function get_by_email( $email ) {
        return $this->data->get_by_email( $email );
    }

    public function get_user_id($id){
        $values = $this->data->get( $id );
        return $values['user_id'] ?? null;
    }

    public function get_user($person_id) {
        $user_id = $this->get_user_id($person_id);
        return $user_id ? get_userdata($user_id) : null;
    }

    public function get_email($person_id){
        $person = $this->data->get($person_id);
        return $person['email'] ?? null;
    }

    public function set( $data ) {
        $data_person = [];
        $meta_person = [];
        $main_fields = $this->data->get_main_fields();
        foreach( $data as $key => $value ) {
            if ( in_array( $key, $main_fields ) ) {
                $data_person[ $key ] = $value;
            } else {
                $meta_person[ $key ] = $value;
            }
        }
        $person_id = $this->data->set( $data );

        if ( ! $person_id ) {

            print_r( $person_id );
            echo '<br/>';
            print_r( $data );
            die('<br/>test');

            throw new RuntimeException( 'Gagal menyimpan data person.' );
        } else {
            $data['id'] = $person_id; // Pastikan ID tersedia untuk meta
            // Simpan meta
            foreach ( $meta_person as $meta_key => $meta_value ) {
                $this->data->set_meta( $person_id, $meta_key, $meta_value );
            }
        }
        return $person_id;
    }

    public function delete( $id ) {
        return $this->data->soft_delete( $id );
    }

    public function list( $args = [] ) {
        return $this->data->get_all( $args );
    }

    /* ---------------------------------------------------------
     * META WRAPPERS
     * --------------------------------------------------------- */

    public function set_meta( $person_id, $key, $value ){
        return $this->data->set_meta( $person_id, $key, $value );
    }

    public function get_meta( $person_id, $key ) {
        return $this->data->get_meta( $person_id, $key );
    }

    public function delete_meta( $person_id, $key ) {
        return $this->data->delete_meta( $person_id, $key );
    }

    public function get_all_meta( $person_id ) {
        return $this->data->get_all_meta( $person_id );
    }

    /* ---------------------------------------------------------
     * BUSINESS LOGIC HELPERS (can grow later)
     * --------------------------------------------------------- */

    /**
     * Find person by email. If not found, create new person.
     * Useful for Membership module.
     * 
     * Catatan Penting:
     * Method ini unsafe untuk UI langsung
     */
    public function find_or_create_by_email( $email, $data = [] ) {

        $person = $this->get_by_email( $email );

        if ( $person ) {
            return $person['id'];
        }

        // Ensure email included in creation data
        $data['email'] = $email;

        return $this->set( $data );
    }

    /**
     * Check if a person exists (by ID)
     */
    public function exists( $person_id ) : bool {
        return (bool) $this->get( $person_id );
    }

    /**
     * Update only meta without touching main table.
     */

    // public function add_meta( $person_id, $key, $value ) {
    //     return $this->data->add_meta( $person_id, $key, $value );
    // }

    // public function update_meta( $person_id, $key, $value ) {
    //     return $this->data->update_meta( $person_id, $key, $value );
    // }

    // public function update_single_meta( $person_id, $key, $value ) {
    //     $current = $this->get_meta( $person_id, $key, true );

    //     if ( $current === null ) {
    //         return $this->add_meta( $person_id, $key, $value );
    //     }

    //     return $this->update_meta( $person_id, $key, $value );
    // }

    public function get_by_meta( $meta_key, $meta_value, $args = [] )
    {
        global $wpdb;

        $persons_table     = $wpdb->prefix . 'wpsg_persons';
        $personmeta_table  = $wpdb->prefix . 'wpsg_personmeta';

        $limit  = intval( $args['limit'] );
        $offset = intval( $args['offset'] );

        $sql = "
            SELECT p.*
            FROM {$persons_table} p
            INNER JOIN {$personmeta_table} pm
                ON pm.person_id = p.id
            WHERE pm.meta_key = %s
            AND pm.meta_value = %s
            ORDER BY {$args['orderby']} {$args['order']}
            LIMIT %d OFFSET %d
        ";

        $prepared = $wpdb->prepare(
            $sql,
            $meta_key,
            $meta_value,
            $limit,
            $offset
        );

        $rows = $wpdb->get_results( $prepared, ARRAY_A );

        return $this->map_rows_to_entities( $rows );
    }

    protected function map_rows_to_entities( array $rows )
    {
        $items = [];

        foreach ( $rows as $row ) {
            $items[] = new WPSG_PersonsData( $row );
        }

        return $items;
    }

}
