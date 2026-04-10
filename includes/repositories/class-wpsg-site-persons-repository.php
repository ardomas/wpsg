<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_SitePersonsRepository {

    /** @var WPSG_SitePersonsData */
    protected $data;

    public function __construct() {
        $this->data = new WPSG_SitePersonsData();
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
        return $this->data->insert([
            'site_id'   => $site_id,
            'person_id' => $person_id,
            'role'      => $role,
            'status'    => $status,
        ]);
    }

    public function update(
        int $site_id,
        int $person_id,
        string $role,
        string $status
    ): bool {
        return $this->data->update(
            $site_id,
            $person_id,
            $role,
            $status
        );
    }

    public function exists(
        int $site_id,
        int $person_id,
        string $role
    ): bool {
        return (bool) $this->data->get_by_site_person_role(
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
        return $this->data->get_by_site( $site_id, $args );
    }

    public function get_sites_by_person(
        int $person_id
    ): array {
        return $this->data->get_by_person( $person_id );
    }

    public function get_by_site_person(
        int $site_id,
        int $person_id
    ): array {
        return $this->data->get_by_site_person( $site_id, $person_id );
    }
}
