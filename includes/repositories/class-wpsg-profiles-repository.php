<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Repository layer for Profiles (Company).
 * Acts as an abstraction between business logic and data access.
 */

class WPSG_ProfilesRepository {

    /** @var WPSG_ProfilesData */
    private $data;

    /**
     * Constructor
     */
    private function __construct() {
        // $this->data = WPSG_ProfilesData::get_instance();
    }

    /**
     * Get singleton instance
     *
     * @return WPSG_PersonsData
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function create_tables() {
        $this->data->create_tables();
    }

    /* ---------------------------------------------------------
     * BASIC WRAPPERS
     * (Forward calls to Data layer, but this is the extension point)
     * --------------------------------------------------------- */

    public static function get( $key, $default=null, $site_id=null ) {
        return $this->data->get_data( $key, $default, $site_id );
    }

    public function set( $key, $values, $site_id=null ) {
        return $this->data->set( $key, $values, $site_id=null );
    }

   
}