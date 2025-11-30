<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_MembershipsRepository
 *
 * Responsible for DB operations related to:
 * - wp_wpsg_site_user    (site <-> wp_user)
 * - wp_wpsg_user_person  (wp_user <-> wpsg_person)
 *
 * Also contains table-creation helpers (to be called on plugin activation).
 */
class WPSG_MembershipsRepository {

    /**
     * @var string
     */
    private $table_site_user;

    /**
     * @var string
     */
    private $table_user_person;

    public function __construct() {
        global $wpdb;
        $this->table_site_user   = $wpdb->base_prefix . 'wpsg_site_user';
        $this->table_user_person = $wpdb->base_prefix . 'wpsg_user_person';
    }

    /**
     * Create necessary tables (call on plugin activation)
     */
    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $table_site_user = $wpdb->base_prefix . 'wpsg_site_user';
        $table_user_person = $wpdb->base_prefix . 'wpsg_user_person';

        $sql1 = "CREATE TABLE {$table_site_user} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(100) DEFAULT NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY site_id_idx (site_id),
            KEY user_id_idx (user_id),
            UNIQUE KEY site_user_unique (site_id, user_id)
        ) ENGINE=InnoDB {$charset};";

        $sql2 = "CREATE TABLE {$table_user_person} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            person_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED DEFAULT NULL,
            role VARCHAR(100) DEFAULT NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id_idx (user_id),
            KEY person_id_idx (person_id),
            KEY site_id_idx (site_id),
            UNIQUE KEY user_person_unique (user_id, person_id, site_id)
        ) ENGINE=InnoDB {$charset};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql1 );
        dbDelta( $sql2 );
    }

    /* ---------------------------
     * Site <-> User operations
     * --------------------------- */

    /**
     * Assign a WP user to a site (idempotent)
     *
     * @param int $site_id
     * @param int $user_id
     * @param string|null $role
     * @param string $status
     * @return int|bool Insert id on new row, true on updated, false on error
     */
    public function link_site_user( $site_id, $user_id, $role = null, $status = 'active' ) {
        global $wpdb;

        $now = current_time( 'mysql' );

        // try update first
        $updated = $wpdb->update(
            $this->table_site_user,
            [ 'role' => $role, 'status' => $status, 'updated_at' => $now ],
            [ 'site_id' => $site_id, 'user_id' => $user_id ],
            [ '%s', '%s', '%s' ],
            [ '%d', '%d' ]
        );

        if ( $updated === false ) {
            return false;
        }

        if ( $updated === 0 ) {
            $inserted = $wpdb->insert(
                $this->table_site_user,
                [
                    'site_id'    => intval( $site_id ),
                    'user_id'    => intval( $user_id ),
                    'role'       => $role,
                    'status'     => $status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [ '%d', '%d', '%s', '%s', '%s', '%s' ]
            );

            if ( $inserted === false ) return false;
            return $wpdb->insert_id;
        }

        return true;
    }

    /**
     * Unlink (remove) mapping site -> user
     *
     * @param int $site_id
     * @param int $user_id
     * @return int|false number of rows deleted or false
     */
    public function unlink_site_user( $site_id, $user_id ) {
        global $wpdb;
        return $wpdb->delete( $this->table_site_user, [ 'site_id' => $site_id, 'user_id' => $user_id ], [ '%d', '%d' ] );
    }

    /**
     * Get users mapped to a site
     *
     * @param int $site_id
     * @return array
     */
    public function get_site_users( $site_id ) {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT * FROM {$this->table_site_user} WHERE site_id = %d", $site_id );
        return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
    }

    /**
     * Count users (members) by site
     *
     * @param int $site_id
     * @return int
     */
    public function count_users_by_site( $site_id ) {
        global $wpdb;
        $c = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table_site_user} WHERE site_id = %d AND status = %s", $site_id, 'active' ) );
        return intval( $c );
    }

    /**
     * Check if a mapping exists
     *
     * @param int $site_id
     * @param int $user_id
     * @return bool
     */
    public function site_user_exists( $site_id, $user_id ) {
        global $wpdb;
        $c = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table_site_user} WHERE site_id=%d AND user_id=%d", $site_id, $user_id ) );
        return intval( $c ) > 0;
    }

    /* ---------------------------
     * User <-> Person operations
     * --------------------------- */

    /**
     * Link WP user to person (idempotent). site_id optional.
     *
     * @param int $user_id
     * @param int $person_id
     * @param int|null $site_id
     * @param string|null $role
     * @param string $status
     * @return int|bool insert id or true on update or false
     */
    public function link_user_person( $user_id, $person_id, $site_id = null, $role = null, $status = 'active' ) {
        global $wpdb;
        $now = current_time( 'mysql' );

        $updated = $wpdb->update(
            $this->table_user_person,
            [ 'role' => $role, 'status' => $status, 'updated_at' => $now ],
            [ 'user_id' => $user_id, 'person_id' => $person_id, 'site_id' => $site_id ],
            [ '%s', '%s', '%s' ],
            [ '%d', '%d', '%d' ]
        );

        if ( $updated === false ) return false;

        if ( $updated === 0 ) {
            $wpdb->insert(
                $this->table_user_person,
                [
                    'user_id'    => intval( $user_id ),
                    'person_id'  => intval( $person_id ),
                    'site_id'    => $site_id !== null ? intval($site_id) : null,
                    'role'       => $role,
                    'status'     => $status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [ '%d', '%d', '%d', '%s', '%s', '%s', '%s' ]
            );
            return $wpdb->insert_id;
        }

        return true;
    }

    /**
     * Unlink user-person mapping
     *
     * @param int $user_id
     * @param int $person_id
     * @param int|null $site_id
     * @return int|false
     */
    public function unlink_user_person( $user_id, $person_id, $site_id = null ) {
        global $wpdb;
        $where = [ 'user_id' => $user_id, 'person_id' => $person_id ];
        $where_formats = [ '%d', '%d' ];
        if ( $site_id !== null ) {
            $where['site_id'] = $site_id;
            $where_formats[] = '%d';
        }
        return $wpdb->delete( $this->table_user_person, $where, $where_formats );
    }

    /**
     * Get persons linked to a WP user
     *
     * @param int $user_id
     * @param bool $only_active
     * @return array
     */
    public function get_user_persons( $user_id, $only_active = true ) {
        global $wpdb;
        $sql = "SELECT * FROM {$this->table_user_person} WHERE user_id = %d";
        $params = [ $user_id ];
        if ( $only_active ) {
            $sql .= " AND status = %s";
            $params[] = 'active';
        }
        $prepared = $wpdb->prepare( $sql, $params );
        return $wpdb->get_results( $prepared, ARRAY_A ) ?: [];
    }

    /**
     * Get sites that a person is associated with (via user_person mapping)
     *
     * @param int $person_id
     * @return array
     */
    public function get_sites_of_person( $person_id ) {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT DISTINCT site_id FROM {$this->table_user_person} WHERE person_id = %d", $person_id );
        return $wpdb->get_col( $sql );
    }

}
