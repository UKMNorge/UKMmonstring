/* KART-CONFIG */
function initMap() {
    // 1) Lag en container for ny widget rett ved siden av eksisterende input
    //    (vi beholder #searchMapInput for backend, men skjuler det og fyller det ved valg)
    let widgetHost = document.getElementById('searchMapInputWidget');
    if (!widgetHost) {
        const oldInput = document.getElementById('searchMapInput');
        widgetHost = document.createElement('div');
        widgetHost.id = 'searchMapInputWidget';
        oldInput.parentNode.insertBefore(widgetHost, oldInput);

        // skjul original input (backend-felt)
        oldInput.style.display = 'none';
    }

    // 2) Sett inn den nye Places-widgeten (lager sitt eget inputfelt)
const existingName = (document.getElementById('location-name')?.value || '').trim();
const existingSted = (document.getElementById('searchMapInput')?.value || '').trim();
const existingAddr = (document.getElementById('location-address')?.value || '').trim();

// Prioriter: navn → sted-feltet → adresse
const existing = existingName || existingSted || existingAddr;

    const widget = new google.maps.places.PlaceAutocompleteElement({
    value: existing
    });
    widgetHost.innerHTML = '';
    widgetHost.appendChild(widget);

    // 3) Når bruker velger et sted
    widget.addEventListener('gmp-select', async (event) => {
        const place = event.placePrediction.toPlace();

        await place.fetchFields({
            fields: ["displayName", "formattedAddress", "location", "googleMapsURI"],
        });

        const name = place.displayName || '';
        const address = place.formattedAddress || '';
        const lat = place.location?.lat();
        const lon = place.location?.lng();
        const url = place.googleMapsURI || '';

        // Oppdater "sted"-feltet backend forventer
        jQuery('#searchMapInput').val(name);
        widget.value = name;


        // Bygg static map med koordinater (mer robust enn name+address)
        const center = `${lat},${lon}`;
        const markerIcon = 'https://assets.ukm.no/img/UKM_logo_64x64.png';

        const map =
            'https://maps.googleapis.com/maps/api/staticmap' +
            '?center=' + encodeURIComponent(center) +
            '&zoom=15&size=400x300&scale=1' +
            '&markers=' + encodeURIComponent(`icon:${markerIcon}|label:U|${center}`) +
            '&key=' + GOOGLE_API_KEY;

        // Sett hidden felter som før
        jQuery('#location-name').val(name);
        jQuery('#location-address').val(address);
        jQuery('#location-lat').val(lat);
        jQuery('#location-lon').val(lon);
        jQuery('#location-map').val(map);
        jQuery('#location-link').val(url);

        // Oppdater preview
        jQuery('#mapLink').attr('href', url || '#');
        jQuery('#map').attr('src', map);
    });

    // 4) Hvis arrangement allerede har kart lagret: vis preview ved load
    const existingMap = jQuery('#location-map').val();
    const existingLink = jQuery('#location-link').val();
    if (existingMap) jQuery('#map').attr('src', existingMap);
    if (existingLink) jQuery('#mapLink').attr('href', existingLink);
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