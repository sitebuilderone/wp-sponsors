jQuery(function ($) {
    let frame;
    $('#wps-choose-logo').on('click', function () {
        if (!frame) {
            frame = wp.media({
                title: wpSponsors.title,
                button: { text: wpSponsors.button },
                library: { type: 'image' },
                multiple: false
            });
            frame.on('select', function () {
                const image = frame.state().get('selection').first().toJSON();
                $('#wps-logo').val(image.id);
                const source = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
                $('#wps-logo-preview').empty().append($('<img>', {
                    src: source, alt: image.alt || '', css: { maxWidth: '150px', maxHeight: '150px' }
                }));
            });
        }
        frame.open();
    });
    $('#wps-remove-logo').on('click', function () {
        $('#wps-logo').val('0');
        $('#wps-logo-preview').empty();
    });
});
