/* SKAL ARRANGEMENTET BRUKE NOMINASJON */
jQuery(document).on('change', '#radioButtonValue_benyttNominasjon', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('.nominasjon_detaljer').slideDown();
    } else {
        jQuery('.nominasjon_detaljer').slideUp();
    }
});

/* SKAL ARRANGEMENTET BRUKE LEDERSKJEMA */
jQuery(document).on('change', '#radioButtonValue_harLederskjema', (e) => {
    if (jQuery(e.target).val() == 'true') {
        jQuery('#lederskjema_detaljer').slideDown();
        jQuery('#lederskjema_neitakk').slideUp();
    } else {
        jQuery('#lederskjema_detaljer').slideUp();
        jQuery('#lederskjema_neitakk').slideDown();
    }
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
