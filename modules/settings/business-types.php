<?php
if (!defined('ABSPATH')) exit;

class WPSG_SettingsBusinessTypes {

    private $option_key = 'wpsg_business_types';
    private $data = [];

    public function __construct() {
        // Load data dari WPSG_AdminData
        $this->data = WPSG_AdminData::get_business_types();

        // Simpan jika ada POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wpsg_business_types_nonce'])) {
            $this->save();
        }
    }

    private function save() {
        if (!wp_verify_nonce($_POST['wpsg_business_types_nonce'], 'wpsg_save_business_types')) {
            return;
        }

        $new_data = [];

        $keys   = $_POST['business_keys'] ?? [];
        $names  = $_POST['business_names'] ?? [];
        $active = $_POST['business_active'] ?? [];

        foreach ($keys as $index => $key) {
            $key = sanitize_text_field($key);
            if (!$key) continue; // skip jika key kosong

            $new_data[$key] = [
                'name'   => sanitize_text_field($names[$index] ?? ''),
                'active' => !empty($active[$index]) ? true : false,
            ];
        }

        // Simpan via WPSG_AdminData
        WPSG_AdminData::set_business_types($new_data);
        $this->data = $new_data;

        echo '<div class="notice notice-success"><p>Business Types saved successfully!</p></div>';
    }

    public function render() {
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('wpsg_save_business_types', 'wpsg_business_types_nonce'); ?>

            <table class="form-table" id="business-types-table">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->data as $key => $item): ?>
                        <tr>
                            <td><input type="text" name="business_keys[]" value="<?php echo esc_attr($key); ?>" /></td>
                            <td><input type="text" name="business_names[]" value="<?php echo esc_attr($item['name']); ?>" /></td>
                            <td><input type="checkbox" name="business_active[]" <?php checked(!empty($item['active'])); ?> /></td>
                            <td><button type="button" class="button remove-row">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p>
                <button type="button" id="add-business-type" class="button">Add Business Type</button>
            </p>

            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const table = document.getElementById('business-types-table').getElementsByTagName('tbody')[0];
                const addBtn = document.getElementById('add-business-type');

                addBtn.addEventListener('click', function() {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><input type="text" name="business_keys[]" /></td>
                        <td><input type="text" name="business_names[]" /></td>
                        <td><input type="checkbox" name="business_active[]" /></td>
                        <td><button type="button" class="button remove-row">Remove</button></td>
                    `;
                    table.appendChild(row);
                });

                table.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-row')) {
                        e.target.closest('tr').remove();
                    }
                });
            });
            </script>
        </form>
        <?php
    }
}
