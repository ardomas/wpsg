<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Service layer for Persons.
 *
 * Orchestrates domain rules around person entities.
 */
class WPSG_PersonsService {

    protected WPSG_PersonsRepository $persons_repository;

    public function __construct(
        // WPSG_PersonsRepository $persons_repository
    ) {
        $this->persons_repository = new WPSG_PersonsRepository;
        // $persons_repository;
    }

    public function set_default_data() {
        return $this->persons_repository->set_default_data();
    }

    /**
     * Ambil data person berdasarkan ID
     */
    public function get_person(int $person_id): ?array {
        return $this->persons_repository->get($person_id);
    }
    public function get_by_ids( array $ids ): ?array { 
        return $this->persons_repository->get_by_ids($ids);
    }
    public function get_by_user_id( $user_id ) {
        return $this->persons_repository->get_by_user_id( $user_id );
    }

    /**
     * Validasi apakah person ada
     */
    public function person_exists(int $person_id): bool {
        return $this->persons_repository->exists($person_id);
    }

    /**
     * Ambil person berdasarkan WP User ID
     */
    public function get_person_by_user(int $user_id): ?array {
        return $this->persons_repository->find_by_user_id($user_id);
    }

    /**
     * Buat person baru
     */
    public function insert(array $data): int {
        // validasi minimal bisa ditambahkan nanti
        return $this->persons_repository->set($data);
    }

    /**
     * Update person
     */
    public function update(int $person_id, array $data): int {
        if (!$this->person_exists($person_id)) {
            return false;
        } else {
            $data['id'] = $person_id;
        }

        // $result = $this->persons_repository->set($data);
        // print_r( $result );
        // die( '$this->persons_repository->set($data)' );

        return $this->persons_repository->set($data);

    }
    public function save( array $data ): int {
        $retval = false;
        if( key_exists( 'id', $data ) ){
            $ref_id = $data['id'];
            $new_data = [];
            foreach( $data as $k=>$v ){
                if( $k!='id' ){
                    $new_data[$k] = $v;
                }
            }
            $retval = $this->update( $ref_id, $new_data );
        } else {
            $retval = $this->insert( $data );
        }
        return $retval;
    }

    public function delete(int $person_id){
        if( !$this->person_exists($person_id) ){
            return false;
        }
        return $this->persons_repository->delete( $person_id );
    }

    public function get_by_meta( $meta_key, $meta_value, $args = [] )
    {
        $defaults = [
            'limit'  => 20,
            'offset' => 0,
            'orderby'=> 'p.id',
            'order'  => 'ASC',
        ];

        $args = wp_parse_args( $args, $defaults );

        return $this->persons_repository->get_by_meta(
            $meta_key,
            $meta_value,
            $args
        );
    }

    public function get_all_meta( $person_id ) {
        return $this->persons_repository->get_all_meta( $person_id );
    }

}
