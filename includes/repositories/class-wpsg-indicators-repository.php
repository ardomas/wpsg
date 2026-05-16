<?php
if (!defined('ABSPATH')) exit;

class WPSG_IndicatorsRepository extends WPSG_RepositoryBase {

    public function __construct() {
        parent::__construct();
        $this->dbcnf_assignment();
    }

    public function dbcnf_assignment(){
        $this->dbdata = new WPSG_IndicatorsData();
    }

    /* ---------------------------------------------------------
     * DESTROY BY CATEGORY METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * ---------------------------------------------------------
     * pada data menggunakan metode delete_by_category, pada repository menggunakan metode destroy_by_category
     * --------------------------------------------------------- */
    public function destroy_by_category($category_id) {
        return $this->dbdata->delete_by_category($category_id);
    }

}