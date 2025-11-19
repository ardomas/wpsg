<?php
// admin/views/profile.php
if (!defined('ABSPATH')) exit;

// Ambil data profile
$profile_data = get_option('wpsg_profile_data', [
    'logo' => '',
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

    $profile_data['description'] = wp_kses_post($_POST['description']);
    $profile_data['history'] = wp_kses_post($_POST['history']);
    $profile_data['vision'] = wp_kses_post($_POST['vision']);
    $profile_data['mission'] = wp_kses_post($_POST['mission']);
    $profile_data['goal'] = wp_kses_post($_POST['goal']);

    // Gunakan logo dari Media Library saja
    if (!empty($_POST['logo_media_url'])) {
        $profile_data['logo'] = esc_url($_POST['logo_media_url']);
    }

    update_option('wpsg_profile_data', $profile_data);
    echo '<div class="notice notice-success"><p>Profile saved successfully.</p></div>';
}

?>

<h1>Organization / Institution Profile</h1>

<form method="post" action="" id="wpsg-profile-form">
    <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>

    <!-- LOGO -->
    <div class="wpsg-form-row">
        <label>Logo</label>
        <div>
            <img id="logo-preview" class="wpsg-logo-preview" src="<?php echo esc_url($profile_data['logo']); ?>" <?php echo $profile_data['logo'] ? '' : 'style="display:none;"'; ?>>
            <input type="hidden" name="logo_media_url" id="logo_media_url" value="<?php echo esc_url($profile_data['logo']); ?>">
            <button type="button" id="choose-logo" class="button">Choose from Media Library</button>
        </div>
    </div>

    <!-- TEXT FIELDS -->
    <div class="wpsg-form-row">
        <label for="name">Institution Name</label>
        <input type="text" name="name" id="name" value="<?php echo esc_attr($profile_data['name']); ?>" required>
    </div>

    <div class="wpsg-form-row">
        <label for="short_name">Short Name</label>
        <input type="text" name="short_name" id="short_name" value="<?php echo esc_attr($profile_data['short_name']); ?>">
    </div>

    <div class="wpsg-form-row">
        <label for="slogan">Slogan</label>
        <input type="text" name="slogan" id="slogan" value="<?php echo esc_attr($profile_data['slogan']); ?>">
    </div>

    <!-- TYPE -->
    <div class="wpsg-form-row">
        <label for="types">Type</label>
        <select name="types[]" id="types" multiple>
            <?php 
            $options = ['Company','Organization','Education','Profit','Non-Profit'];
            foreach ($options as $opt):
                $selected = in_array($opt, $profile_data['types']) ? 'selected' : '';
            ?>
                <option value="<?php echo esc_attr($opt); ?>" <?php echo $selected; ?>><?php echo esc_html($opt); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- WYSIWYG FIELDS -->
    <?php 
    $wysiwyg_fields = ['description'=>'Description','history'=>'Brief History','vision'=>'Vision','mission'=>'Mission','goal'=>'Goal'];
    foreach ($wysiwyg_fields as $field_key => $label): ?>
        <div class="wpsg-form-row" style="width:100%;">
            <label for="<?php echo $field_key; ?>"><?php echo $label; ?></label>
            <?php wp_editor($profile_data[$field_key], $field_key, [
                'textarea_name'=>$field_key,
                'editor_height'=>250,
                'media_buttons'=>true,
                'tinymce'=>true
            ]); ?>
        </div>
    <?php endforeach; ?>

    <div class="wpsg-form-row">
        <button type="submit" class="button button-primary">Save Profile</button>
    </div>
</form>
