<?php
if (!defined('ABSPATH')) exit;

class WPSG_ProfileContact {

    private $data_key = 'profile_contact';
    private $data = [];

    public function __construct() {
        $this->data = WPSG_ProfilesData::get_data($this->data_key);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wpsg_profile_contact_nonce'])) {
            $this->save();
        }
    }

    /**
     * Save Contact Data
     */
    private function save() {
        if (!wp_verify_nonce($_POST['wpsg_profile_contact_nonce'], 'wpsg_save_profile_contact')) {
            return;
        }

        $new = [];

        // Basic Contact
        $new['email'] = sanitize_text_field($_POST['contact_email'] ?? '');
        $new['phone'] = sanitize_text_field($_POST['contact_phone'] ?? '');

        // Messaging Platforms
        $new['messaging'] = [];

        $platform_keys = $_POST['msg_keys'] ?? [];
        $platform_vals = $_POST['msg_vals'] ?? [];

        foreach ($platform_keys as $i => $key) {
            $key = sanitize_text_field($key);
            if (!$key) continue;

            $new['messaging'][$key] = sanitize_text_field($platform_vals[$i] ?? '');
        }

        // Save to DB
        WPSG_ProfilesData::set_data($this->data_key, $new);
        $this->data = $new;

        echo '<div class="notice notice-success"><p>Contact updated successfully.</p></div>';
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

    /**
     * Render Form
     */
    public function render() {

        $platforms = WPSG_ProfilesData::get_platform_private(); // dynamic platforms
        
        $saved_email = $this->data['email'] ?? '';
        $saved_phone = $this->data['phone'] ?? '';
        $saved_messaging = $this->data['messaging'] ?? [];

        ?>
        <form method="post">

            <div class="wpsg wpsg-boxed">

                <div class="wrap">

                    <h2>Contact Information</h2>
                    <p>Manage basic contacts and messaging platforms used on your site.</p>

                    <?php wp_nonce_field('wpsg_save_profile_contact', 'wpsg_profile_contact_nonce'); ?>

                    <!-- ===================== -->
                    <!-- BASIC CONTACT SECTION -->
                    <!-- ===================== -->
                    <h3>Basic Contact</h3>

                    <table class="widefat striped">
                        <tr>
                            <th style="width: 15%; min-width: 160px;"><label for="contact_email">Email</label></th>
                            <td>
                                <div class="wpsg-form input-wrapper">
                                    <input type="text"
                                        name="contact_email"
                                        id="contact_email"
                                        value="<?php echo esc_attr($saved_email); ?>"
                                        class="regular-text"/>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th><label for="contact_phone">Phone</label></th>
                            <td>
                                <div class="wpsg-form input-wrapper">
                                <input type="text"
                                    name="contact_phone"
                                    id="contact_phone"
                                    value="<?php echo esc_attr($saved_phone); ?>"
                                    class="regular-text"/>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <!-- =========================== -->
                    <!-- MESSAGING PLATFORM SECTION -->
                    <!-- =========================== -->
                    <h3>Messaging Platform</h3>

                    <p>Input the account ID for each platform. Preview will show the final link used on your site.</p>

                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th style="width: 15%; min-width: 160px;">Platform</th>
                                <th style="width: 250px;">Account ID</th>
                                <th>Preview</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($platforms as $key => $item): 

                            $label   = esc_html($item['name']);
                            $url     = $item['url'];
                            $pattern = $item['pattern'];
                            $value   = $saved_messaging[$key] ?? '';

                            $preview = self::build_preview($pattern, $value);

                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($url); ?>" target="_blank">
                                        <?php echo $label; ?>
                                    </a>
                                </td>

                                <td>
                                    <input type="hidden" name="msg_keys[]" value="<?php echo esc_attr($key); ?>">
                                    <input type="text"
                                        name="msg_vals[]"
                                        value="<?php echo esc_attr($value); ?>"
                                        class="regular-text wpsg-contact-input"
                                        data-pattern="<?php echo esc_attr($pattern); ?>">
                                </td>

                                <td>
                                    <div class="wpsg-contact-preview"
                                        style="font-family: monospace; padding: 4px 0;">
                                        <?php echo esc_html($preview); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            </div>
                
            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>

        </form>

        <!-- ================== -->
        <!-- JS: Live Preview  -->
        <!-- ================== -->
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

            document.querySelectorAll('.wpsg-contact-input').forEach(function(input){
                input.addEventListener('input', function(){

                    let value = input.value;
                    let pattern = input.dataset.pattern;

                    let preview = input.closest('tr').querySelector('.wpsg-contact-preview');
                    if (!preview) return;

                    preview.textContent = buildPreview(pattern, value);

                });
            });

        });
        </script>

        <?php
    }
}
