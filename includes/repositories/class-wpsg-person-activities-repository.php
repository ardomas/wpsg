<?php
if (!defined('ABSPATH')) exit;

class WPSG_PersonActivitiesRepository extends WPSG_RepositoryBase {

    public object $dbdata;
    
    public function __construct() {
        parent::__construct();
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_PersonActivitiesData();
    }

    public function publish_data(int $id) {
        $this->dbdata->publish_data($id);
    }

    public function unpublish_data(int $id) {
        $this->dbdata->unpublish_data($id);
    }

}