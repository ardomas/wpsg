<?php

if (!defined('ABSPATH')) exit;

/**
 * Repository minimalis untuk WPSG_ProfilesData.
 * Memisahkan data-layer dari logic agar mudah dikembangkan.
 */
class WPSG_ProfilesRepository {

    /** @var WPSG_ProfilesData */
    protected static $data;

    /**
     * Inisialisasi dependency.
     * Agar tidak mengikat class secara hard-coded.
     */
    public static function init() {
        if (self::$data === null) {
            if (!class_exists('WPSG_ProfilesData')) {
                throw new Exception("WPSG_ProfilesData not found.");
            }
            WPSG_ProfilesData::create_tables();
            self::$data = WPSG_ProfilesData::get_instance();
        }
    }

    /**
     * Ambil profil berdasarkan key.
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $site_id
     * @return mixed
     */
    public static function get($key, $default = null, $site_id = null) {
        self::init();
        return self::$data::get_data($key, $default, $site_id);
    }

    public static function get_all_data( $site_id = null ){
        self::init();
        return self::$data::get_all_data( $site_id );
    }

    /**
     * Simpan data profil.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $site_id
     * @return bool
     */
    public static function set($key, $value, $site_id = null) {
        self::init();
        return self::$data::set_data($key, $value, $site_id);
    }

    /**
     * Hapus data profil.
     *
     * @param string $key
     * @param int|null $site_id
     * @return bool
     */
    public static function delete($key, $site_id = null) {
        self::init();
        return self::$data::delete_data($key, $site_id);
    }

    /**
     * Ambil semua profil untuk *seluruh site*.
     *
     * @param string $key
     * @return array
     */
    public static function get_all_sites($key) {
        self::init();
        return self::$data::get_data($key, null, "*");
    }

}
