<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_MembershipsService
 *
 * Business layer for membership operations.
 * Modules/UI should interact with this class only.
 */
class WPSG_MembershipsService {

    /** @var WPSG_MembershipsRepository */
    private $membership_repo;

    /** @var WPSG_PersonsRepository */
    private $persons_repo;

    public function __construct() {
        $this->membership_repo = new WPSG_MembershipsRepository();
        // Persons repo is optional; used when we need to show person details or create person
        if ( class_exists( 'WPSG_PersonsRepository' ) ) {
            $this->persons_repo = new WPSG_PersonsRepository();
        } else {
            $this->persons_repo = null;
        }
    }

    /* ---------------------------------------------
     * Listing / Index
     * --------------------------------------------- */

    /**
     * Return flattened list of "memberships" where membership is represented by wp_site entry.
     *
     * Each item:
     *  - site_id
     *  - domain
     *  - path
     *  - members_count (active)
     *
     * @param array $args (reserved for future: limit/offset/order)
     * @return array
     */
    public function list_memberships( $args = [] ) {
        global $wpdb;

        $defaults = [
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'site_id',
            'order' => 'ASC',
        ];
        $args = wp_parse_args( $args, $defaults );

        // Note: wp_site table has columns: id, domain, path (depending on WP version)
        $site_table = $wpdb->base_prefix . 'site'; // use base_prefix for multisite site table

        $order = ( strtoupper($args['order']) === 'DESC' ) ? 'DESC' : 'ASC';
        $orderby = in_array( $args['orderby'], ['site_id','domain','path'], true ) ? $args['orderby'] : 'id';

        // Build base query
        $query = "SELECT s.id AS site_id, s.domain, s.path
                  FROM {$site_table} s
                  ORDER BY {$orderby} {$order}";

        if ( intval( $args['limit'] ) > 0 ) {
            $query .= $wpdb->prepare( " LIMIT %d OFFSET %d", intval($args['limit']), intval($args['offset']) );
        }

        $sites = $wpdb->get_results( $query, ARRAY_A );

        if ( ! $sites ) {
            return [];
        }

        // attach members_count
        foreach ( $sites as &$row ) {
            $row['members_count'] = $this->membership_repo->count_users_by_site( $row['site_id'] );
        }

        return $sites;
    }

    /* ---------------------------------------------
     * Detail / Site users
     * --------------------------------------------- */

    /**
     * Get users assigned to a particular site (with optional person mapping info)
     *
     * @param int $site_id
     * @param array $opts  ['with_person' => true]
     * @return array|WP_Error
     */
    public function get_site_users( $site_id, $opts = [] ) {
        $site_id = intval( $site_id );
        if ( $site_id <= 0 ) {
            return new WP_Error( 'invalid_site', 'Invalid site id' );
        }

        $users = $this->membership_repo->get_site_users( $site_id );

        if ( empty( $users ) ) {
            return [];
        }

        $with_person = isset($opts['with_person']) && $opts['with_person'];

        // optionally attach person info (via user_person mapping)
        if ( $with_person && $this->persons_repo ) {
            foreach ( $users as &$u ) {
                $persons = $this->membership_repo->get_user_persons( $u['user_id'], true );
                $u['persons'] = [];
                foreach ( $persons as $p ) {
                    // fetch base person record if persons_repo available
                    if ( $this->persons_repo ) {
                        $person = $this->persons_repo->get( $p['person_id'] );
                        if ( $person ) {
                            $u['persons'][] = $person;
                        }
                    } else {
                        $u['persons'][] = $p;
                    }
                }
            }
        }

        return $users;
    }

    /* ---------------------------------------------
     * Assign / Remove
     * --------------------------------------------- */

    /**
     * Assign a WP user to a site (and optionally link to a person)
     *
     * @param int $site_id
     * @param int $user_id
     * @param array $opts ['role'=>..., 'status'=>..., 'person_id' => int (optional), 'person_link_role' => ...]
     * @return array|WP_Error  ['site_user_id'=>..., 'user_person_id'=>...]
     */
    public function assign_user_to_site( $site_id, $user_id, $opts = [] ) {
        $site_id = intval( $site_id );
        $user_id = intval( $user_id );

        if ( $site_id <= 0 ) return new WP_Error( 'invalid_site', 'Invalid site id' );
        if ( $user_id <= 0 ) return new WP_Error( 'invalid_user', 'Invalid user id' );

        $role = isset($opts['role']) ? sanitize_text_field( $opts['role'] ) : null;
        $status = isset($opts['status']) ? sanitize_text_field( $opts['status'] ) : 'active';

        // link site <-> user (idempotent)
        $su_result = $this->membership_repo->link_site_user( $site_id, $user_id, $role, $status );
        if ( $su_result === false ) {
            return new WP_Error( 'db_error', 'Failed to link user to site' );
        }

        $out = [
            'site_user' => $su_result,
        ];

        // optionally link user -> person
        if ( isset($opts['person_id']) && intval($opts['person_id']) > 0 ) {
            $person_id = intval( $opts['person_id'] );
            $person_link_role = isset($opts['person_link_role']) ? sanitize_text_field($opts['person_link_role']) : null;

            $up_result = $this->membership_repo->link_user_person( $user_id, $person_id, $site_id, $person_link_role, 'active' );
            if ( $up_result === false ) {
                // rollback site-user link? for now, we return error and leave site-user mapping as-is.
                return new WP_Error( 'db_error', 'Failed to link user to person' );
            }
            $out['user_person'] = $up_result;
        }

        return $out;
    }

    /**
     * Remove/unassign a user from a site (and optionally unlink user->person)
     *
     * @param int $site_id
     * @param int $user_id
     * @param array $opts ['unlink_person' => bool, 'person_id' => int|null]
     * @return bool|WP_Error
     */
    public function remove_user_from_site( $site_id, $user_id, $opts = [] ) {
        $site_id = intval( $site_id );
        $user_id = intval( $user_id );

        if ( $site_id <= 0 ) return new WP_Error( 'invalid_site', 'Invalid site id' );
        if ( $user_id <= 0 ) return new WP_Error( 'invalid_user', 'Invalid user id' );

        $deleted = $this->membership_repo->unlink_site_user( $site_id, $user_id );
        if ( $deleted === false ) {
            return new WP_Error( 'db_error', 'Failed to unlink user from site' );
        }

        if ( isset( $opts['unlink_person'] ) && $opts['unlink_person'] ) {
            $person_id = isset($opts['person_id']) ? intval($opts['person_id']) : null;
            // if person_id provided, we unlink specific mapping; otherwise attempt to unlink all person links for user+site
            if ( $person_id ) {
                $this->membership_repo->unlink_user_person( $user_id, $person_id, $site_id );
            } else {
                // unlink all user-person mappings for that site
                $persons = $this->membership_repo->get_user_persons( $user_id, true );
                foreach ( $persons as $p ) {
                    if ( isset($p['site_id']) && intval($p['site_id']) === intval($site_id) ) {
                        $this->membership_repo->unlink_user_person( $user_id, $p['person_id'], $site_id );
                    }
                }
            }
        }

        return true;
    }

    /* ---------------------------------------------
     * Helpers
     * --------------------------------------------- */

    /**
     * Get membership summary for a single site (site info + counts + owner candidate)
     *
     * @param int $site_id
     * @return array|WP_Error
     */
    public function get_membership_summary( $site_id ) {
        global $wpdb;
        $site_id = intval( $site_id );
        if ( $site_id <= 0 ) return new WP_Error( 'invalid_site', 'Invalid site id' );

        $site_table = $wpdb->base_prefix . 'site';
        $site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$site_table} WHERE id = %d", $site_id ), ARRAY_A );
        if ( ! $site ) return new WP_Error( 'not_found', 'Site not found' );

        $count = $this->membership_repo->count_users_by_site( $site_id );
        $users = $this->membership_repo->get_site_users( $site_id );

        // try find an "owner" role
        $owner = null;
        foreach ( $users as $u ) {
            if ( isset($u['role']) && in_array( strtolower($u['role']), ['owner','admin','primary'], true ) ) {
                $owner = $u;
                break;
            }
        }

        return [
            'site' => $site,
            'members_count' => intval( $count ),
            'members' => $users,
            'owner' => $owner
        ];
    }

    public function get_person( $person_id ){
        return $this->persons_repo->get($person_id);
    }

    /* -----------------------------------------
     * USER - PERSON HANDLING
     * ----------------------------------------- */
    public function get_person_user( $person_id ){
        return $this->persons_repo->get_user( $person_id );
    }
    public function set_person_user( $person_id, $user_id ){
        return $this->persons_repo->set_user( $person_id, $user_id );
    }
    public function unset_person_user( $person_id ){
        return $this->persons_repo->unset_person_user( $person_id );
    }

}
