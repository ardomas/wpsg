<?php
if (!defined('ABSPATH')) exit;

class WPSG_DailyActivitiesRepository {

    private $data;

    public function __construct() {
        $this->data = new WPSG_DailyActivitiesData();
    }

    public function blank_data() {
        return $this->data->blank_data();
    }
    public function get($id, $include_deleted = false) {
        return $this->data->get($id, $include_deleted);
    }
    public function get_list($args = [], $include_deleted = false) {
        return $this->data->get_list($args, $include_deleted);
    }

    public function insert($data) {
        return $this->data->insert($data);
    }
    public function update($id, $data) {
        return $this->data->update($id, $data);
    }

    /* ---------------------------------------------------------
     * SAVE METHODS
     * area aman, karena metode ini bisa digunakan untuk menyimpan data baru maupun memperbarui
     * data yang sudah ada, tergantung pada apakah $data['id'] disertakan atau tidak,
     * jika disertakan apakah null atau tidak
     * --------------------------------------------------------- */
    public function save($data) {
        $is_new = true;
        if( isset( $data['id'] ) && !empty( trim( $data['id'] ) ) ){
            $is_new = false;
        }
        if( !isset( $data['site_id'] ) || empty( $data['site_id'] ) ){
            $data['site_id'] = wpsg_get_network_id();
        }
        if( empty( $data['sort_order'] ) && $is_new ){
            $data['sort_order'] = $this->data->get_last_order_number('sort_order');
        }
        if( $is_new ){
            return $this->data->insert($data);
        } else {
            return $this->data->update($data['id'], $data);
        }
    }

    /* ---------------------------------------------------------
     * SOFT DELETE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode soft_delete, pada repository menggunakan metode delete
     * --------------------------------------------------------- */
    public function delete($id) {
        return $this->data->soft_delete($id, [ 'deleted_at' => current_time('mysql') ]);
    }

    /** ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore($id) {
        return $this->data->restore($id, [ 'deleted_at' => null ]);
    }


    /* ---------------------------------------------------------
     * AREA BERBAHAYA, PASTIKAN UNTUK MENGGUNAKAN METODE INI DENGAN HATI-HATI,
     * KARENA PROSES INI MENGHAPUS DATA SECARA PERMANEN TANPA BISA DIKEMBALIKAN
     * --------------------------------------------------------- */

    /* ---------------------------------------------------------
     * DESTROY METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini menghapus data secara permanen tanpa bisa dikembalikan
     * ---------------------------------------------------------
     * pada data menggunakan metode delete, pada repository menggunakan metode destroy
     * --------------------------------------------------------- */
    public function destroy($id) {
        return $this->data->delete($id);
    }
    /* ---------------------------------------------------------
     * DESTROY BY SITE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini menghapus data secara permanen tanpa bisa dikembalikan
     * ---------------------------------------------------------
     * pada data menggunakan metode delete_by_site, pada repository menggunakan metode destroy_by_site
     * --------------------------------------------------------- */
    public function destroy_by_site($site_id) {
        return $this->data->delete_by_site($site_id);
    }


}