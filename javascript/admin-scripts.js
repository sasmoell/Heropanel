jQuery(document).ready(function($) {
    // Media uploader for cover image
    $('#wp_comics_cover_image_button').on('click', function(e) {
        e.preventDefault();

        var imageUploader = wp.media({
            title: 'Wähle ein Bild',
            button: {
                text: 'Bild auswählen'
            },
            multiple: false
        });

        imageUploader.on('select', function() {
            var attachment = imageUploader.state().get('selection').first().toJSON();
            $('#wp_comics_cover_image').val(attachment.url);
            $('#wp_comics_cover_image_preview').html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;" />');
        });

        imageUploader.open();
    });
});


// Aktivierung der Limited
jQuery(document).ready(function($) {
    function toggleLimitedFields() {
        if ($('#wp_comics_is_limited').is(':checked')) {
            $('#wp_comics_limited_number').removeAttr('disabled');
            $('#wp_comics_limited_total').removeAttr('disabled');
        } else {
            $('#wp_comics_limited_number').attr('disabled', 'disabled');
            $('#wp_comics_limited_total').attr('disabled', 'disabled');
        }
    }

    // Initialer Aufruf bei Seitenladen
    toggleLimitedFields();

    // Aufruf bei Änderung der Checkbox
    $('#wp_comics_is_limited').change(function() {
        toggleLimitedFields();
    });
});


