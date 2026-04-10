<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_PersonRecordsData {

    private static $instance = null;

    private $table_records;
    private $table_recordmeta;
    private $wpdb;

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->table_records    = $wpdb->base_prefix . 'wpsg_person_records';
        $this->table_recordmeta = $wpdb->base_prefix . 'wpsg_person_recordmeta';
    }

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /* ---------------------------------------------------------
     * ACTIVATION
     * --------------------------------------------------------- */

    public function activate() {

        $charset_collate  = $this->wpdb->get_charset_collate();
        $table_records    = $this->table_records;
        $table_recordmeta = $this->table_recordmeta;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_records = "CREATE TABLE {$table_records} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            person_id BIGINT UNSIGNED NOT NULL,

            record_type VARCHAR(50) NOT NULL,
            record_subtype VARCHAR(50) DEFAULT NULL,
            record_date DATE DEFAULT NULL,

            title VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,

            created_by BIGINT UNSIGNED DEFAULT NULL,

            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (id),
            KEY idx_person_id (person_id),
            KEY idx_record_type (record_type),
            KEY idx_record_subtype (record_subtype),
            KEY idx_record_date (record_date)
        ) $charset_collate;
        ";

        $sql_meta = "CREATE TABLE {$table_recordmeta} (
            meta_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            record_id BIGINT UNSIGNED NOT NULL,

            meta_key VARCHAR(191) NOT NULL,
            meta_value LONGTEXT DEFAULT NULL,

            PRIMARY KEY (meta_id),
            KEY idx_record_id (record_id),
            KEY idx_meta_key (meta_key),

            CONSTRAINT fk_wpsg_recordmeta_record
                FOREIGN KEY (record_id)
                REFERENCES {$this->table_records}(id)
                ON DELETE CASCADE
        ) $charset_collate;
        ";

        dbDelta( $sql_records );
        dbDelta( $sql_meta );
    }

    /* ---------------------------------------------------------
     * RECORD CRUD
     * --------------------------------------------------------- */

    public function get( $id ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_records} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
    }

    public function insert( $data ) {
        $this->wpdb->insert( $this->table_records, $data );
        return $this->wpdb->insert_id;
    }

    public function update( $id, $data ) {
        return $this->wpdb->update(
            $this->table_records,
            $data,
            [ 'id' => $id ]
        );
    }

    public function delete( $id ) {
        return $this->wpdb->delete(
            $this->table_records,
            [ 'id' => $id ]
        );
    }

    public function get_by_person( $person_id, $args = [] ) {

        $where = [ 'person_id = %d' ];
        $values = [ $person_id ];

        if ( ! empty( $args['record_type'] ) ) {
            $where[]  = 'record_type = %s';
            $values[] = $args['record_type'];
        }

        if ( ! empty( $args['record_subtype'] ) ) {
            $where[]  = 'record_subtype = %s';
            $values[] = $args['record_subtype'];
        }

        $sql = "
            SELECT * FROM {$this->table_records}
            WHERE " . implode( ' AND ', $where ) . "
            ORDER BY record_date DESC, created_at DESC
        ";

        return $this->wpdb->get_results(
            $this->wpdb->prepare( $sql, $values ),
            ARRAY_A
        );
    }

    /* ---------------------------------------------------------
     * META HANDLERS
     * --------------------------------------------------------- */

    public function add_meta( $record_id, $key, $value ) {
        return $this->wpdb->insert(
            $this->table_recordmeta,
            [
                'record_id'  => $record_id,
                'meta_key'   => $key,
                'meta_value' => maybe_serialize( $value ),
            ]
        );
    }

    public function update_meta( $record_id, $key, $value ) {
        return $this->wpdb->update(
            $this->table_recordmeta,
            [
                'meta_value' => maybe_serialize( $value ),
            ],
            [
                'record_id' => $record_id,
                'meta_key'  => $key,
            ]
        );
    }

    public function set_meta( $record_id, $key, $value ) {
        $exists = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT meta_id FROM {$this->table_recordmeta}
                 WHERE record_id = %d AND meta_key = %s",
                $record_id,
                $key
            )
        );

        if ( $exists ) {
            return $this->update_meta( $record_id, $key, $value );
        }

        return $this->add_meta( $record_id, $key, $value );
    }

    public function get_meta( $record_id, $key = '', $single = true ) {

        if ( $key ) {
            $sql = "
                SELECT meta_value FROM {$this->table_recordmeta}
                WHERE record_id = %d AND meta_key = %s
            ";
            $results = $this->wpdb->get_col(
                $this->wpdb->prepare( $sql, $record_id, $key )
            );
        } else {
            $sql = "
                SELECT meta_key, meta_value FROM {$this->table_recordmeta}
                WHERE record_id = %d
            ";
            $results = $this->wpdb->get_results(
                $this->wpdb->prepare( $sql, $record_id ),
                ARRAY_A
            );
        }

        if ( $single && $key ) {
            return isset( $results[0] ) ? maybe_unserialize( $results[0] ) : null;
        }

        return array_map( 'maybe_unserialize', $results );
    }

    public function delete_meta( $record_id, $key ) {
        return $this->wpdb->delete(
            $this->table_recordmeta,
            [
                'record_id' => $record_id,
                'meta_key'  => $key,
            ]
        );
    }
}
