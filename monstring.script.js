/* KONTAKTPERSONER */
jQuery(document).ready(function() {
    jQuery("#kontaktpersoner").sortable({
        update: function(event, ui) {
            jQuery.post(ajaxurl, {
                'action': 'UKMmonstring_save_kontaktpersoner',
                'order': jQuery('#kontaktpersoner').sortable('toArray')
            });
        }
    });
});

jQuery(document).on('click', ".kontaktperson a.slettKontaktperson", function(e) {
    e.preventDefault();
    var result = confirm('Sikker på at du vil slette denne kontaktpersonen?');

    if (result) {
        var id = $(this).parents('.kontaktperson').attr('data-id');
        console.log('Delete contact ' + id);
        var DeleteContact = UKMresources.Request({
            action: 'UKMmonstring',
            controller: 'slett',
            module: 'kontakt',
            containers: {
                fatalError: '#status',
                main: '#arrangementForm'
            },
            handleSuccess: (response) => {
                $('#kontakt_' + response.POST.id).fadeOut();
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


/* KART-CONFIG */
function initMap() {
    var autocomplete = new google.maps.places.Autocomplete(document.getElementById('searchMapInput'));

    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();

        var map = 'https://maps.googleapis.com/maps/api/staticmap?center=' + place.name + ',' + place.formatted_address + '&zoom=15&size=400x300&scale=1' + '&markers=' + encodeURIComponent('icon:https://grafikk.ukm.no/profil/bobla/UKM-bobla_bla_0064.png|' + 'label:U|' + place.formatted_address);

        $('#location-name').val(place.name);
        $('#location-address').val(place.formatted_address);
        $('#location-lat').val(place.geometry.location.lat());
        $('#location-lon').val(place.geometry.location.lng());
        $('#location-map').val(map);
        $('#location-link').val(place.url);

        $('#mapLink').attr('href', place.url);
        $('#map').attr('src', map + '&key=' + GOOGLE_API_KEY);
    });
}

jQuery(document).on('click', '#formSubmit', function() {
    jQuery('#formSubmit').removeClass('btn-success').addClass('btn-primary').html('Lagrer...');
});

/* KONTAKTPERSON: BILDE */
jQuery(document).on('click', '#imageedit', function(e) {
    e.preventDefault();

    var custom_uploader = wp.media({
            title: 'Velg nytt bilde for kontaktperson',
            button: {
                text: 'Bruk dette bildet'
            },
            multiple: false // Set this to true to allow multiple files to be selected
        })
        .on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            jQuery('#contact_image').attr('src', attachment.url);
            jQuery('#contact_image_input').val(attachment.url);
        })
        .open();
});

/* KONTAKTPERSON: REDIGERING */
$(document).on('click', '#redigerKontaktpersoner', (e) => {
    e.preventDefault();
    $('#kontaktpersoner_compact').slideUp();
    $('#kontaktpersoner_edit').slideDown();
});

$(document).on('click', '#skjulKontaktpersoner', (e) => {
    e.preventDefault();
    $('#kontaktpersoner_edit').slideUp();
    $('#kontaktpersoner_compact').slideDown();
})


/*
 * UKM MØNSTRINGER
 * Utgående funksjoner I think (2019)
 * 
 */

jQuery(document).on('click', '.UKMmonstringer .details_show', function() {
    show = jQuery(this);
    monstring = jQuery(this).parents('li');
    monstring.find('.details_hide').show();
    show.hide();
    monstring.find('.details').slideDown()
});
jQuery(document).on('click', '.UKMmonstringer .details_hide', function() {
    hide = jQuery(this);
    monstring = jQuery(this).parents('li');
    monstring.find('.details_show').show();
    hide.hide();
    monstring.find('.details').slideUp()
});

jQuery(document).on('click', '.UKMmonstringer .wpadmin', function() {
    window.open(jQuery(this).attr('data-url'), '_blank');
});

jQuery(document).on('click', '.UKMmonstringer #smstoall', function() {
    var recipients = new Array();
    jQuery('.UKMSMS').each(function() {
        recipients.push(jQuery(this).find('a').html());
    });
    jQuery('#UKMSMS_to').val(recipients.join(','));
    jQuery('#UKMSMS_form').submit();
});