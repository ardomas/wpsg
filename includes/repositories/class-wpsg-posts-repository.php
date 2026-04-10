<?php
if (!defined('ABSPATH')) exit;

class WPSG_PostsRepository {

    protected $data;

    public function __construct() {
        $this->data = WPSG_PostsData::get_instance();
    }

    /* =====================
     * POSTS
     * ===================== */

    public function create(array $data): int {
        return $this->data->create_post($data);
    }

    public function find(int $id) {
        return $this->data->get_post($id);
    }

    public function get_list(array $args = []) {
        return $this->data->get_all_posts($args);
    }

    public function update(int $id, array $data): bool {
        return (bool) $this->data->set_post($id, $data);
    }

    public function delete(int $id): bool {
        return (bool) $this->data->del_post($id);
    }

    /* =====================
     * META
     * ===================== */

    public function get_meta(int $post_id, string $key = null) {
        return $this->data->get_meta($post_id, $key);
    }

    public function set_meta(int $post_id, string $key, $value): bool {
        return (bool) $this->data->set_meta($post_id, $key, $value);
    }

    public function delete_meta(int $post_id, string $key = null): bool {
        return (bool) $this->data->del_meta($post_id, $key);
    }

    /* =====================
     * COMMENTS
     * ===================== */

    public function add_comment(
        int $post_id,
        int $author_id,
        string $content,
        ?int $parent_id = null,
        string $status = 'pending'
    ): bool {
        return (bool) $this->data->add_comment(
            $post_id, $author_id, $content, $parent_id, $status
        );
    }

    public function get_comments(int $post_id, string $status = 'approved') {
        return $this->data->get_comments($post_id, $status);
    }

    public function update_comment(int $id, array $data): bool {
        return (bool) $this->data->set_comment($id, $data);
    }

    public function delete_comment(int $id): bool {
        return (bool) $this->data->del_comment($id);
    }
}
