<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * MembershipService - orchestrates linking user, person and site
 */
class WPSG_MembershipsService {

    private $persons_repo;      // WPSG_PersonsRepository instance
    private $membership_repo;   // WPSG_MembershipsRepository instance

    public function __construct() {
        $this->persons_repo    = new WPSG_PersonsRepository(); // or get instance if you prefer singleton
        $this->membership_repo = new WPSG_MembershipsRepository();
    }

    /**
     * Link a WP user to a person and person to a site.
     * This is high-level: ensures person exists, then links both relations.
     *
     * Returns array with keys: 'person_id', 'person_site_id', 'user_person_id' or WP_Error.
     */
    public function link_user_person_site( $user_id, $person_data, $site_id, $role = null ) {

        /** -------------------------------------
         * 1) Validate WP user
         * ------------------------------------- */
        $user_obj = get_user_by('id', intval($user_id));
        if ( ! $user_obj ) {
            return new WP_Error('invalid_user', 'WP user not found');
        }

        /** -------------------------------------
         * 2) Validate site (multisite or not)
         * ------------------------------------- */
        $blog = get_blog_details( $site_id );
        if ( ! $blog ) {
            return new WP_Error('invalid_site', 'Site not found');
        }

        /** -------------------------------------
         * 3) Resolve / Create Person
         * ------------------------------------- */
        $person_id = null;

        // Case A: person_data = ID
        if ( is_numeric($person_data) ) {
            $person_id = intval($person_data);

            if ( ! $this->persons_repo->exists($person_id) ) {
                return new WP_Error('invalid_person', 'Person not found');
            }

        // Case B: person_data = array
        } else if ( is_array($person_data) ) {

            // If email exists → find or create
            if ( ! empty($person_data['email']) ) {
                $existing = $this->persons_repo->get_person_by_email($person_data['email']);

                if ( $existing ) {
                    $person_id = $existing['id'];
                    $this->persons_repo->update_person($person_id, $person_data);
                } else {
                    $person_id = $this->persons_repo->create_person($person_data);
                }

            // No email → create minimal person
            } else {
                $person_id = $this->persons_repo->create_person($person_data);
            }

        } else {
            return new WP_Error('invalid_person_data', 'Invalid person data format.');
        }

        if ( ! $person_id ) {
            return new WP_Error('person_error', 'Failed to resolve/create person.');
        }

        /** -------------------------------------
         * 4) Assign Person → Site
         * ------------------------------------- */
        $ps_result = $this->membership_repo->assign_person_to_site(
            $person_id,
            $site_id,
            $role,
            'active'
        );

        if ( is_wp_error($ps_result) ) {
            return $ps_result;
        }

        /** -------------------------------------
         * 5) Link User → Person
         * ------------------------------------- */
        $up_result = $this->membership_repo->link_user_to_person(
            $user_id,
            $person_id,
            $site_id,
            $role,
            'active'
        );

        if ( is_wp_error($up_result) ) {
            return $up_result;
        }

        /** -------------------------------------
         * 6) Add WP user to blog (Multisite)
         * ------------------------------------- */
        if ( function_exists('add_user_to_blog') ) {

            $blogs = get_blogs_of_user( $user_id );
            $already_in_site = false;

            foreach ( $blogs as $b ) {
                if ( intval($b->userblog_id) === intval($site_id) ) {
                    $already_in_site = true;
                    break;
                }
            }

            if ( ! $already_in_site ) {
                add_user_to_blog( $site_id, $user_id, 'subscriber' );
            }
        }

        /** -------------------------------------
         * 7) Return structured result
         * ------------------------------------- */
        return [
            'person_id'      => $person_id,
            'site_id'        => intval($site_id),
            'person_site'    => $ps_result,
            'user_person'    => $up_result,
        ];
    }

    public function get_all_person_site_links($args = []) {
        return $this->membership_repo->get_all_person_site_links($args);
    }

    /**
     * Unlink user from person and/or person from site.
     */
    public function unlink_user_person_site( $user_id, $person_id, $site_id = null ) {
        $this->membership_repo->unlink_user_from_person($user_id, $person_id, $site_id);
        if ( $site_id ) {
            $this->membership_repo->remove_person_from_site($person_id, $site_id);
        }
        return true;
    }

    /** Helpers */
    public function get_person_sites($person_id) {
        return $this->membership_repo->get_person_sites($person_id);
    }

    public function get_site_persons($site_id) {
        return $this->membership_repo->get_site_persons($site_id);
    }

    public function get_person_users($person_id) {
        return $this->membership_repo->get_person_users($person_id);
    }
}
