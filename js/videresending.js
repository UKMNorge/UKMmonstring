jQuery(document).ready(() => {
    UKMresources.radioButtons.get('videresending').on('change', (value) => {
        if (value == 'true') {
            jQuery('.hvisIkkeVideresending:visible').slideUp();
            jQuery('.hvisVideresending:hidden').slideDown();
        } else {
            jQuery('.hvisIkkeVideresending:hidden').slideDown();
            jQuery('.hvisVideresending:visible').slideUp();
        }
    });

    /* SKAL ARRANGEMENTET BRUKE NOMINASJON */
    UKMresources.radioButtons.get('benyttNominasjon').on('change', (value) => {
        if (value == 'true') {
            jQuery('.hvisIkkeNominasjon:visible').slideUp();
            jQuery('.hvisNominasjon:hidden').slideDown();
            /* HJELP AUTOGROW MED Å VELGE RIKTIG STØRRELSE */
            jQuery('#nominasjon_informasjon').trigger('change');
        } else {
            jQuery('.hvisIkkeNominasjon:hidden').slideDown();
            jQuery('.hvisNominasjon:visible').slideUp();
        }
    });
    
    /* SKAL ARRANGEMENTET BRUKE LEDERSKJEMA */
        UKMresources.radioButtons.get('harLederskjema').on('change', (value) => {
        if (value == 'true') {
            jQuery('.hvisIkkeLederskjema:visible').slideUp();
            jQuery('.hvisLederskjema:hidden').slideDown();
        } else {
            jQuery('.hvisIkkeLederskjema:hidden').slideDown();
            jQuery('.hvisLederskjema:visible').slideUp();
        }
    });
    
    /* SKAL ARRANGEMENTET OGSÅ BRUKE EGENDEFINERT SKJEMA */
        UKMresources.radioButtons.get('vilHaSkjema').on('change', (value) => {
        if (value == 'true') {
            jQuery('.hvisIkkeSkjema:visible').slideUp();
            jQuery('.hvisSkjema:hidden').slideDown();
        } else {
            jQuery('.hvisIkkeSkjema:hidden').slideDown();
            jQuery('.hvisSkjema:visible').slideUp();
        }
    });

});    





/* FILTRER ARRANGEMENT SOM KAN VIDERESENDE TIL ARRANGEMENTET */
jQuery(document).on('keyup', '#filterArrangement', function () {
    var words = jQuery(this).val().toLowerCase().split(' ');
    if (words.length > 1 || (words.length == 1 && words[0].length > 0)) {
        jQuery('.avsenderListe li.avsenderMonstring').show().filter(function () {
            var searchIn = jQuery(this).data('filter').toLowerCase();
            var found = false;

            words.forEach(function (word) {
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

// Hjelpefunksjon for ledetekst i "disse kan videresende til deg"
function skjulVisIngenAvsendere() {
    if (jQuery('#visAvsenderArrangement').find('.avsender').length == 0) {
        jQuery('#ingenAvsendere').show();
    } else {
        jQuery('#ingenAvsendere').hide();
    }
}

/* SKJUL/VIS KOMPAKT LISTE-VISNING OVER ARRANGEMENT SOM KAN VIDERESENDE TIL DETTE ARRANGEMENTET */
jQuery(document).on('click', '#visRedigerAvsenderArrangement', (e) => {
    e.preventDefault();

    skjulVisIngenAvsendere();

    if (jQuery('#container_arrangementVelger').is(':visible')) {
        jQuery('#container_arrangementVelger').slideUp();
        // Oppdater knappens tekst
        jQuery('#visRedigerAvsenderArrangement').html(jQuery('#visRedigerAvsenderArrangement').attr('data-vis'));
    } else {
        jQuery('#container_arrangementVelger').slideDown();
        // Oppdater knappens tekst
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
    skjulVisIngenAvsendere();
});


jQuery(document).ready(() => {
    /* GJØR TEKSTEN TOGGLE-BAR FOR VIS/SKJUL ARRANGEMENTER SOM KAN VIDERESENDE */
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

