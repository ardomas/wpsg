<?php

if (!defined('ABSPATH')) exit;

class WPSG_BaseConfigService extends WPSG_ServiceBase {

    public function __construct() {
        parent::__construct();
        $this->repo = new WPSG_BaseConfigRepository();
    }

    public function get_by_meta_key(string $meta_key) {
        return $this->repo->get_by_meta_key($meta_key);
    }

    public function get_meta_value(string $meta_key) {
        return $this->repo->get_meta_value($meta_key);
    }

}