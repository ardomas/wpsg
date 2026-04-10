<?php

if (!defined('ABSPATH')) exit;

class WPSG_IndicatorAttributeRelationsRepository {

    private $data;

    public function __construct() {
        $this->data = new WPSG_IndicatorAttributeRelationsData();
    }

    public function get($id, $include_deleted = false) {
        return $this->data->get($id, $include_deleted);
    }
    public function get_list($args = [], $include_deleted = false) {
        return $this->data->get_list($args, $include_deleted);
    }

    public function insert($data) {
        if( empty($data['site_id']) || empty($data['indicator_id']) || empty($data['attribute_id']) ){
            return false;
        }
        return $this->data->insert($data);
    }
    public function update($id, $data) {
        if( empty($id) || empty($data) ){
            return false;
        }
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
        if( empty($id) ){
            return false;
        }
        return $this->data->soft_delete($id, [ 'deleted_at' => current_time('mysql') ]);
    }
    public function delete_by_ids($ids) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        return $this->data->soft_delete_by_ids($ids);
    }
    public function delete_by_site($site_id) {
        if( empty($site_id) ){
            return false;
        }
        return $this->data->soft_delete_by_site($site_id);
    }
    public function delete_by_indicator($indicator_id) {
        if( empty($indicator_id) ){
            return false;
        }
        return $this->data->soft_delete_by_indicator($indicator_id);
    }
    public function delete_by_attribute($attribute_id) {
        if( empty($attribute_id) ){
            return false;
        }
        return $this->data->soft_delete_by_attribute($attribute_id);
    }

    /* ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore($id) {
        if( empty($id) ){
            return false;
        }
        return $this->data->restore($id, [ 'deleted_at' => null ]);
    }
    public function restore_by_ids($ids) {
        if( empty($ids) || !is_array($ids) ){
            return false;
        }
        return $this->data->restore_by_ids($ids);
    }
    public function restore_by_site($site_id) {
        if( empty($site_id) ){
            return false;
        }
        return $this->data->restore_by_site($site_id);
    }
    public function restore_by_indicator($indicator_id) {
        if( empty($indicator_id) ){
            return false;
        }
        return $this->data->restore_by_indicator($indicator_id);
    }
    public function restore_by_attribute($attribute_id) {
        if( empty($attribute_id) ){
            return false;
        }
        return $this->data->restore_by_attribute($attribute_id);
    }

    /* ---------------------------------------------------------
     * AREA BERBAHAYA, 
     * PASTIKAN UNTUK MENGGUNAKAN METODE INI DENGAN HATI-HATI YANG TERDAFTAR DI BAWAH INI,
     * KARENA PROSES INI MENGHAPUS DATA SECARA PERMANEN TANPA BISA DIKEMBALIKAN
     * --------------------------------------------------------- */

    /* ---------------------------------------------------------
     * METHOD DESTROY / DELETE PERMANENTLY
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * ---------------------------------------------------------
     * area ini berisiko menyebabkan kerusakan data yang tidak bisa dikembalikan jika terjadi kesalahan,
     * pastikan untuk melakukan validasi dan konfirmasi sebelum menggunakan metode ini,
     * terutama jika melibatkan penghapusan atau pemulihan data dalam jumlah besar atau berdasarkan kriteria yang luas
     * --------------------------------------------------------- */
    public function destroy($id) {
        if( empty($id) ){
            return false;
        }
        return $this->data->delete($id);
    }
    public function destroy_by_site($site_id) {
        if( empty($site_id) ){
            return false;
        }
        return $this->data->delete_by_site($site_id);
    }
    public function destroy_by_indicator($indicator_id) {
        if( empty($indicator_id) ){
            return false;
        }
        return $this->data->delete_by_indicator($indicator_id);
    }
    public function destroy_by_attribute($attribute_id) {
        if( empty($attribute_id) ){
            return false;
        }
        return $this->data->delete_by_attribute($attribute_id);
    }

}