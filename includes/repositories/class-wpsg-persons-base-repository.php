<?php
if (!defined('ABSPATH')) exit;

class WPSG_PersonsBaseRepository extends WPSG_RepositoryBase {

    private array $person_data;

    public function __construct() {
        parent::__construct();
        $this->person_data = [];
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_PersonsBaseData();
    }

    public function get( int $id, $include_deleted = false ) {
        if( $this->person_data!=[] && $this->person_data['id']!=$id ){
            $this->person_data = $this->dbdata->get( $id, $include_deleted );
        }
        return $this->person_data;
    }

    public function get_user_id( int $id, $include_deleted = false ) {
        if( $this->person_data!=[] && $this->person_data['id']!=$id ){
            $this->person_data = $this->dbdata->get( $id, $include_deleted );
        }
        return $this->person_data['user_id'] ?? null;
    }

    public function get_by_email( string $email, $include_deleted = false ) {
        return $this->dbdata->get_by_fields( ['email'=>$email], $include_deleted );
    }

    public function get_by_phone( string $phone, $include_deleted = false ) {
        return $this->dbdata->get_by_fields( ['phone'=>$phone], $include_deleted );
    }

}