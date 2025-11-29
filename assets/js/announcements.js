// File: assets/js/announcements.js
jQuery(document).ready(function($) {

    // =========================
    // MEDIA UPLOADER
    // =========================
    let mediaFrame;
    $('#ann_image_select').on('click', function(e){
        e.preventDefault();
        if(mediaFrame) {
            mediaFrame.open();
            return;
        }
        mediaFrame = wp.media({
            title: 'Select Featured Image',
            button: { text: 'Use this image' },
            multiple: false
        });
        mediaFrame.on('select', function(){
            let attachment = mediaFrame.state().get('selection').first().toJSON();
            $('#ann_image').val(attachment.url);
            $('#ann_image_wrapper .preview-image').remove();
            $('#ann_image_wrapper').prepend('<img src="'+attachment.url+'" class="preview-image"/>');
        });
        mediaFrame.open();
    });

    $('#ann_image_remove').on('click', function(){
        $('#ann_image').val('');
        $('#ann_image_wrapper .preview-image').remove();
    });

    flatpickr("#ann_date_start", { dateFormat: "Y-m-d" });
    flatpickr("#ann_date_end"  , { dateFormat: "Y-m-d" });
    flatpickr("#ann_time_start", { enableTime: true, noCalendar: true, dateFormat: "H:i" });
    flatpickr("#ann_time_end"  , { enableTime: true, noCalendar: true, dateFormat: "H:i" });

    flatpickr("#publish_date"  , { enableDate: true, enableTime: true, dateFormat: "Y-m-d H:i" })
    flatpickr("#expiry_date"   , { enableDate: true, enableTime: true, dateFormat: "Y-m-d H:i" })

    // =========================
    // REPEATABLE FIELDS
    // =========================
    function addRepeatable(type) {
        let wrapper = $('#' + type + 's_wrapper');
        let index = wrapper.children().length;
        let html = '';

        if(type === 'speaker') {
            html += '<div class="repeatable-row">';
            html += '<input type="text" name="speakers['+index+'][name]" placeholder="Name" required />';
            html += '<input type="text" name="speakers['+index+'][company]" placeholder="Company" />';
            html += '<input type="text" name="speakers['+index+'][position]" placeholder="Position" />';
            html += '<button type="button" class="button remove-row"></button></div>';
        }

        if(type === 'organizer') {
            html += '<div class="repeatable-row">';
            html += '<input type="text" name="organizers['+index+'][name]" placeholder="Name / Organization" required />';
            // html += '<label><input type="checkbox" name="organizers['+index+'][is_main]"> Main</label>';
            html += '<input type="text" name="organizers['+index+'][description]" placeholder="Description" />';
            html += '<button type="button" class="button remove-row"></button></div>';
        }

        if(type === 'contact') {
            html += '<div class="repeatable-row">';
            html += '<input type="text" name="contacts['+index+'][name]" placeholder="Name" required />';
            html += '<input type="text" name="contacts['+index+'][number]" placeholder="Phone / Email" />';
            html += '<button type="button" class="button remove-row"></button></div>';
        }

        if(type === 'pricing') {
            html += '<div class="repeatable-row">';
            // html += '<input type="text" name="pricings['+index+'][label]" placeholder="Label (default: Admin Fee)" required />';
            html += '<input type="text" name="ann_price_values['+index+'][value]" placeholder="Price / Nominal" required />';
            html += '<input type="text" name="ann_price_values['+index+'][note]" placeholder="Price Note" />';
            html += '<button type="button" class="button remove-row"></button></div>';
        }

        wrapper.append(html);
    }

    $('.add-repeatable').on('click', function(){
        console.log('nongol gak?');
        let type = $(this).data('type');
        addRepeatable(type);
    });

    $(document).on('click', '.remove-row', function(){
        $(this).closest('.repeatable-row').remove();
    });

    // =========================
    // SLUG VALIDATION (optional: AJAX check)
    // =========================
    $('#ann_title').on('blur', function(){
        let title = $(this).val();
        let slug = title.toLowerCase().replace(/ /g,'-').replace(/[^\w-]+/g,'');
        $('#ann_slug').val(slug);
        $('#slug-status').text('Slug generated: '+slug);
    });

    // =========================
    // FORM VALIDATION BEFORE SUBMIT
    // =========================
    $('.wpsg-form').on('submit', function(e){
        let title = $('#ann_title').val().trim();
        if(title === '') {
            alert('Title cannot be empty!');
            e.preventDefault();
            return false;
        }

        // Optionally: check at least one speaker/organizer/contact
        // let speakerCount = $('#speakers_wrapper .repeatable-row').length;
        // if(speakerCount === 0){ alert('Add at least one speaker'); e.preventDefault(); return false;}
    });

});

/*
jQuery(document).ready(function($){
    $('#wpsg-ann-form').on('submit', function(e){
        e.preventDefault(); // cegah reload

        var formData = $(this).serialize();

        console.log( WPSG_ANN_DATA );
        console.log( formData );

        $.ajax({
            url: WPSG_ANN_DATA.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
        }).done((response)=>{
            if(response.success){
                alert('Announcement saved! ID: ' + response.data.post_id);
                // bisa reset form atau update UI sesuai kebutuhan
            } else {
                alert('Failed to save: ' + response.data.message);
            }
        }).fail((xhr,status,error)=>{
            console.error(error);
            alert('AJAX error!');
        });

    });
});

*/