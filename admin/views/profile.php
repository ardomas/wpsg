<?php
// admin/views/profile.php
if (!defined('ABSPATH')) exit;

// Load existing profile data or default values
$profile_data = get_option('wpsg_profile_data', [
    'logo' => get_theme_mod('custom_logo') ? wp_get_attachment_url(get_theme_mod('custom_logo')) : '',
    'name' => '',
    'short_name' => '',
    'types' => [],
    'slogan' => '',
    'description' => '',
    'history' => '',
    'vision' => '',
    'mission' => '',
    'goal' => '',
    'address' => '',
    'city' => '',
    'country' => '',
    'postcode' => '',
    'phone' => '',
    'email' => '',
    'map' => '',
]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wpsg_profile_nonce']) && wp_verify_nonce($_POST['wpsg_profile_nonce'], 'wpsg_save_profile')) {
    
    // Sanitize text inputs
    $profile_data['name'] = sanitize_text_field($_POST['name']);
    $profile_data['short_name'] = sanitize_text_field($_POST['short_name']);
    $profile_data['slogan'] = sanitize_text_field($_POST['slogan']);
    $profile_data['types'] = isset($_POST['types']) ? array_map('sanitize_text_field', $_POST['types']) : [];

    $profile_data['address'] = sanitize_text_field($_POST['address']);
    $profile_data['city'] = sanitize_text_field($_POST['city']);
    $profile_data['country'] = sanitize_text_field($_POST['country']);
    $profile_data['postcode'] = sanitize_text_field($_POST['postcode']);
    $profile_data['phone'] = sanitize_text_field($_POST['phone']);
    $profile_data['email'] = sanitize_email($_POST['email']);
    $profile_data['map'] = sanitize_text_field($_POST['map']);

    // Rich text fields
    $profile_data['description'] = wp_kses_post($_POST['description']);
    $profile_data['history'] = wp_kses_post($_POST['history']);
    $profile_data['vision'] = wp_kses_post($_POST['vision']);
    $profile_data['mission'] = wp_kses_post($_POST['mission']);
    $profile_data['goal'] = wp_kses_post($_POST['goal']);

    // Handle logo upload or media library selection
    if (!empty($_FILES['logo']['name'])) {
        $upload = wp_handle_upload($_FILES['logo'], ['test_form' => false]);
        if (!isset($upload['error'])) {
            $profile_data['logo'] = $upload['url'];
        }
    } elseif (!empty($_POST['logo_media_url'])) {
        $profile_data['logo'] = esc_url($_POST['logo_media_url']);
    }

    update_option('wpsg_profile_data', $profile_data);
    echo '<div class="notice notice-success"><p>Profile saved successfully.</p></div>';
}

// Enqueue WP editor and media library
wp_enqueue_editor();
wp_enqueue_media();
?>

<h1>Organization / Institution Profile</h1>

<form method="post" action="" enctype="multipart/form-data" id="wpsg-profile-form">
    <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>

    <!-- INSTITUTION IDENTITY -->
    <div class="wpsg-section-card">
        <h2>Institution Identity</h2>

        <!-- Logo -->
        <div class="wpsg-form-row">
            <label for="logo">Logo</label>
            <div>
                <img src="<?php echo esc_url($profile_data['logo']); ?>" alt="Logo" class="wpsg-logo-preview" id="logo-preview" <?php echo $profile_data['logo'] ? '' : 'style="display:none;"'; ?>>
                <input type="file" name="logo" id="logo">
                <input type="hidden" name="logo_media_url" id="logo_media_url" value="<?php echo esc_url($profile_data['logo']); ?>">
                <button type="button" id="choose-logo" class="button">Choose from Media Library</button>
            </div>
        </div>

        <!-- Name -->
        <div class="wpsg-form-row">
            <label for="name">Institution Name</label>
            <input type="text" name="name" id="name" value="<?php echo esc_attr($profile_data['name']); ?>" required>
        </div>

        <!-- Short Name -->
        <div class="wpsg-form-row">
            <label for="short_name">Short Name (Optional)</label>
            <input type="text" name="short_name" id="short_name" value="<?php echo esc_attr($profile_data['short_name']); ?>">
        </div>

        <!-- Type -->
        <div class="wpsg-form-row">
            <label for="types">Type</label>
            <select name="types[]" id="types" multiple>
                <?php 
                $options = ['Company','Organization','Education','Profit','Non-Profit'];
                foreach ($options as $opt) :
                    $selected = in_array($opt, $profile_data['types']) ? 'selected' : '';
                ?>
                    <option value="<?php echo esc_attr($opt); ?>" <?php echo $selected; ?>><?php echo esc_html($opt); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Slogan -->
        <div class="wpsg-form-row">
            <label for="slogan">Slogan</label>
            <input type="text" name="slogan" id="slogan" value="<?php echo esc_attr($profile_data['slogan']); ?>">
        </div>
    </div>

    <!-- DESCRIPTION, VISION, MISSION, GOAL -->
    <div class="wpsg-section-card">
        <h2>Description, Vision, Mission & Goal</h2>

        <?php
        $wysiwyg_fields = ['description'=>'Description','history'=>'Brief History','vision'=>'Vision','mission'=>'Mission','goal'=>'Goal'];
        foreach ($wysiwyg_fields as $field_key => $label) :
        ?>
            <div class="wpsg-form-row" style="width:100%;">
                <label for="<?php echo $field_key; ?>"><?php echo $label; ?></label>
                <?php 
                wp_editor($profile_data[$field_key], $field_key, [
                    'textarea_name'=>$field_key,
                    'editor_height'=>250,
                    'media_buttons'=>true,
                    'tinymce'=>true
                ]); 
                ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- CONTACT & LOCATION -->
    <div class="wpsg-section-card">
        <h2>Contact & Location</h2>

        <?php
        $contact_fields = [
            'address'=>'Address',
            'city'=>'City',
            'country'=>'Country',
            'postcode'=>'Postal Code',
            'phone'=>'Phone Number',
            'email'=>'Institution Email',
            'map'=>'Map Pin Point (Latitude, Longitude)'
        ];
        foreach ($contact_fields as $field_key => $label) :
        ?>
            <div class="wpsg-form-row">
                <label for="<?php echo $field_key; ?>"><?php echo $label; ?></label>
                <input type="text" name="<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" value="<?php echo esc_attr($profile_data[$field_key]); ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wpsg-form-row">
        <button type="submit" class="button button-primary">Save Profile</button>
    </div>
</form>

<style>
.wpsg-section-card { border: 1px solid #ddd; padding: 20px; margin-bottom: 25px; border-radius: 6px; background: #fff; }
.wpsg-section-card h2 { margin-top: 0; font-size: 1.3rem; margin-bottom: 15px; }
.wpsg-form-row { display: flex; flex-wrap: wrap; margin-bottom: 15px; align-items: flex-start; }
.wpsg-form-row label { width: 200px; font-weight: bold; padding-top: 5px; }
.wpsg-form-row input[type=text], .wpsg-form-row input[type=email], .wpsg-form-row textarea, .wpsg-form-row select { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
.wpsg-logo-preview { display: block; max-width: 150px; max-height: 150px; margin-bottom: 10px; border: 1px solid #ccc; padding: 5px; border-radius: 4px; }
#choose-logo { margin-top: 5px; }

/* Full-width WYSIWYG */
.wp-editor-wrap, .wp-editor-container, .wp-editor-area { width: 100% !important; min-height: 250px; }
</style>

<script>
jQuery(document).ready(function($){
    // Preview logo on upload
    $('#logo').on('change', function(e){
        const reader = new FileReader();
        reader.onload = function(e){
            $('#logo-preview').attr('src', e.target.result).show();
            $('#logo_media_url').val('');
        }
        reader.readAsDataURL(this.files[0]);
    });

    // Integrate WordPress Media Library for selecting logo
    $('#choose-logo').on('click', function(e){
        e.preventDefault();
        var media_frame = wp.media({
            title: 'Select Institution Logo',
            button: { text: 'Select' },
            multiple: false
        });
        media_frame.on('select', function(){
            var attachment = media_frame.state().get('selection').first().toJSON();
            $('#logo-preview').attr('src', attachment.url).show();
            $('#logo_media_url').val(attachment.url);
            $('#logo').val('');
        });
        media_frame.open();
    });
});
</script>
