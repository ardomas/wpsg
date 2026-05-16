<?php
if (!defined('ABSPATH')) exit;

abstract class WPSG_ServiceBase {

    protected object $repo;

    public function __construct() {
        // $this->repo = null;
    }

    public function repo_assignment(object $repo) {
        $this->repo = $repo;
    }

    public function blank_data() {
        if( $this->repo==null ) return [];
        return $this->repo->blank_data();
    }

    public function get(int $id, $include_deleted = false) {
        if( $this->repo==null ) return [];
        if( $id==0 ){
            $data = $this->repo->blank_data();
        } else {
            $data = $this->repo->get($id, $include_deleted);
            if( $data==[] ){
                $data = $this->repo->blank_data();
            }
        }
        return $data;
    }

    public function get_list($args = [], $include_deleted = false) {
        if( $this->repo==null ) return [];
        return $this->repo->get_list($args, $include_deleted);
    }

    public function get_count($args = [], $include_deleted = false) {
        if( $this->repo==null ) return [];
        return $this->repo->get_count($args, $include_deleted);
    }

    public function get_by_fields(array $args=[], $include_deleted = false) {
        if( $this->repo==null ) return [];
        return $this->repo->get_by_fields($args, $include_deleted);
    }

    /* ---------------------------------------------------------
     * SAVE METHODS
     * area aman, karena metode ini bisa digunakan untuk menyimpan data baru maupun memperbarui
     * data yang sudah ada, tergantung pada apakah $data['id'] disertakan atau tidak,
     * jika disertakan apakah null atau tidak
     * --------------------------------------------------------- */
    public function save(array $data) {
        if( $this->repo==null ) return 0;
        return $this->repo->save($data);
    }
    public function ensure_data(array $data=[]) {
        if( $this->repo==null ) return [];
        return $this->repo->ensure_data($data);
    }

    /* ---------------------------------------------------------
     * SOFT DELETE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode soft_delete, pada repository menggunakan metode delete
     * --------------------------------------------------------- */
    public function delete(int $id) {
        if( $this->repo==null ) return false;
        return $this->repo->delete($id);
    }
    public function delete_by_ids(array $ids) {
        if( $this->repo==null ) return false;
        return $this->repo->delete_by_ids($ids);
    }
    public function delete_by_site(int $site_id) {
        if( $this->repo==null ) return false;
        return $this->repo->delete_by_site($site_id);
    }

    /** ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore(int $id) {
        if( $this->repo==null ) return 0;
        return $this->repo->restore($id);
    }
    public function restore_by_ids(array $ids) {
        if( $this->repo==null ) return 0;
        return $this->repo->restore_by_ids($ids);
    }
    public function restore_by_site(int $site_id) {
        if( $this->repo==null ) return 0;
        return $this->repo->restore_by_site($site_id);
    }

    /* ---------------------------------------------------------
     * DESTROY BY SITE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini menghapus data secara permanen tanpa bisa dikembalikan
     * ---------------------------------------------------------
     * pada data menggunakan metode delete_by_site, pada repository menggunakan metode destroy_by_site
     * --------------------------------------------------------- */
    public function destroy(int $id) {
        if( $this->repo==null ) return false;
        return $this->repo->destroy($id);
    }
    public function destroy_by_ids(array $ids) {
        if( $this->repo==null ) return false;
        return $this->repo->destroy_by_ids($ids);
    }
    public function destroy_by_site(int $site_id) {
        if( $this->repo==null ) return false;
        return $this->repo->destroy_by_site($site_id);
    }

}

?>