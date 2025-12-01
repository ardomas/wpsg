<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_PersonsData
 *
 * Data-layer for 'persons' entity.
 * Responsibilities:
 *  - create / migrate persons tables
 *  - CRUD persons
 *  - provide safe helper methods (slug generation, search, count)
 *
 * Notes:
 *  - uses $wpdb->base_prefix because WPSG operates network-wide
 *  - returns standardized arrays (not stdClass) so upper layers get consistent payload
 *
 * @package WPSG
 * @since 0.1.0
 */
class WPSG_PersonsData {

    /**
     * @var string Database table for persons
     */
    private $table_name;

    /**
     * @var string Database table for personmeta (optional)
     */
    private $table_meta;

    /**
     * Singleton instance
     *
     * @var WPSG_PersonsData|null
     */
    private static $instance = null;

    /** 
     * @var class wp_error
     */
    private $wp_error;
    /**
     * @var int is_wp_error
     */
    private $is_wp_error;

    /**
     * Constructor (private for singleton)
     */
    private function __construct() {
        global $wpdb;
        $this->wp_error     = new WP_Error();
        $this->is_wp_error  = false;
        $this->table_name   = $wpdb->base_prefix . 'wpsg_persons';
        $this->table_meta   = $wpdb->base_prefix . 'wpsg_personmeta';
    }

    /**
     * Get singleton instance
     *
     * @return WPSG_PersonsData
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Called on plugin activation
     *
     * @return void
     */
    public static function activate() {
        self::create_tables();
    }

    /**
     * Create or update DB tables for persons & personmeta
     *
     * @return void
     */
    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $table_name = $this->table_name;
        // $wpdb->base_prefix . 'wpsg_persons';
        $table_meta = $this->table_meta;
        // $wpdb->base_prefix . 'wpsg_personmeta';

        $sql_person = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL,                  -- relasi ke wp_users
            name VARCHAR(191) NOT NULL,
            email VARCHAR(191) NULL,                       -- field baru untuk identitas sementara
            slug VARCHAR(191) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            description TEXT NULL,
            meta LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug_unique (slug),
            UNIQUE KEY user_id_unique (user_id),
            UNIQUE KEY email_unique (email),              -- optional, tergantung kebutuhan
            KEY status_idx (status)
        ) ENGINE=InnoDB {$charset};";

        $sql_meta = "CREATE TABLE {$table_meta} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            person_id BIGINT UNSIGNED NOT NULL,
            meta_key VARCHAR(191) NOT NULL,
            meta_value LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY person_id_idx (person_id),
            KEY meta_key_idx (meta_key)
        ) ENGINE=InnoDB {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_person );
        dbDelta( $sql_meta );
    }

    /* -------------------------
     * Field filtering & normalizer
     * ------------------------- */

    /**
     * Filter input fields (whitelist) and sanitize values
     *
     * @param array $data
     * @return array
     */
    private function filter_fields( $data ) {
        $allowed = [
            'name',
            'slug',
            'status',
            'description',
            'meta',
        ];

        $out = [];

        if ( ! is_array( $data ) ) {
            return $out;
        }

        foreach ( $allowed as $k ) {
            if ( array_key_exists( $k, $data ) ) {
                switch ( $k ) {
                    case 'name':
                        $out['name'] = sanitize_text_field( $data['name'] );
                        break;
                    case 'slug':
                        // allow user provided slug but sanitize to safe title
                        $out['slug'] = sanitize_title( $data['slug'] );
                        break;
                    case 'status':
                        $out['status'] = sanitize_text_field( $data['status'] );
                        break;
                    case 'description':
                        // description may contain HTML — sanitize minimally or use wp_kses_post
                        $out['description'] = wp_kses_post( $data['description'] );
                        break;
                    case 'meta':
                        // meta can be array or JSONable structure
                        if ( is_array( $data['meta'] ) || is_object( $data['meta'] ) ) {
                            $out['meta'] = wp_json_encode( $data['meta'] );
                        } else {
                            // try sanitize if it's a JSON string or scalar
                            $out['meta'] = is_string( $data['meta'] ) ? wp_json_encode( json_decode( wp_unslash( $data['meta'] ), true ) ?: $data['meta'] ) : wp_json_encode( $data['meta'] );
                        }
                        break;
                }
            }
        }

        return $out;
    }

    /**
     * Normalize DB row to standardized associative array
     *
     * @param object|array|null $row
     * @return array|null
     */
    private function normalize( $row ) {
        if ( empty( $row ) ) {
            return null;
        }

        // Accept both stdClass (wpdb) or associative array
        $r = ( is_array( $row ) ) ? $row : (array) $row;

        $meta = null;
        if ( ! empty( $r['meta'] ) ) {
            $decoded = json_decode( $r['meta'], true );
            $meta = ( $decoded === null ) ? $r['meta'] : $decoded;
        }

        return [
            'id'          => isset( $r['id'] ) ? intval( $r['id'] ) : 0,
            'name'        => isset( $r['name'] ) ? $r['name'] : '',
            'slug'        => isset( $r['slug'] ) ? $r['slug'] : '',
            'status'      => isset( $r['status'] ) ? $r['status'] : '',
            'description' => isset( $r['description'] ) ? $r['description'] : '',
            'meta'        => $meta,
            'created_at'  => isset( $r['created_at'] ) ? $r['created_at'] : null,
            'updated_at'  => isset( $r['updated_at'] ) ? $r['updated_at'] : null,
        ];
    }

    /**
     * Decode JSON meta value safely
     *
     * @param string|null $value
     * @return mixed
     */
    private function decode_meta( $value ) {
        if ( empty( $value ) ) return null;
        $decoded = json_decode( $value, true );
        return is_null( $decoded ) ? $value : $decoded;
    }

    /* -------------------------
     * Existence & slug helpers
     * ------------------------- */

    /**
     * Check if a slug exists
     *
     * @param string $slug
     * @param int|null $exclude_id optional ID to exclude (useful on update)
     * @return bool
     */
    public function slug_exists( $slug, $exclude_id = null ) {
        global $wpdb;
        $slug = sanitize_title( $slug );
        $table_name = $this->table_name;

        if ( $exclude_id ) {
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE slug = %s AND id != %d", $slug, intval( $exclude_id ) ) );
        } else {
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE slug = %s", $slug ) );
        }

        return intval( $count ) > 0;
    }

    /**
     * Generate a unique slug from name
     *
     * @param string $name
     * @return string
     */
    public function generate_slug( $name ) {
        $base = sanitize_title( $name );
        $slug = $base;
        $i = 2;
        while ( $this->slug_exists( $slug ) ) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    /* -------------------------
    * PRIVATE: INSERT
    * ------------------------- */
    private function insert_person( $data ) {
        global $wpdb;

        $this->is_wp_error = false;

        $now = current_time( 'mysql' );
        $data = $this->filter_fields( $data );

        // Pastikan field penting
        $data['user_id'] = $data['user_id'] ?? null;
        $data['email']   = $data['email'] ?? null;

        if ( empty( $data['name'] ) ) {
            $this->is_wp_error = true;
            $this->wp_error->add( 'invalid_data', 'Name is required' );
            return false;
        }

        // Generate atau cek slug
        if ( empty( $data['slug'] ) || $this->slug_exists( $data['slug'] ) ) {
            $data['slug'] = $this->generate_slug( $data['name'] );
        }

        $insert = [
            'name'        => $data['name'],
            'user_id'     => $data['user_id'],
            'email'       => $data['email'],
            'slug'        => $data['slug'],
            'status'      => $data['status'] ?? 'active',
            'description' => $data['description'] ?? null,
            'meta'        => $data['meta'] ?? null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        $formats = [ '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ];
        $res = $wpdb->insert( $this->table, $insert, $formats );

        if ( $res === false ) {
            $this->is_wp_error = true;
            $this->wp_error->add( 'db_error', 'Failed to insert person' );
            return false;
        }

        return intval( $wpdb->insert_id );
    }

    /* -------------------------
    * PRIVATE: UPDATE
    * ------------------------- */
    private function update_person( $id, $data ) {
        global $wpdb;

        $this->is_wp_error = false;

        $id = intval( $id );
        if ( $id <= 0 ) {
            $this->is_wp_error = true;
            $this->wp_error->add( 'invalid_id', 'Invalid person id' );
            return false;
        }

        $data = $this->filter_fields( $data );
        if ( empty( $data ) ) {
            $this->is_wp_error = true;
            $this->wp_error->add( 'invalid_data', 'No valid fields to update' );
            return false;
        }

        // Handle slug
        if ( isset( $data['slug'] ) && $this->slug_exists( $data['slug'], $id ) ) {
            $this->is_wp_error = true;
            $this->wp_error->add( 'slug_exists', 'Slug already exists' );
            return false;
        } elseif ( isset( $data['name'] ) ) {
            $current = $this->get( $id );
            if ( $current && $current['name'] !== $data['name'] ) {
                $data['slug'] = $this->generate_slug( $data['name'] );
            }
        }

        $data['updated_at'] = current_time( 'mysql' );

        $formats = array_fill( 0, count( $data ), '%s' );
        $res = $wpdb->update( $this->table, $data, [ 'id' => $id ], $formats, ['%d'] );

        if ( $res === false ) {
            $this->is_wp_error = true;
            $this->wp_error->add( 'db_error', 'Failed to update person' );
            return false;
        }

        return $id;
    }

    /* -------------------------
    * PUBLIC: SET (INSERT OR UPDATE)
    * ------------------------- */
    public function set( $data ) {
        $id = $data['id'] ?? null;

        if ( empty( $id ) ) {
            $id = $this->insert_person( $data );
        } else {
            $id = $this->update_person( $id, $data );
        }

        return $this->is_wp_error ? $this->wp_error : $id;
    }

    /* -------------------------
     * CRUD: READ
     * ------------------------- */

    /**
     * Get person by ID
     *
     * @param int $id
     * @return array|null
     */
    public function get( $id ) {
        global $wpdb;
        $id = intval( $id );
        if ( $id <= 0 ) return null;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ), ARRAY_A );
        return $this->normalize( $row );
    }

    /**
     * Get person by user_id
     *
     * @param int $user_id
     * @return array|null
     */
    public function get_by_user_id($user_id){
        global $wpdb;

        $table_name = $this->table_name;
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d LIMIT 1",
            intval($user_id)
        );

        return $wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get person by email
     *
     * @param string $email
     * @return array|null
     */
    public function get_by_email($email){
        global $wpdb;

        $table_name = $this->table_name;
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE email = %s LIMIT 1",
            $email
        );

        return $wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get person by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function get_by_slug( $slug ) {
        global $wpdb;
        $slug = sanitize_title( $slug );
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE slug = %s", $slug ), ARRAY_A );
        return $this->normalize( $row );
    }

    /**
     * Get all persons (with optional args: limit, offset, status)
     *
     * @param array $args
     * @return array
     */
    public function get_all( $args = [] ) {
        global $wpdb;
        $defaults = [
            'limit'  => 0,
            'offset' => 0,
            'status' => '', // '' means all
            'orderby'=> 'id',
            'order'  => 'ASC',
        ];
        $args = wp_parse_args( $args, $defaults );

        $order = ( strtoupper( $args['order'] ) === 'DESC' ) ? 'DESC' : 'ASC';
        $allowed_orderby = [ 'id', 'name', 'slug', 'created_at', 'updated_at' ];
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'id';

        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ( ! empty( $args['status'] ) ) {
            $sql .= " AND status = %s";
            $params[] = $args['status'];
        }

        $sql .= " ORDER BY {$orderby} {$order}";

        if ( intval( $args['limit'] ) > 0 ) {
            $params[] = intval( $args['limit'] );
            $params[] = intval( $args['offset'] );
            $sql = $wpdb->prepare( $sql . " LIMIT %d OFFSET %d", $params );
            $rows = $wpdb->get_results( $sql, ARRAY_A );
        } else {
            if ( ! empty( $params ) ) {
                // prepare with only status
                $rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
            } else {
                $rows = $wpdb->get_results( $sql, ARRAY_A );
            }
        }

        if ( empty( $rows ) ) return [];

        $out = [];
        foreach ( $rows as $r ) {
            $out[] = $this->normalize( $r );
        }
        return $out;
    }

    /* -------------------------
     * SEARCH & COUNT
     * ------------------------- */

    /**
     * Search persons by keyword (searches name and description)
     *
     * @param string $keyword
     * @param array $args
     * @return array
     */
    public function search( $keyword, $args = [] ) {
        global $wpdb;
        $keyword = '%' . $wpdb->esc_like( sanitize_text_field( $keyword ) ) . '%';

        $defaults = [
            'limit'  => 20,
            'offset' => 0,
        ];
        $args = wp_parse_args( $args, $defaults );

        $sql = "SELECT * FROM {$this->table} WHERE (name LIKE %s OR description LIKE %s) ORDER BY name ASC LIMIT %d OFFSET %d";
        $prepared = $wpdb->prepare( $sql, $keyword, $keyword, intval( $args['limit'] ), intval( $args['offset'] ) );
        $rows = $wpdb->get_results( $prepared, ARRAY_A );

        $out = [];
        foreach ( $rows as $r ) {
            $out[] = $this->normalize( $r );
        }
        return $out;
    }

    /**
     * Count all persons (optionally by status)
     *
     * @param string $status
     * @return int
     */
    public function count( $status = '' ) {
        global $wpdb;
        if ( empty( $status ) ) {
            $c = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
        } else {
            $c = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE status = %s", $status ) );
        }
        return intval( $c );
    }

    /* -------------------------
     * DELETE (soft)
     * ------------------------- */

    /**
     * Soft delete person (set status = 'deleted')
     *
     * @param int $id
     * @return bool|WP_Error
     */
    public function soft_delete( $id ) {
        return $this->update_person( $id, [ 'status' => 'deleted' ] );
    }

    /* -------------------------
     * META Handlers
     * -------------------------
     * Meta helpers (basic)
     * ------------------------- */

    /**
     * Add person meta
     *
     * @param int $person_id
     * @param string $meta_key
     * @param mixed $meta_value
     * @return int|false insert id or false
     */
    public function add_meta( $person_id, $meta_key, $meta_value ) {
        global $wpdb;
        $now = current_time( 'mysql' );

        $insert = [
            'person_id'  => intval( $person_id ),
            'meta_key'   => sanitize_text_field( $meta_key ),
            'meta_value' => is_scalar( $meta_value ) ? maybe_serialize( $meta_value ) : wp_json_encode( $meta_value ),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $res = $wpdb->insert( $this->table_meta, $insert );
        if ( $res === false ) return false;
        return intval( $wpdb->insert_id );
    }

    /**
     * Get person all_meta (single)
     *
     * @param int $_person_id
     * @return array
     */
    public function get_all_meta( $_person_id ) {
        global $wpdb;
        $values = [];
        $person_id = intval( $_person_id );
        $sql  = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$this->table_meta} WHERE person_id = %d", $p_id );
        $rows = $wpdb->get_results( $sql );
        if( !empty( $rows ) ){
            foreach( $rows as $row ){

                // Step 1: unserialize (for WP-style serialized php arrays)
                $value = maybe_unserialize( $row->meta_value );

                // Step 2: JSON decode if it looks like JSON
                if ( is_string( $value ) ) {
                    $decoded = json_decode( $value, true );
                    if ( json_last_error() === JSON_ERROR_NONE ) {
                        $value = $decoded;
                    }
                }

                // Store as associative array: meta_key => value
                $values[ $row->meta_key ] = $value;

            }
        }
        return $values;
    }

    /**
     * Get person meta (single)
     *
     * @param int $person_id
     * @param string $meta_key
     * @return mixed|null
     */
    public function get_meta( $person_id, $meta_key ) {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT meta_value FROM {$this->table_meta} WHERE person_id = %d AND meta_key = %s ORDER BY id ASC LIMIT 1", intval( $person_id ), sanitize_text_field( $meta_key ) );
        $val = $wpdb->get_var( $sql );
        if ( $val === null ) return null;
        // try unserialize or json decode
        $un = maybe_unserialize( $val );
        $dec = json_decode( $un, true );
        return ( $dec === null ) ? $un : $dec;
    }

    protected function delete_duplicate_meta($person_id, $key)
    {
        // Ambil semua meta_key yang sama
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT id 
                FROM {$this->table_postmeta}
                WHERE post_id = %d AND meta_key = %s
                ORDER BY id DESC",
                $post_id, 
                $key
            )
        );

        // Jika hanya ada 0 atau 1 baris, tidak perlu dibersihkan
        if (count($rows) <= 1) {
            return;
        }

        // Simpan ID yang terbaru (baris pertama karena ORDER BY id DESC)
        $latest_id = $rows[0]->id;

        // Sisanya akan dihapus
        $ids_to_delete = array_map(
            fn($row) => $row->id,
            array_slice($rows, 1)
        );

        if (!empty($ids_to_delete)) {
            $ids_in = implode(',', array_map('intval', $ids_to_delete));

            // Hapus baris-baris duplikat lama
            $this->wpdb->query("
                DELETE FROM {$this->table_postmeta}
                WHERE id IN ($ids_in)
            ");
        }
    }

}
