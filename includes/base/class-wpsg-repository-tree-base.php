<?php
if (!defined('ABSPATH')) exit;

class WPSG_RepositoryTreeBase extends WPSG_RepositoryBase {

    public string $parent_field;

    public function __construct() {
        parent::__construct();
        $this->assign_parent_field();
    }

    public function assign_parent_field( $field_name='parent_id' ){
        $this->parent_field = $field_name;
    }

    public function get_parent(int $id){
        $data = $this->get($id);
        if ($data && !is_null( $this->parent_field )){
            return $this->get( $this->parent_field );
        }
        return null;
    }

    public function get_children(int $id, $include_deleted = false) {
        return $this->dbdata->get_list([ $this->parent_field => $id ], $include_deleted);
    }

    public function get_tree($args = [], $include_deleted = false) {
        if( $this->dbdata==null ) return [];

        $data = $this->get_list($args, $include_deleted);

        $lookup = [];
        // PASS 1: build lookup
        foreach ($data as $item) {
            $data['children'] = [];
            $lookup[$item['id']] = $item;
        }

        $tree = [];
        // PASS 2: build hierarchy
        foreach ($lookup as $id => &$item) {
            if (!is_null($item[ $this->parent_field ]) && isset($lookup[$item[ $this->parent_field ]])) {
                $lookup[$item[ $this->parent_field ]]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }

        return $tree;
    }

    private function build_parent_map($data=[]) {
        $map = [];
        foreach ($data as $item) {
            if (!is_null($item[ $this->parent_field ])) {
                $map[$item[ $this->parent_field ]][] = $item;
            }
        }
        return $map;
    }

    private function collect_descendants(int $id, array $map, array &$result, &$visited = []) {
        if (!isset($map[$id])) return;
        foreach ($map[$id] as $child) {
            if( isset($visited[$child['id']]) ) continue;
            $visited[$child['id']] = true;
            $result[] = $child;
            $this->collect_descendants($child['id'], $map, $result, $visited );
        }
    }

    private function collect_descendants_by_ids(array $ids, array $map, array &$result) {
        $visited = [];
        foreach ($ids as $id) {
            if( isset($visited[$id]) ) {
                continue;
            }
            $visited[$id] = true;
            $this->collect_descendants($id, $map, $result, $visited );
        }
    }

    public function get_descendants(int $id, $include_deleted = false) {
        $descendants = [];
        $all = $this->get_list([], $include_deleted);
        $map = $this->build_parent_map($all);

        $this->collect_descendants($id, $map, $descendants);
        return $descendants;
    }

    /* ---------------------------------------------------------
     * SOFT DELETE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode soft_delete, pada repository menggunakan metode delete
     * --------------------------------------------------------- */
    public function delete(int $id) {
        $descendants = $this->get_descendants($id, true);
        $descendants_ids = array_map(function($item){ return $item['id']; }, $descendants);
        $ids_to_delete = array_unique(array_merge([$id], $descendants_ids));
        return $this->dbdata->soft_delete_by_ids($ids_to_delete);
    }
    public function delete_by_ids($ids=[]) {
        if( empty($ids) ) {
            return false;
        }
        $all = $this->get_list([], true);
        $map = $this->build_parent_map($all);
        $descendants = [];
        $this->collect_descendants_by_ids($ids, $map, $descendants);
        $new_ids = array_unique(array_merge($ids, array_map(function($item){ return $item['id']; }, $descendants)));
        return $this->dbdata->soft_delete_by_ids($new_ids);
    }
    public function delete_by_site($site_id) {
        return $this->dbdata->soft_delete_by_site($site_id);
    }

    /* ---------------------------------------------------------
     * RESTORE METHODS
     * area aman, karena data yang dihapus masih bisa dikembalikan dengan mudah
     * ---------------------------------------------------------
     * pada data menggunakan metode restore, pada repository menggunakan metode restore
     * --------------------------------------------------------- */
    public function restore(int $id) {
        $descendants = $this->get_descendants($id, true);
        $descendants_ids = array_map(function($item){ return $item['id']; }, $descendants);
        $ids_to_restore = array_unique(array_merge([$id], $descendants_ids));
        return $this->dbdata->restore_by_ids($ids_to_restore);
    }
    public function restore_by_ids($ids=[]) {
        if( empty($ids) ) {
            return false;
        }
        $all = $this->get_list([], true);
        $map = $this->build_parent_map($all);
        $descendants = [];
        $this->collect_descendants_by_ids($ids, $map, $descendants);
        $new_ids = array_unique(array_merge($ids, array_map(function($item){ return $item['id']; }, $descendants)));
        return $this->dbdata->restore_by_ids($new_ids);
    }
    public function restore_by_site($site_id) {
        return $this->dbdata->restore_by_site($site_id);
    }

    /* ---------------------------------------------------------
     * DESTROY METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini menghapus data secara permanen tanpa bisa dikembalikan
     * ---------------------------------------------------------
     * pada data menggunakan metode delete, pada repository menggunakan metode destroy
     * --------------------------------------------------------- */
    public function destroy(int $id) {
        $descendants = $this->get_descendants($id, true);
        $descendants_ids = array_map(function($item){ return $item['id']; }, $descendants);
        $ids_to_delete = array_unique(array_merge([$id], $descendants_ids));
        return $this->dbdata->delete_by_ids($ids_to_delete);
    }
    public function destroy_by_ids($ids=[]) {
        if( empty($ids) ) {
            return false;
        }
        $all = $this->get_list([], true);
        $map = $this->build_parent_map($all);
        $descendants = [];
        $this->collect_descendants_by_ids($ids, $map, $descendants);
        $new_ids = array_unique(array_merge($ids, array_map(function($item){ return $item['id']; }, $descendants)));
        return $this->dbdata->delete_by_ids($new_ids);
    }
    /* ---------------------------------------------------------
     * DESTROY BY SITE METHODS
     * area berbahaya, pastikan untuk menggunakan metode ini dengan hati-hati,
     * karena proses ini bisa menghapus data secara permanen tanpa bisa dikembalikan
     * --------------------------------------------------------- */
    public function destroy_by_site(int $site_id) {
        return $this->dbdata->delete_by_site($site_id);
    }

}