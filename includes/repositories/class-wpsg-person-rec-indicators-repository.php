<?php
if (!defined('ABSPATH')) exit;

class WPSG_PersonRecIndicatorsRepository extends WPSG_RepositoryTreeBase {

    public $dbdata;
    
    public function __construct() {
        parent::__construct();
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_PersonRecIndicatorsData();
    }

    public function publish_data($id) {
        $this->dbdata->publish_data($id);
    }

    public function unpublish_data($id) {
        $this->dbdata->unpublish_data($id);
    }

}