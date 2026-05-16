<?php
if (!defined('ABSPATH')) exit;

class WPSG_PersonsMetaRepository extends WPSG_RepositoryBase {

    public function __construct() {
        parent::__construct();
        $person_data = [];
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_PersonsBaseData();
    }

}