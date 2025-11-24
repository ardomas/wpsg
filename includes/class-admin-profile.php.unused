<?php
namespace WPSG;

if (!defined('ABSPATH')) exit;

class WPSG_AdminProfile
{
    private $config;
    private $savedValues;

    public function __construct()
    {
        // $wpsg_config      = WPSG_Config::instance();
        $wpsg_profile_settings = WPSG_Config::get( 'profile_settings', [] );
        $this->config = $wpsg_profile_settings[ 'branding' ] ?? [];
        // WPSG_Config::instance()->get('profile_settings.branding', []);

        $this->savedValues = $this->load_saved_values();

        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_post_wpsg_save_profile_branding', [$this, 'handle_save']);
    }

    public function register_menu()
    {
        add_submenu_page(
            'wpsg-dashboard',
            'Branding Settings',
            'Branding',
            'manage_options',
            'wpsg-profile-branding',
            [$this, 'render_page']
        );
    }

    public function render_page()
    {
        echo '<div class="wrap">';
        echo '<h1>Branding Settings</h1>';

        echo '<form method="POST" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="wpsg_save_profile_branding" />';
        wp_nonce_field('wpsg_save_profile_branding');

        // RENDER SECTION
        WPSG_FormRenderer::render_section($this->config, $this->savedValues);

        echo '<p><button type="submit" class="button button-primary">Save Settings</button></p>';
        echo '</form>';

        echo '</div>';
    }

    private function load_saved_values()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'wpsg_data';

        $row = $wpdb->get_row(
            "SELECT data_value FROM {$table} WHERE data_key = 'profile_settings' LIMIT 1"
        );

        if (!$row) return [];

        $data = json_decode($row->data_value, true);
        return is_array($data) ? $data : [];
    }

    public function handle_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die('No permission.');
        }

        check_admin_referer('wpsg_save_profile_branding');

        $posted = $_POST;
        $clean = [];

        // Parse config dan masukkan ke array final
        foreach ($this->config as $fieldName => $fieldConfig) {
            $key = $fieldConfig['key'];

            if (isset($posted[$key])) {
                $clean[$key] = $posted[$key];
            }
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wpsg_data';

        $wpdb->replace(
            $table,
            [
                'data_key' => 'profile_settings',
                'data_value' => wp_json_encode($clean)
            ],
            ['%s', '%s']
        );

        wp_redirect(admin_url('admin.php?page=wpsg-profile-branding&saved=1'));
        exit;
    }
}
