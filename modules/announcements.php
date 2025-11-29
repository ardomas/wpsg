<?php
// File: wpsg/modules/announcements.php

if (!defined('ABSPATH')) exit;

class WPSG_Announcements {

    protected $posts_handler;
    protected $url_base;

    public function __construct() {

        /* handling - wpsg tables => wp_wpsg_posts, wp_wpsg_postmeta, wp_wpsg_cmments */
        require_once WPSG_DIR . '/includes/data/class-wpsg-posts-data.php';

        $this->posts_handler = WPSG_PostsData::get_instance();
        $this->url_base      = 'admin.php?page=wpsg-admin&view=announcements';

    }

    public function list(){

        add_action('admin_menu', [$this, 'register_menu']);
        $this->generate_list();

    }

    protected function list_register_menu() {
        add_menu_page(
            'Announcements',
            'Announcements',
            'manage_options',
            'wpsg_announcements',
            [$this, 'render'],
            'dashicons-megaphone',
            6
        );
    }

    protected function generate_list(){

        $page = 'wpsg-admin';
        $view = 'announcements';
        $announcements = $this->posts_handler->get_posts([
            'post_type' => 'announcement'
        ]);

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Announcements</h1>
            <a href="<?php echo admin_url( $this->url_base . '&action=add' ); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <!-- <table class="wp-list-table widefat fixed striped posts"> -->

            <table class="wpsg-full-width outer-border bordered hover stripped">
                <thead>
                    <tr>
                        <th></th>
                        <th class="manage-column" colspan="2">Title</th>
                        <th class="manage-column">Tags</th>
                        <th class="manage-column">Author</th>
                        <th class="manage-column">Date</th>
                    </tr>
                </thead>
                <tbody>
        <?php

                if (!empty($announcements)) {
                    foreach ($announcements as $ann) {

                        $meta = [];
                        if ($ann) { // pastikan post memang ada
                            $meta  = $this->posts_handler->get_meta($ann->id);
                            // $ann = array_merge((array) $ann, $meta);
                        }

                        $edit_link = admin_url( $this->url_base . '&action=edit&id=' . $ann->id);
                        $delete_link = wp_nonce_url(admin_url( $this->url_base .  '&action=delete&id=' . $ann->id), 'wpsg_ann_delete_' . $ann->id);

                        ?><tr>
                            <td>&nbsp;</td>
                            <td style="min-width: 250px;">
                                <strong></strong><a href="<?php echo esc_url($edit_link); ?>"><?php echo esc_html($ann->title); ?></a></strong><br/>
                                <div class="wpsg-flex">
                                    <div><?php echo ucfirst( $meta['locations']['city'] ); ?></div> |
                                    <div><?php 
                                        echo $meta['date_start'];
                                        if( $meta['date_start'] != $meta['date_end'] ){
                                            echo ' to ' . $meta['date_end'];
                                        }
                                    ?></div>
                                    <?php

                                        if( !is_null( $meta['time_start'] ) &&
                                            !is_null( $meta['time_end'  ] ) && 
                                            ( $meta['time_start']!=$meta['time_end'] ) ){
                                            ?><div><?php echo $meta['time_start'] . ' - ' . $meta['time_end']; ?></div><?php
                                        }

                                    ?>
                                    
                                </div>
                                <div class="row-actions">
                                    <span class="edit"><a href="<?php echo esc_url($edit_link); ?>">Edit</a> | </span>
                                    <span class="trash"><a href="<?php echo esc_url($delete_link); ?>">Trash</a></span>
                                </div>
                            </td><td class="column-thumbnail" style="width:70px;"><?php

                                // default placeholder box (abu-abu)
                                $placeholder_style = 'display:inline-block;width:60px;height:60px;background:#ddd;border:1px solid #ccc;border-radius:4px;';

                                if (!empty($meta['image'])) {
                                    $img_url = esc_url($meta['image']);
                                    echo '<img src="'.$img_url.'" style="width:60px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ccc;" />';
                                } else {
                                    echo '<span style="'.$placeholder_style.'"></span>';
                                }

                            ?></td><td><?php
                                echo $meta['tagline'] ?? '-';
                            ?></td><td><?php
                                echo esc_html(get_userdata($ann->author_id)->display_name);
                            ?></td><td><?php 
                                echo '<strong>' . ucfirst( $ann->status ) . '</strong>';

                                if( trim(strtolower( $ann->status )) == 'published' ){
                                    echo '<div>Date : ' . esc_html(date('Y-m-d H:i', strtotime($ann->published_at))) . '</div>';
                                }
                                if( $meta!=[] ){
                                    if( isset( $meta['expiry_date'] ) ){
                                        ?><div>Expired Date: <?php
                                        echo esc_html(date('Y-m-d H:i', strtotime($meta['expiry_date'])));
                                        ?></div><?php
                                    }
                                }

                            ?></td>
                        </tr><?php

                    }
                } else {
                    echo '<tr><td colspan="6">No announcements found.</td></tr>';
                }
        ?>

                </tbody>
            </table>
        </div>
        <?php

    }

    public function form(){

        $this->data = [
            'id' => 0,
            'title' => '',
            'status' => 'draft',
            'post_type' => 'announcement',
        ];

        $post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($post_id != 0) {
            $posts = WPSG_PostsData::get_instance();
            $post  = $posts->get_post($post_id);

            if ($post) { // pastikan post memang ada
                $meta  = $posts->get_meta($post_id);
                $this->data = array_merge((array) $post, $meta);
            }
        }

        $this->generate_form();
        $this->enqueue_assets();

    }

    protected function enqueue_assets() {
        // -----------------------------
        // WP Media (untuk upload image)
        // -----------------------------
        wp_enqueue_media();

        // -----------------------------
        // Flatpickr (date/time picker)
        // -----------------------------
        wp_enqueue_style(
            'flatpickr-css',
            'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
            [],
            '4.6.13' // versi Flatpickr bisa dicantumkan
        );

        wp_enqueue_script(
            'flatpickr-js',
            'https://cdn.jsdelivr.net/npm/flatpickr',
            [],
            '4.6.13',
            true // load di footer
        );

        // -----------------------------
        // Plugin Announcements CSS & JS
        // -----------------------------
        wp_enqueue_style(
            'wpsg-announcements-css',
            WPSG_URL . 'assets/css/announcements.css',
            [],
            WPSG_VERSION
        );

        wp_enqueue_script(
            'wpsg-announcements-js',
            WPSG_URL . 'assets/js/announcements.js',
            ['jquery', 'flatpickr-js'], // pastikan jQuery dan flatpickr sudah tersedia
            WPSG_VERSION,
            true // load di footer
        );

        // -----------------------------
        // Localize JS
        // -----------------------------
        wp_localize_script(
            'wpsg-announcements-js', // HARUS sama dengan handle JS di atas
            'WPSG_ANN_DATA',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'post_id'  => intval($this->data['id'] ?? 0),
                'nonce'    => wp_create_nonce('wpsg_ann_nonce'),
            ]
        );
    }

    protected function generate_form(){

        /*
        echo '$this->data:'
        ?><xmp><?php
        print_r( $this->data )
        ?></xmp><?php
        */

?>
        <form class="wpsg-form" method="post" id="wpsg-ann-form">

            <input type="hidden" name="action" value="wpsg_save_announcement">
            <input type="hidden" name="post_id" value="<?php echo esc_attr($this->data['id'] ?? 0); ?>">

            <?php wp_nonce_field('wpsg_save_announcement', 'wpsg_announcement_nonce'); ?>

            <div class="wpsg wpsg-boxed">
                <div class="wrap">
                    <h1><?php 
                        echo empty($this->data['id']) ? 'Add New Announcement' : 'Edit Announcement'; ?>
                        <a href="<?php echo admin_url( $this->url_base ); ?>" class="page-title-action">Back to List (All Announcements)</a>
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

                                        <div class="wpsg-form-field">

                                            <div class="postbox">
                                                <div class="inside">

                                                    <div class="wpsg-row">
                                                        <div class="col-12">
                                                            <div class="wpsg-form-field">
                                                                <label for="ann_subtitle">Sub Title</label>
                                                                <input type="text" id="ann_subtitle" name="ann_subtitle"
                                                                    value="<?php echo esc_attr($this->data['subtitle'] ?? ''); ?>" />
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div class="wpsg-row">
                                                        <div class="col-6">
                                                            <!-- Slug -->
                                                            <div class="wpsg-form-field">
                                                                <label for="ann_slug">Slug</label>
                                                                <input type="text" id="ann_slug" name="ann_slug"
                                                                    value="<?php echo esc_attr($this->data['slug'] ?? ''); ?>" />
                                                                <p class="description" id="slug-status"><?php echo '&nbsp'; ?></p>
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <!-- Tagline -->
                                                            <div class="wpsg-form-field">
                                                                <label for="ann_tagline">Tagline</label>
                                                                <input type="text" id="ann_tagline" name="ann_tagline"
                                                                    value="<?php echo esc_attr($this->data['tagline'] ?? ''); ?>" />
                                                            </div>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-4">
                                        <label for="publish_status">Settings</label>
                                        <div class="wpsg-form-field">
                                            <div class="postbox">
                                                <div class="inside">

                                                    <div class="wpsg-row">
                                                        <div class="col-12">
                                                            <div class="wpsg-form-field">
                                                                <label for="publish_status">Status</label>
                                                                <select id="publish_status" name="publish_status">
                                                                    <option value="draft" <?php selected($this->data['status'] ?? '', 'draft'); ?>>Draft</option>
                                                                    <option value="published" <?php selected($this->data['status'] ?? '', 'published'); ?>>Published</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="wpsg-row">
                                                        <div class="col-6">
                                                            <div class="wpsg-form-field">
                                                                <label for="publish_date">Published At</label>
                                                                <input type="datetime-local" id="publish_date" name="publish_date" 
                                                                    value="<?php echo esc_attr($this->data['published_at'] ?? ''); ?>" />
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
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
                                </div>

                                <div class="wpsg-row">
                                    <div class="col-8">
                                        <div class="wpsg-form-field">
                                            <label for="ann_content">Content</label>
                                            <div class="postbox">
                                                <div class="inside">

                                                    <!-- Content -->
                                                    <div class="wpsg-form-field">
                                                        <?php
                                                        wp_editor(
                                                            $this->data['content'] ?? '',
                                                            'ann_content',
                                                            ['textarea_name' => 'ann_content', 'media_buttons' => true, 'textarea_rows' => 8]
                                                        );
                                                        ?>
                                                    </div>

                                                </div>
                                            </div>

                                            <label>Speakers</label>
                                            <div class="postbox">
                                                <div class="inside">

                                                    <div class="wpsg-form-field">
                                                        <!-- repeatable -->
                                                        <div id="speakers_wrapper"><?php

            if (!empty($this->data['speakers']) && is_array($this->data['speakers'])) {
                foreach ($this->data['speakers'] as $index => $spk) {
                    echo '<div class="repeatable-row">';
                    echo '<input type="text" name="speakers['.$index.'][name]" value="'.esc_attr($spk['name'] ?? '').'" placeholder="Name" required />';
                    echo '<input type="text" name="speakers['.$index.'][company]" value="'.esc_attr($spk['company'] ?? '').'" placeholder="Company" />';
                    echo '<input type="text" name="speakers['.$index.'][position]" value="'.esc_attr($spk['position'] ?? '').'" placeholder="Position" />';
                    echo '<button type="button" class="button remove-row"></button>';
                    echo '</div>';
                }
            }
                                                        ?></div>
                                                        <button type="button" class="button add-repeatable" data-type="speaker">Add Speaker</button>
                                                    </div>

                                                </div>
                                            </div>

                                            <label>Location</label>
                                            <div class="postbox">
                                                <div class="inside">

                                                    <div class="wpsg-form-field">
                                                        <div id="wpsg-row">
                                                            <div class="wpsg-form-field">
                                                                <label>Address</label>
                                                                <textarea name="ann_address" class="regular-text"><?php 
                                                                    echo esc_attr( $this->data['locations']['address'] ?? '' ); 
                                                                ?></textarea>
                                                            </div>
                                                            <div class="wpsg-form-field">
                                                                <div class="wpsg-row">
                                                                    <div class="col-6">
                                                                        <label>City</label>
                                                                        <input name="ann_city" value="<?php echo esc_attr( $this->data['locations']['city'] ?? '' ); ?>" placeholder="City's Name"/>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label>Google Map</label>
                                                                        <input name="ann_map_url" value="<?php echo esc_attr( $this->data['locations']['map_url'] ?? '' ); ?>" placeholder="map url (google)"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <label>Organizers</label>
                                            <div class="postbox">
                                                <div class="inside">
                                                    <!-- Organizers -->
                                                    <div class="wpsg-form-field">
                                                        <!-- repeatable -->
                                                        <div id="organizers_wrapper"><?php

            if (!empty($this->data['organizers']) && is_array($this->data['organizers'])) {
                foreach ($this->data['organizers'] as $index => $org) {
                    echo '<div class="repeatable-row">';
                    echo '<input type="text" name="organizers['.$index.'][name]" value="'.esc_attr($org['name'] ?? '').'" placeholder="Name / Organization" required />';
                    echo '<input type="text" name="organizers['.$index.'][description]" value="'.esc_attr($org['description'] ?? '').'" placeholder="Description" />';
                    echo '<button type="button" class="button remove-row"></button>';
                    echo '</div>';
                }
            }

                                                        ?></div>
                                                        <button type="button" class="button add-repeatable" data-type="organizer">Add Organizer</button>
                                                    </div>


                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="wpsg-form-field">

                                            <label>Featured Image</label>
                                            <div class="postbox">
                                                <div class="inside">
                                                    <!-- Featured Image -->
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

                                            <label>Dates and Times</label>
                                            <div class="postbox">
                                                <div class="inside">
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
                                                </div>
                                            </div>

                                            <label>Pricing</label>
                                            <div class="postbox">
                                                <div class="inside">

                                                    <!-- Pricings -->
                                                    <div class="wpsg-form-field">
                                                        <label for="ann_price_label">Price Label</label>
                                                        <input type="input" class="ann_price_label" name="ann_price_label" 
                                                            value="<?php echo esc_attr( $this->data['pricings']['label'] ?? '' ); ?>" placeholder="Administration's Fee"/>
                                                    </div>
                                                    <div class="wpsg-form-field">
                                                        <!-- repeatable -->
                                                        <div id="pricings_wrapper"><?php

if (!empty($this->data['pricings']['values']) && is_array($this->data['pricings']['values'])) {
    foreach ($this->data['pricings']['values'] as $index => $pr) {
        echo '<div class="repeatable-row">';
        echo '<input type="text" name="ann_price_values['.$index.'][value]" value="'.esc_attr($pr['value'] ?? '').'" placeholder="Price / Nominal" required />';
        echo '<input type="text" name="ann_price_values['.$index.'][note]" value="'.esc_attr($pr['note'] ?? '').'" placeholder="Price Note" />';
        echo '<button type="button" class="button remove-row"></button>';
        echo '</div>';
    }
}

                                                        ?></div>
                                                        <button type="button" class="button add-repeatable" data-type="pricing">Add Pricing</button>
                                                    </div>


                                                </div>
                                            </div>

                                            <label>Contacts</label>
                                            <div class="postbox">
                                                <div class="inside">

                                                    <!-- Contacts -->
                                                    <div class="wpsg-form-field">
                                                        <!-- repeatable -->
                                                        <div id="contacts_wrapper"><?php

if (!empty($this->data['contacts']) && is_array($this->data['contacts'])) {
    foreach ($this->data['contacts'] as $index => $ct) {
        echo '<div class="repeatable-row">';
        echo '<input type="text" name="contacts['.$index.'][name]" value="'.esc_attr($ct['name'] ?? '').'" placeholder="Name" required />';
        echo '<input type="text" name="contacts['.$index.'][number]" value="'.esc_attr($ct['number'] ?? '').'" placeholder="Phone / Email" />';
        echo '<button type="button" class="button remove-row"></button>';
        echo '</div>';
    }
}

                                                        ?></div>
                                                        <button type="button" class="button add-repeatable" data-type="contact">Add Contact</button>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="wpsg-form-field">

                                    <button type="submit" class="button button-primary">Save Announcement</button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <script>

                jQuery(document).ready(function($){
                    $('#wpsg-ann-form').on('submit', function(e){
                        e.preventDefault(); // cegah reload

                        var formData = $(this).serialize();

                        // console.log( formData );

                        $.ajax({
                            url: WPSG_ANN_DATA.ajax_url,
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                        }).done((response)=>{
                            if(response.success){
                                alert('Announcement saved! ID: ' + response.data.post_id);
                            } else {
                                alert('Failed to save: ' + response.data.message);
                            }
                        }).fail((xhr,status,error)=>{
                            console.error(error);
                            alert('AJAX error!');
                        });

                    });
                });

            </script>
        </form>
        <?php

    }

    public function save_announcement() {

        if (empty($_POST) || !isset($_POST['wpsg_announcement_nonce'])) {
            return false;
        }

        // Periksa nonce untuk keamanan
        if (!wp_verify_nonce($_POST['wpsg_announcement_nonce'], 'wpsg_save_announcement')) {
            wp_die('Security check failed.');
        }

        // error_log("===== POST DATA =====");
        // error_log(print_r($_POST, true));

        $wpsg_posts = $this->posts_handler;

        // Ambil ID jika update
        $post_id = intval($_POST['post_id'] ?? 0);

        // Data utama post
        $post_data = [
            'post_type'    => 'announcement',
            'title'        => sanitize_text_field($_POST['ann_title'] ?? ''),
            'status'       => sanitize_text_field($_POST['publish_status'] ?? 'draft'),
            'author_id'    => get_current_user_id(),
            'published_at' => sanitize_text_field($_POST['publish_date'] ?? current_time('mysql')),
            'updated_at'   => current_time('mysql'),
        ];

        if ($post_id > 0) {
            // Update existing post
            $wpsg_posts->set_post($post_id, $post_data);
        } else {
            // Create new post
            $post_data['created_at'] = current_time('mysql');
            $post_data['site_id'] = get_current_blog_id();
            $post_id = $wpsg_posts->create_post($post_data);
        }

        // Data meta
        $meta_fields = [
            'slug'       => sanitize_title( $_POST['ann_slug'] ?? '' ),
            'subtitle'   => sanitize_text_field( $_POST['ann_subtitle'] ?? '' ),
            'content'    => wp_kses_post($_POST['ann_content'] ?? ''),
            'date_start' => sanitize_text_field( $_POST['ann_date_start'] ?? '' ),
            'date_end'   => sanitize_text_field( $_POST['ann_date_end'  ] ?? '' ),
            'time_start' => sanitize_text_field( $_POST['ann_time_start'] ?? '' ),
            'time_end'   => sanitize_text_field( $_POST['ann_time_end'  ] ?? '' ),
            'image'      => esc_url_raw($_POST['ann_image'] ?? ''),
            'locations'  => [
                'address'=> sanitize_text_field( $_POST['ann_address'] ?? '' ),
                'city'   => sanitize_text_field( $_POST['ann_city'   ] ?? '' ),
                'map_url'=> sanitize_text_field( $_POST['ann_map_url'] ?? '' ),
            ],
            'speakers'   => $_POST['speakers'  ] ?? [],
            'organizers' => $_POST['organizers'] ?? [],
            'pricings'   => [
                'label' => sanitize_text_field( $_POST['ann_price_label'] ?? 'Administration\'s Fees' ),
                'values'=> $_POST['ann_price_values'] ?? [],
            ],
            'contacts'   => $_POST['contacts'] ?? [],
            'tagline'    => sanitize_text_field($_POST['ann_tagline'] ?? ''),
            'expiry_date'=> sanitize_text_field($_POST['expiry_date'] ?? ''),
        ];

        // Simpan meta
        foreach ($meta_fields as $key => $value) {
            $wpsg_posts->set_meta($post_id, $key, $value);
        }

        return $post_id;
    }

}