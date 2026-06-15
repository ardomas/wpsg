<?php
if (!defined('ABSPATH')) exit;

class WPSG_PersonRecIndicatorDetailRepository extends WPSG_RepositoryBase {

    public object $dbdata;

    public function __construct() {
        parent::__construct();
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_PersonRecIndicatorDetailData();
    }

}