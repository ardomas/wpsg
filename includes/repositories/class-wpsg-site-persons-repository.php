<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_SitePersonsRepository {

    /** @var WPSG_SitePersonsData */
    protected $dbdata;

    public function __construct() {
        $this->dbdata = new WPSG_SitePersonsData();
    }

    /* ---------------------------------------------------------
     * BASIC OPERATIONS
     * --------------------------------------------------------- */

    public function add_person(
        int $site_id,
        int $person_id,
        string $role,
        string $status = 'active'
    ): bool {
        $data = [
            'site_id'   => $site_id,
            'person_id' => $person_id,
            'role'      => $role,
            'status'    => $status,
        ];
        return $this->dbdata->insert( $data );
    }

    public function update(
        int $site_id,
        int $person_id,
        string $role,
        string $status
    ): bool {
        $data = [
            'site_id'   => $site_id,
            'person_id' => $person_id,
            'role'      => $role,
            'status'    => $status,
        ];
        $init_data = $this->dbdata->get_by_site_person_role( $site_id, $person_id, $role );
        if( $init_data ){
            if( isset( $init_data['id'] ) && !is_null($init_data['id']) ){
                if( $this->dbdata->update( $init_data['id'], $data ) ){
                    return true;
                }
            }
        }
        return false;
    }

    public function delete( $person_id ){
        return $this->dbdata->soft_delete($person_id);
    }

    public function exists(
        int $site_id,
        int $person_id,
        string $role
    ): bool {
        return (bool) $this->dbdata->get_by_site_person_role(
            $site_id,
            $person_id,
            $role
        );
    }

    public function ensure_link(
        int $site_id,
        int $person_id,
        string $role,
        string $status = 'active'
    ): bool {
        if ( ! $this->exists( $site_id, $person_id, $role ) ) {
            return $this->add_person(
                $site_id,
                $person_id,
                $role,
                $status
            );
        }
        return true;
    }

    public function get_persons_by_site(
        int $site_id,
        array $args = []
    ): array {
        return $this->dbdata->get_by_site( $site_id, $args );
    }

    public function get_sites_by_person(
        int $person_id
    ): array {
        return $this->dbdata->get_by_person( $person_id );
    }

    public function get_by_site_person(
        int $site_id,
        int $person_id
    ): array {
        return $this->dbdata->get_by_site_person( $site_id, $person_id );
    }

    public function delete_by_site_person( $site_id, $person_id ){
        return $this->dbdata->delete_by_site_person( $site_id, $person_id );
    }

    public function delete_by_person( $person_id ){
        return $this->dbdata->delete_by_person( $person_id );
    }

}
