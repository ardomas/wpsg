<?php
if (!defined('ABSPATH')) exit;

class WPSG_PersonActivityDetailRepository extends WPSG_RepositoryBase {

    public $dbdata;
    
    public function __construct() {
        parent::__construct();
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_PersonActivityDetailData();
    }

}