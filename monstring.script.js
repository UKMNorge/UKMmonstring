/* KART-CONFIG */
function initMap() {
    var autocomplete = new google.maps.places.Autocomplete(document.getElementById('searchMapInput'));

    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();

        var map = 'https://maps.googleapis.com/maps/api/staticmap?center=' + place.name + ',' + place.formatted_address + '&zoom=15&size=400x300&scale=1' + '&markers=' + encodeURIComponent('icon:https://grafikk.ukm.no/profil/bobla/UKM-bobla_bla_0064.png|' + 'label:U|' + place.formatted_address);

        jQuery('#location-name').val(place.name);
        jQuery('#location-address').val(place.formatted_address);
        jQuery('#location-lat').val(place.geometry.location.lat());
        jQuery('#location-lon').val(place.geometry.location.lng());
        jQuery('#location-map').val(map);
        jQuery('#location-link').val(place.url);

        jQuery('#mapLink').attr('href', place.url);
        jQuery('#map').attr('src', map + '&key=' + GOOGLE_API_KEY);
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
jQuery(document).on('click', ".kontaktperson a.slettKontaktperson", function(e) {
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
jQuery(document).on('change', '#radioButtonValue_tillatVideresending', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('#videresending_detaljer').slideDown();
    } else {
        jQuery('#videresending_detaljer').slideUp();
    }
});

/* SKAL ARRANGEMENTET BRUKE NOMINASJON */
jQuery(document).on('change', '#radioButtonValue_benyttNominasjon', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('#nominasjon_detaljer').slideDown();
    } else {
        jQuery('#nominasjon_detaljer').slideUp();
    }
});

/* FILTRER ARRANGEMENT SOM KAN VIDERESENDE TIL ARRANGEMENTET */
jQuery(document).on('keyup', '#filterArrangement', function() {
    var words = jQuery(this).val().toLowerCase().split(' ');
    if (words.length > 1 || (words.length == 1 && words[0].length > 0)) {
        jQuery('.avsenderListe li').show().filter(function() {
            var searchIn = jQuery(this).attr('data-filter').toLowerCase();
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
        jQuery('.avsenderListe li').stop().slideDown(200);
    }
});

/* SKJUL/VIS KOMPAKT LISTE-VISNING OVER ARRANGEMENT SOM KAN VIDERESENDE TIL DETTE ARRANGEMENTET */
jQuery(document).on('click', '#visRedigerAvsenderArrangement', (e) => {
    e.preventDefault();

    if (jQuery('#visAvsenderArrangement').find('.avsender').length == 0) {
        jQuery('#ingenAvsendere').show();
    } else {
        jQuery('#ingenAvsendere').hide();
    }

    if (jQuery('#redigerAvsenderArrangement').is(':visible')) {
        jQuery('#redigerAvsenderArrangement').slideUp();
        jQuery('#visRedigerAvsenderArrangement').html(jQuery('#visRedigerAvsenderArrangement').attr('data-vis'));
    } else {
        jQuery('#redigerAvsenderArrangement').slideDown();
        jQuery('#visRedigerAvsenderArrangement').html(jQuery('#visRedigerAvsenderArrangement').attr('data-skjul'));

    }
});

/* OPDATER KOMPAKT LISTE OVER ARRANGEMENT SOM KAN VIDERESENDE TIL DETTE ARRANGEMENTET */
jQuery(document).on('change', '.avsenderMonstring input[type="checkbox"]', (e) => {
    var checkbox = jQuery(e.target);
    var arrangement = checkbox.parents('.avsenderMonstring');
    var id = 'visAvsenderArrangement_' + arrangement.attr('data-id');

    if (checkbox.is(':checked')) {
        jQuery('#visAvsenderArrangement').append(
            jQuery('<span class="avsender label label-primary" id="' + id + '">' +
                arrangement.attr('data-name') +
                /*'<a href="#" class="deleteAvsenderArrangement delete"><span class="dashicons dashicons-no-alt"></span></a>' + */
                ' </span>'));
    } else {
        jQuery('#' + id).fadeOut().remove();
    }
});


/* GJØR TEKSTEN TOGGLE-BAR FOR VIS/SKJUL ARRANGEMENTER SOM KAN VIDERESENDE */
jQuery(document).ready(() => {
    jQuery('#visRedigerAvsenderArrangement').attr('data-vis', jQuery('#visRedigerAvsenderArrangement').html());
});

/* SKAL ARRANGEMENTET BRUKE VIDERESENDINGSSKJEMA */
jQuery(document).on('change', '#radioButtonValue_vilHaSkjema', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('#previewForm').slideDown();
    } else {
        jQuery('#previewForm').slideUp();
    }
});

/* SPØRRESKJEMA: SLETT SPØRSMÅL */
jQuery(document).on('click', '.actions > .delete', (e) => {
    e.preventDefault();

    var sure = confirm('Er du sikker på at du vil slette dette spørsmålet?');
    if (sure) {
        var sporsmal = jQuery(e.target).parents('li.sporsmal');
        var sporsmal_id = jQuery(sporsmal).find('.sporsmal_id').val();
        sporsmal.fadeOut(300, function() {
            jQuery(this).remove();
        });
        // Sletter kun spørsmål som faktisk er lagret i databasen.
        if (sporsmal_id != "new") {
            jQuery("#skjema").append('<input type="hidden" name="slettede_sporsmal[]" value="' + sporsmal_id + '">');
        }
        return true;
    }
    return false;
});

/* SPØRRESKJEMA: LEGG TIL / FJERN OVERSKRIFT-KLASSE FOR ØKT SYNLIGHET */
jQuery(document).on('change', '.sporsmal_type', (e) => {
    if (jQuery(e.target).val() == 'overskrift') {
        jQuery(e.target).parents('.sporsmal').addClass('overskrift');
    } else {
        jQuery(e.target).parents('.sporsmal').removeClass('overskrift');
    }
});

/** SPØRRESKJEMA: LEGG TIL SPØRSMÅLET */
jQuery(document).on('click', '#addSporsmal', (e) => {
    var sporsmal = jQuery(e.target).parents('li.sporsmal');
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
    jQuery('#noSporsmal').slideUp(200);

    // Fjern ID, da denne er #addSporsmal atm
    var newId = jQuery('#skjema li.sporsmal').length;
    sporsmal.attr('id', 'new_' + newId);
    id.attr('name', 'sporsmal_id[new_' + newId + ']');
    type.attr('name', 'sporsmal_type[new_' + newId + ']');
    tittel.attr('name', 'sporsmal_tittel[new_' + newId + ']');
    tekst.attr('name', 'sporsmal_tekst[new_' + newId + ']');

    // Bytt legg til med slett-ikon
    sporsmal.find('.actions').html(jQuery('<span class="dashicons dashicons-trash action delete"></span>'));

    // Flytt til bunn av skjema
    sporsmal.appendTo('#skjema');

    // Fjern grå bakgrunn
    jQuery(sporsmal).attr('style', "");

    // Rendre nytt skjema for legg til spørsmål
    jQuery('#skjema').prepend(
        jQuery('<li class="list-group-item sporsmal" style="background-color: rgb(217,218,218);"/>').html(twigJS_skjemaRad.render({ sporsmal: { id: 'new' } }))
    );
    jQuery('#newSporsmalContainer textarea').autogrow();
});

/* INITIER SKJEMA-FUNKSJONER */
jQuery(document).ready(function() {
    jQuery("#skjema").sortable({ items: "> li:not(:first)" });
    jQuery('#newSporsmalContainer').html(twigJS_skjemaRad.render({ sporsmal: { id: 'new' } }));
    jQuery('textarea').autogrow();
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