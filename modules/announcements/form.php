<?php
// admin/modules/announcements/form-complete.php
if (!defined('ABSPATH')) exit;

class WPSG_AnnouncementsForm {

    private $data;

    public function __construct($data = []) {
        $this->data = $data;
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        // enqueue WP media
        wp_enqueue_media();

        // enqueue our JS
        wp_enqueue_script(
            'wpsg-announcements',
            WPSG_URL . 'assets/js/announcements.js',
            ['jquery'],
            WPSG_VERSION,
            true
        );

        // Localize script with existing data
        wp_localize_script('wpsg-announcements', 'WPSG_ANN_DATA', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id'  => intval($this->data['id'] ?? 0),
            'nonce'    => wp_create_nonce('wpsg_ann_nonce'),
            'existing' => [
                'speakers'   => $this->data['speakers'] ?? [],
                'organizers' => $this->data['organizers'] ?? [],
                'image'      => $this->data['image'] ?? '',
            ],
            'slug_check_action' => 'wpsg_check_slug'
        ]);
    }

    public function render() {
        ?>
        <style>
        <?php
        // Inline CSS dari sebelumnya
        echo file_get_contents(WPSG_DIR . 'modules/announcements/announcements.css');
        ?>
        </style>

        <form class="wpsg-form" method="post" action="">
            <div class="wpsg wpsg-boxed">
                <div class="wrap">
                    <h1><?php echo empty($this->data['id']) ? 'Add New Announcement' : 'Edit Announcement'; ?></h1>

                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">

                            <!-- LEFT COLUMN -->
                            <div id="post-body-content" class="wpsg-form-left">
                                <!-- Title -->
                                <div class="form-field">
                                    <label for="ann_title">Title</label>
                                    <input type="text" id="ann_title" name="ann_title"
                                        value="<?php echo esc_attr($this->data['title'] ?? ''); ?>" required />
                                </div>

                                <!-- Slug & Tagline -->
                                <div class="flex-row">
                                    <div class="form-field form-col-2">
                                        <label for="ann_slug">Slug</label>
                                        <input type="text" id="ann_slug" name="ann_slug"
                                            value="<?php echo esc_attr($this->data['slug'] ?? ''); ?>" />
                                        <p class="description" id="slug-status"></p>
                                    </div>
                                    <div class="form-field form-col-2">
                                        <label for="ann_tagline">Tagline</label>
                                        <input type="text" id="ann_tagline" name="ann_tagline"
                                            value="<?php echo esc_attr($this->data['tagline'] ?? ''); ?>" />
                                    </div>
                                </div>

                                <!-- Subtitle -->
                                <div class="form-field">
                                    <label for="ann_subtitle">Subtitle</label>
                                    <input type="text" id="ann_subtitle" name="ann_subtitle"
                                        value="<?php echo esc_attr($this->data['subtitle'] ?? ''); ?>" />
                                </div>

                                <!-- Date & Time -->
                                <div class="form-field">
                                    <label>Date & Time</label>
                                    <div class="wpsg-datetime-row">
                                        <div>
                                            <label for="date_start">Start Date</label>
                                            <input type="date" id="date_start" name="date_start"
                                                value="<?php echo esc_attr($this->data['date_start'] ?? ''); ?>" />
                                        </div>
                                        <div>
                                            <label for="date_end">End Date</label>
                                            <input type="date" id="date_end" name="date_end"
                                                value="<?php echo esc_attr($this->data['date_end'] ?? ''); ?>" />
                                        </div>
                                    </div>
                                    <div class="wpsg-datetime-row">
                                        <div>
                                            <label for="time_start">Start Time</label>
                                            <input type="time" id="time_start" name="time_start"
                                                value="<?php echo esc_attr($this->data['time_start'] ?? ''); ?>" />
                                        </div>
                                        <div>
                                            <label for="time_end">End Time</label>
                                            <input type="time" id="time_end" name="time_end"
                                                value="<?php echo esc_attr($this->data['time_end'] ?? ''); ?>" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="form-field">
                                    <label for="ann_content">Content</label>
                                    <?php
                                    wp_editor(
                                        $this->data['content'] ?? '',
                                        'ann_content',
                                        [
                                            'textarea_name' => 'ann_content',
                                            'media_buttons' => true,
                                            'textarea_rows' => 8,
                                        ]
                                    );
                                    ?>
                                </div>

                                <!-- Featured Image -->
                                <div class="form-field">
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

                                <!-- Speakers -->
                                <div class="form-field">
                                    <label>Speakers</label>
                                    <div id="speakers_wrapper"></div>
                                    <button type="button" class="button" id="add_speaker">Add Speaker</button>
                                </div>

                                <!-- Location -->
                                <div class="form-field">
                                    <label for="ann_location">Location</label>
                                    <textarea id="ann_location" name="ann_location"><?php echo esc_textarea($this->data['location'] ?? ''); ?></textarea>
                                    <label for="ann_map_url" style="margin-top:10px;">Map URL (Google Maps)</label>
                                    <input type="url" id="ann_map_url" name="ann_map_url" value="<?php echo esc_attr($this->data['map_url'] ?? ''); ?>" />
                                </div>

                                <!-- Organizers -->
                                <div class="form-field">
                                    <label>Organizers</label>
                                    <div id="organizers_wrapper"></div>
                                    <button type="button" class="button" id="add_organizer">Add Organizer</button>
                                </div>

                            </div> <!-- end left column -->

                            <!-- RIGHT COLUMN -->
                            <div id="postbox-container-1" class="postbox-container">
                                <div class="postbox">
                                    <h2 class="hndle"><span>Settings</span></h2>
                                    <div class="inside">
                                        
                                        <!-- Publish Status -->
                                        <div class="form-field">
                                            <label for="publish_status">Status</label>
                                            <select id="publish_status" name="publish_status">
                                                <option value="draft" <?php selected($this->data['publish_status'] ?? '', 'draft'); ?>>Draft</option>
                                                <option value="published" <?php selected($this->data['publish_status'] ?? '', 'published'); ?>>Published</option>
                                            </select>
                                        </div>

                                        <!-- Published At -->
                                        <div class="form-field">
                                            <label for="publish_date">Published At</label>
                                            <input type="datetime-local" id="publish_date" name="publish_date" 
                                                value="<?php echo esc_attr($this->data['publish_date'] ?? ''); ?>" />
                                        </div>

                                        <!-- Expiry Date -->
                                        <div class="form-field">
                                            <label for="expiry_date">Expiry Date</label>
                                            <input type="datetime-local" id="expiry_date" name="expiry_date" 
                                                value="<?php echo esc_attr($this->data['expiry_date'] ?? ''); ?>" />
                                        </div>

                                        <!-- Contacts -->
                                        <div class="form-field">
                                            <label for="contact">Contact</label>
                                            <textarea id="contact" name="contact"><?php echo esc_textarea($this->data['contact'] ?? ''); ?></textarea>
                                        </div>

                                        <!-- Pricing (repeatable) -->
                                        <div class="form-field">
                                            <label>Pricing</label>
                                            <div id="pricing_wrapper"></div>
                                            <button type="button" class="button" id="add_pricing">Add Pricing</button>
                                        </div>

                                    </div> <!-- inside -->
                                </div> <!-- postbox -->
                            </div> <!-- postbox-container-1 -->

                        </div> <!-- end post-body -->
                    </div> <!-- end poststuff -->

                </div> <!-- end wrap -->
            </div> <!-- end wpsg-boxed -->
        </form>
        <?php
    }
}

// Usage:
new WPSG_AnnouncementsForm();
