<?php
if (!defined('ABSPATH')) exit;

class WPSG_IndicatorCategoriesService {

    private $repo;

    public function __construct() {
        $this->repo = new WPSG_IndicatorCategoriesRepository();
    }

    /* ---------------------------------------------------------
     * GET BLANK DATA
     * --------------------------------------------------------- */
    public function blank_data(){
        return $this->repo->blank_data();
    }

    /* ---------------------------------------------------------
     * GET DATA
     * --------------------------------------------------------- */
    public function get($id,$include_deleted=false) {
        return $this->repo->get($id,$include_deleted);
    }
    public function get_list(array $args=[],$include_deleted=false){
        return $this->repo->get_list( $args, $include_deleted );
    }
    public function get_parent($id){
        return $this->repo->get_parent($id);
    }
    public function get_children($id, $include_deleted = false) {
        return $this->repo->get_children( $id, $include_deleted );
    }
    public function get_tree( array $args, $include_deleted = false ){
        return $this->repo->get_tree( $args, $include_deleted );
    }
    public function get_descendants( $id, $include_deleted = false ){
        return $this->repo->get_descendants( $id, $include_deleted );
    }

    /* ---------------------------------------------------------
     * SAVE DATA
     * menggunakan satu perintah save saja,
     * insert dan update ada dalam repository
     * --------------------------------------------------------- */
    public function save( $data ){
        return $this->repo->save( $data );
    }

    /* ---------------------------------------------------------
     * DELETE DATA
     * ---------------------------------------------------------
     * (area aman)
     * soft delete data, data tidak terhapus, hanya marked deleted
     * 
     * @param int $id ID dari data yang ingin dihapus
     * @return bool Hasil dari proses hapus data
     * --------------------------------------------------------- */
    public function delete($id){
        return $this->repo->delete($id);
    }
    public function delete_by_ids( array $ids) {
        return $this->repo->delete_by_ids( $ids );
    }
    public function delete_by_site_id( int $site_id ) {
        return $this->repo->delete_by_site( $site_id );
    }

    /* ---------------------------------------------------------
     * RESTORE DATA
     * ---------------------------------------------------------
     * (area aman)
     * restore data yang sudah dihapus
     * 
     * @param int $id ID dari data yang ingin dihapus
     * @return bool Hasil dari proses hapus data
     * --------------------------------------------------------- */
    public function restore($id){
        return $this->repo->restore($id);
    }
    public function restore_by_ids( array $ids ) {
        return $this->repo->restore_by_ids( $ids );
    }
    public function restore_by_site_id( int $site_id ) {
        return $this->repo->restore_by_site( $site_id );
    }

    /* ---------------------------------------------------------
     * DESTROY DATA
     * ---------------------------------------------------------
     * (area berbahaya)
     * Menghapus data secara permanen tanpa bisa dikembalikan
     * 
     * @param int $id ID dari data yang ingin dihapus
     * @return bool Hasil dari proses hapus data
     * --------------------------------------------------------- */
    public function destroy( int $id ){
        return $this->repo->destroy( $id );
    }
    public function destroy_by_ids( array $ids ){
        return $this->repo->destroy_by_ids( $ids );
    }
    public function destroy_by_site( int $site_id ) {
        return $this->repo->destroy_by_site( $site_id );
    }

}