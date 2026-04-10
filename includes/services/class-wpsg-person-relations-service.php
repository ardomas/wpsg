<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Service layer for Person Relations.
 *
 * Orchestrates domain rules around person-to-person relationships.
 */
class WPSG_PersonRelationsService {

    protected WPSG_PersonsRepository $persons_repo;

    /** @var WPSG_PersonRelationsRepository */
    protected $relations_repo;

    public function __construct(
    ) {
        $this->persons_repo = new WPSG_PersonsRepository();
        $this->relations_repo = new WPSG_PersonRelationsRepository();
    }

    /**
     * Create a generic relation between two persons.
     *
     * @param int $from Person ID (subject)
     * @param int $to Person ID (object)
     * @param string $type Relation type (e.g., 'parent', 'sibling')
     * @return bool True on success, false on failure
     */
    public function create_relation(int $from, int $to, string $type): bool {
        if ( $from <= 0 || $to <= 0 || $from === $to ) {
            return false;
        }
        if (
            !$this->persons_repo->exists($from) ||
            !$this->persons_repo->exists($to)
        ) {
            return false;
        }

        return $this->relations_repo->create([
            'person_id'         => $from,
            'related_person_id' => $to,
            'relation_type'     => $type,
        ]);
    }

    public function activate_relation( $person_id, $related_person_id, $relation_type=null ){
        if( $rel_id = $this->relations_repo->get_relation_id( $person_id, $related_person_id, $relation_type ) ){
            return $this->relations_repo->activate_relation( $rel_id );
        } else {
            return $this->ensure_relation($person_id, $related_person_id, $relation_type);
        }
    }
    public function exists( $person_id, $related_person_id, $relation_type=null ){
        return $this->relations_repo->exists( $person_id, $related_person_id, $relation_type );
    }

    public function ensure_relation( $person_id, $related_person_id, $relation_type=null ){
        if( $this->exists( $person_id, $related_person_id, $relation_type ) ){
            return true;
        } else {
            return $this->create_relation( $person_id, $related_person_id, $relation_type );
        }
    }

    /* ---------------------------------------------------------
     * CORE DOMAIN ACTIONS
     * --------------------------------------------------------- */

    /**
     * Link a parent to a child.
     *
     * @param int $parent_id
     * @param int $child_id
     * @param array $args
     * @return int|false
     */
    public function add_parent_to_child(
        int $parent_id,
        int $child_id,
        array $args = []
    ) {

        if ( $parent_id <= 0 || $child_id <= 0 ) {
            return false;
        }

        if ( $parent_id === $child_id ) {
            return false; // cannot relate self
        }

        // Ensure both persons exist
        if (
            ! $this->persons_repo->exists( $parent_id ) ||
            ! $this->persons_repo->exists( $child_id )
        ) {
            return false;
        }

        $data = array_merge(
            [
                'person_id'         => $parent_id,
                'related_person_id' => $child_id,
                'relation_type'     => 'parent',
                'is_active'         => 1,
            ],
            $args
        );

        return $this->relations_repo->relate_once( $data );
    }

    /**
     * Remove parent-child relation (soft deactivate).
     */
    public function remove_parent_from_child(
        int $parent_id,
        int $child_id
    ) : bool {

        $relations = $this->relations_repo->get_relations_of_person(
            $parent_id,
            'parent',
            false
        );

        foreach ( $relations as $relation ) {
            if ( intval( $relation['related_person_id'] ) === $child_id ) {
                return $this->relations_repo->deactivate( $relation['id'] );
            }
        }

        return false;
    }
    public function remove_relation(
        int $person_id,
        int $related_person_id,
        string $relation_type = ''
    ) {
        if( $relation_id = $this->relations_repo->get_relation_id( $person_id, $related_person_id, $relation_type ) ){
            return $this->relations_repo->deactivate( $relation_id );
        }
    }

    public function remove_relations_by_type(
        int $person_id = 0,
        string $relation_type = ''
    ) {
        $this->relations_repo->remove_relations_by_type( $person_id, $relation_type );
    }

    public function is_related( $person_id, $relation_person_id, $relation_type=null ){
        return $this->relations_repo->is_related( $person_id, $relation_person_id, $relation_type );
    }

    /* ---------------------------------------------------------
     * READ HELPERS
     * --------------------------------------------------------- */

    /**
     * Get all children of a parent.
     */
    public function get_children_of_parent(
        int $parent_id,
        bool $active_only = true
    ) : array {
        return $this->relations_repo->get_children( $parent_id, $active_only );
    }

    /**
     * Get parents of a child.
     */
    public function get_parents_of_child(
        int $child_id,
        bool $active_only = true
    ) : array {
        return $this->relations_repo->get_parents( $child_id, $active_only );
    }

    public function get_related_persons_by_type( int $person_id, string $relation_type ): array {
        return $this->relations_repo->get_relations_of_person( $person_id, $relation_type );
/*
        $results = [];
        $obj_data = $this->relations_repo->get_relations_of_person( $person_id, $relation_type );
        if( !$obj_data || !is_array($obj_data) ){
            return [];
        } else {
            foreach( $obj_data as $relation ){
                $related_person = $this->persons_repo->get( $relation['related_person_id'] );
                if( $related_person ){
                    $results[] = $related_person;
                }
            }
            // $obj_data = $results;
        }
        return $results;
        // $this->relations_repo->get_relations_of_person( $person_id, $relation_type );
*/
    }

    public function get_related_persons_by_types( int $person_id, array $relation_types ): array {
        return $this->relations_repo->get_related_persons_by_types( $person_id, $relation_types );
        /*
        $results = [];
        foreach( $relation_types as $relation_type ){
            // $all_related = $this->get_related_persons_by_type( $person_id, $relation_type );
            // $this->relations_repo->get_relations_of_person( $person_id, $relation_type );
            $results[ $relation_type ] = $this->get_related_persons_by_type( $person_id, $relation_type );
        }
        // return $this->relations_repo->get_related_persons( $person_id, $relation_type );
        return $results;
        */
    }

    /* ---------------------------------------------------------
     * FUTURE EXTENSION POINTS (INTENTIONAL)
     * --------------------------------------------------------- */

    /**
     * Placeholder for:
     * - guardian role
     * - step-parent
     * - custody logic
     */
    // public function add_guardian() {}

    /**
     * Placeholder for:
     * - access rules
     * - report visibility
     */
    // public function can_view_child_report() {}
}
