<?php
// File: wpsg/includes/class-wpsg-posts.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WPSG_Posts {

    protected $table_posts    = 'wp_wpsg_posts';
    protected $table_postmeta = 'wp_wpsg_postmeta';
    protected $table_comments = 'wp_wpsg_comments';
    protected $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->create_tables();
    }

    /**
     * Create tables if not exists
     */
    protected function create_tables() {
        $charset_collate = $this->wpdb->get_charset_collate();

        // wp_wpsg_posts
/*
        $sql_posts = "CREATE TABLE IF NOT EXISTS {$this->table_posts} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            site_id INT(11) NOT NULL,
            post_type VARCHAR(50) NOT NULL DEFAULT 'post',
            title VARCHAR(255) NOT NULL,
            status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            author_id INT(11) NOT NULL,

            start_date DATE DEFAULT NULL,
            end_date DATE DEFAULT NULL,

            start_time TIME DEFAULT NULL,
            end_time TIME DEFAULT NULL,

            published_at DATETIME NULL,
            expired_at DATETIME NOT NULL,

            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
*/

        $sql_posts = "CREATE TABLE IF NOT EXISTS {$this->table_posts} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            site_id INT(11) NOT NULL,
            post_type VARCHAR(50) NOT NULL DEFAULT 'post',
            title VARCHAR(255) NOT NULL,
            status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            author_id INT(11) NOT NULL,

            published_at DATETIME NULL,

            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        $this->wpdb->query($sql_posts);

        // wp_wpsg_postmeta
        $sql_postmeta = "CREATE TABLE IF NOT EXISTS {$this->table_postmeta} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            post_id INT(11) NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        $this->wpdb->query($sql_postmeta);

        // wp_wpsg_comments
        $sql_comments = "CREATE TABLE IF NOT EXISTS {$this->table_comments} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            post_id INT(11) NOT NULL,
            parent_id INT(11) DEFAULT NULL,
            author_id INT(11) NOT NULL,
            content TEXT NOT NULL,
            status ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        $this->wpdb->query($sql_comments);
    }

    // ========================
    // POSTS CRUD
    // ========================
    public function create_post($data = []) {
/*
        $defaults = [
            'post_type'     => 'post',
            'title'         => '',
            'status'        => 'draft',
            'site_id'       => get_current_blog_id(),
            'author_id'     => get_current_user_id(),
            'published_at'  => current_time('mysql'),
            'start_date'    => null,
            'end_date'      => null,
            'start_time'    => null,
            'end_time'      => null,
            'created_at'    => current_time('mysql'),
            'updated_at'    => current_time('mysql'),
        ];
*/
        $defaults = [
            'post_type'     => 'post',
            'title'         => '',
            'status'        => 'draft',
            'site_id'       => get_current_blog_id(),
            'author_id'     => get_current_user_id(),
            'published_at'  => current_time('mysql'),
            'created_at'    => current_time('mysql'),
            'updated_at'    => current_time('mysql'),
        ];
        $data = wp_parse_args($data, $defaults);
        $this->wpdb->insert($this->table_posts, $data);
        return $this->wpdb->insert_id;
    }

    public function get_post($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_posts} WHERE id = %d", $id)
        );
    }

    public function get_all_posts($args = []) {
        $defaults = [
            'post_type' => 'post',
            'status'    => 'published',
        ];
        $args = wp_parse_args($args, $defaults);

        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_posts} 
             WHERE post_type = %s 
             AND status = %s 
             ORDER BY published_at DESC",
            $args['post_type'], $args['status']
        );
        return $this->wpdb->get_results($query);
    }

    public function get_posts($args = []) {
        $defaults = [
            'post_type' => 'post',
            'status'    => 'published',
            'site_id'   => get_current_blog_id(),
        ];
        $args = wp_parse_args($args, $defaults);

        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_posts} 
             WHERE post_type = %s 
             AND status = %s 
             AND site_id = %d 
             ORDER BY published_at DESC",
            $args['post_type'], $args['status'], $args['site_id']
        );
        return $this->wpdb->get_results($query);
    }

    public function set_post($id, $data = []) {
        $data['updated_at'] = current_time('mysql');
        return $this->wpdb->update($this->table_posts, $data, ['id' => $id]);
    }

    public function del_post($id) {
        // hapus meta
        $this->wpdb->delete($this->table_postmeta, ['post_id' => $id]);
        // hapus komentar
        $this->wpdb->delete($this->table_comments, ['post_id' => $id]);
        // hapus post
        return $this->wpdb->delete($this->table_posts, ['id' => $id]);
    }

    // ========================
    // POSTMETA
    // ========================
    public function add_meta($post_id, $key, $value) {
        $time = current_time('mysql');
        return $this->wpdb->insert($this->table_postmeta, [
            'post_id' => $post_id,
            'meta_key' => $key,
            'meta_value' => maybe_serialize($value),
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }

    public function get_meta($post_id, $key = null) {
        if ($key) {
            return maybe_unserialize($this->wpdb->get_var(
                $this->wpdb->prepare(
                    "SELECT meta_value FROM {$this->table_postmeta} WHERE post_id = %d AND meta_key = %s",
                    $post_id, $key
                )
            ));
        } else {
            $results = $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "SELECT meta_key, meta_value FROM {$this->table_postmeta} WHERE post_id = %d",
                    $post_id
                ),
                OBJECT_K
            );
            foreach ($results as $k => $v) {
                $results[$k] = maybe_unserialize($v->meta_value);
            }
            return $results;
        }
    }

    public function set_meta($post_id, $key, $value) {
        $existing = $this->get_meta($post_id, $key);
        if ($existing !== null) {
            return $this->wpdb->update(
                $this->table_postmeta,
                ['meta_value' => maybe_serialize($value), 'updated_at' => current_time('mysql')],
                ['post_id' => $post_id, 'meta_key' => $key]
            );
        } else {
            return $this->add_meta($post_id, $key, $value);
        }
    }

    public function del_meta($post_id, $key = null) {
        if ($key) {
            return $this->wpdb->delete($this->table_postmeta, ['post_id' => $post_id, 'meta_key' => $key]);
        } else {
            return $this->wpdb->delete($this->table_postmeta, ['post_id' => $post_id]);
        }
    }

    // ========================
    // COMMENTS
    // ========================
    public function add_comment($post_id, $author_id, $content, $parent_id = null, $status = 'pending') {
        $time = current_time('mysql');
        return $this->wpdb->insert($this->table_comments, [
            'post_id' => $post_id,
            'parent_id' => $parent_id,
            'author_id' => $author_id,
            'content' => $content,
            'status' => $status,
            'created_at' => $time,
            'updated_at' => $time
        ]);
    }

    public function get_comments($post_id, $status = 'approved') {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_comments} WHERE post_id = %d AND status = %s ORDER BY created_at ASC",
                $post_id, $status
            )
        );
    }

    public function set_comment($id, $data = []) {
        $data['updated_at'] = current_time('mysql');
        return $this->wpdb->update($this->table_comments, $data, ['id' => $id]);
    }

    public function del_comment($id) {
        return $this->wpdb->delete($this->table_comments, ['id' => $id]);
    }
}
