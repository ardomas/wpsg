<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_ParticipantsService
 *
 * Service-level abstraction for participants.
 * A participant is a PERSON with participation records.
 *
 * @since 0.1.0
 */
class WPSG_ParticipantsService {

    /** @var WPSG_PersonsService */
    protected $persons;

    /** @var WPSG_PersonRecordsService */
    protected $records;

    public function __construct() {
        $this->persons = new WPSG_PersonsService();
        $this->records = new WPSG_PersonRecordsService();
    }

    /* ------------------------------------
     * PARTICIPATION LIFECYCLE (MINIMAL)
     * ------------------------------------ */

    /**
     * Register a person as participant
     *
     * @param int   $person_id
     * @param array $args
     * @return int|WP_Error
     */
    public function enroll( $person_id, array $args = [] ) {

        if ( ! $this->persons->exists( $person_id ) ) {
            return new WP_Error( 'invalid_person', 'Person not found' );
        }

        $record_data = [
            'person_id' => $person_id,
            'type'      => 'participation',
            'title'     => $args['title'] ?? 'Participant Enrollment',
            'status'    => $args['status'] ?? 'active',
        ];

        $record_id = $this->records->create_record( $record_data );

        if ( is_wp_error( $record_id ) ) {
            return $record_id;
        }

        // Optional metadata
        if ( ! empty( $args['context'] ) ) {
            $this->records->set_meta( $record_id, 'context', $args['context'] );
        }

        return $record_id;
    }

    /**
     * Check if a person is a participant
     *
     * @param int $person_id
     * @return bool
     */
    public function is_participant( $person_id ) {
        return $this->records->has_record_type(
            $person_id,
            'participation'
        );
    }

    /**
     * Get participants
     *
     * @param array $args
     * @return array
     */
    public function get_participants( array $args = [] ) {

        $defaults = [
            'context' => '',
            'status'  => 'active',
        ];

        $args = wp_parse_args( $args, $defaults );

        return $this->records->get_persons_by_record_type(
            'participation',
            $args
        );
    }

    public function get_list(array $args = [])
    {
        $defaults = [
            'type'   => 'child',
            'status' => null,
            'limit'  => 50,
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        // ambil persons berdasarkan meta participant_type
        $persons = $this->persons->get_by_meta(
            'participant_type',
            $args['type'],
            [
                'limit'  => $args['limit'],
                'offset' => $args['offset'],
            ]
        );

        if (empty($persons)) {
            return [];
        }

        $results = [];

        foreach ($persons as $person) {
            $status = $this->persons->get_meta(
                $person->id,
                'participant_status'
            );

            if ($args['status'] && $status !== $args['status']) {
                continue;
            }

            $results[] = [
                'id'     => (int) $person->id,
                'name'   => $person->full_name,
                'type'   => $args['type'],
                'status' => $status ?: 'active',
            ];
        }

        return $results;
    }

}
