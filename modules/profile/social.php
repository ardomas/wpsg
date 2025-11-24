<?php
if (!defined('ABSPATH')) exit;

class WPSG_ProfileSocial {

    private $option_key = 'profile_social';
    private $platforms_key = 'wpsg_platform_public';
    private $data = [];
    private $platforms = [];

    public function __construct() {
        // Load user data
        $this->data = WPSG_AdminData::get_data($this->option_key) ?? [];

        // Load platform list
        $this->platforms = $this->load_platforms();

        // Save if POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wpsg_profile_social_nonce'])) {
            $this->save();
        }
    }

    private function load_platforms() {
        // Ambil dari wp_wpsg_settings
        $platforms = WPSG_AdminData::get_platform_public();

        // Jika kosong, fallback ke admin.json
        if (empty($platforms)) {
            $json_file = WPSG_PLUGIN_DIR . '/admin.json';
            if (file_exists($json_file)) {
                $json_data = json_decode(file_get_contents($json_file), true);
                $platforms = $json_data['platform-public-default'] ?? [];
            }
        }

        return $platforms;
    }

    private function save() {
        if (!wp_verify_nonce($_POST['wpsg_profile_social_nonce'], 'wpsg_save_profile_social')) {
            return;
        }

        $new_data = [];
        $keys = $_POST['social_keys'] ?? [];
        $values = $_POST['social_values'] ?? [];

        foreach ($keys as $index => $key) {
            $key = sanitize_text_field($key);
            if (!$key) continue;

            $new_data[$key] = sanitize_text_field($values[$index] ?? '');
        }

        // Save via WPSG_AdminData
        WPSG_AdminData::set_data($this->option_key, $new_data);
        $this->data = $new_data;

        echo '<div class="notice notice-success"><p>Social platforms saved successfully!</p></div>';
    }

    /**
     * Build dynamic preview (server-side fallback)
     */
    private static function build_preview($pattern, $value=null) {
        if( is_null($value) || trim($value) === '' ) {
            return $pattern;
        }
        return preg_replace_callback('/\{([^}]+)\}/', function($matches) use ($value) {

            $placeholder = $matches[1];

            // If starts with @ → auto prepend
            if (strpos($placeholder, '@') === 0) {
                return '@' . ltrim($value, '@');
            }

            return $value;

        }, $pattern);
    }

    public function render() {
        ?>
        <form method="post" action="">
            <div class="wpsg wpsg-boxed">

                <h2>Social Media</h2>
                <p>Link your organization's social media profiles. Preview below shows how it will appear on your site.</p>

                <?php wp_nonce_field('wpsg_save_profile_social', 'wpsg_profile_social_nonce'); ?>

                <table class="widefat striped" id="profile-social-table">
                    <thead>
                        <tr>
                            <th style="width: 15%; min-width: 160px;">Platform</th>
                            <th style="width: 250px;">Account</th>
                            <th>Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->platforms as $key => $item): ?>
                            <?php

                            $label   = esc_html($item['name']);
                            $url     = $item['url'];
                            $pattern = $item['pattern'];
                            $value   = $this->data[$key] ?? '';

                            $preview = self::build_preview($pattern, $value);

                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html($item['name'] ?? $key); ?></a>
                                </td>
                                <td>
                                    <input type="text" 
                                        class="regular-text wpsg-social-input"
                                        name="social_values[]"
                                        value="<?php echo esc_attr($value); ?>"
                                        data-pattern="<?php echo $pattern; ?>" />
                                    <input type="hidden"
                                        name="social_keys[]"
                                        value="<?php echo esc_attr($key); ?>" />
                                </td>
                                <td>
                                    <div class="wpsg-social-preview"
                                        style="font-family: monospace; padding: 4px 0;">
                                        <?php echo esc_html($preview); ?>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>

            <script>

            document.addEventListener('DOMContentLoaded', function(){

                function buildPreview(pattern, value=null) {
                    if( value==null || value.trim() === '' ) {
                        return pattern;
                    } else {
                        return pattern.replace(/\{([^}]+)\}/g, function(_, placeholder) {

                            if (placeholder.startsWith('@')) {
                                return '@' + value.replace(/^@/, '');
                            }
                            return value;
                        });
                    }


                }

                document.querySelectorAll('.wpsg-social-input').forEach(function(input){
                    input.addEventListener('input', function(){

                        let value = input.value;
                        let pattern = input.dataset.pattern;

                        let preview = input.closest('tr').querySelector('.wpsg-social-preview');

                        if (!preview) return;
                        let test = buildPreview(pattern, value);

                        preview.textContent = buildPreview(pattern, value);

                    });
                });

            });

            </script>

        </form>
        <?php
    }
}
