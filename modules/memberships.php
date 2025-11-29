<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * WPSG Memberships Module
 *
 * Admin-only module to list and manage membership links:
 * person <-> site <-> user
 *
 * Table shown here: person-site memberships.
 */

class WPSG_Memberships {

    private $membership_service;

    public function __construct() {
        // Load service
        $this->membership_service = new WPSG_MembershipsService();

        // Register admin menu
        // add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    /**
     * Register a menu page for Memberships
     */
    public function register_menu() {
        add_menu_page(
            'Memberships',                  // page title
            'Memberships',                  // menu title
            'manage_options',               // capability
            'wpsg-memberships',             // slug
            [ $this, 'render_page' ],       // callback
            'dashicons-groups',             // icon
            56                               // position
        );
    }

    /**
     * Render the main memberships page
     */
    public function render_page() {
        echo '<div class="wrap">';
        echo '<h1>Memberships</h1>';

        // Fetch memberships (person-site)
        $items = [];
        $items = $this->membership_service->get_all_person_site_links();

        if ( empty( $items ) ) {
            echo '<p>No memberships found.</p>';
            echo '</div>';
            return;
        }

        $this->render_table( $items );

        echo '</div>';
    }

    /**
     * Render a clean HTML table
     */
    private function render_table( $items ) {

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th width="60">ID</th>
                    <th>Person</th>
                    <th>Site</th>
                    <th>User(s)</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>';
        echo '<tbody>';

        foreach ( $items as $row ) {

            $person_id = intval($row['person_id']);
            $site_id   = intval($row['site_id']);

            // Person data
            $person = $this->membership_service
                           ->persons_repo
                           ->get_person($person_id);

            // Site data
            $site = get_blog_details($site_id);

            // Users linked to this person
            $users = $this->membership_service
                          ->membership_repo
                          ->get_person_users($person_id);

            $user_list = empty($users)
                ? '<em>-</em>'
                : implode(', ', array_map(function($u){
                        return esc_html($u['user_login']);
                    }, $users));

            echo '<tr>';
            echo '<td>' . intval($row['id']) . '</td>';
            echo '<td>' . esc_html($person['name'] ?? '(no name)') . '</td>';
            echo '<td>' . esc_html($site->blogname ?? '(unknown site)') . '</td>';
            echo '<td>' . $user_list . '</td>';
            echo '<td>' . esc_html($row['role'] ?? '-') . '</td>';
            echo '<td>' . esc_html($row['status'] ?? 'active') . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }
}
