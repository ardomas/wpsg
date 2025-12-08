<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPSG_Memberships
 *
 * Module UI for Memberships (admin)
 * - List memberships (uses service->list_memberships())
 * - Form add/edit membership (uses service->save_membership())
 * - Delete membership (uses service->delete_membership())
 *
 * Note:
 * - Service layer is the source of truth; module is thin UI/controller.
 */
class WPSG_Memberships {

    protected $service;
    protected $page;
    protected $view;
    protected $base_url;

    public function __construct() {
        $this->service = new WPSG_MembershipsService();

        $this->page = 'wpsg-admin';
        $this->view = 'memberships';
        $this->base_url = "admin.php?page={$this->page}&view={$this->view}";

        // Hooking into admin screen rendering might be done outside this class.
        // Example: add_submenu_page or add_menu_page will call ->render_list / ->render_form
    }

    /* -----------------------------
     * Helpers
     * ---------------------------- */

    protected function safe_redirect( $url ) {
        if ( function_exists( 'wpsg_safe_redirect' ) ) {
            return wpsg_safe_redirect( $url );
        }
        wp_safe_redirect( $url );
        exit;
    }

    protected function current_user_can_manage() {
        return current_user_can( 'manage_options' );
    }

    /* -----------------------------
     * Handle Save
     * ---------------------------- */

    public function handle_save_membership() {
        // Process only on POST submit
        if ( ! isset( $_POST['wpsg_membership_submit'] ) ) return;

        if ( ! $this->current_user_can_manage() ) {
            wp_die( __( 'You do not have permission to perform this action.', 'wpsg' ) );
        }

        if ( ! isset( $_POST['wpsg_membership_nonce'] ) || ! wp_verify_nonce( $_POST['wpsg_membership_nonce'], 'wpsg_save_membership' ) ) {
            wp_die( __( 'Invalid nonce. Please try again.', 'wpsg' ) );
        }

        // Prepare data
        $network_payload = $_POST['network'] ?? [];
        $membership_payload = $_POST['membership'] ?? [];

        // Sanitization (simple)
        array_walk_recursive( $membership_payload, function( &$val ) {
            $val = is_string($val) ? sanitize_text_field( $val ) : $val;
        } );

        array_walk_recursive( $network_payload, function( &$val ) {
            $val = is_string($val) ? sanitize_text_field( $val ) : $val;
        } );

        // Convert datetime-local to MySQL format
        if ( ! empty( $membership_payload['start_date'] ) ) {
            $ts = strtotime( $membership_payload['start_date'] );
            if ( $ts !== false ) {
                $membership_payload['start_date'] = date( 'Y-m-d H:i:s', $ts );
            }
        }
        if ( ! empty( $membership_payload['end_date'] ) ) {
            $ts = strtotime( $membership_payload['end_date'] );
            if ( $ts !== false ) {
                $membership_payload['end_date'] = date( 'Y-m-d H:i:s', $ts );
            }
        }

        // Build full_data for service
        $full_data = [
            'network'    => $network_payload,
            'membership' => $membership_payload,
            // Meta fields can be passed via 'meta' key if present in form (optional)
            'meta'       => $_POST['meta'] ?? [],
            // site_meta for network options (optional)
            'site_meta'  => $_POST['site_meta'] ?? [],
        ];

        $res = $this->service->save_membership( $full_data );

        if ( is_wp_error( $res ) ) {
            $msg = $res->get_error_message();
            // You may prefer to set an admin notice instead of die.
            wp_die( sprintf( __( 'Error saving membership: %s', 'wpsg' ), esc_html( $msg ) ) );
        }

        // Redirect back to list with success status
        $redirect = admin_url( $this->base_url . '&action=list&status=ok' );
        $this->safe_redirect( $redirect );
    }

    /* -----------------------------
     * Handle Delete
     * ---------------------------- */

    public function handle_delete_membership() {
        if ( ! isset( $_GET['action'] ) ) return;

        if ( $_GET['action'] !== 'delete' ) return;

        if ( ! $this->current_user_can_manage() ) {
            wp_die( __( 'You do not have permission to perform this action.', 'wpsg' ) );
        }

        // Accept either membership id (id) or site_id fallback (legacy)
        $id = intval( $_GET['id'] ?? 0 );
        $site_id = intval( $_GET['site_id'] ?? 0 );

        $membership_id = $id;
        if ( $membership_id <= 0 && $site_id > 0 ) {
            // try to find membership by site_id via repo/service
            $found = $this->service->list_memberships([ 'site_id' => $site_id ]);
            if ( ! empty( $found ) ) {
                // pick first membership with that site_id
                $membership_id = intval( $found[0]['membership']['id'] ?? 0 );
            }
        }

        if ( $membership_id <= 0 ) {
            $this->redirect_list( 'invalid' );
        }

        // verify nonce passed in URL
        $nonce = $_REQUEST['_wpnonce'] ?? '';
        if ( ! wp_verify_nonce( $nonce, 'wpsg_delete_membership' ) ) {
            wp_die( __( 'Invalid nonce for delete.', 'wpsg' ) );
        }

        $deleted = $this->service->delete_membership( $membership_id );

        if ( is_wp_error( $deleted ) ) {
            $this->redirect_list( 'failed' );
        }

        $this->redirect_list( $deleted ? 'deleted' : 'failed' );
    }

    protected function redirect_list( $status = '' ) {
        $url = admin_url( $this->base_url . '&action=list' );
        if ( $status !== '' ) {
            $url = add_query_arg( 'status', $status, $url );
        }
        $this->safe_redirect( $url );
    }

    /* -----------------------------
     * Renderers
     * ---------------------------- */

    public function render_list() {

        // Handle delete first
        $this->handle_delete_membership();

        // Get membership list from service
        $args = [];
        if ( isset($_GET['status']) ) {
            $args['status'] = sanitize_text_field($_GET['status']);
        }

        $memberships = $this->service->list_memberships( $args );

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <?php esc_html_e( 'Memberships', 'wpsg' ); ?>
            </h1>

            <a href="<?php echo esc_url( admin_url($this->base_url . '&action=add') ); ?>"
            class="page-title-action">
                <?php esc_html_e( 'Add New', 'wpsg' ); ?>
            </a>

            <hr class="wp-header-end">

            <table class="wp-list-table fixed bordered striped hover table-view-list">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php esc_html_e('Member', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Name', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Status', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Type', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Level', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Start Date', 'wpsg'); ?></th>
                        <th><?php esc_html_e('End Date', 'wpsg'); ?></th>
                        <th><?php esc_html_e('ID', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Site ID', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Domain / Path', 'wpsg'); ?></th>
                        <th><?php esc_html_e('Site Name', 'wpsg'); ?></th>
                    </tr>
                </thead>

                <tbody>
                <?php if ( empty($memberships) ) : ?>

                    <tr>
                        <td colspan="12" style="text-align:center; padding:30px;">
                            <?php esc_html_e('No memberships found.', 'wpsg'); ?>
                        </td>
                    </tr>

                <?php else: ?>

                    <?php 
                    $num_order = 0;
                    /*
                    ?><xmp>Test:<br/><?php
                    print_r( $memberships )
                    ?></xmp><?php
                    */

                    foreach ( $memberships as $row ) :
                        $num_order++;
                        $m  = $row['membership'];
                        $site = $row['site'] ?? [];

                        $id = intval($m['id']);
                        $member_number = esc_html($m['member_number'] ?? '-');
                        $name = esc_html($m['name'] ?? '');
                        $status = esc_html($m['status'] ?? '');
                        $type = esc_html($m['member_type'] ?? '');
                        $level = esc_html($m['membership_level'] ?? '');
                        $start = esc_html($m['start_date'] ?? '');
                        $end = esc_html($m['end_date'] ?? '');
                        $site_id = intval($m['site_id'] ?? 0);

                        $domain = esc_html($site['domain'] ?? '');
                        $path   = esc_html($site['path'] ?? '');
                        $site_name = esc_html($site['site_name'] ?? '');

                        // Row actions like Posts
                        $edit_url = admin_url( $this->base_url . '&action=edit&id=' . $id );
                        $delete_url = wp_nonce_url(
                            admin_url( $this->base_url . '&action=delete&id=' . $id ),
                            'wpsg_delete_membership'
                        );

                        $actions = sprintf(
                            '<span class="edit"><a href="%s">%s</a></span>
                            <span class="delete"><a href="%s" onclick="return confirm(\'Delete this membership?\');">%s</a></span>',
                            esc_url($edit_url),
                            __('Edit', 'wpsg'),
                            esc_url($delete_url),
                            __('Delete', 'wpsg')
                        );
                    ?>

                    <tr>
                        <!-- row order -->
                        <td class="column-primary"><?php echo $num_order; ?></td>
                        <!-- 1. Member # + actions -->
                        <td class="column-primary">
                            <strong><?php echo $member_number; ?></strong>
                            <div class="row-actions"><?php echo $actions; ?></div>
                        </td>

                        <!-- 2. Name -->
                        <td><?php echo $name; ?></td>

                        <!-- 3–7: membership info -->
                        <td><?php echo $status; ?></td>
                        <td><?php echo $type; ?></td>
                        <td><?php echo $level; ?></td>
                        <td><?php echo $start; ?></td>
                        <td><?php echo $end; ?></td>

                        <!-- 8–10: Site info OR colspan if empty -->
                        <?php if ( empty($site_id) ) : ?>
                            <td colspan="4" style="font-style:italic; color:#777;">
                                <?php esc_html_e('No network assigned — contact network admin to add network.', 'wpsg'); ?>
                            </td>
                        <?php else: ?>
                            <td><?php echo $id; ?></td>
                            <td><?php echo $site_id; ?></td>
                            <td><?php echo $domain . $path; ?></td>
                            <td><?php echo $site_name; ?></td>
                        <?php endif; ?>
                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
                </tbody>

            </table>

        </div>
        <?php
    }

    public function render_form() {
        // handle POST save
        $this->handle_save_membership();

        // Detect action: add or edit
        $action = sanitize_text_field( $_GET['action'] ?? 'add' );
        $id     = intval( $_GET['id'] ?? 0 );

        $membership_wrapper = [
            'membership' => [
                'id' => 0,
                'site_id' => 0,
                'person_id' => '',
                'name' => '',
                'member_number' => '',
                'member_type' => '',
                'membership_level' => '',
                'status' => 'active',
                'start_date' => current_time( 'mysql' ),
                'end_date' => '',
                'address' => '',
            ],
            'meta' => [],
            'site' => null
        ];

        if ( $action === 'edit' && $id > 0 ) {
            $wrapper = $this->service->get_membership( $id );
            if ( $wrapper ) {
                $membership_wrapper['membership'] = $wrapper['membership'] ?? $membership_wrapper['membership'];
                $membership_wrapper['meta'] = $wrapper['meta'] ?? [];
                $membership_wrapper['site'] = $wrapper['site'] ?? null;
            }
        }

        $mdata = $membership_wrapper['membership'];
        $meta  = $membership_wrapper['meta'];
        $site  = $membership_wrapper['site'];

        // For site selector: list networks
        $networks = [];
        if ( function_exists( 'get_networks' ) ) {
            $nets = get_networks();
            if ( is_array( $nets ) ) {
                foreach ( $nets as $n ) {
                    $networks[ intval($n->id) ] = $n;
                }
            }
        }

        // Render form HTML
        ?>
        <div class="wrap" style="max-width:1200px;">
            <h1 class="wp-heading-inline">
                <?php echo $action === 'edit' ? esc_html__( 'Edit Membership', 'wpsg' ) : esc_html__( 'Add Membership', 'wpsg' ); ?>
            </h1>
            <a href="<?php echo esc_url( admin_url( $this->base_url . '&action=list' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back to list', 'wpsg' ); ?></a>

            <form method="post" action="">
                <?php wp_nonce_field( 'wpsg_save_membership', 'wpsg_membership_nonce' ); ?>
                <input type="hidden" name="wpsg_membership_submit" value="1" />
                <input type="hidden" name="membership[id]" value="<?php echo esc_attr( $mdata['id'] ); ?>" />

                <h2><?php esc_html_e( 'Network / Site', 'wpsg' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Select existing network', 'wpsg' ); ?></label></th>
                            <td>
                                <select name="membership[site_id]" id="membership_site_id">
                                    <option value="0"><?php esc_html_e( '-- Select --', 'wpsg' ); ?></option>
                                    <?php foreach ( $networks as $nid => $n ) : ?>
                                        <option value="<?php echo esc_attr( $nid ); ?>" <?php selected( $mdata['site_id'], $nid ); ?>>
                                            <?php echo esc_html( $n->site_name . ' (' . $n->domain . $n->path . ')' ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e( 'Choose an existing network/site to link with this membership. If left empty, you may provide domain & site name to auto-create network (if enabled).', 'wpsg' ); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Or create new network', 'wpsg' ); ?></label></th>
                            <td>
                                <input type="text" name="network[domain]" placeholder="<?php esc_attr_e( 'domain.tld', 'wpsg' ); ?>" value="<?php echo esc_attr( $site['domain'] ?? '' ); ?>" />
                                <input type="text" name="network[site_name]" placeholder="<?php esc_attr_e( 'Site Name', 'wpsg' ); ?>" value="<?php echo esc_attr( $site['site_name'] ?? '' ); ?>" />
                                <p class="description"><?php esc_html_e( 'If you provide domain + site name and no existing network is selected, service may attempt to ensure/create the network.', 'wpsg' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h2><?php esc_html_e( 'Membership Data', 'wpsg' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th><label for="membership_name"><?php esc_html_e( 'Name', 'wpsg' ); ?></label></th>
                            <td><input name="membership[name]" id="membership_name" class="regular-text" value="<?php echo esc_attr( $mdata['name'] ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><label for="membership_type"><?php esc_html_e( 'Type', 'wpsg' ); ?></label></th>
                            <td><input name="membership[member_type]" id="membership_type" class="regular-text" value="<?php echo esc_attr( $mdata['member_type'] ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><label for="membership_level"><?php esc_html_e( 'Level', 'wpsg' ); ?></label></th>
                            <td><input name="membership[membership_level]" id="membership_level" class="regular-text" value="<?php echo esc_attr( $mdata['membership_level'] ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><label for="membership_status"><?php esc_html_e( 'Status', 'wpsg' ); ?></label></th>
                            <td>
                                <select name="membership[status]" id="membership_status">
                                    <option value="active" <?php selected( $mdata['status'], 'active' ); ?>><?php esc_html_e( 'Active', 'wpsg' ); ?></option>
                                    <option value="inactive" <?php selected( $mdata['status'], 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'wpsg' ); ?></option>
                                    <option value="suspended" <?php selected( $mdata['status'], 'suspended' ); ?>><?php esc_html_e( 'Suspended', 'wpsg' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php esc_html_e( 'Start Date', 'wpsg' ); ?></label></th>
                            <td><input name="membership[start_date]" type="datetime-local" value="<?php echo esc_attr( date( 'Y-m-d\TH:i', strtotime( $mdata['start_date'] ) ) ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><label><?php esc_html_e( 'End Date', 'wpsg' ); ?></label></th>
                            <td><input name="membership[end_date]" type="datetime-local" value="<?php echo esc_attr( $mdata['end_date'] ? date( 'Y-m-d\TH:i', strtotime( $mdata['end_date'] ) ) : '' ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><label><?php esc_html_e( 'Address', 'wpsg' ); ?></label></th>
                            <td><textarea name="membership[address]" class="large-text"><?php echo esc_textarea( $mdata['address'] ); ?></textarea></td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <button type="submit" name="wpsg_membership_submit" class="button button-primary"><?php echo $action === 'edit' ? esc_html__( 'Update Membership', 'wpsg' ) : esc_html__( 'Add Membership', 'wpsg' ); ?></button>
                </p>
            </form>
        </div>
        <?php
    }
}

// Initialize module (if this file is loaded directly)
return new WPSG_Memberships();
