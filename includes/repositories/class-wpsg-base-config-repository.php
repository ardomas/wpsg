<?php
if (!defined('ABSPATH')) exit;

class WPSG_BaseConfigRepository extends WPSG_RepositoryBase {

    public function __construct() {
        parent::__construct();
        $this->dbdata = new WPSG_BaseConfigData();
    }

    public function get_by_meta_key(string $meta_key) {
        $init_data = $this->dbdata->get_list(['meta_key'=>$meta_key]);
        if( $init_data!=[] ){
            return $init_data[0];
        }
        return null;
    }

    public function get_meta_value(string $meta_key) {
        $init_data = $this->get_by_meta_key($meta_key);
        if( $init_data!=[] ){
            return $init_data['meta_value'];
        }
        return null;
    }   

}