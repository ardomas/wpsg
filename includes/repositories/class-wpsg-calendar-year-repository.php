<?php
if (!defined('ABSPATH')) exit;

class WPSG_CalendarYearRepository extends WPSG_RepositoryBase {

    public function __construct() {
        parent::__construct();
        $this->dbdata = new WPSG_CalendarYearData();
    }

}