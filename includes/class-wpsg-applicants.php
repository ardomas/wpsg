<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_ApplicantsData {

    private static $table;
    private static $instance = null;

    private function __construct() {
        global $wpdb;
        self::$table = $wpdb->prefix . 'wpsg_applicants';
    }

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Called on plugin activation
     */
    public static function activate() {
        self::create_table();
    }

    /**
     * Create or update database table
     */
    public static function create_table() {
        global $wpdb;

        $table = $wpdb->prefix . 'wpsg_applicants';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(191) NOT NULL,
            full_name VARCHAR(191) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            notes TEXT NULL,
            status ENUM('pending','approved','rejected','suspended') DEFAULT 'pending',
            site_id BIGINT UNSIGNED DEFAULT NULL,
            referral_code VARCHAR(50) DEFAULT NULL,
            referred_by BIGINT UNSIGNED DEFAULT NULL,
            application_date DATETIME NOT NULL,
            approved_date DATETIME DEFAULT NULL,
            rejected_date DATETIME DEFAULT NULL,
            suspended_date DATETIME DEFAULT NULL,
            metadata LONGTEXT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email_unique (email)
        ) ENGINE=InnoDB $charset;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /* -----------------------------------------
     *  GET ONE
     * ----------------------------------------- */
    public function get( $id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . self::$table . " WHERE id = %d", $id),
            ARRAY_A
        );
    }

    /* -----------------------------------------
     *  GET BY EMAIL
     * ----------------------------------------- */
    public function get_by_email( $email ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . self::$table . " WHERE email = %s", $email),
            ARRAY_A
        );
    }

    /* -----------------------------------------
     *  UPDATE
     * ----------------------------------------- */
    public function update( $id, $data ) {
        global $wpdb;
        return $wpdb->update(
            self::$table,
            $data,
            [ 'id' => $id ]
        );
    }

    /* -----------------------------------------
     *  DELETE
     * ----------------------------------------- */
    public function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( self::$table, [ 'id' => $id ] );
    }

    /* -----------------------------------------
     *  LIST / QUERY
     * ----------------------------------------- */
    public function list( $args = [] ) {
        global $wpdb;

        $defaults = [
            'status' => '',
            'orderby' => 'application_date',
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0,
        ];

        $args = wp_parse_args( $args, $defaults );

        $query = "SELECT * FROM " . self::$table . " WHERE 1=1";

        if ( ! empty( $args['status'] ) ) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }

        $query .= " ORDER BY {$args['orderby']} {$args['order']}";

        if ( $args['limit'] > 0 ) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        return $wpdb->get_results( $query, ARRAY_A );
    }
}
