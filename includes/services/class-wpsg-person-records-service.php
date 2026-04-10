<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_PersonRecordsService {

    /**
     * @var WPSG_PersonRecordsRepository
     */
    protected $records_repo;

    /**
     * @var WPSG_PersonsRepository
     */
    protected $persons_repo;

    public function __construct() {
        $this->records_repo = new WPSG_PersonRecordsRepository();
        $this->persons_repo = new WPSG_PersonsRepository();
    }

    /* ---------------------------------------------------------
     * BASIC RECORD CREATION
     * --------------------------------------------------------- */

    /**
     * Create a record for a person
     *
     * @param int   $person_id
     * @param array $record_data
     * @param array $meta_data
     * @return int|WP_Error
     */
    public function create_record( $person_id, array $record_data, array $meta_data = [] ) {

        if ( ! $this->persons_repo->exists( $person_id ) ) {
            return new WP_Error(
                'person_not_found',
                'Person does not exist'
            );
        }

        $record_data['person_id'] = $person_id;

        $record_id = $this->records_repo->create_with_meta(
            $record_data,
            $meta_data
        );

        if ( ! $record_id ) {
            return new WP_Error(
                'record_create_failed',
                'Failed to create person record'
            );
        }

        return $record_id;
    }

    /* ---------------------------------------------------------
     * READ OPERATIONS
     * --------------------------------------------------------- */

    public function get_record( $record_id ) {
        return $this->records_repo->get( $record_id );
    }

    public function get_person_records( $person_id, array $args = [] ) {

        if ( ! $this->persons_repo->exists( $person_id ) ) {
            return [];
        }

        return $this->records_repo->get_by_person( $person_id, $args );
    }

    public function get_person_records_by_type(
        $person_id,
        $record_type,
        $record_subtype = null
    ) {
        return $this->records_repo->get_by_person_and_type(
            $person_id,
            $record_type,
            $record_subtype
        );
    }

    /* ---------------------------------------------------------
     * UPDATE & META
     * --------------------------------------------------------- */

    public function update_record( $record_id, array $record_data ) {
        return $this->records_repo->update( $record_id, $record_data );
    }

    public function set_record_meta( $record_id, $key, $value ) {
        return $this->records_repo->set_meta( $record_id, $key, $value );
    }

    /* ---------------------------------------------------------
     * SOFT DOMAIN HELPERS (EXTENSION POINTS)
     * --------------------------------------------------------- */

    /**
     * Example: create student daily report
     * (placeholder for future specialization)
     */
    public function create_student_daily_report(
        $person_id,
        $date,
        $description,
        array $scores = []
    ) {

        return $this->create_record(
            $person_id,
            [
                'record_type' => 'student_activity',
                'record_subtype' => 'daily',
                'record_date' => $date,
                'title' => 'Laporan Harian',
                'description' => $description,
            ],
            $scores
        );
    }
}
