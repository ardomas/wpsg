<?php if (!defined('ABSPATH')) exit;

class WPSG_ProfileAddress {

    public function render() {

        $wpsg_address = get_option('wpsg_profile_address', [
            'address'        => '',
            'address_line_2' => '',
            'city'           => '',
            'state'          => '',
            'postal_code'    => '',
            'country'        => '',
            'maps_link'      => '',
        ]);

        // SAVE HANDLER
        if (isset($_POST['wpsg_address'])) {

            $clean = [
                'address'        => sanitize_textarea_field($_POST['wpsg_address']['address'] ?? ''),
                'address_line_2' => sanitize_textarea_field($_POST['wpsg_address']['address_line_2'] ?? ''),
                'city'           => sanitize_text_field($_POST['wpsg_address']['city'] ?? ''),
                'state'          => sanitize_text_field($_POST['wpsg_address']['state'] ?? ''),
                'postal_code'    => sanitize_text_field($_POST['wpsg_address']['postal_code'] ?? ''),
                'country'        => sanitize_text_field($_POST['wpsg_address']['country'] ?? ''),
                'maps_link'      => esc_url_raw($_POST['wpsg_address']['maps_link'] ?? ''),
            ];

            update_option('wpsg_profile_address', $clean);

            echo '<div class="updated"><p>Address saved.</p></div>';
            $wpsg_address = $clean;
        }
?>

<div class="wrap wpsg">
    <h1>Address</h1>
    <p>Manage your organization’s Address.</p>

    <p>
        <a href="<?php echo admin_url('admin.php?page=wpsg-admin&view=profile'); ?>"
            class="button button-secondary">
            ← Back to Profile
        </a>
    </p>


    <form method="post">

        <!-- Address -->
        <div class="form-field">
            <label for="wpsg_address_address"><strong>Address</strong></label>
            <textarea id="wpsg_address_address" name="wpsg_address[address]" rows="2" style="width:100%;"><?php 
                echo esc_textarea($wpsg_address['address']); 
            ?></textarea>
        </div>

        <!-- Address Line 2 -->
        <div class="form-field">
            <label for="wpsg_address_line_2"><strong>Address Line 2 (optional)</strong></label>
            <textarea id="wpsg_address_line_2" name="wpsg_address[address_line_2]" rows="2" style="width:100%;"><?php 
                echo esc_textarea($wpsg_address['address_line_2']); 
            ?></textarea>
        </div>

        <!-- City -->
        <div class="form-field">
            <label for="wpsg_city"><strong>City</strong></label>
            <input type="text" id="wpsg_city" name="wpsg_address[city]" 
                   value="<?php echo esc_attr($wpsg_address['city']); ?>" 
                   style="width:100%;">
        </div>

        <!-- State / Province -->
        <div class="form-field">
            <label for="wpsg_state"><strong>State / Province</strong></label>
            <input type="text" id="wpsg_state" name="wpsg_address[state]" 
                   value="<?php echo esc_attr($wpsg_address['state']); ?>" 
                   style="width:100%;">
        </div>

        <!-- Country + Postal Code -->
        <div style="display:flex; gap:20px;">
            <div style="flex: 0 0 calc( 70% - 10px );">
                <label for="wpsg_country"><strong>Country</strong></label>
                <input type="text" id="wpsg_country" name="wpsg_address[country]" 
                       value="<?php echo esc_attr($wpsg_address['country']); ?>" 
                       style="width:100%;">
            </div>

            <div style="flex:0 0 calc(  30% - 10px );">
                <label for="wpsg_postal"><strong>Postal Code</strong></label>
                <input type="text" id="wpsg_postal" name="wpsg_address[postal_code]" 
                       value="<?php echo esc_attr($wpsg_address['postal_code']); ?>" 
                       style="width:100%;">
            </div>
        </div>

        <!-- Google Maps Link -->
        <div class="form-field" style="margin-top:20px;">
            <label for="wpsg_maps"><strong>Google Maps Link</strong></label>
            <input type="url" id="wpsg_maps" name="wpsg_address[maps_link]" 
                   value="<?php echo esc_attr($wpsg_address['maps_link']); ?>" 
                   style="width:100%;">
        </div>

        <p>
            <button type="submit" class="button button-primary">Save Address</button>
        </p>

    </form>
</div>

<?php
    }
}
