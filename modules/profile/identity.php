<?php
if (!defined('ABSPATH')) exit;

class WPSG_ProfileIdentity {

    private $option_key = 'profile_identity';
    private $business_types = [];

    public function __construct() {
        $this->load_data_references();
        $this->form_handler();
    }

    /** Load business types dari Settings -> General */
    private function load_data_references() {
        $this->business_types = WPSG_AdminData::get_setting('wpsg_business_types', '*'); // Ambil semua site
    }

    public function render() {
        $data = $this->get_data();
        ?>
        <form method="post">

            <div class="wpsg wpsg-boxed">

                <div class="wrap">

                    <h3>Manage your organization’s identity and essential details.</h3>

                    <?php wp_nonce_field('wpsg_identity_save', 'wpsg_identity_nonce'); ?>

                    <div class="wpsg-boxed wpsg-form-field">
                        <!-- Full Name -->
                        <label for="full_name"><strong>Full Name</strong></label>
                        <input type="text"
                                id="full_name"
                                name="full_name"
                                class="regular-text"
                                value="<?php echo esc_attr($data['full_name'] ?? ''); ?>">
                    </div>

                    <div class="wpsg-flex">
                        <div class="wpsg-card">

                            <div class="wpsg-form-field">
                                    <label for="short_name"><strong>Short Name</strong></label>
                                    <input type="text"
                                            id="short_name"
                                            name="short_name"
                                            class="regular-text"
                                            placeholder="Short Name / Abbreviation"
                                            value="<?php echo esc_attr($data['short_name'] ?? ''); ?>">
                            </div>
                            <div class="wpsg-form-field">
                                <label for="tagline">Tagline / Motto / Slogan</label>
                                <input type="text"
                                        id="tagline"
                                        name="tagline"
                                        class="regular-text"
                                        style="width: 100%;"
                                        value="<?php echo esc_attr($data['tagline'] ?? ''); ?>">
                            </div>

                            <div class="wpsg-form-field">
                                <label for="year_established"><strong>Year Established</strong></label>
                                <input type="number"
                                    id="year_established"
                                    name="year_established"
                                    width="100px; text-align: center;"
                                    min="1800"
                                    max="<?php echo date('Y'); ?>"
                                    value="<?php echo esc_attr($data['year_established'] ?? ''); ?>">
                            </div>

                        </div>
                        <div class="wpsg-card">
                            <div class="wpsg-form-field">
                                <label for="business_type"><strong>Business Type</strong></label>
                                <select id="business_type"
                                        name="business_type[]"
                                        multiple
                                        style="width: 100%; min-width: 280px; height: 120px;">
                                    <?php
                                    $selected_types = $data['business_type'] ?? [];
                                    if (!is_array($selected_types)) $selected_types = [];
                                    foreach ($this->business_types as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>"
                                            <?php echo in_array($key, $selected_types) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($label['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>

                    </div>


                    <div class="wpsg-boxed wpsg-form-field">
                        <label for="profile_summary"><strong>Profile Summary</strong></label>
                        <div class="wpsg-row">

                            <?php
                                $content = $data['profile_summary'] ?? '';
                                wp_editor(
                                    $content,
                                    'wpsg_identity_profile_summary',
                                    [
                                        'textarea_name' => 'profile_summary',
                                        'textarea_rows' => 6,
                                        'media_buttons' => false,
                                        'teeny' => true
                                    ]
                                );
                            ?>

                        </div>
                    </div>

                    <div class="wpsg-boxed wpsg-form-field">
                        <label for="brief_history"><strong>Brief History</strong></label>
                        <div class="wpsg-row">

                            <?php
                                $content = $data['brief_history'] ?? '';
                                wp_editor(
                                    $content,
                                    'wpsg_identity_brief_history',
                                    [
                                        'textarea_name' => 'brief_history',
                                        'textarea_rows' => 6,
                                        'media_buttons' => false,
                                        'teeny' => true
                                    ]
                                );
                            ?>

                        </div>
                    </div>

                </div>
            </div>

            <p>
                <button type="submit" class="button button-primary">
                    Save Changes
                </button>
            </p>

        </form>
        <?php
    }

    /** PROCESS FORM **/
    private function form_handler() {
        if (!isset($_POST['wpsg_identity_nonce'])) return;
        if (!wp_verify_nonce($_POST['wpsg_identity_nonce'], 'wpsg_identity_save')) return;

        $business_type = $_POST['business_type'] ?? [];
        if (!is_array($business_type)) $business_type = [];

        $clean = [
            'full_name'        => sanitize_text_field($_POST['full_name'] ?? ''),
            'short_name'       => sanitize_text_field($_POST['short_name'] ?? ''),
            'business_type'    => array_map('sanitize_text_field', $business_type),
            'tagline'          => sanitize_text_field($_POST['tagline'] ?? ''),
            'profile_summary'  => wp_kses_post($_POST['profile_summary'] ?? ''),
            'brief_history'    => wp_kses_post($_POST['brief_history'] ?? ''),
            'year_established' => intval($_POST['year_established'] ?? 0),
        ];

        WPSG_AdminData::set_data($this->option_key, $clean);

        add_action('admin_notices', function() {
            echo '<div class="updated"><p>Identity updated successfully.</p></div>';
        });
    }

    /** GET DATA **/
    private function get_data() {
        return WPSG_AdminData::get_data($this->option_key);
    }
}
