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
        $items = $this->membership_service->list_memberships();

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

        echo '<table class="wpsg-full-width hover bordered striped">';
        echo '<thead>
                <tr>
                    <th width="30">#</th>
                    <th width="30">ID</th>
                    <th colspan="2">Site</th>
                    <th>Person</th>
                    <th>User(s)</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>';
        echo '<tbody>';

        $num_order=0;
        foreach ( $items as $row ) {
            $num_order++;

            $person_id = intval($row['person_id'] ?? '');
            $site_id   = intval($row['site_id'  ] ?? '');

            // Site data
            $site = get_blog_details($site_id);

            // Person data
            $person = $this->membership_service
                           ->get_person($person_id);

            // Users linked to this person
            $users = $this->membership_service
                          ->get_person_user($person_id);

            $user_list = empty($users)
                ? '<em>-</em>'
                : implode(', ', array_map(function($u){
                        return esc_html($u['user_login']);
                    }, $users));

            echo '<tr>';
            echo '<td style="text-align: right;">' . intval($num_order) . '</td>';
            echo '<td>' . intval($row['site_id']) . '</td>';
            echo '<td>' . esc_html($site->blogname ?? '(unknown site)') . '</td>';
            echo '<td style="width: 30px; white-space: nowrap;">' . 'edit | remove' . '</td>';
            echo '<td>' . esc_html($person['name'] ?? '(no name)') . '</td>';
            echo '<td>' . $user_list . '</td>';
            echo '<td>' . esc_html($row['role'] ?? '-') . '</td>';
            echo '<td>' . esc_html($row['status'] ?? 'active') . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }
}
