jQuery(document).ready(function($){
    var media_frame;

    $('#choose-logo').on('click', function(e){
        e.preventDefault();

        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            alert('Media Library not loaded yet.');
            return;
        }

        if (media_frame) {
            media_frame.open();
            return;
        }

        media_frame = wp.media({
            title: 'Select Institution Logo',
            button: { text: 'Select' },
            multiple: false
        });

        media_frame.on('select', function(){
            var attachment = media_frame.state().get('selection').first().toJSON();
            $('#logo-preview').attr('src', attachment.url).show();
            $('#logo_media_url').val(attachment.url);
        });

        media_frame.open();
    });
});
