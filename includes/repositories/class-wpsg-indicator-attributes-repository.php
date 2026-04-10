<?php
if (!defined('ABSPATH')) exit;

class WPSG_IndicatorAttributesRepository {

    private $data;

    public function __construct() {
        $this->data = new WPSG_IndicatorAttributesData();
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
        if( !empty($data['id']) ){
            return $this->data->update($data['id'], $data);
        }
        return $this->data->insert($data);
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

    /* ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore($id) {
        return $this->data->restore($id, [ 'deleted_at' => null ]);
    }

    /* ---------------------------------------------------------
     * AREA BERBAHAYA, 
     * PASTIKAN UNTUK MENGGUNAKAN METODE INI DENGAN HATI-HATI YANG TERDAFTAR DI BAWAH INI,
     * KARENA PROSES INI MENGHAPUS DATA SECARA PERMANEN TANPA BISA DIKEMBALIKAN
     * --------------------------------------------------------- */

    /* ---------------------------------------------------------
     * DESTROY METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * ---------------------------------------------------------
     * pada data menggunakan metode delete, pada repository menggunakan metode destroy
     * --------------------------------------------------------- */
    public function destroy($id) {
        return $this->data->delete($id);
    }

}