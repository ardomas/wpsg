<?php
if (!defined('ABSPATH')) exit;

class WPSG_IndicatorCategoriesRepository {

    private $data;

    public function __construct() {
        $this->data = new WPSG_IndicatorCategoriesData();
    }

    public function blank_data(){
        return $this->data->blank_data();
    }

    public function get($id, $include_deleted = false) {
        return $this->data->get($id, $include_deleted);
    }
    public function get_list($args = [], $include_deleted = false) {
        return $this->data->get_list($args, $include_deleted);
    }
    public function get_parent($id){
        $category = $this->get($id);
        if ($category && !is_null($category['parent_id'])){
            return $this->get($category['parent_id']);
        }
        return null;
    }
    public function get_children($id, $include_deleted = false) {
        return $this->data->get_list([ 'parent_id' => $id ], $include_deleted);
    }

    public function get_tree($args = [], $include_deleted = false) {
        $categories = $this->get_list($args, $include_deleted);

        $lookup = [];

        // PASS 1: build lookup
        foreach ($categories as $category) {
            $category['children'] = [];
            $lookup[$category['id']] = $category;
        }

        $tree = [];

        // PASS 2: build hierarchy
        foreach ($lookup as $id => &$category) {
            if (!is_null($category['parent_id']) && isset($lookup[$category['parent_id']])) {
                $lookup[$category['parent_id']]['children'][] = &$category;
            } else {
                $tree[] = &$category;
            }
        }

        return $tree;
    }

    private function build_parent_map($categories) {
        $map = [];
        foreach ($categories as $category) {
            if (!is_null($category['parent_id'])) {
                $map[$category['parent_id']][] = $category;
            }
        }
        return $map;
    }
    private function collect_descendants($id, $map, &$result, &$visited = []) {
        if (!isset($map[$id])) return;
        foreach ($map[$id] as $child) {
            if( isset($visited[$child['id']]) ) continue;
            $visited[$child['id']] = true;
            $result[] = $child;
            $this->collect_descendants($child['id'], $map, $result, $visited );
        }
    }
    private function collect_descendants_by_ids($ids, $map, &$result) {
        $visited = [];
        foreach ($ids as $id) {
            if( isset($visited[$id]) ) {
                continue;
            }
            $visited[$id] = true;
            $this->collect_descendants($id, $map, $result, $visited );
        }
    }

    public function get_descendants($id, $include_deleted = false) {
        $descendants = [];
        $all = $this->get_list([], $include_deleted);
        $map = $this->build_parent_map($all);

        $this->collect_descendants($id, $map, $descendants);
        return $descendants;
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
        if( empty( $data['slug'] ) ){
            $data['slug'] = wpsg_generate_unique_slug( $this->data->table, $data['name'] );
        }
        if( empty( $data['sort_order'] ) && $is_new ){
            $data['sort_order'] = $this->data->get_last_order_number();
        }
        if( !$is_new ){
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
        $descendants = $this->get_descendants($id, true);
        $descendants_ids = array_map(function($item){ return $item['id']; }, $descendants);
        $ids_to_delete = array_unique(array_merge([$id], $descendants_ids));
        return $this->data->soft_delete_by_ids($ids_to_delete);
    }
    public function delete_by_ids($ids) {
        if( empty($ids) ) {
            return false;
        }
        $all = $this->get_list([], true);
        $map = $this->build_parent_map($all);
        $descendants = [];
        $this->collect_descendants_by_ids($ids, $map, $descendants);
        $new_ids = array_unique(array_merge($ids, array_map(function($item){ return $item['id']; }, $descendants)));
        return $this->data->soft_delete_by_ids($new_ids);
    }
    public function delete_by_site($site_id) {
        return $this->data->soft_delete_by_site($site_id);
    }
    /* ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore($id) {
        $descendants = $this->get_descendants($id, true);
        $descendants_ids = array_map(function($item){ return $item['id']; }, $descendants);
        $ids_to_restore = array_unique(array_merge([$id], $descendants_ids));
        return $this->data->restore_by_ids($ids_to_restore);
    }
    public function restore_by_ids($ids) {
        if( empty($ids) ) {
            return false;
        }
        $all = $this->get_list([], true);
        $map = $this->build_parent_map($all);
        $descendants = [];
        $this->collect_descendants_by_ids($ids, $map, $descendants);
        $new_ids = array_unique(array_merge($ids, array_map(function($item){ return $item['id']; }, $descendants)));
        return $this->data->restore_by_ids($new_ids);
    }
    public function restore_by_site($site_id) {
        return $this->data->restore_by_site($site_id);
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
        $descendants = $this->get_descendants($id, true);
        $descendants_ids = array_map(function($item){ return $item['id']; }, $descendants);
        $ids_to_delete = array_unique(array_merge([$id], $descendants_ids));
        return $this->data->delete_by_ids($ids_to_delete);
    }
    public function destroy_by_ids($ids) {
        if( empty($ids) ) {
            return false;
        }
        $all = $this->get_list([], true);
        $map = $this->build_parent_map($all);
        $descendants = [];
        $this->collect_descendants_by_ids($ids, $map, $descendants);
        $new_ids = array_unique(array_merge($ids, array_map(function($item){ return $item['id']; }, $descendants)));
        return $this->data->delete_by_ids($new_ids);
    }
    /* ---------------------------------------------------------
     * DESTROY BY SITE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function destroy_by_site($site_id) {
        return $this->data->delete_by_site($site_id);
    }

}