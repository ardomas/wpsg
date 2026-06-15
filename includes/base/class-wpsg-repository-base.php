<?php
if (!defined('ABSPATH')) exit;

class WPSG_RepositoryBase {

    protected object $dbdata;

    public function __construct() {
        // do nothing
        // $this->dbdata defined by decendant
    }

    public function blank_data() {
        if( $this->dbdata==null ) return [];
        return $this->dbdata->blank_data();
    }
    public function get(int $id, bool $include_deleted = false) {
        if( $this->dbdata==null ) return [];
        return $this->dbdata->get($id, $include_deleted);
    }
    public function get_list($args = [], bool $include_deleted = false) {
        if( $this->dbdata==null ) return [];
        return $this->dbdata->get_list($args, $include_deleted);
    }
    public function get_by_fields(array $args=[], bool $include_deleted = false) {
        if( $this->dbdata==null ) return [];
        return $this->dbdata->get_by_fields($args, $include_deleted);
    }
    public function get_count(array $args=[], bool $include_deleted = false) {
        if( $this->dbdata==null ) return [];
        return $this->dbdata->get_count($args, $include_deleted);
    }

    private function treat_special_columns(array $data){
        if( $data==[] ) return [];
        $columns = $this->dbdata->columns_assoc;
        if( isset( $columns['site_id'] ) ){
            if( isset( $data['site_id'] ) && !empty( $data['site_id'] ) ){
                // do nothing
            } else {
                $data['site_id'] = wpsg_get_network_id();
            }
        }
        return $data;
    }

    public function insert(array $data) {
        if( $this->dbdata==null ) return 0;
        $data = $this->treat_special_columns($data);
        return $this->dbdata->insert($data);
    }

    public function update(int $id, array $data) {
        if( $this->dbdata==null ) return 0;
        $data = $this->treat_special_columns($data);
        return $this->dbdata->update($id, $data);
    }

    /* ---------------------------------------------------------
     * SAVE METHODS
     * area aman, karena metode ini bisa digunakan untuk menyimpan data baru maupun memperbarui
     * data yang sudah ada, tergantung pada apakah $data['id'] disertakan atau tidak,
     * jika disertakan apakah null atau tidak
     * --------------------------------------------------------- */
    public function save(array $data) {
        if( $this->dbdata==null ) return 0;
        $is_new = true;
        if( isset( $data['id'] ) && !empty( trim( $data['id'] ) ) ){
            $is_new = false;
        }
        $data = $this->treat_special_columns($data);
        if( $is_new ){
            return $this->dbdata->insert($data);
        } else {
            return $this->dbdata->update($data['id'], $data);
        }
    }

    public function ensure_data($data=[]) {
        $data = $this->treat_special_columns($data);
        return $this->dbdata->_ensure_data($data);
    }

    /* ---------------------------------------------------------
     * SOFT DELETE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode soft_delete, pada repository menggunakan metode delete
     * --------------------------------------------------------- */
    public function delete(int $id) {
        if( $this->dbdata==null ) return false;
        return $this->dbdata->soft_delete($id);
    }
    public function delete_by_ids(array $ids){
        if( $this->dbdata==null ) return false;
        return $this->dbdata->soft_delete_by_ids( $ids );
    }
    public function delete_by_site(int $site_id){
        if( $this->dbdata==null ) return false;
        return $this->dbdata->soft_delete_by_site( $site_id );
    }

    /** ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore( int $id ) {
        if( $this->dbdata==null ) return 0;
        return $this->dbdata->restore($id, [ 'deleted_at' => null ]);
    }
    public function restore_by_ids( array $ids ){
        if( $this->dbdata==null ) return 0;
        return $this->dbdata->restore_by_ids( $ids );
    }
    public function restore_by_site( int $site_id ){
        if( $this->dbdata==null ) return 0;
        return $this->dbdata->restore_by_site_id( $site_id );
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
    public function destroy( int $id ) {
        if( $this->dbdata==null ) return false;
        return $this->dbdata->delete( $id );
    }
    public function destroy_by_ids(array $ids){
        if( $this->dbdata==null ) return false;
        return $this->dbdata->delete_by_ids($ids);
    }
    /* ---------------------------------------------------------
     * DESTROY BY SITE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini menghapus data secara permanen tanpa bisa dikembalikan
     * ---------------------------------------------------------
     * pada data menggunakan metode delete_by_site, pada repository menggunakan metode destroy_by_site
     * --------------------------------------------------------- */
    public function destroy_by_site( int $site_id ) {
        if( $this->dbdata==null ) return false;
        return $this->dbdata->delete_by_site($site_id);
    }
}