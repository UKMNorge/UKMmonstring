/* KART-CONFIG */
function initMap() {
    var autocomplete = new google.maps.places.Autocomplete(document.getElementById('searchMapInput'));

    autocomplete.addListener('place_changed', function () {
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

/* SKAL ARRANGEMENTET VÆRE SYNLIG */
jQuery(document).on('change', '#radioButtonValue_synlig', (e) => {
    if (jQuery(e.target).val() == 'true') {
        // Vis kartet (i potensielt skjult container)
        jQuery('#mapContainer').show();
        // Basics skjuler kart hvis usynlig arr, som krever nytt oppsett på starttid
        jQuery('#startTimeContainer').addClass('col-sm-7').removeClass('col-sm-12');

        jQuery('.hvisSynlig:hidden').slideDown();
        jQuery('.hvisIkkeSynlig:visible').slideUp();
    } else {
        // Hurtig-skjul kartet for animasjonen
        jQuery('#mapContainer').fadeOut(150);
        // Basics skjuler kart hvis usynlig arr, som krever nytt oppsett på starttid
        jQuery('#startTimeContainer').addClass('col-sm-12').removeClass('col-sm-7');

        jQuery('.hvisIkkeSynlig:hidden').slideDown();
        jQuery('.hvisSynlig:visible').slideUp();
    }
});

/* JA/NEI TILLAT PÅMELDING */
jQuery(document).on('change', '#radioButtonValue_pamelding', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('.hvisPamelding:hidden').slideDown();
        jQuery('.hvisIkkePamelding:visible').slideUp();
    } else {
        jQuery('.hvisIkkePamelding:hidden').slideDown();
        jQuery('.hvisPamelding:visible').slideUp();
    }
});


/* JA/NEI SKJEMA */
jQuery(document).on('change', '#radioButtonValue_harDeltakerskjema', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('.hvisDeltakerskjema:hidden').slideDown();
        jQuery('.hvisIkkeDeltakerskjema:visible').slideUp();
    } else {
        jQuery('.hvisIkkeDeltakerskjema:hidden').slideDown();
        jQuery('.hvisDeltakerskjema:visible').slideUp();
    }
});

/* JA/NEI SKJEMA MAKS ANTALL DELTAGERE */
jQuery(document).on('change', '#radioButtonValue_maksantall', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('.hvisMaksantall:hidden').slideDown();
        jQuery('.hvisIkkeMaksantall:visible').slideUp();
    } else {
        jQuery('.hvisIkkeMaksantall:hidden').slideDown();
        jQuery('.hvisMaksantall:visible').slideUp();
    }
});

/* JA/NEI TILLAT VIDERESENDING */
jQuery(document).on('change', '#radioButtonValue_videresending', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('#warn_videresending_false').hide(); // Advarsel i videresendingsboksen
        jQuery('#warn_videresending_true').fadeIn(); // Advarsel i påmeldingsboksen

        jQuery('.hvisVideresending:hidden').slideDown();
        jQuery('.hvisIkkeVideresending:visible').slideUp();
    } else {
        jQuery('#warn_videresending_true').hide();
        jQuery('#warn_videresending_false').fadeIn();

        jQuery('.hvisIkkeVideresending:hidden').slideDown();
        jQuery('.hvisVideresending:visible').slideUp();
    }
});

/* JA/NEI DELTAKERSKJEMA */
jQuery(document).on('change', '#radioButtonValue_harDeltakerskjema', function (e) {
    if (jQuery(e.target).val() == 'true') {
        jQuery('#harDeltakerskjemaDetaljer').slideDown();
    } else {
        jQuery('#harDeltakerskjemaDetaljer').slideUp();
    }
});

/* VIS / SKJUL NEDSLAGSFELT-SELECTOR */
jQuery(document).ready(() => {
    jQuery(document).on('change', '#radioButtonValue_pamelding, #radioButtonValue_synlig', (e) => {
        if (jQuery('#radioButtonValue_synlig').val() == jQuery('#radioButtonValue_pamelding').val() && jQuery('#radioButtonValue_synlig').val() == 'true') {
            jQuery('#nedslagsfelt_selector').slideDown();
        } else {
            jQuery('#nedslagsfelt_selector').slideUp();
        }
    });
});

jQuery(document).on('click', '#editVideresending', (e) => {
    e.preventDefault();
    alert('Lagre endringene dine på denne siden, og velg "Videresending" i menyen til venstre (under Arrangement).');
});