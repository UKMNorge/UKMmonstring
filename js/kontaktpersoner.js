/* KONTAKTPERSONER: SORTERING */
jQuery(document).ready(function () {
    jQuery("#kontaktpersoner_edit > ol").sortable({
        items: "> li:not(:last)",
        update: function (event, ui) {
            UKMresources.Request({
                action: 'UKMmonstring',
                controller: 'rekkefolge',
                module: 'kontakt',
                containers: {
                    fatalError: '#status',
                    main: '#arrangementForm'
                },
                handleSuccess: (response) => {
                    // keep calm.
                },
                handleError: (response) => {
                    emitter.emit('error', response);
                }
            }).do({
                order: jQuery('#kontaktpersoner_edit > ol').sortable('toArray')
            });
        }
    });
});

/* KONTAKTPERSON: VIS REDIGERINGSLISTE */
jQuery(document).on('click', '#redigerKontaktpersoner', (e) => {
    e.preventDefault();
    jQuery('#kontaktpersoner_compact').slideUp();
    jQuery('#kontaktpersoner_edit').slideDown();
});

/* KONTAKTPERSON: SKJUL REDIGERINGSLISTE */
jQuery(document).on('click', '#skjulKontaktpersoner', (e) => {
    e.preventDefault();
    jQuery('#kontaktpersoner_edit').slideUp();
    jQuery('#kontaktpersoner_compact').slideDown();
});


/* KONTAKTPERSONER: SLETT */
jQuery(document).on('click', ".kontaktperson a.slettKontaktperson", function (e) {
    e.preventDefault();
    var result = confirm('Sikker på at du vil slette denne kontaktpersonen?');

    if (result) {
        var id = jQuery(this).parents('.kontaktperson').attr('data-id');
        var DeleteContact = UKMresources.Request({
            action: 'UKMmonstring',
            controller: 'slett',
            module: 'kontakt',
            containers: {
                fatalError: '#status',
                main: '#arrangementForm'
            },
            handleSuccess: (response) => {
                jQuery('#kontakt_' + response.POST.id).fadeOut();
                jQuery('#visKontakt_' + response.POST.id).fadeOut();
            },
            handleError: (response) => {
                emitter.emit('error', response);
            }
        }).do({
            id: id
        });
    }
    return false;
});

/* KONTAKTPERSON: BILDE */
jQuery(document).on('click', '#imageedit', function (e) {
    e.preventDefault();

    var custom_uploader = wp.media({
        title: 'Velg nytt bilde for kontaktperson',
        button: {
            text: 'Bruk dette bildet'
        },
        multiple: false // Set this to true to allow multiple files to be selected
    })
        .on('select', function () {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            jQuery('#contact_image').attr('src', attachment.url);
            jQuery('#contact_image_input').val(attachment.url);
        })
        .open();
});