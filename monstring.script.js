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


/* KONTAKTPERSONER: SORTERING */
jQuery(document).ready(function() {
    jQuery("#kontaktpersoner_edit > ol").sortable({
        items: "> li:not(:last)",
        update: function(event, ui) {
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
$(document).on('click', '#redigerKontaktpersoner', (e) => {
    e.preventDefault();
    $('#kontaktpersoner_compact').slideUp();
    $('#kontaktpersoner_edit').slideDown();
});

/* KONTAKTPERSON: SKJUL REDIGERINGSLISTE */
$(document).on('click', '#skjulKontaktpersoner', (e) => {
    e.preventDefault();
    $('#kontaktpersoner_edit').slideUp();
    $('#kontaktpersoner_compact').slideDown();
});


/* KONTAKTPERSONER: SLETT */
jQuery(document).on('click', ".kontaktperson a.slettKontaktperson", function(e) {
    e.preventDefault();
    var result = confirm('Sikker på at du vil slette denne kontaktpersonen?');

    if (result) {
        var id = $(this).parents('.kontaktperson').attr('data-id');
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
                $('#visKontakt_' + response.POST.id).fadeOut();
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



/* SKAL ARRANGEMENTET TA I MOT VIDERESENDINGER */
$(document).on('change', '#radioButtonValue_tillatVideresending', (e) => {
    if ($(e.target).val() == 'true') {
        $('#videresending_detaljer').slideDown();
    } else {
        $('#videresending_detaljer').slideUp();
    }
});

/* FILTRER ARRANGEMENT SOM KAN VIDERESENDE TIL ARRANGEMENTET */
$(document).on('keyup', '#filterArrangement', function() {
    var words = $(this).val().toLowerCase().split(' ');
    if (words.length > 1 || (words.length == 1 && words[0].length > 0)) {
        $('.avsenderListe li').show().filter(function() {
            var searchIn = $(this).attr('data-filter').toLowerCase();
            var found = false;

            words.forEach(function(word) {
                if (searchIn.indexOf(word) !== -1) {
                    found = true;
                    return; // return ut av forEach
                }
            });

            return !found;
        }).slideUp({ duration: 100 });
    } else {
        $('.avsenderListe li').stop().slideDown(200);
    }
});

/* SKJUL/VIS KOMPAKT LISTE-VISNING OVER ARRANGEMENT SOM KAN VIDERESENDE TIL DETTE ARRANGEMENTET */
$(document).on('click', '#visRedigerAvsenderArrangement', (e) => {
    e.preventDefault();

    if ($('#visAvsenderArrangement').find('.avsender').length == 0) {
        $('#ingenAvsendere').show();
    } else {
        $('#ingenAvsendere').hide();
    }

    if ($('#redigerAvsenderArrangement').is(':visible')) {
        $('#redigerAvsenderArrangement').slideUp();
        $('#visRedigerAvsenderArrangement').html($('#visRedigerAvsenderArrangement').attr('data-vis'));
    } else {
        $('#redigerAvsenderArrangement').slideDown();
        $('#visRedigerAvsenderArrangement').html($('#visRedigerAvsenderArrangement').attr('data-skjul'));

    }
});

/* OPDATER KOMPAKT LISTE OVER ARRANGEMENT SOM KAN VIDERESENDE TIL DETTE ARRANGEMENTET */
$(document).on('change', '.avsenderMonstring input[type="checkbox"]', (e) => {
    var checkbox = $(e.target);
    var arrangement = checkbox.parents('.avsenderMonstring');
    var id = 'visAvsenderArrangement_' + arrangement.attr('data-id');

    if (checkbox.is(':checked')) {
        $('#visAvsenderArrangement').append(
            $('<span class="avsender label label-primary" id="' + id + '">' +
                arrangement.attr('data-name') +
                /*'<a href="#" class="deleteAvsenderArrangement delete"><span class="dashicons dashicons-no-alt"></span></a>' + */
                ' </span>'));
    } else {
        $('#' + id).fadeOut().remove();
    }
});


/* GJØR TEKSTEN TOGGLE-BAR FOR VIS/SKJUL ARRANGEMENTER SOM KAN VIDERESENDE */
$(document).ready(() => {
    $('#visRedigerAvsenderArrangement').attr('data-vis', $('#visRedigerAvsenderArrangement').html());
});

/* SKAL ARRANGEMENTET BRUKE VIDERESENDINGSSKJEMA */
$(document).on('change', '#radioButtonValue_vilHaSkjema', (e) => {
    if ($(e.target).val() == 'true') {
        $('#previewForm').slideDown();
    } else {
        $('#previewForm').slideUp();
    }
});

/* SPØRRESKJEMA: SLETT SPØRSMÅL */
$(document).on('click', '.actions > .delete', (e) => {
    e.preventDefault();

    var sure = confirm('Er du sikker på at du vil slette dette spørsmålet?');
    if (sure) {
        var sporsmal = $(e.target).parents('li.sporsmal');
        sporsmal.fadeOut(300, function() {
            $(this).remove();
        });
        return true;
    }
    return false;
});

/* SPØRRESKJEMA: LEGG TIL / FJERN OVERSKRIFT-KLASSE FOR ØKT SYNLIGHET */
$(document).on('change', '.sporsmal_type', (e) => {
    if ($(e.target).val() == 'overskrift') {
        $(e.target).parents('.sporsmal').addClass('overskrift');
    } else {
        $(e.target).parents('.sporsmal').removeClass('overskrift');
    }
});

/** SPØRRESKJEMA: LEGG TIL SPØRSMÅLET */
$(document).on('click', '#addSporsmal', (e) => {
    var sporsmal = $(e.target).parents('li.sporsmal');
    var type = sporsmal.find('.sporsmal_type');
    var tittel = sporsmal.find('.sporsmal_tittel');
    var tekst = sporsmal.find('.sporsmal_tekst');
    var id = sporsmal.find('.sporsmal_id');

    // Sjekk at type er valgt
    if (type.val() == null) {
        alert('Du må velge type før du kan legge til spørsmålet');
        type.effect('shake', {
            distance: 10,
            times: 2,
            duration: 200
        });
        return false;
    }

    // Sjekk at spørsmålet har en tittel
    if (tittel.val() == null || tittel.val().length == 0) {
        alert('Du må skrive inn spørsmålet før du legger det til');
        tittel.effect('shake', {
            distance: 10,
            times: 2,
            duration: 200
        });
        return false;
    }

    // Skjul advarsel om at skjemaet ikke har spørsmål
    $('#noSporsmal').slideUp(200);

    // Fjern ID, da denne er #addSporsmal atm
    var newId = $('#skjema li.sporsmal').length;
    sporsmal.attr('id', 'new_' + newId);
    id.attr('name', 'sporsmal_id[new_' + newId + ']');
    type.attr('name', 'sporsmal_type[new_' + newId + ']');
    tittel.attr('name', 'sporsmal_tittel[new_' + newId + ']');
    tekst.attr('name', 'sporsmal_tekst[new_' + newId + ']');

    // Bytt legg til med slett-ikon
    sporsmal.find('.actions').html($('<span class="dashicons dashicons-trash action delete"></span>'));

    // Flytt til bunn av skjema
    sporsmal.appendTo('#skjema');

    // Rendre nytt skjema for legg til spørsmål
    $('#skjema').prepend(
        $('<li class="list-group-item sporsmal"/>').html(twigJS_skjemaRad.render({ sporsmal: { id: 'new' } }))
    );
    $('#newSporsmalContainer textarea').autogrow();
});

/* INITIER SKJEMA-FUNKSJONER */
$(document).ready(function() {
    $("#skjema").sortable({ items: "> li:not(:first)" });
    $('#newSporsmalContainer').html(twigJS_skjemaRad.render({ sporsmal: { id: 'new' } }));
    $('textarea').autogrow();
});







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