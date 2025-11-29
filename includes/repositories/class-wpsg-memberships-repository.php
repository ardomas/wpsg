<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Membership repository - DB operations for person<->site and user<->person relations
 */
class WPSG_MembershipsRepository {

    private $person_site_table;
    private $user_person_table;

    public function __construct() {
        global $wpdb;
        $this->person_site_table = $wpdb->prefix . 'wpsg_person_site';
        $this->user_person_table = $wpdb->prefix . 'wpsg_user_person';
    }

    /** create tables (call on plugin activate) */
    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $ps_table = $wpdb->prefix . 'wpsg_person_site';
        $up_table = $wpdb->prefix . 'wpsg_user_person';

        $sql1 = "CREATE TABLE $ps_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            person_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(50) DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY person_id_idx (person_id),
            KEY site_id_idx (site_id),
            UNIQUE KEY person_site_unique (person_id, site_id)
        ) ENGINE=InnoDB $charset;";

        $sql2 = "CREATE TABLE $up_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            person_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED DEFAULT NULL,
            role VARCHAR(50) DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id_idx (user_id),
            KEY person_id_idx (person_id),
            KEY site_id_idx (site_id),
            UNIQUE KEY user_person_unique (user_id, person_id, site_id)
        ) ENGINE=InnoDB $charset;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql1 );
        dbDelta( $sql2 );
    }

    /* ---------------------------
     * Person <-> Site ops
     * --------------------------- */

    public function assign_person_to_site( $person_id, $site_id, $role = null, $status = 'active' ) {
        global $wpdb;
        $now = current_time('mysql');

        // if exists update, else insert. Use replace style: try update first.
        $updated = $wpdb->update(
            $this->person_site_table,
            [ 'role' => $role, 'status' => $status, 'updated_at' => $now ],
            [ 'person_id' => $person_id, 'site_id' => $site_id ]
        );

        if ( $updated === false ) {
            return false;
        }

        if ( $updated === 0 ) {
            // no row updated => insert new
            $wpdb->insert( $this->person_site_table, [
                'person_id'  => $person_id,
                'site_id'    => $site_id,
                'role'       => $role,
                'status'     => $status,
                'created_at' => $now,
                'updated_at' => $now
            ] );
            return $wpdb->insert_id;
        }

        // updated >0 -> return true
        return true;
    }

    public function remove_person_from_site( $person_id, $site_id ) {
        global $wpdb;
        return $wpdb->delete( $this->person_site_table, [
            'person_id' => $person_id,
            'site_id'   => $site_id
        ] );
    }

    public function get_person_sites( $person_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$this->person_site_table} WHERE person_id = %d", $person_id ),
            ARRAY_A
        );
    }

    public function get_site_persons( $site_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$this->person_site_table} WHERE site_id = %d", $site_id ),
            ARRAY_A
        );
    }

    public function person_site_exists( $person_id, $site_id ) {
        global $wpdb;
        $c = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$this->person_site_table} WHERE person_id=%d AND site_id=%d", $person_id, $site_id )
        );
        return ($c > 0);
    }

    /* ---------------------------
     * User <-> Person ops
     * --------------------------- */

    public function link_user_to_person( $user_id, $person_id, $site_id = null, $role = null, $status = 'active' ) {
        global $wpdb;
        $now = current_time('mysql');

        $updated = $wpdb->update(
            $this->user_person_table,
            [ 'role' => $role, 'status' => $status, 'updated_at' => $now ],
            [ 'user_id' => $user_id, 'person_id' => $person_id, 'site_id' => $site_id ]
        );

        if ( $updated === false ) return false;

        if ( $updated === 0 ) {
            $wpdb->insert( $this->user_person_table, [
                'user_id'    => $user_id,
                'person_id'  => $person_id,
                'site_id'    => $site_id,
                'role'       => $role,
                'status'     => $status,
                'created_at' => $now,
                'updated_at' => $now
            ] );
            return $wpdb->insert_id;
        }

        return true;
    }

    public function unlink_user_from_person( $user_id, $person_id, $site_id = null ) {
        global $wpdb;
        return $wpdb->delete( $this->user_person_table, [
            'user_id'   => $user_id,
            'person_id' => $person_id,
            'site_id'   => $site_id
        ] );
    }

    public function get_person_users( $person_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$this->user_person_table} WHERE person_id = %d", $person_id ),
            ARRAY_A
        );
    }

    public function get_user_persons( $user_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$this->user_person_table} WHERE user_id = %d", $user_id ),
            ARRAY_A
        );
    }

    public function user_person_exists( $user_id, $person_id, $site_id = null ) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->user_person_table} WHERE user_id = %d AND person_id = %d";
        $params = [ $user_id, $person_id ];
        if ( $site_id !== null ) {
            $sql .= " AND site_id = %d";
            $params[] = $site_id;
        } else {
            $sql .= " AND site_id IS NULL";
        }
        $prepared = $wpdb->prepare( $sql, $params );
        $c = $wpdb->get_var( $prepared );
        return ($c > 0);
    }

    /**
     * Get all person<->site link records.
     *
     * $args optional keys:
     *  - 'status'    => string, filter by status
     *  - 'site_id'   => int,   filter by site_id
     *  - 'person_id' => int,   filter by person_id
     *  - 'orderby'   => string, one of allowed columns (default: id)
     *  - 'order'     => string, 'ASC' or 'DESC' (default: ASC)
     *  - 'limit'     => int,   0 = no limit
     *  - 'offset'    => int
     *
     * @param array $args
     * @return array list of associative arrays (ARRAY_A)
     */
    public function get_all_person_site_links( $args = [] ) {
        global $wpdb;

        $defaults = [
            'status'    => '',
            'site_id'   => null,
            'person_id' => null,
            'orderby'   => 'id',
            'order'     => 'ASC',
            'limit'     => 0,
            'offset'    => 0,
        ];

        $args = wp_parse_args( $args, $defaults );

        // Validate orderby & order to avoid SQL injection on identifiers
        $allowed_orderby = [ 'id', 'person_id', 'site_id', 'role', 'status', 'created_at', 'updated_at' ];
        if ( ! in_array( $args['orderby'], $allowed_orderby, true ) ) {
            $args['orderby'] = 'id';
        }

        $args['order'] = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        // Build WHERE clause and parameters
        $where = '1=1';
        $params = [];

        if ( $args['status'] !== '' ) {
            $where .= " AND `status` = %s";
            $params[] = $args['status'];
        }

        if ( $args['site_id'] !== null ) {
            $where .= " AND `site_id` = %d";
            $params[] = intval( $args['site_id'] );
        }

        if ( $args['person_id'] !== null ) {
            $where .= " AND `person_id` = %d";
            $params[] = intval( $args['person_id'] );
        }

        // Base query
        $query = "SELECT * FROM {$this->person_site_table} WHERE {$where} ORDER BY {$args['orderby']} {$args['order']}";

        // Add limit if requested
        if ( intval( $args['limit'] ) > 0 ) {
            $query .= " LIMIT %d OFFSET %d";
            $params[] = intval( $args['limit'] );
            $params[] = intval( $args['offset'] );
        }

        // Prepare query with dynamic params (if any)
        if ( ! empty( $params ) ) {
            // call_user_func_array to pass variable args to $wpdb->prepare
            $prepare_args = array_merge( [ $query ], $params );
            $prepared = call_user_func_array( [ $wpdb, 'prepare' ], $prepare_args );
        } else {
            $prepared = $query;
        }

        $results = $wpdb->get_results( $prepared, ARRAY_A );

        return $results ?: [];
    }

}
