<?php
// admin/modules/announcements/form.php
if (!defined('ABSPATH')) exit;

class WPSG_AnnouncementsForm {

    private $data;
    private $posts_class;

    public function __construct($data = []) {
        $this->data = $data;
        $this->posts_class = new WPSG_Posts();
    }

    public function enqueue_assets() {
        // enqueue WP media
        wp_enqueue_media();

        // tambahan dari luar
        wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], false, true);

        // enqueue our JS
        wp_enqueue_script(
            'wpsg-announcements',
            WPSG_URL . 'assets/js/announcements.js',
            ['jquery'],
            WPSG_VERSION,
            true
        );

        wp_enqueue_style(
            'wpsg-announcements-css',
            WPSG_URL . 'assets/css/announcements.css',
            [],
            WPSG_VERSION
        );

        // Localize script
        wp_localize_script('wpsg-announcements', 'WPSG_ANN_DATA', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id'  => intval($this->data['id'] ?? 0),
            'nonce'    => wp_create_nonce('wpsg_ann_nonce'),
        ]);
    }

    protected function generate_form() {

        $submit_form = "wp-admin/admin.php?page=wpsg-admin&view=announcements&action=save";

?>
        <form class="wpsg-form" method="post" action="<?php 
            echo esc_url( $submit_form );
            // echo esc_url(admin_url('admin-post.php')); 
        ?>">
            <input type="hidden" name="action" value="wpsg_save_announcement">
            <input type="hidden" name="post_id" value="<?php echo esc_attr($this->data['id'] ?? 0); ?>">
            <?php wp_nonce_field('wpsg_save_announcement', 'wpsg_announcement_nonce'); ?>

            <div class="wpsg wpsg-boxed">
                <div class="wrap">
                    <h1><?php 
                        echo empty($this->data['id']) ? 'Add New Announcement' : 'Edit Announcement'; ?>
                        <a href="<?php echo admin_url('admin.php?page=wpsg-admin&view=announcements'); ?>" class="page-title-action">Back to List (All Announcements)</a>
                    </h1>

                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder">
                            <div class="wpsg-form-full">

                                <div class="wpsg-row">
                                    <div class="col-8">
                                        <div class="wpsg-form-field">

                                            <label for="ann_title">Title</label>
                                            <input type="text" id="ann_title" name="ann_title"
                                                value="<?php echo esc_attr($this->data['title'] ?? ''); ?>" required />

                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <!-- Slug & Tagline -->
                                        <div class="wpsg-form-field">
                                            <label for="ann_slug">Slug</label>
                                            <input type="text" id="ann_slug" name="ann_slug"
                                                value="<?php echo esc_attr($this->data['slug'] ?? ''); ?>" />
                                            <p class="description" id="slug-status"></p>

                                        </div>                 
                                    </div>
                                </div>

                                <div class="wpsg-row">
                                    <div class="col-8">

                                        <!-- Sub Title -->
                                        <div class="wpsg-form-field">
                                            <div class="wpsg-row">
                                                <div class="col-12">
                                                    <div class="wpsg-form-field">
                                                        <label for="ann_subtitle">Sub Title</label>
                                                        <input type="text" id="ann_subtitle" name="ann_subtitle"
                                                            value="<?php echo esc_attr($this->data['subtitle'] ?? ''); ?>" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                    

                                        <!-- Content -->
                                        <div class="wpsg-form-field">
                                            <label for="ann_content">Content</label>
                                            <?php
                                            wp_editor(
                                                $this->data['content'] ?? '',
                                                'ann_content',
                                                ['textarea_name' => 'ann_content', 'media_buttons' => true, 'textarea_rows' => 8]
                                            );
                                            ?>
                                        </div>

                                    </div>
                                    <div class="col-4">

                                        <!-- Date and Time -->
                                        <div class="wpsg-form-field">
                                            <div class="wpsg-row">

                                                <div class="col-6">
                                                    <div class="wpsg-form-field">
                                                        <label for="ann_date_start">Date Start</label>
                                                        <input type="date" id="ann_date_start" name="ann_date_start"
                                                            value="<?php echo esc_attr($this->data['date_start'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="wpsg-form-field">
                                                        <label for="ann_date_end">Date End</label>
                                                        <input type="date" id="ann_date_end" name="ann_date_end"
                                                            value="<?php echo esc_attr($this->data['date_end'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wpsg-row">
                                                <div class="col-6">
                                                    <div class="wpsg-form-field">
                                                        <label for="ann_time_start">Time Start</label>
                                                        <input type="time" id="ann_time_start" name="ann_time_start"
                                                            value="<?php echo esc_attr($this->data['time_start'] ?? ''); ?>">
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="wpsg-form-field">
                                                        <label for="ann_time_end">Time End</label>
                                                        <input type="time" id="ann_time_end" name="ann_time_end"
                                                            value="<?php echo esc_attr($this->data['time_end'] ?? ''); ?>">
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- Featured Image -->
                                        <div class="wpsg-form-field">
                                            <label>Featured Image</label>
                                            <div id="ann_image_wrapper">
                                                <?php if (!empty($this->data['image'])): ?>
                                                    <img src="<?php echo esc_url($this->data['image']); ?>" class="preview-image"/>
                                                <?php endif; ?>
                                                <input type="hidden" id="ann_image" name="ann_image" value="<?php echo esc_attr($this->data['image'] ?? ''); ?>" />
                                                <button type="button" class="button" id="ann_image_select">Select Image</button>
                                                <button type="button" class="button" id="ann_image_remove">Remove</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="wpsg-row">
                                    <div class="col-12">
                                        <div class="postbox">
                                            <div class="inside">
                                                <div class="wpsg-row">

                                                    <div class="col-6">

                                                        <div class="wpsg-form-field">
                                                            <label>Speakers</label>
                                                            <div id="speakers_wrapper"></div>
                                                            <button type="button" class="button add-repeatable" data-type="speaker">Add Speaker</button>
                                                        </div>

                                                    </div>
                                                    <div class="col-6">

                                                        <div class="wpsg-form-field">
                                                            <label>Organizers</label>
                                                            <div id="organizers_wrapper"></div>
                                                            <button type="button" class="button add-repeatable" data-type="organizer">Add Organizer</button>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="wpsg-row">
                                    <div class="col-12">
                                        <div class="postbox">
                                            <div class="inside">
                                                <div class="wpsg-row">

                                                    <div class="col-6">
                                                        <div class="wpsg-form-field">
                                                            <label for="ann_price_label">Price Label</label>
                                                            <input type="input" class="ann_price_label" name="ann_price_label" 
                                                                value="<?php echo esc_attr( $this->data['price_label'] ?? '' ); ?>" placeholder="Administration's Fee"/>
                                                        </div>
                                                        <div class="wpsg-form-field">
                                                            <div id="pricings_wrapper"></div>
                                                            <button type="button" class="button add-repeatable" data-type="pricing">Add Pricing</button>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="wpsg-form-field">
                                                            <label>Contacts</label>
                                                            <div id="contacts_wrapper"></div>
                                                            <button type="button" class="button add-repeatable" data-type="contact">Add Contact</button>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="wpsg-row">
                                    <!-- RIGHT COLUMN -->
                                    <div class="col-12">

                                        <div class="postbox">

                                            <div class="inside">

                                                <div class="wpsg-row">

                                                    <div class="col-6">
                                                        <div class="wpsg-form-field">
                                                            <label for="ann_tagline">Tagline</label>
                                                            <input type="text" id="ann_tagline" name="ann_tagline"
                                                                value="<?php echo esc_attr($this->data['tagline'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <div class="wpsg-form-field">
                                                            <label for="publish_status">Status</label>
                                                            <select id="publish_status" name="publish_status">
                                                                <option value="draft" <?php selected($this->data['status'] ?? '', 'draft'); ?>>Draft</option>
                                                                <option value="published" <?php selected($this->data['status'] ?? '', 'published'); ?>>Published</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <div class="wpsg-form-field">
                                                            <label for="publish_date">Published At</label>
                                                            <input type="datetime-local" id="publish_date" name="publish_date" 
                                                                value="<?php echo esc_attr($this->data['published_at'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <div class="wpsg-form-field">
                                                            <label for="expiry_date">Expired At</label>
                                                            <input type="datetime-local" id="expiry_date" name="expiry_date" 
                                                                value="<?php echo esc_attr($this->data['published_at'] ?? ''); ?>" />
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="wpsg-row">

                                    <div class="wpsg-form-field">
                                        <button type="submit" class="button button-primary">Save Announcement</button>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </form>
        <?php
    }

    public function render(){

        $this->generate_form();
        add_action('in_admin_footer', [$this, 'enqueue_assets']);

    }

}

new WPSG_AnnouncementsForm();
