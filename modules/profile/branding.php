<?php
if (!defined('ABSPATH')) exit;

class WPSG_ProfileBranding {

    private $option_key = 'profile_branding';

    public function __construct() {
        $this->form_handler();
    }

    /** RENDER FORM **/
    public function render() {
        $data = $this->get_data();
        ?>
        <form method="post">

            <div class="wpsg wpsg-boxed">

                <div class="wrap">

                    <h3>Manage your organization’s branding details.</h3>

                    <?php wp_nonce_field('wpsg_branding_save', 'wpsg_branding_nonce'); ?>

                    <!-- Core Purpose -->
                    <div class="wpsg-boxed wpsg-form-field">
                        <label for="core_purpose"><strong>Brand Core Purpose</strong></label>
                        <?php
                        $core_purpose = $data['core_purpose'] ?? '';
                        wp_editor(
                            $core_purpose,
                            'wpsg_core_purpose',
                            [
                                'textarea_name' => 'core_purpose',
                                'textarea_rows' => 6,
                                'media_buttons' => false,
                                'teeny' => true
                            ]
                        );
                        ?>
                    </div>

                    <!-- Logos -->
<div class="wpsg-boxed wpsg-form-field">
    <label><strong>Brand Logos</strong></label>
    <table class="wpsg-logo-table" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="width:20%">Key <br><small>hanya bisa diubah untuk data baru</small></th>
                <th style="width:40%">Label</th>
                <th style="width:30%">Preview</th>
                <th style="width:10%">Action</th>
            </tr>
        </thead>
        <tbody id="wpsg-logo-rows">
            <?php
            if(!empty($data['logo']) && is_array($data['logo'])):
                foreach($data['logo'] as $key => $logo):
                    $readonly = 'readonly';
                    $attachment_id = intval($logo['id'] ?? 0);
                    $label = sanitize_text_field($logo['label'] ?? '');
                    $image_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
            ?>
            <tr>
                <td><input type="text" name="logo[<?php echo esc_attr($key); ?>][key]" value="<?php echo esc_attr($key); ?>" readonly></td>
                <td><input type="text" name="logo[<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($label); ?>"></td>
                <td style="text-align: center;">
                    <input type="hidden" class="wpsg-logo-id" name="logo[<?php echo esc_attr($key); ?>][id]" value="<?php echo esc_attr($attachment_id); ?>">
                    <?php if($image_url): ?>
                        <img src="<?php echo esc_url($image_url); ?>" class="wpsg-logo-preview" style="max-height:50px; cursor:pointer;" title="Click image to change">
                    <?php else: ?>
                        <div class="wpsg-logo-placeholder" style="border:1px dashed #ccc; padding:10px; cursor:pointer;">Click to select image</div>
                    <?php endif; ?>
                </td>
                <td><button class="button wpsg-remove-logo" type="button">Remove</button></td>
            </tr>
            <?php
                endforeach;
            endif;
            ?>
        </tbody>
    </table>
    <p>
        <button type="button" class="button" id="wpsg-add-logo">Add New Logo</button>
    </p>
</div>

                </div>
            </div>

            <p>
                <button type="submit" class="button button-primary">
                    Save Changes
                </button>
            </p>

        </form>

        <script>
jQuery(document).ready(function($){
    function generateSlug(text){
        return text.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
    }

    // Add new logo row
    $('#wpsg-add-logo').on('click', function(){
        let rowCount = $('#wpsg-logo-rows tr').length;
        let key = 'new-logo-'+rowCount;
        let html = '<tr>';
        html += '<td><input type="text" name="logo['+key+'][key]" value="'+key+'"></td>';
        html += '<td><input type="text" name="logo['+key+'][label]" value=""></td>';
        html += '<td style="text-align: center;"><input type="hidden" class="wpsg-logo-id" name="logo['+key+'][id]" value="">';
        html += '<div class="wpsg-logo-placeholder" style="border:1px dashed #ccc; padding:10px; cursor:pointer; text-align: center;">Click to select image</div></td>';
        html += '<td><button class="button wpsg-remove-logo" type="button">Remove</button></td>';
        html += '</tr>';
        $('#wpsg-logo-rows').append(html);
    });

    // Remove logo row
    $(document).on('click', '.wpsg-remove-logo', function(){
        $(this).closest('tr').remove();
    });

    // Media Library selector (klik pada preview atau placeholder)
    $(document).on('click', '.wpsg-logo-preview, .wpsg-logo-placeholder', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        var hidden_input = row.find('.wpsg-logo-id');
        var preview = row.find('.wpsg-logo-preview');
        var placeholder = row.find('.wpsg-logo-placeholder');

        var file_frame = wp.media({
            title: 'Select Logo',
            button: { text: 'Use this image' },
            multiple: false
        });

        file_frame.on('select', function(){
            var attachment = file_frame.state().get('selection').first().toJSON();
            hidden_input.val(attachment.id);

            if(preview.length){
                preview.attr('src', attachment.url);
            } else {
                placeholder.replaceWith('<img src="'+attachment.url+'" class="wpsg-logo-preview" style="max-height:50px; cursor:pointer;" title="Click image to change">');
            }
        });

        file_frame.open();
    });

    // Auto-generate key from label
    $(document).on('input','input[name$="[label]"]',function(){
        var row = $(this).closest('tr');
        var key_input = row.find('input[name$="[key]"]');
        if(!key_input.prop('readonly')){
            key_input.val(generateSlug($(this).val()));
        }
    });
});

        </script>
        <?php
    }

    /** PROCESS FORM **/
    private function form_handler() {
        if(!isset($_POST['wpsg_branding_nonce'])) return;
        if(!wp_verify_nonce($_POST['wpsg_branding_nonce'],'wpsg_branding_save')) return;

        $clean = [];

        // Core Purpose
        $clean['core_purpose'] = wp_kses_post($_POST['core_purpose'] ?? '');

        // Logos
        $clean['logo'] = [];
        if(!empty($_POST['logo']) && is_array($_POST['logo'])){
            foreach($_POST['logo'] as $key => $row){
                $key_clean = sanitize_key($row['key'] ?? $key);
                $label_clean = sanitize_text_field($row['label'] ?? '');
                $id_clean = intval($row['id'] ?? 0);
                if($key_clean && $id_clean){
                    $clean['logo'][$key_clean] = [
                        'id' => $id_clean,
                        'label' => $label_clean
                    ];
                }
            }
        }

        // Save using WPSG_ProfilesData
        WPSG_ProfilesRepository::set($this->option_key, $clean);

        add_action('admin_notices', function(){
            echo '<div class="updated"><p>Branding updated successfully.</p></div>';
        });
    }

    /** GET DATA **/
    private function get_data(){
        return WPSG_ProfilesRepository::get($this->option_key);
    }
}
