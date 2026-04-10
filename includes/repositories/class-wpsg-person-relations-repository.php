<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Repository layer for Person Relations.
 *
 * Acts as an abstraction between business logic (Service)
 * and low-level data access (Data).
 */
class WPSG_PersonRelationsRepository {

    /** @var WPSG_PersonRelationsData */
    private $data;

    /**
     * Constructor
     */
    public function __construct() {
        $this->data = WPSG_PersonRelationsData::get_instance();
    }

    /**
     * Called on plugin activation
     */
    public function activate() {
        $this->data->activate();
    }

    /* ---------------------------------------------------------
     * BASIC WRAPPERS
     * --------------------------------------------------------- */

    public function get( int $id ) {
        return $this->data->get( $id );
    }

    public function create( array $data ) {
        return $this->data->insert( $data );
    }

    public function delete( int $id ) : bool {
        return $this->data->delete( $id );
    }

    public function deactivate( int $id ) : bool {
        return $this->data->deactivate( $id );
    }

    public function activate_relation( int $id ) : bool {
        return $this->data->activate_relation( $id );
    }

    /* ---------------------------------------------------------
     * QUERY HELPERS
     * --------------------------------------------------------- */

    /**
     * Get all relations for a person (as subject)
     */
    public function get_relations_of_person(
        int $person_id,
        string $relation_type = '',
        bool $active_only = true
    ) : array {
        return $this->data->get_relations_by_person(
            $person_id,
            $relation_type,
            $active_only
        );
    }

    /**
     * Get all relations where person is related object
     */
    public function get_relations_to_person(
        int $person_id,
        string $relation_type = '',
        bool $active_only = true
    ) : array {
        return $this->data->get_relations_to_person(
            $person_id,
            $relation_type,
            $active_only
        );
    }

    public function get_related_persons_by_type( int $person_id, string $relation_type ): array {
        $results = [];
        $obj_data = $this->get_relations_of_person( $person_id, $relation_type );
        if( !$obj_data || !is_array($obj_data) ){
            return [];
        } else {
            foreach( $obj_data as $relation ){
                $related_person = $this->get_all_by_related_person( $relation['related_person_id'] );
                if( $related_person && $related_person!=[] ){
                    foreach( $related_person as $person ){
                        $results[] = $person;
                    }
                    // $results[] = $related_person[0];
                }
            }
        }
        return $results;
    }

    public function get_related_persons_by_types( int $person_id, array $relation_types ): array {
        $results = [];
        foreach( $relation_types as $relation_type ){
            $results[ $relation_type ] = $this->get_related_persons_by_type( $person_id, $relation_type );
        }
        return $results;
    }

    public function get_all_by_related_person(
        int $person_id,
        bool $active_only = true
    ): array {
        return $this->data->get_all_by_related_person(
            $person_id,
            $active_only
        );
    }

    public function get_by_persons_relations(
        int $person_id,
        int $related_person_id,
        string $relation_type = ''
    ): ?array {
        return $this->data->get_by_persons_relation(
            $person_id,
            $related_person_id,
            $relation_type
        );
    }

    public function exists(
        int $person_id,
        int $related_person_id,
        string $relation_type = ''
    ): bool {
        return (bool) $this->get_by_persons_relations(
            $person_id,
            $related_person_id,
            $relation_type
        );
    }

    /**
     * Get children of a parent
     */
    public function get_children( int $parent_id, bool $active_only = true ) : array {
        return $this->get_relations_of_person(
            $parent_id,
            'parent',
            $active_only
        );
    }

    /**
     * Get parents of a child
     */
    public function get_parents( int $child_id, bool $active_only = true ) : array {
        return $this->get_relations_to_person(
            $child_id,
            'parent',
            $active_only
        );
    }

    public function get_relations_by_type( int $person_id, string $relation_type='', $active_only=true ) {
        return $this->data->get_relations_by_type( $person_id, $relation_type, $active_only );
    }

    public function remove_relations_by_type(
        int $person_id = 0,
        string $relation_type = ''
    ) {
        $init_data = $this->get_relations_by_type( $person_id, $relation_type );
        foreach( $init_data as $item ){
            $this->deactivate( $item['id'] );
        }
    }

    /* ---------------------------------------------------------
     * DOMAIN HELPERS (SAFE)
     * --------------------------------------------------------- */

    /**
     * Check if two persons are related
     */
    public function is_related(
        int $person_id,
        int $related_person_id,
        string $relation_type = ''
    ) : bool {
        return $this->data->relation_exists(
            $person_id,
            $related_person_id,
            $relation_type
        ) ?? false;
    }

    public function get_relation_id(
        int $person_id,
        int $related_person_id,
        string $relation_type = ''
    ):int {
        return $this->data->relation_exists(
            $person_id,
            $related_person_id,
            $relation_type
        );
    }
    /**
     * Create relation only if not exists
     */
    public function relate_once( array $data ) {
        $exists = $this->is_related(
            $data['person_id'],
            $data['related_person_id'],
            $data['relation_type'] ?? ''
        );

        if ( $exists ) {
            return false;
        }

        return $this->create( $data );
    }

}
