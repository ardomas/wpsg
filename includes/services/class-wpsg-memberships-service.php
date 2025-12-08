<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_MembershipsService
 *
 * Business/service layer for memberships.
 * - wp_wpsg_sites is the MAIN table (via repository)
 * - wp_site (network) is optional info (LEFT JOIN style)
 * - wp_wpsg_sitemeta is plugin meta (managed by repo)
 * - wp_sitemeta / network options are updated only if site/network exists
 *
 * Responsibilities:
 * - validation and normalization
 * - composition of membership + site info for UI/API
 * - managing writes across plugin tables and network options (if applicable)
 * - event triggers (actions)
 */
class WPSG_MembershipsService {

    /** @var WPSG_MembershipsRepository */
    protected $repo;

    /** @var wpdb (for optional site queries) */
    protected $wpdb;

    public function __construct() {
        $this->repo  = new WPSG_MembershipsRepository();
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /* ------------------------------------------------------------------
     * LIST / GET
     * ------------------------------------------------------------------ */

    /**
     * List memberships with optional filters.
     * Attach optional wp_site info (LEFT JOIN style) and plugin meta.
     *
     * $args forwarded to repo->get_all()
     * Returns array of items:
     * [
     *   'membership' => array|object (row from wp_wpsg_sites),
     *   'meta'       => [ meta_key => meta_value, ... ],
     *   'site'       => object|null  (row from wp_site table if exists),
     * ]
     */
    public function list_memberships( $args = [] ) {
        // get raw memberships from repo (wp_wpsg_sites)
        $memberships = $this->repo->get_all( $args );

        if ( empty( $memberships ) ) {
            return [];
        }

        // Collect site_ids to fetch wp_site info (many in one query)
        $site_ids = [];
        $metas    = [];
        foreach ( $memberships as $m ) {
            $sid = intval( $m['site_id'] ?? 0 );
            if ( $sid > 0 ) $site_ids[ $sid ] = $sid;
            $metas[$sid] = [];
        }

        // Fetch site rows (if any) using wp_site table (multi-network)
        // $sites_indexed = $this->fetch_sites_indexed( array_values( $site_ids ) );
        $sites_indexed = $this->fetch_site_all_metas( $site_ids );

        // Prepare result: membership with meta + site info
        $result = [];
        foreach ( $memberships as $m ) {
            $site_id = intval( $m['site_id'] ?? 0 );
            // $metas[$site_id] = $this->get_all_site_m

            // plugin meta for this membership (wp_wpsg_sitemeta)
            $meta_rows = $this->repo->get_all_meta( $site_id ); // returns [] if site_id <=0
            $meta = $this->meta_rows_to_kv( $meta_rows );

            $result[] = [
                'membership' => $m,
                'meta'       => $meta,
                'site'       => $sites_indexed[ $site_id ] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Get single membership by primary id (id in wp_wpsg_sites).
     * Returns array with membership, meta, site.
     */
    public function get_membership( $id ) {
        $id = intval( $id );
        if ( $id <= 0 ) return null;

        $m = $this->repo->get( $id );
        if ( ! $m ) return null;

        $site_id = intval( $m['site_id'] ?? 0 );
        $meta_rows = $this->repo->get_all_meta( $site_id );
        $meta = $this->meta_rows_to_kv( $meta_rows );
        $site = $site_id > 0 ? $this->fetch_site_row( $site_id ) : null;

        return [
            'membership' => $m,
            'meta'       => $meta,
            'site'       => $site,
        ];
    }

    /* ------------------------------------------------------------------
     * SAVE (create / update)
     * ------------------------------------------------------------------ */

    /**
     * Save membership (create or update).
     *
     * $full_data is an array that may include:
     * - 'membership' => main membership fields for wp_wpsg_sites (id optional)
     * - 'meta'       => associative array meta_key => meta_value for wp_wpsg_sitemeta
     * - 'site_meta'  => associative array meta_key => meta_value for network (wp_sitemeta) — only saved if site_id exists
     * - 'network'    => optional network payload (domain,path,title) — used to ensure network exists (if you want to auto create)
     *
     * Behavior:
     * - normalize membership via normalize_membership_data()
     * - save membership via repo->set() (returns insert_id or rows affected)
     * - save plugin meta via repo->set_meta()
     * - if site_id exists and 'site_meta' present, save via update_network_option()
     *
     * Returns: insert_id | rows affected | WP_Error on fatal failure
     */
    public function save_membership( $full_data = [] ) {
        if ( empty( $full_data ) || ! is_array( $full_data ) ) {
            return new WP_Error( 'missing_data', 'Data required.' );
        }

        echo '<xmp>Test';
        print_r( $full_data );
        echo '</xmp>';

        $membership  = $full_data['membership'] ?? [];
        $meta        = $full_data['meta'] ?? [];
        $site_meta   = $full_data['site_meta'] ?? []; // network options
        $network_in  = $full_data['network'] ?? [];

        // If network info provided and site_id empty -> ensure/create network (optional)
        // network_in keys: domain, path, title
        if ( ! empty( $network_in ) && empty( $membership['site_id'] ) ) {
            $domain = strtolower( trim( $network_in['domain'] ?? '' ) );
            $path   = ! empty( $network_in['path'] ) ? $network_in['path'] : '/';
            $title  = $network_in['site_name'] ?? 'New Network';

            if ( $domain !== '' ) {
                try {
                    $network_id = $this->ensure_network_exists( $domain, $path, $title );
                    if ( $network_id ) {
                        $membership['site_id'] = intval( $network_id );
                    }
                } catch ( Exception $e ) {
                    return new WP_Error( 'network_creation_failed', 'Failed creating or retrieving network: ' . $e->getMessage() );
                }
            }
        }

        // Normalize membership payload
        $membership = $this->normalize_membership_data( $membership );

        // Before save hook
        do_action( 'wpsg_membership_before_save', $membership, $meta, $site_meta );

        // Persist main membership via repository
        // echo '<xmp>';
        // print_r( $membership );
        // echo '</xmp>';
        /*
        if( $membership['id']==0 ){
            $tmp = [];
            foreach( $membership as $k=>$v ){
                if( $k!=='id' ){
                    $tmp[$k] = $v;
                }
            }
            $membership = $tmp;
        }
        */
        // echo '<xmp>';
        // print_r( $membership );
        // echo '</xmp>';
        // die('lihat hasil');
        $res = $this->repo->set( $membership );
        // repo->set returns insert_id (int) on insert, or number of rows affected on update, or false
        if ( $res === false ) {
            return new WP_Error( 'db_error', 'Failed saving membership.' );
        }

        // If new insert, ensure we have primary id
        $insert_id = is_int( $res ) && intval( $res ) > 0 ? intval( $res ) : ( intval( $membership['id'] ?? 0 ) > 0 ? intval( $membership['id'] ) : null );

        // If there is a site_id, use it as "site identifier" for meta operations
        $site_id = intval( $membership['site_id'] ?? 0 );

        // Save plugin meta (wp_wpsg_sitemeta) — iterate through $meta
        if ( ! empty( $meta ) && $site_id > 0 ) {
            foreach ( $meta as $k => $v ) {
                $this->repo->set_meta( $site_id, $k, $v );
            }
        }

        // Save network/site meta (wp_sitemeta) if provided and network function available
        if ( $site_id > 0 && ! empty( $site_meta ) ) {
            foreach ( $site_meta as $k => $v ) {
                $update = $this->update_network_option_safe( $site_id, $k, $v );
                if ( is_wp_error( $update ) ) {
                    // Log but do not fail entire save; continue
                    do_action( 'wpsg_membership_network_meta_error', $site_id, $k, $v, $update );
                }
            }
        }

        // After save hook
        do_action( 'wpsg_membership_after_save', $membership, $res );

        return $res;
    }

    /* ------------------------------------------------------------------
     * DELETE
     * ------------------------------------------------------------------ */

    /**
     * Delete membership.
     *
     * $id is primary id of wp_wpsg_sites.
     * Options:
     *  - 'remove_network_meta' => bool (default false) — if true, attempt to remove keys from network options
     *
     * Returns: boolean | WP_Error
     */
    public function delete_membership( $id, $options = [] ) {
        $id = intval( $id );
        if ( $id <= 0 ) return new WP_Error( 'invalid_id', 'Invalid membership id' );

        // fetch membership to know site_id
        $membership = $this->repo->get( $id );
        if ( ! $membership ) return new WP_Error( 'not_found', 'Membership not found' );

        $site_id = intval( $membership['site_id'] ?? 0 );

        // Before delete hook
        do_action( 'wpsg_membership_before_delete', $id, $membership );

        // Delete plugin meta first (safe)
        if ( $site_id > 0 ) {
            $this->repo->delete_all_meta( $site_id );
        }

        // Optionally remove specified network meta keys (caller must supply meta keys)
        if ( ! empty( $options['remove_network_meta_keys'] ) && is_array( $options['remove_network_meta_keys'] ) && $site_id > 0 ) {
            foreach ( $options['remove_network_meta_keys'] as $k ) {
                if ( function_exists( 'delete_network_option' ) ) {
                    delete_network_option( $site_id, $k );
                } else {
                    // no-op if function missing; log
                    do_action( 'wpsg_membership_delete_network_option_missing', $site_id, $k );
                }
            }
        }

        // Delete main membership row (soft delete via repo->delete expects id)
        $deleted = $this->repo->delete( $id );

        // After delete hook
        do_action( 'wpsg_membership_after_delete', $id, $membership, $deleted );

        return (bool) $deleted;
    }

    /* ------------------------------------------------------------------
     * META HANDLERS (wrap repo)
     * ------------------------------------------------------------------ */

    public function get_meta( $site_id, $meta_key ) {
        return $this->repo->get_meta( $site_id, $meta_key );
    }

    public function set_meta( $site_id, $meta_key, $meta_value ) {
        return $this->repo->set_meta( $site_id, $meta_key, $meta_value );
    }

    public function delete_meta( $site_id, $meta_key ) {
        return $this->repo->delete_meta( $site_id, $meta_key );
    }

    /* ------------------------------------------------------------------
     * HELPERS
     * ------------------------------------------------------------------ */

    /**
     * Convert meta rows (array of [meta_key, meta_value]) to kv array.
     */
    protected function meta_rows_to_kv( $rows ) {
        $out = [];
        if ( empty( $rows ) ) return $out;
        if( is_array( $rows ) ){
            foreach ( $rows as $r ) {
                if( isset( $r['meta_key'] ) && isset($r['meta_value']) ){
                    $out[ $r['meta_key'] ] = $r['meta_value'];
                }
            }
        }
        return $out;
    }

    /**
     * Fetch wp_site rows for given array of site_ids and return indexed by id.
     * Uses $wpdb and base_prefix . 'site' table (multi-network).
     * If no site table or zero ids, returns [].
     */
    protected function fetch_sites_indexed( $site_ids = [] ) {
        $out = [];
        if ( empty( $site_ids ) ) return $out;

        $site_table = $this->wpdb->base_prefix . 'site';

        // guard: if table doesn't exist, return empty
        if ( $this->table_exists( $site_table ) === false ) return $out;

        $placeholders = implode( ',', array_fill(0, count($site_ids), '%d') );
        $sql = "SELECT * FROM {$site_table} WHERE id IN ({$placeholders})";
        $rows = $this->wpdb->get_results( $this->wpdb->prepare( $sql, $site_ids ), ARRAY_A );

        if ( empty( $rows ) ) return $out;

        foreach ( $rows as $r ) {
            $out[ intval( $r['id'] ) ] = $r;
        }
        return $out;
    }

    protected function fetch_site_all_metas( $ids ){
        $rows = [];
        foreach( $ids as $site_id ){
            $meta_table = $this->wpdb->base_prefix . 'sitemeta';
            $meta_sql = $this->wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$meta_table}
                WHERE site_id = %d",
                $site_id
            );
            if( $site_id!=0 ){
                $row = $this->fetch_site_all_meta( $site_id );
                if( !$row!=null ){
                    $rows[ $site_id ] = $row;
                }
            }
        }
        return $rows;
    }
    /**
     * Fetch a single wp_site row by id.
     */
    /*
    protected function fetch_site_row( $site_id ) {
        $site_table = $this->wpdb->base_prefix . 'site';
        if ( $this->table_exists( $site_table ) === false ) return null;

        $sql = $this->wpdb->prepare( "SELECT * FROM {$site_table} WHERE id = %d", intval( $site_id ) );
        $row = $this->wpdb->get_row( $sql, ARRAY_A );
        return $row ? $row : null;
    }
    */
    protected function fetch_site_all_meta( $site_id ){
        $meta_table = $this->wpdb->base_prefix . 'sitemeta';
        $meta_sql = $this->wpdb->prepare(
            "SELECT meta_key, meta_value FROM {$meta_table}
            WHERE site_id = %d",
            $site_id
        );
        $rows = [];

        $msrc = $this->wpdb->get_var( $meta_sql );

        $rows = $this->meta_rows_to_kv( array($msrc) );

        return $rows;

    }

    protected function fetch_site_row( $site_id ) {
        $site_table = $this->wpdb->base_prefix . 'site';
        /*
        $meta_table = $this->wpdb->base_prefix . 'sitemeta';
        */

        if ( $this->table_exists( $site_table ) === false ) {
            return null;
        }

        $site_id = intval( $site_id );

        // Fetch row from wp_site
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$site_table} WHERE id = %d",
            $site_id
        );
        $row = $this->wpdb->get_row( $sql, ARRAY_A );

        if ( ! $row ) {
            return null;
        }

        // ======================================================
        // Fetch site_name meta
        // ======================================================
        $meta_rows = $this->fetch_site_all_meta( $site_id );
        $site_name = $meta_rows['site_name'] ?? 'New Network';
        /*
        $meta_sql = $this->wpdb->prepare(
            "SELECT meta_value FROM {$meta_table}
            WHERE site_id = %d AND meta_key = %s
            LIMIT 1",
            $site_id,
            'site_name'
        );

        $site_name = $this->wpdb->get_var( $meta_sql );
        */

        // Tambahkan ke hasil
        $row['meta']['site_name'] = $site_name ? $site_name : '';

        return $row;
    }

    /**
     * Safe wrapper for update_network_option (network/site meta).
     * Returns true on success, WP_Error on fatal.
     */
    protected function update_network_option_safe( $site_id, $key, $value ) {
        if ( ! function_exists( 'update_network_option' ) ) {
            return new WP_Error( 'no_network_api', 'Network API not available' );
        }

        // use update_network_option which manages sitemeta for networks
        $ok = update_network_option( intval($site_id), $key, $value );
        return $ok;
    }

    /**
     * Check table exists
     */
    protected function table_exists( $table_name ) {
        $q = $this->wpdb->prepare( "SHOW TABLES LIKE %s", $table_name );
        $res = $this->wpdb->get_var( $q );
        return ! empty( $res );
    }

    /* ------------------------------------------------------------------
     * NETWORK HELPERS (keberadaan multi-network)
     * ------------------------------------------------------------------ */

    /**
     * Ensure network exists: try to find network by domain+path, otherwise create.
     * Returns network id (int) or throws Exception.
     */
    private function ensure_network_exists( string $domain, string $path = '/', string $title = 'New Network' ) : int {

        $domain = strtolower( trim( $domain ) );
        $path   = '/' . trim( $path, '/' ) . '/';

        // try to find network via get_networks (if available)
        if ( function_exists( 'get_networks' ) ) {
            $existing = get_networks([
                'domain' => $domain,
                'path'   => $path,
                'number' => 1,
            ]);

            if ( ! empty( $existing ) ) {
                // ensure name updated
                if ( function_exists( 'update_network_option' ) ) {
                    update_network_option( intval( $existing[0]->id ), 'site_name', $title );
                }
                return intval( $existing[0]->id );
            }
        }

        // not found -> create if possible
        return $this->auto_create_network( $domain, $path, $title );
    }

    /**
     * Auto-create network (requires multi-network capability / add_network).
     * Throws Exception on failure.
     */
    private function auto_create_network( string $domain, string $path, string $title='New Network' ) : int {
        if ( ! function_exists( 'add_network' ) ) {
            throw new Exception('WP Multi Network is required to create a new network.');
        }

        $network_id = add_network([
            'domain'=> $domain,
            'path'  => $path,
            'title' => $title,
        ]);

        if ( is_wp_error( $network_id ) ) {
            throw new Exception( 'Failed to create network: ' . $network_id->get_error_message() );
        }

        // update name if possible
        if ( function_exists( 'update_network_option' ) ) {
            update_network_option( intval($network_id), 'site_name', $title );
        }

        return intval( $network_id );
    }

    /* ------------------------------------------------------------------
     * UTILITY: normalize membership data
     * ------------------------------------------------------------------ */

    protected function normalize_membership_data( $data ) {
        if ( ! is_array( $data ) ) $data = [];

        // Ensure allowed keys exist and default values
        $normalized = [
            'id'               => isset($data['id']) ? intval($data['id']) : 0,
            'site_id'          => isset($data['site_id']) && $data['site_id'] !== '' ? intval($data['site_id']) : 0,
            'person_id'        => isset($data['person_id']) && $data['person_id'] !== '' ? intval($data['person_id']) : null,
            'name'             => isset($data['name']) ? (string)$data['name'] : '',
            'member_number'    => isset($data['member_number']) ? (string)$data['member_number'] : null,
            'member_type'      => isset($data['member_type']) ? (string)$data['member_type'] : '',
            'membership_level' => isset($data['membership_level']) ? (string)$data['membership_level'] : '',
            'status'           => isset($data['status']) ? (string)$data['status'] : 'active',
            'start_date'       => isset($data['start_date']) ? (string)$data['start_date'] : current_time('mysql'),
            'end_date'         => isset($data['end_date']) && $data['end_date'] !== '' ? (string)$data['end_date'] : null,
            'address'          => isset($data['address']) ? (string)$data['address'] : '',
        ];

        return $normalized;
    }

}
