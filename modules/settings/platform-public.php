<?php
if (!defined('ABSPATH')) exit;

class WPSG_SettingsPlatformPublic {

    private $option_key = 'wpsg_platform_public';
    private $data = [];

    public function __construct() {
        // Load data dari WPSG_AdminData
        $this->data = WPSG_AdminData::get_platform_public();

        // Simpan jika ada POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wpsg_platform_public_nonce'])) {
            $this->save();
        }
    }

    private function save() {
        if (!wp_verify_nonce($_POST['wpsg_platform_public_nonce'], 'wpsg_save_platform_public')) {
            return;
        }

        $new_data = [];

        $keys    = $_POST['platform_keys'] ?? [];
        $names   = $_POST['platform_names'] ?? [];
        $urls    = $_POST['platform_urls'] ?? [];
        $patterns = $_POST['platform_patterns'] ?? [];
        $actives = $_POST['platform_active'] ?? [];

        foreach ($keys as $index => $key) {
            $key = sanitize_text_field($key);
            if (!$key) continue; // skip jika key kosong

            $new_data[$key] = [
                'name'    => sanitize_text_field($names[$index] ?? ''),
                'url'     => esc_url_raw($urls[$index] ?? ''),
                'pattern' => sanitize_text_field($patterns[$index] ?? ''),
                'active'  => !empty($actives[$index]) ? true : false,
            ];
        }

        // Simpan via WPSG_AdminData
        WPSG_AdminData::set_platform_public($new_data);
        $this->data = $new_data;

        echo '<div class="notice notice-success"><p>Public platforms saved successfully!</p></div>';
    }

    public function render() {
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('wpsg_save_platform_public', 'wpsg_platform_public_nonce'); ?>

            <table class="table bordered striped hover" id="platform-public-table">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Pattern</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->data as $key => $item): ?>
                        <tr>
                            <td><input type="text" name="platform_keys[]" value="<?php echo esc_attr($key); ?>" /></td>
                            <td><input type="text" name="platform_names[]" value="<?php echo esc_attr($item['name']); ?>" /></td>
                            <td><input type="text" name="platform_urls[]" value="<?php echo esc_url($item['url']); ?>" /></td>
                            <td><input type="text" name="platform_patterns[]" value="<?php echo esc_attr($item['pattern']); ?>" /></td>
                            <td><input type="checkbox" name="platform_active[]" <?php checked(!empty($item['active'])); ?> /></td>
                            <td><button type="button" class="button remove-row">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p>
                <button type="button" id="add-platform-public" class="button">Add Platform</button>
            </p>

            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const table = document.getElementById('platform-public-table').getElementsByTagName('tbody')[0];
                const addBtn = document.getElementById('add-platform-public');

                addBtn.addEventListener('click', function() {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><input type="text" name="platform_keys[]" /></td>
                        <td><input type="text" name="platform_names[]" /></td>
                        <td><input type="text" name="platform_urls[]" /></td>
                        <td><input type="text" name="platform_patterns[]" /></td>
                        <td><input type="checkbox" name="platform_active[]" /></td>
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
