<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Data layer for Person Relations.
 * Handles raw CRUD for wp_wpsg_person_relations table.
 */
class WPSG_PersonRelationsData {

    private static ?self $instance = null;

    /** @var wpdb */
    private $db;

    /** @var string */
    private $table;

    /**
     * Singleton accessor
     */
    public static function get_instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor (private)
     */
    private function __construct() {
        global $wpdb;
        $this->db    = $wpdb;
        $this->table = $wpdb->base_prefix . 'wpsg_person_relations';
    }

    /* ---------------------------------------------------------
     * ACTIVATION
     * --------------------------------------------------------- */

    public function activate(): void {
        $charset = $this->db->get_charset_collate();

        $sql = "CREATE TABLE {$this->table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            person_id BIGINT UNSIGNED NOT NULL,
            related_person_id BIGINT UNSIGNED NOT NULL,
            relation_type VARCHAR(50) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            start_date DATE NULL,
            end_date DATE NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY person_id (person_id),
            KEY related_person_id (related_person_id),
            KEY relation_type (relation_type),
            KEY idx_is_active (is_active)
        ) {$charset};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function get_by_persons_relation($person_id, $related_person_id, $relation_type): ?array {
        $sql = "SELECT * FROM {$this->table}
            WHERE person_id = %d
              AND related_person_id = %d
              AND relation_type = %s
        ";

        $row = $this->db->get_row(
            $this->db->prepare( $sql, $person_id, $related_person_id, $relation_type ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /* ---------------------------------------------------------
     * BASIC CRUD
     * --------------------------------------------------------- */

    /**
     * Insert new relation
     */
    public function insert( array $data ): int|false {

        $defaults = [
            'person_id'         => null,
            'related_person_id' => null,
            'relation_type'     => '',
            'is_active'         => 1,
            'start_date'        => null,
            'end_date'          => null,
            'created_at'        => current_time( 'mysql' ),
            'updated_at'        => current_time( 'mysql' ),
        ];

        $data = wp_parse_args( $data, $defaults );

        if ( empty( $data['person_id'] ) || empty( $data['related_person_id'] ) ) {
            return false;
        }

        $inserted = $this->db->insert(
            $this->table,
            $data,
            [
                '%d',
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
            ]
        );

        return $inserted ? (int) $this->db->insert_id : false;
    }

    /**
     * Get relation by ID
     */
    public function get( int $id ): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = %d";
        return $this->db->get_row(
            $this->db->prepare( $sql, $id ),
            ARRAY_A
        );
    }

    /**
     * Get all relations for a person
     */
    public function get_by_person_id(
        int $person_id,
        bool $only_active = true
    ): array {

        $sql = "SELECT * FROM {$this->table} WHERE person_id = %d";

        if ( $only_active ) {
            $sql .= " AND is_active = 1";
        }

        return $this->db->get_results(
            $this->db->prepare( $sql, $person_id ),
            ARRAY_A
        );
    }

    /**
     * Get relations between two persons
     */
    public function get_between(
        int $person_id,
        int $related_person_id,
        bool $only_active = true
    ): array {

        $sql = "
            SELECT * FROM {$this->table}
            WHERE person_id = %d
              AND related_person_id = %d
        ";

        if ( $only_active ) {
            $sql .= " AND is_active = 1";
        }

        return $this->db->get_results(
            $this->db->prepare( $sql, $person_id, $related_person_id ),
            ARRAY_A
        );
    }

    public function get_all_by_related_person(
        int $person_id,
        bool $active_only = true
    ): array {
        $sql = "SELECT * FROM {$this->table} WHERE related_person_id = %d";
        if( $active_only ){
            $sql .= " AND is_active";
         }
        return $this->db->get_results(
            $this->db->prepare( $sql, $person_id ),
            ARRAY_A
        );
    }

    public function get_relations_by_person(
        int $person_id,
        string $relation_type = '',
        bool $active_only = true
    ){
        $sql = "
            SELECT * FROM {$this->table}
            WHERE  person_id = %d
              AND  relation_type = %s
        ";
        if( $active_only ){
            $sql .= " AND is_active";
        }

        // echo( '<br/>' . $sql . '; ' . $person_id . '; ' . $relation_type  );

        return $this->db->get_results(
            $this->db->prepare( $sql, $person_id, $relation_type ),
            ARRAY_A
        );
    }

    public function get_relations_to_person(
        int $person_id,
        string $relation_type = '',
        bool $active_only = true
    ){
        $sql = "
            SELECT * FROM {$this->table}
            WHERE  related_person_id = %d
              AND  relation_type = %s
        ";
        if( $active_only ){
            $sql .= " AND is_active";
        }
        return $this->db->get_results(
            $this->db->prepare( $sql, $person_id, $relation_type ),
            ARRAY_A
        );
    }

    public function relation_exists( 
        int $person_id  =0, 
        int $related_person_id = 0, 
        string $relation_type = '' ) : int {
        $init_data = $this->db->get_results(
            $this->db->prepare( 
                "SELECT * FROM {$this->table} WHERE `person_id` = %d AND `related_person_id`= %d AND `relation_type` = %s ", 
                $person_id, $related_person_id, $relation_type ),
            ARRAY_A
        );
        if( $init_data==[] ){
            return false;
        }
        return $init_data[0]['id'];
    }

    public function get_relations_by_type(
        int $person_id = 0,
        string $relation_type = '',
        $active_only = true
    ): ?Array {

        return $this->db->get_results(
            $this->db->prepare( 
                "SELECT * FROM {$this->table} WHERE `person_id` = %d AND `relation_type` = %s " . ( $active_only ? " AND is_active " : "" ), 
                $person_id, $relation_type ),
            ARRAY_A
        );

    }

    /* ---------------------------------------------------------
     * STATE MANAGEMENT
     * --------------------------------------------------------- */

    /**
     * Soft deactivate relation
     */
    public function deactivate( int $id ): bool {

        return (bool) $this->db->update(
            $this->table,
            [
                'is_active'  => 0,
                'updated_at' => current_time( 'mysql' ),
            ],
            [ 'id' => $id ],
            [ '%d', '%s' ],
            [ '%d' ]
        );

    }

    /**
     * Reactivate relation
     */
    public function activate_relation( int $id ): bool {
        return (bool) $this->db->update(
            $this->table,
            [
                'is_active'  => 1,
                'updated_at' => current_time( 'mysql' ),
            ],
            [ 'id' => $id ],
            [ '%d', '%s' ],
            [ '%d' ]
        );

    }

    /**
     * Hard delete relation (use carefully)
     */
    public function delete( int $id ): bool {
        return (bool) $this->db->delete(
            $this->table,
            [ 'id' => $id ],
            [ '%d' ]
        );
    }
}
