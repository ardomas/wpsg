<?php
if (!defined('ABSPATH')) exit;

class WPSG_IndicatorCategoriesRepository extends WPSG_RepositoryTreeBase {

    public function __construct() {
        parent::__construct();
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_IndicatorCategoriesData();
    }

}