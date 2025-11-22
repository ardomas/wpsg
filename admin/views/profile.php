<?php
// admin/views/profile.php
if (!defined('ABSPATH')) exit;

// load existing profile option (single option)
$profile = get_option('wpsg_organization_profile', []);

// default fields
$defaults = [
    'full_name' => '',
    'short_name' => '',
    'tagline' => '',
    'description' => '',
    'founded_year' => '',
    'phone' => '',
    'email' => '',
    'website' => '',
    'address' => '',
    'city' => '',
    'province' => '',
    'country' => '',
    'postal_code' => '',
    'map_link' => '',
    'logo_id' => get_theme_mod('custom_logo') ? get_theme_mod('custom_logo') : '',
    'socials' => [],
    'history' => '',
    'vision' => '',
    'missions' => [],
    'goals' => [],
];

$profile = wp_parse_args($profile, $defaults);

// helpers
function wpsg_profile_field($key, $default='') {
    global $profile;
    return isset($profile[$key]) ? $profile[$key] : $default;
}

$logo_url = '';
if (!empty($profile['logo_id'])) {
    $logo_url = wp_get_attachment_url($profile['logo_id']);
} elseif (get_theme_mod('custom_logo')) {
    $logo_url = wp_get_attachment_url(get_theme_mod('custom_logo'));
}

$base_admin_url = admin_url('admin.php?page=wpsg-admin');
?>

<div class="wpsg-page-header">
    <h1>Organization Profile</h1>
    <p class="description">Information about your organization (company, school, community or NGO).</p>
</div>

<div style="display: flex; gap: 20px;">

    <!-- LEFT COLUMN -->
    <div style="width: calc(100vw - 160px); ">


        <h2>Manage Profile Information</h2>
        <div style="width: calc(100vw-160px); display: flex; gap: 20px;">
            <div class="wpsg-col-2">

                <!-- GENERAL / IDENTITY -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="wpsg-partial-form" data-section="general">
                    <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>
                    <input type="hidden" name="action" value="wpsg_save_section">
                    <input type="hidden" name="section" value="general">

                    <div class="wpsg-card">
                        <h2 class="wpsg-card-title">General Information</h2>

                        <div class="wpsg-row">
                            <div class="wpsg-col-2">
                                <label for="full_name" class="wpsg-row">Full Name</label>
                                <input id="full_name" name="full_name" type="text" value="<?php echo esc_attr( wpsg_profile_field('full_name') ); ?>" class="regular-text" required>
                            </div>
                            <div class="wpsg-col-2">
                                <label for="short_name" class="wpsg-row">Short Name / Abbreviation (optional)</label>
                                <input id="short_name" name="short_name" type="text" value="<?php echo esc_attr( wpsg_profile_field('short_name') ); ?>" class="regular-text">
                            </div>
                        </div>

                        <div class="wpsg-field">
                            <label for="tagline" class="wpsg-row">Tagline / Motto / Slogan</label>
                            <input id="tagline" name="tagline" type="text" value="<?php echo esc_attr( wpsg_profile_field('tagline') ); ?>" class="regular-text">
                        </div>

                        <div class="wpsg-field">
                            <label for="description" class="wpsg-row">Description</label>
                            <textarea id="description" name="description" rows="5"><?php echo esc_textarea( wpsg_profile_field('description') ); ?></textarea>
                        </div>

                        <div class="wpsg-row">
                            <div class="wpsg-col-2">
                                <label for="founded_year" class="wpsg-row">Founded Year</label>
                                <input id="founded_year" name="founded_year" type="text" value="<?php echo esc_attr( wpsg_profile_field('founded_year') ); ?>" class="regular-text" placeholder="e.g. 1998">
                            </div>
                            <div class="wpsg-col-2">
                                <p class="description" style="margin-top:28px;">Save this section to preserve General Information.</p>
                            </div>
                        </div>

                        <p>
                            <button type="submit" class="button button-primary">Save General</button>
                        </p>
                    </div>
                </form>

                <!-- CONTACT -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="wpsg-partial-form" data-section="contact">
                    <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>
                    <input type="hidden" name="action" value="wpsg_save_section">
                    <input type="hidden" name="section" value="contact">

                    <div class="wpsg-card">
                        <h2 class="wpsg-card-title">Contact Information</h2>

                        <div class="wpsg-field">
                            <label for="phone">Phone Number</label>
                            <input id="phone" name="phone" type="text" value="<?php echo esc_attr( wpsg_profile_field('phone') ); ?>" class="regular-text">
                        </div>

                        <div class="wpsg-field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="<?php echo esc_attr( wpsg_profile_field('email') ); ?>" class="regular-text">
                        </div>

                        <div class="wpsg-field">
                            <label for="website">Website</label>
                            <input id="website" name="website" type="text" value="<?php echo esc_attr( wpsg_profile_field('website') ); ?>" class="regular-text">
                        </div>

                        <p>
                            <button type="submit" class="button button-primary">Save Contact</button>
                        </p>
                    </div>
                </form>

                <!-- ADDRESS -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="wpsg-partial-form" data-section="address">
                    <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>
                    <input type="hidden" name="action" value="wpsg_save_section">
                    <input type="hidden" name="section" value="address">

                    <div class="wpsg-card">
                        <h2 class="wpsg-card-title">Address</h2>

                        <div class="wpsg-field">
                            <label for="address">Street Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo esc_textarea( wpsg_profile_field('address') ); ?></textarea>
                        </div>

                        <div class="wpsg-row">
                            <div class="wpsg-col-3">
                                <label for="city">City</label>
                                <input id="city" name="city" type="text" value="<?php echo esc_attr( wpsg_profile_field('city') ); ?>" class="regular-text">
                            </div>
                            <div class="wpsg-col-3">
                                <label for="province">State / Province</label>
                                <input id="province" name="province" type="text" value="<?php echo esc_attr( wpsg_profile_field('province') ); ?>" class="regular-text">
                            </div>
                            <div class="wpsg-col-3">
                                <label for="postal_code">Postal Code</label>
                                <input id="postal_code" name="postal_code" type="text" value="<?php echo esc_attr( wpsg_profile_field('postal_code') ); ?>" class="regular-text">
                            </div>
                        </div>

                        <div class="wpsg-field">
                            <label for="country">Country</label>
                            <input id="country" name="country" type="text" value="<?php echo esc_attr( wpsg_profile_field('country') ); ?>" class="regular-text">
                        </div>

                        <div class="wpsg-field">
                            <label for="map_link">Map Link (Google Maps URL)</label>
                            <input id="map_link" name="map_link" type="text" value="<?php echo esc_attr( wpsg_profile_field('map_link') ); ?>" class="regular-text" placeholder="https://maps.google.com/...">
                        </div>

                        <p>
                            <button type="submit" class="button button-primary">Save Address</button>
                        </p>
                    </div>
                </form>


            </div>
            <div class="wpsg-col-2">

                <!-- LOGO -->
                <div class="wpsg-card">
                    <h2 class="wpsg-card-title">Logo</h2>
                    <p class="description">Use WordPress Media Library to select organization logo. If blank, website logo will be used.</p>

                    <div class="wpsg-logo-preview">
                        <?php if ($logo_url): ?>
                            <img id="wpsg-logo-img" src="<?php echo esc_url($logo_url); ?>" alt="Logo Preview">
                        <?php else: ?>
                            <img id="wpsg-logo-img" src="" alt="Logo Preview" style="display:none;">
                        <?php endif; ?>
                    </div>

                    <p>
                        <button id="wpsg-select-logo" class="button">Select Logo from Media Library</button>
                        <button id="wpsg-remove-logo" class="button" style="<?php echo $profile['logo_id'] ? '' : 'display:none;'; ?>">Remove Logo</button>
                    </p>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="wpsg-partial-form" data-section="logo" style="margin-top:12px;">
                        <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>
                        <input type="hidden" name="action" value="wpsg_save_section">
                        <input type="hidden" name="section" value="logo">
                        <input type="hidden" id="wpsg_logo_id" name="logo_id" value="<?php echo esc_attr( wpsg_profile_field('logo_id') ); ?>">
                        <button type="submit" class="button button-primary">Save Logo</button>
                    </form>
                </div>

                <!-- SOCIALS -->
                <div class="wpsg-card">
                    <h2 class="wpsg-card-title">Social Media</h2>
                    <p class="description">Add the social platforms used by your organization. Choose a platform and add the account handle or full url.</p>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="wpsg-partial-form" data-section="socials">
                        <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>
                        <input type="hidden" name="action" value="wpsg_save_section">
                        <input type="hidden" name="section" value="socials">

                        <div id="wpsg-social-list">
                            <?php
                            $socials = wpsg_profile_field('socials', []);
                            if (!is_array($socials)) $socials = [];
                            foreach ($socials as $s) {
                                $platform = isset($s['platform']) ? $s['platform'] : '';
                                $handle = isset($s['handle']) ? $s['handle'] : '';
                                echo '<div class="wpsg-social-row">';
                                echo '<select name="socials_platform[]">'. wpsg_social_platform_options($platform) .'</select>';
                                echo '<input type="text" name="socials_handle[]" value="'.esc_attr($handle).'" class="regular-text" placeholder="username or full URL">';
                                echo '<a href="#" class="wpsg-remove-item">Remove</a>';
                                echo '</div>';
                            }
                            ?>
                        </div>

                        <p>
                            <button class="button wpsg-add-social">+ Add Social</button>
                        </p>

                        <p>
                            <button type="submit" class="button button-primary">Save Socials</button>
                        </p>
                    </form>
                </div>

            </div>
        </div>

        <!-- IDENTITY & DIRECTION (history, vision, mission, goals) -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="wpsg-partial-form" data-section="identity">
            <?php wp_nonce_field('wpsg_save_profile', 'wpsg_profile_nonce'); ?>
            <input type="hidden" name="action" value="wpsg_save_section">
            <input type="hidden" name="section" value="identity">

            <div class="wpsg-card">
                <h2 class="wpsg-card-title">History, Vision, Mission & Goals</h2>

                <div class="wpsg-field">
                    <label for="history">Brief History</label>
                    <?php wp_editor( wpsg_profile_field('history'), 'wpsg_history', ['textarea_name'=>'history','textarea_rows'=>6] ); ?>
                </div>

                <div class="wpsg-field">
                    <label for="vision">Vision</label>
                    <?php wp_editor( wpsg_profile_field('vision'), 'wpsg_vision', ['textarea_name'=>'vision','textarea_rows'=>4] ); ?>
                </div>

                <div class="wpsg-field">
                    <label>Mission (multiple items)</label>
                    <div id="wpsg-missions">
                        <?php
                        $missions = wpsg_profile_field('missions', []);
                        if (!is_array($missions)) $missions = [];
                        foreach ($missions as $m) {
                            echo '<div class="wpsg-repeat-item"><input type="text" name="missions[]" value="'.esc_attr($m).'" class="regular-text"><a href="#" class="wpsg-remove-item">Remove</a></div>';
                        }
                        ?>
                    </div>
                    <p><button class="button wpsg-add-item" data-target="#wpsg-missions">Add Mission</button></p>
                </div>

                <div class="wpsg-field">
                    <label>Goals (multiple items)</label>
                    <div id="wpsg-goals">
                        <?php
                        $goals = wpsg_profile_field('goals', []);
                        if (!is_array($goals)) $goals = [];
                        foreach ($goals as $g) {
                            echo '<div class="wpsg-repeat-item"><input type="text" name="goals[]" value="'.esc_attr($g).'" class="regular-text"><a href="#" class="wpsg-remove-item">Remove</a></div>';
                        }
                        ?>
                    </div>
                    <p><button class="button wpsg-add-item" data-target="#wpsg-goals">Add Goal</button></p>
                </div>

                <p>
                    <button type="submit" class="button button-primary">Save Identity</button>
                </p>
            </div>
        </form>

    </div> <!-- left -->

</div> <!-- wrapper -->

<?php
// Helper to render platform options (server-side)
function wpsg_social_platform_options($selected = '') {
    $platforms = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'twitter' => 'Twitter / X',
        'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn',
        'tiktok' => 'TikTok',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'custom' => 'Custom (name)'
    ];
    $out = '';
    foreach($platforms as $key=>$label) {
        $sel = ($selected === $key) ? ' selected' : '';
        $out .= '<option value="'.esc_attr($key).'"'.$sel.'>'.esc_html($label).'</option>';
    }
    return $out;
}
?>
