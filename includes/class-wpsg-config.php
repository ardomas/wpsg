<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WPSG_Config
{
    private static $instance = null;

    private $config_path;
    private $data = [];

    /**
     * Singleton
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->config_path = plugin_dir_path(__FILE__) . '../assets/json/settings.json';
        $this->load();
    }

    /**
     * Load JSON config from file
     */
    private function load()
    {
        if (!file_exists($this->config_path)) {
            $this->data = [];
            return;
        }

        $json = file_get_contents($this->config_path);
        $decoded = json_decode($json, true);

        $this->data = is_array($decoded) ? $decoded : [];
    }

    /**
     * Get entire config or specific section/key
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * For future use: overwrite/merge/save config
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function save()
    {
        file_put_contents(
            $this->config_path,
            json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
