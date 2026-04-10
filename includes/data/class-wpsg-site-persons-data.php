<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Data object for site-person relation
 * Pure data holder (no DB logic)
 */
class WPSG_SitePersonsData {

    /** Status constants */
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_DELETED  = 'deleted'; // soft-delete optional

    /** @var int */
    public int $id = 0;

    /** @var int */
    public int $site_id = 0;

    /** @var int */
    public int $person_id = 0;

    /**
     * child | parent | guardian | staff | teacher | etc
     * @var string
     */
    public string $role = '';

    /**
     * active | inactive | archived | deleted
     * @var string
     */
    public string $status = self::STATUS_ACTIVE;

    /** @var string */
    public string $created_at = '';

    /** @var string|null */
    public ?string $updated_at = null;

    /**
     * Hydrate data object from array
     */
    public function __construct( array $data = [] ) {

        foreach ( $data as $key => $value ) {
            if ( property_exists( $this, $key ) ) {
                $this->{$key} = $value;
            }
        }

    }

    public static function table_name(): string {
        global $wpdb;
        return $wpdb->base_prefix . 'wpsg_site_persons';
    }

    /**
     * Create database table (called on plugin activation)
     */
    public static function create_table(): void {

        global $wpdb;

        $table_name = self::table_name();
        $charset    = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table_name} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                site_id BIGINT UNSIGNED NOT NULL,
                person_id BIGINT UNSIGNED NOT NULL,
                role VARCHAR(32) NOT NULL,
                status VARCHAR(16) NOT NULL DEFAULT 'active',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_site_person_role (site_id, person_id, role),
                KEY site_persons_idx_person_id (person_id),
                KEY site_persons_idx_site_id (site_id, deleted_at),
                KEY site_persons_idx_status (status)
            ) {$charset};
        ";

        dbDelta( $sql );
    }

    /* ---------------------------------------------------------
     * INSERT
     * --------------------------------------------------------- */
    public function insert( array $data ): bool {
        global $wpdb;

        $table = self::table_name();

        $defaults = [
            'status' => 'active',
        ];

        $data = array_merge( $defaults, $data );

        $result = $wpdb->insert(
            $table,
            [
                'site_id'   => (int) $data['site_id'],
                'person_id' => (int) $data['person_id'],
                'role'      => sanitize_text_field( $data['role'] ),
                'status'    => sanitize_text_field( $data['status'] ),
            ],
            [ '%d', '%d', '%s', '%s' ]
        );

        return $result !== false;
    }


    /* ---------------------------------------------------------
     * UPDATE
     * --------------------------------------------------------- */
    public function update(
        int $site_id,
        int $person_id,
        string $role,
        string $status
    ): bool {
        global $wpdb;

        $table = self::table_name();

        $fields = [];
        $formats = [];

        if ( isset( $status ) ) {
            $fields['status'] = sanitize_text_field( $status );
            $formats[] = '%s';
        }

        if ( empty( $fields ) ) {
            return false;
        }

        // updated_at selalu disentuh
        $fields['updated_at'] = current_time( 'mysql' );
        $formats[] = '%s';

        $result = $wpdb->update(
            $table,
            $fields,
            [
                'site_id'   => $site_id,
                'person_id' => $person_id,
                'role'      => $role,
            ],
            $formats,
            [ '%d', '%d', '%s' ]
        );

        return $result !== false;
    }

    /* ---------------------------------------------------------
     * GET BY SITE
     * --------------------------------------------------------- */
    public function get_by_site(
        int $site_id,
        array $args = []
    ): array {
        global $wpdb;

        $table = self::table_name();

        $defaults = [
            'status' => null,
            'limit'  => 0,
            'offset' => 0,
        ];

        $args = array_merge( $defaults, $args );

        foreach( $args as $key => $value ){
            if( is_null( $value ) ){
                unset( $args[ $key ] );
            }
        }

        $where = [ 'site_id = %d', 'person_id IS NOT NULL', 'person_id != 0' ];
        $params = [ $site_id ];

        if ( ! empty( $args['status'] ) ) {
            $where[]  = 'status = %s';
            $params[] = $args['status'];
        }
        foreach( $args as $key => $value ){
            if( !in_array( $key, ['status', 'limit', 'offset'] ) ){
                $where[]  = "{$key} = %s";
                $params[] = $value;
            }
        }

        $sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where );

        if ( $args['limit'] > 0 ) {
            $sql .= $wpdb->prepare(
                ' LIMIT %d OFFSET %d',
                (int) $args['limit'],
                (int) $args['offset']
            );
        }

        return $wpdb->get_results(
            $wpdb->prepare( $sql, $params ),
            ARRAY_A
        );
    }

    /* ---------------------------------------------------------
     * GET BY PERSON
     * --------------------------------------------------------- */
    public function get_by_person(
        int $person_id
    ): array {
        global $wpdb;

        $table = self::table_name();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE person_id = %d",
                $person_id
            ),
            ARRAY_A
        );
    }

    /* ---------------------------------------------------------
     * GET BY SITE AND PERSON  (UTILITY)
     * --------------------------------------------------------- */
    public function get_by_site_person(
        int $site_id,
        int $person_id
    ): array {
        global $wpdb;
        $table = self::table_name();
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE person_id = %d AND site_id = %d",
                $person_id, $site_id
            ),
            ARRAY_A
        );

    }

    /* ---------------------------------------------------------
     * GET SINGLE (UTILITY)
     * --------------------------------------------------------- */
    public function get_by_site_person_role(
        int $site_id,
        int $person_id,
        string $role
    ): ?array {
        global $wpdb;

        $table = self::table_name();

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table}
                 WHERE site_id = %d
                   AND person_id = %d
                   AND role = %s",
                $site_id,
                $person_id,
                $role
            ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Helper: check active state
     */
    public function is_active(): bool {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Helper: mark as inactive (no DB action)
     */
    public function mark_inactive(): void {
        $this->status = self::STATUS_INACTIVE;
    }

    /**
     * Helper: mark as soft-deleted (no DB action)
     */
    public function mark_deleted(): void {
        $this->status = self::STATUS_DELETED;
    }

}
