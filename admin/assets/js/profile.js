// admin/assets/js/profile.js
jQuery(document).ready(function($){

    // WP media frame for logo
    var logoFrame = null;
    $('#wpsg-select-logo').on('click', function(e){
        e.preventDefault();
        if (typeof wp === 'undefined' || !wp.media) {
            alert('Media Library not available.');
            return;
        }
        if (logoFrame) { logoFrame.open(); return; }

        logoFrame = wp.media({
            title: 'Select Organization Logo',
            button: { text: 'Select' },
            multiple: false
        });

        logoFrame.on('select', function(){
            var attachment = logoFrame.state().get('selection').first().toJSON();
            $('#wpsg_logo_id').val(attachment.id);
            $('#wpsg-logo-img').attr('src', attachment.url).show();
            $('#wpsg-remove-logo').show();
        });

        logoFrame.open();
    });

    $('#wpsg-remove-logo').on('click', function(e){
        e.preventDefault();
        $('#wpsg_logo_id').val('');
        $('#wpsg-logo-img').attr('src','').hide();
        $(this).hide();
    });

    // Add social row
    $('.wpsg-add-social').on('click', function(e){
        e.preventDefault();
        var tpl = '<div class="wpsg-social-row">'+
                  '<select name="socials_platform[]">'+
                  '<?php ' + '?>' +
                  '</select>'+
                  '<input type="text" name="socials_handle[]" class="regular-text" placeholder="username or full URL">'+
                  '<a href="#" class="wpsg-remove-item">Remove</a>'+
                  '</div>';
        // render clone from server side template - simpler: append manual options
        var options = [
            ['facebook','Facebook'],
            ['instagram','Instagram'],
            ['twitter','Twitter / X'],
            ['youtube','YouTube'],
            ['linkedin','LinkedIn'],
            ['tiktok','TikTok'],
            ['whatsapp','WhatsApp'],
            ['telegram','Telegram'],
            ['custom','Custom (name)']
        ];
        var sel = '<select name="socials_platform[]">';
        for (var i=0;i<options.length;i++){
            sel += '<option value="'+options[i][0]+'">'+options[i][1]+'</option>';
        }
        sel += '</select>';
        var row = $('<div class="wpsg-social-row"></div>');
        row.append(sel);
        row.append('<input type="text" name="socials_handle[]" class="regular-text" placeholder="username or full URL">');
        row.append('<a href="#" class="wpsg-remove-item">Remove</a>');
        $('#wpsg-social-list').append(row);
    });

    // Add repeat item (mission/goal)
    $(document).on('click', '.wpsg-add-item', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        var node = $('<div class="wpsg-repeat-item"><input type="text" name="'+(target.indexOf('missions')>-1 ? 'missions[]' : 'goals[]')+'" class="regular-text"><a href="#" class="wpsg-remove-item">Remove</a></div>');
        $(target).append(node);
    });

    // Remove item
    $(document).on('click', '.wpsg-remove-item', function(e){
        e.preventDefault();
        $(this).closest('.wpsg-repeat-item, .wpsg-social-row').remove();
    });

    // Partial form submission: we let forms submit normally to admin-post.php
    // But to give nicer UX we can do AJAX submit via admin-ajax if desired.
    // For now, we keep standard post (server will redirect back to wpsg-admin page).
});
