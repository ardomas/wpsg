<?php
if (!defined('ABSPATH')) exit;

class WPSG_SettingsGeneral {

    private $option_key = 'wpsg_general';
    private $data = [];

    public function __construct() {
        // Load data dari WPSG_AdminData
        $this->data = [
            'default_timezone'       => WPSG_AdminData::get_setting($this->option_key . '_timezone', 'Asia/Jakarta'),
            'default_country'        => WPSG_AdminData::get_setting($this->option_key . '_country', ''),
            'default_city'           => WPSG_AdminData::get_setting($this->option_key . '_city', ''),
            'default_business_types' => maybe_unserialize(WPSG_AdminData::get_setting($this->option_key . '_business_types', [
                'msme' => 'Micro, Small, and Medium Enterprise (MSME)'
            ]))
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

        // Save business type array sebagai key => label
        $types_keys = $_POST['default_business_type_keys'] ?? [];
        $types_vars = $_POST['default_business_type_labels'] ?? [];

        $assoc_types = [];
        foreach ($types_keys as $index => $key) {
            $in_key = sanitize_text_field($key);
            $in_val = sanitize_text_field($types_vars[$index] ?? '');
            if ($in_key) $assoc_types[$in_key] = $in_val;
        }

        $this->data['default_business_types'] = $assoc_types;

        // Simpan ke wp_wpsg_settings via WPSG_AdminData
        WPSG_AdminData::set_setting($this->option_key . '_timezone', $this->data['default_timezone']);
        WPSG_AdminData::set_setting($this->option_key . '_country', $this->data['default_country']);
        WPSG_AdminData::set_setting($this->option_key . '_city', $this->data['default_city']);
        WPSG_AdminData::set_setting($this->option_key . '_business_types', $this->data['default_business_types']);

        echo '<div class="notice notice-success"><p>General settings saved successfully!</p></div>';
    }

    public function render() {
        $business_types = $this->data['default_business_types'];
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('wpsg_save_general_settings', 'wpsg_general_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="default_timezone">Default Timezone</label></th>
                    <td>
                        <select name="default_timezone" id="default_timezone">
                            <?php 
                            $timezones = timezone_identifiers_list();
                            foreach ($timezones as $tz) : ?>
                                <option value="<?php echo esc_attr($tz); ?>" <?php selected($this->data['default_timezone'], $tz); ?>>
                                    <?php echo esc_html($tz); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="default_country">Default Country</label></th>
                    <td><input type="text" name="default_country" id="default_country" value="<?php echo esc_attr($this->data['default_country']); ?>" /></td>
                </tr>

                <tr>
                    <th><label for="default_city">Default City</label></th>
                    <td><input type="text" name="default_city" id="default_city" value="<?php echo esc_attr($this->data['default_city']); ?>" /></td>
                </tr>

                <tr>
                    <th><label>Default Business Types</label></th>
                    <td>
                        <div id="business-types-container">
                            <?php foreach ($business_types as $key => $label): ?>
                                <div class="business-type-item" style="display: flex; gap: 10px; margin-bottom: 8px;">
                                    <input type="text" name="default_business_type_keys[]" value="<?php echo esc_attr($key); ?>" placeholder="Key" />
                                    <input type="text" name="default_business_type_labels[]" value="<?php echo esc_attr($label); ?>" placeholder="Label" />
                                    <button type="button" class="remove-type button">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="add-business-type" class="button">Add Business Type</button>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.getElementById('business-types-container');
                    const addBtn = document.getElementById('add-business-type');

                    addBtn.addEventListener('click', function() {
                        const div = document.createElement('div');
                        div.classList.add('business-type-item');
                        div.innerHTML = '<input type="text" name="default_business_type_keys[]" placeholder="Key" /> ' +
                                        '<input type="text" name="default_business_type_labels[]" placeholder="Label" /> ' +
                                        '<button type="button" class="remove-type button">Remove</button>';
                        container.appendChild(div);
                    });

                    container.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-type')) {
                            e.target.parentElement.remove();
                        }
                    });
                });
            </script>
        </form>
        <?php
    }
}
