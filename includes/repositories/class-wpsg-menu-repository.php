<?php
if (!defined('ABSPATH')) exit;

class WPSG_MenuRepository extends WPSG_RepositoryTreeBase {

    public function __construct() {
        parent::__construct();
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_MenuData();
    }

}