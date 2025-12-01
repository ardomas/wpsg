<?php
if (!defined('ABSPATH')) exit;

class WPSG_ProfileValues {

    private $option_key = 'profile_values';

    public function __construct() {
        $this->form_handler();
    }

    public function render() {
        $data = $this->get_data();
        ?>
        <form method="post">
            <div class="wpsg wpsg-boxed">

                <div class="wrap wpsg">

                    <h3>Vision, Mission, and Goal</h3>
                    <p>Input your organization's core values. Each field supports rich text editing.</p>

                    <?php wp_nonce_field('wpsg_values_save', 'wpsg_values_nonce'); ?>

                    <div class="wpsg-boxed wpsg-form-field">
                        <label for="vision"><strong>Vision</strong></label>
                        <?php
                            $content = $data['vision'] ?? '';
                            wp_editor(
                                $content,
                                'wpsg_values_vision',
                                [
                                    'textarea_name' => 'vision',
                                    'textarea_rows' => 6,
                                    'media_buttons' => false,
                                    'teeny' => true
                                ]
                            );
                        ?>
                    </div>

                    <div class="wpsg-boxed wpsg-form-field">
                        <label for="mission"><strong>Mission</strong></label>
                        <?php
                            $content = $data['mission'] ?? '';
                            wp_editor(
                                $content,
                                'wpsg_values_mission',
                                [
                                    'textarea_name' => 'mission',
                                    'textarea_rows' => 6,
                                    'media_buttons' => false,
                                    'teeny' => true
                                ]
                            );
                        ?>
                    </div>

                    <div class="wpsg-boxed wpsg-form-field">
                        <label for="goal"><strong>Goal</strong></label>
                        <?php
                            $content = $data['goal'] ?? '';
                            wp_editor(
                                $content,
                                'wpsg_values_goal',
                                [
                                    'textarea_name' => 'goal',
                                    'textarea_rows' => 6,
                                    'media_buttons' => false,
                                    'teeny' => true
                                ]
                            );
                        ?>
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
        if (!isset($_POST['wpsg_values_nonce'])) return;
        if (!wp_verify_nonce($_POST['wpsg_values_nonce'], 'wpsg_values_save')) return;

        $clean = [
            'vision'  => wp_kses_post($_POST['vision'] ?? ''),
            'mission' => wp_kses_post($_POST['mission'] ?? ''),
            'goal'    => wp_kses_post($_POST['goal'] ?? ''),
        ];

        WPSG_ProfilesData::set_data($this->option_key, $clean);

        add_action('admin_notices', function() {
            echo '<div class="updated"><p>Values updated successfully.</p></div>';
        });
    }

    /** GET DATA **/
    private function get_data() {
        return WPSG_ProfilesData::get_data($this->option_key);
    }
}
