<?php
if (!defined('ABSPATH')) exit;

class WPSG_SettingsGeneral {

    private $option_key = 'wpsg_general';
    private $data = [];

    public function __construct() {
        // Load data dari WPSG_SettingsData
        $this->data = [
            'default_timezone'       => WPSG_SettingsData::get($this->option_key . '_timezone', 'Asia/Jakarta'),
            'default_country'        => WPSG_SettingsData::get($this->option_key . '_country', ''),
            'default_city'           => WPSG_SettingsData::get($this->option_key . '_city', '')
        ];

        // Simpan jika ada POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wpsg_general_nonce'])) {
            $this->save();
        }
    }

    private function save() {
        if (!wp_verify_nonce($_POST['wpsg_general_nonce'], 'wpsg_save_general_settings')) {
            return;
        }

        $this->data['default_timezone'] = sanitize_text_field($_POST['default_timezone'] ?? '');
        $this->data['default_country']  = sanitize_text_field($_POST['default_country'] ?? '');
        $this->data['default_city']     = sanitize_text_field($_POST['default_city'] ?? '');

        // Simpan ke wp_wpsg_settings via WPSG_SettingsData
        WPSG_SettingsData::set($this->option_key . '_timezone', $this->data['default_timezone']);
        WPSG_SettingsData::set($this->option_key . '_country', $this->data['default_country']);
        WPSG_SettingsData::set($this->option_key . '_city', $this->data['default_city']);

        echo '<div class="notice notice-success"><p>General settings saved successfully!</p></div>';
    }

    public function render() {

        ?>
        <form method="post" action="">
            <?php wp_nonce_field('wpsg_save_general_settings', 'wpsg_general_nonce'); ?>

            <div class="wpsg wpsg-boxed">

                <div class="wpsg-form-full wrap">

                    <div class="wpsg-row">
                        <div class="wpsg-form-field">
                            <label for="default_timezone">Default Timezone</label>
                            <select class="regular-text" name="default_timezone" id="default_timezone">
                                <?php 
                                $timezones = timezone_identifiers_list();
                                foreach ($timezones as $tz) : ?>
                                    <option value="<?php echo esc_attr($tz); ?>" <?php selected($this->data['default_timezone'], $tz); ?>>
                                        <?php echo esc_html($tz); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="wpsg-row">
                        <div class="wpsg-form-field">
                            <label for="default_country">Default Country</label>
                            <input class="regular-text" type="text" name="default_country" id="default_country" value="<?php echo esc_attr($this->data['default_country']); ?>" />
                        </div>

                    </div>

                    <div class="wpsg-row">
                        <div class="wpsg-form-field">
                            <label for="default_city">Default City</label>
                            <input class="regular-text" type="text" name="default_city" id="default_city" value="<?php echo esc_attr($this->data['default_city']); ?>" />
                        </div>

                    </div>

                </div>

            </div>

            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>

        </form>
        <?php
    }
}
