<?php
// modules/announcements/form-left.php
if (!defined('ABSPATH')) exit;

/**
 * Announcement Form - Left Column
 */
?>

<div class="wpsg-form-left">

    <!-- Title -->
    <div class="form-field">
        <label for="ann_title">Title</label>
        <input type="text" id="ann_title" name="ann_title" 
               value="<?php echo esc_attr($data['title'] ?? ''); ?>" required />
    </div>

    <!-- Slug & Tagline -->
    <div class="flex-row">

        <div class="form-field form-col-2">
            <label for="ann_slug">Slug</label>
            <input type="text" id="ann_slug" name="ann_slug" 
                   value="<?php echo esc_attr($data['slug'] ?? ''); ?>" />
            <p class="description" id="slug-status"></p>
        </div>

        <div class="form-field form-col-2">
            <label for="ann_tagline">Tagline</label>
            <input type="text" id="ann_tagline" name="ann_tagline" 
                   value="<?php echo esc_attr($data['tagline'] ?? ''); ?>" />
        </div>

    </div>

    <!-- Subtitle -->
    <div class="form-field">
        <label for="ann_subtitle">Subtitle</label>
        <input type="text" id="ann_subtitle" name="ann_subtitle"
               value="<?php echo esc_attr($data['subtitle'] ?? ''); ?>" />
    </div>

    <!-- Date & Time -->
    <div class="form-field">
        <label>Date & Time</label>

        <div class="wpsg-datetime-row">
            <div>
                <label for="date_start">Start Date</label>
                <input type="date" id="date_start" name="date_start"
                       value="<?php echo esc_attr($data['date_start'] ?? ''); ?>" />
            </div>

            <div>
                <label for="date_end">End Date</label>
                <input type="date" id="date_end" name="date_end"
                       value="<?php echo esc_attr($data['date_end'] ?? ''); ?>" />
            </div>
        </div>

        <div class="wpsg-datetime-row">
            <div>
                <label for="time_start">Start Time</label>
                <input type="time" id="time_start" name="time_start"
                       value="<?php echo esc_attr($data['time_start'] ?? ''); ?>" />
            </div>

            <div>
                <label for="time_end">End Time</label>
                <input type="time" id="time_end" name="time_end"
                       value="<?php echo esc_attr($data['time_end'] ?? ''); ?>" />
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="form-field">
        <label for="ann_content">Content</label>
        <?php
        wp_editor(
            $data['content'] ?? '',
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

            <?php if (!empty($data['image'])): ?>
                <img src="<?php echo esc_url($data['image']); ?>" 
                     style="max-width:150px; display:block; margin-bottom:10px;" />
            <?php endif; ?>

            <input type="hidden" id="ann_image" name="ann_image" 
                   value="<?php echo esc_attr($data['image'] ?? ''); ?>" />

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
        <textarea id="ann_location" name="ann_location"><?php echo esc_textarea($data['location'] ?? ''); ?></textarea>

        <label for="ann_map_url" style="margin-top:10px;">Map URL (Google Maps)</label>
        <input type="url" id="ann_map_url" name="ann_map_url"
               value="<?php echo esc_attr($data['map_url'] ?? ''); ?>" />
    </div>

    <!-- Organizers -->
    <div class="form-field">
        <label>Organizers</label>
        <div id="organizers_wrapper"></div>
        <button type="button" class="button" id="add_organizer">Add Organizer</button>
    </div>

</div>
