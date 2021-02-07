/* SPØRRESKJEMA: SLETT SPØRSMÅL */
jQuery(document).on('click', '.actions > .delete', (e) => {
    e.preventDefault();

    var sure = confirm('Er du sikker på at du vil slette dette spørsmålet?');
    if (sure) {
        var sporsmal = jQuery(e.target).parents('li.sporsmal');
        var sporsmal_id = jQuery(sporsmal).find('.sporsmal_id').val();
        sporsmal.fadeOut(300, function () {
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

    if (maxQuestions && maxQuestions < jQuery('#skjema > li.sporsmal').length) {
        return alert('Beklager, du kan legge til maks ' + maxQuestions + ' spørsmål');
    }
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
        jQuery('<li class="list-group-item sporsmal" style="background-color: rgb(217,218,218);"/>').html(twigJS_skjemaRad.render({ sporsmal: { id: 'new' }, excludeQuestionTypes }))
    );
    jQuery('#newSporsmalContainer textarea').autogrow();
});

/* INITIER SKJEMA-FUNKSJONER */
jQuery(document).ready(function () {
    jQuery("#skjema").sortable({ items: "> li:not(:first)" });
    if (jQuery('#newSporsmalContainer').length) {
        jQuery('#newSporsmalContainer').html(twigJS_skjemaRad.render({ sporsmal: { id: 'new' }, excludeQuestionTypes }));
    }
    jQuery('textarea').autogrow();
});