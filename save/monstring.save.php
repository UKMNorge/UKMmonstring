<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Skjema\Write as SkjemaWrite;
use UKMNorge\Arrangement\Write;
use UKMNorge\Google\StaticMap;
use UKMNorge\Innslag\Typer\Typer;
use UKMNorge\Geografi\Kommune;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement(intval(get_option('pl_id')));
$metadata = [];

/**
 * BASIC INFOS
 */
// NAVN
if (isset($_POST['navn'])) {
    $arrangement->setNavn($_POST['navn']);
}

// STED
if (isset($_POST['sted'])) {
    $arrangement->setSted($_POST['sted']);
}

// GOOGLE-MAP
if (isset($_POST['location_name'])) {
    $map = StaticMap::fromPOST('location');
    $arrangement->setGoogleMapData($map->toJSON());
}

// LAGRE TIDSPUNKTER (start, stop, påmeldingsfrister)
$tidspunkter = [
    'pl_start' => 'Når arrangementet starter',
    'pl_stop' => 'Når arrangementet omtrent slutter',
    'pl_deadline' => 'Påmeldingsfrist for de som viser frem noe',
    'pl_deadline2' => 'Påmeldingsfrist for de som bidrar som noe'
];
$funksjoner = [
    'pl_start' => 'setStart',
    'pl_stop' => 'setStop',
    'pl_deadline' => 'setFrist1',
    'pl_deadline2' => 'setFrist2'
];

foreach ($tidspunkter as $felt => $tekst) {
    if (isset($_POST[$felt])) {
        $tidspunkt = DateTime::createFromFormat(
            'd.m.Y-H:i',
            $_POST[$felt] . '-' . $_POST[$felt . '_time']
        );
        if (is_bool($tidspunkt)) {
            UKMmonstring::getFlashbag()->add(
                'danger',
                $tekst . ' ble dessverre ikke lagret. Prøv igjen.'
            );
        } else {
            $funksjon = $funksjoner[$felt];
            $arrangement->$funksjon($tidspunkt->getTimestamp());
        }
    }
}

if (isset($_POST['kommuner']) && is_array($_POST['kommuner'])) {
    $kommuner = [];

    foreach ($_POST['kommuner'] as $kommune_id) {
        $kommuner[] = (int) $kommune_id;
    }

    $arrangement->setKommuner($kommuner);
}

// Hvis brukeren har lastet opp bilde
if( empty( $_POST['banner_image'] ) || $_POST['banner_image'] == 'false' ) {
    delete_option('UKM_banner_image');
    delete_option('UKM_banner_image_large');
} else {
    delete_option('UKM_banner_image_large');
    update_option('UKM_banner_image', $_POST['banner_image'] );
    update_option('UKM_banner_image_position_y', $_POST['banner_image_position_y'] );
    if( isset( $_POST['banner_image_id'] ) && !empty( $_POST['banner_image_id'] ) ) {
        $wp_image = wp_get_attachment_metadata( $_POST['banner_image_id'] );
        if( isset( $wp_image['sizes']['forsidebilde'] ) ) {
            $override = wp_get_attachment_image_src( $_POST['banner_image_id'], 'forsidebilde');
            if( is_array( $override ) && is_string( $override[0] ) && !empty( $override[0] ) ) {
                update_option('UKM_banner_image_large', $override[0]);
            }
        }
    }
}


/**
 * SYNLIGHET
 */
// SYNLIGHET
if (isset($_POST['synlig'])) {
    $arrangement->setSynlig($_POST['synlig'] == 'true');
}

if (isset($_POST['beskrivelse'])) {
    $arrangement->setBeskrivelse($_POST['beskrivelse']);
}


// Synlighet av deltakere
if (isset($_POST['deltakeresynlig'])) {
    $arrangement->setDeltakereSynlig($_POST['deltakeresynlig'] == 'true');
}

// PÅMELDING
if (isset($_POST['pamelding'])) {
    $pamelding = str_replace(['true','false'],['apen','ingen'], $_POST['pamelding']);
    $arrangement->setPamelding($pamelding);
}

// VIDERESENDING
if (isset($_POST['videresending'])) {
    $arrangement->setHarVideresending($_POST['videresending'] == 'true');
}

/**
 * PÅMELDING
 */
// TYPER INNSLAG SOM TILLATES
// Hvis arrangement er kunstgalleri så kan arrangementet ha bare innslag av type utstilling
if($arrangement->erKunstgalleri()) {
    $arrangement->getInnslagtyper()->leggTil(Typer::getByKey('utstilling'));
}
else {
    $arrangement->getInnslagtyper()->getAll(); // laster de inn
    foreach (Typer::getAllTyper() as $tilbud) {
        if (!isset($_POST['tilbud_' . $tilbud->getKey()])) {
            try {
                $arrangement->getInnslagtyper()->fjern(Typer::getByKey($tilbud->getKey()));
            } catch (Exception $e) {
                if ($e->getCode() != 110001) {
                    throw $e;
                }
            }
        } else {
            $arrangement->getInnslagtyper()->leggTil(Typer::getByKey($tilbud->getKey()));
        }
    }
}
if ($arrangement->erArrangement()) {
    $arrangement->getInnslagTyper()->leggTil(Typer::getByKey('enkeltperson'));
} elseif ($arrangement->getInnslagTyper()->getAntall() == 0) {
    UKMmonstring::getFlashbag()->info(
        'Det er ikke mulig å ha et arrangement med påmelding uten noen tillatte kategorier. ' .
            'Vi har derfor åpnet for standard-kategoriene, men du må gjerne redigere de.'
    );
}

// FYLKE: nedslagsfelt (fylke / land)
if (isset($_POST['nedslagsfelt'])) {
    $metadata[] = 'nedslagsfelt';
}

// SKJEMA: for enkeltpersoner
if (isset($_POST['harDeltakerskjema'])) {
    $metadata[] = 'harDeltakerskjema';
    if ($_POST['harDeltakerskjema'] == 'true') {
        try {
            $skjema = $arrangement->getDeltakerSkjema();
        } catch (Exception $e) {
            // Fant ikke skjema
            if ($e->getCode() == 151002) {
                $skjema = SkjemaWrite::createForPerson($arrangement);
            } else {
                throw $e;
            }
        }
    }
}

// Sett div metadata før lagring
foreach ($metadata as $meta_key) {
    if (isset($_POST[$meta_key])) {
        $value = $_POST[$meta_key];
        if ($value == 'false') {
            $value = false;
        } elseif ($value == 'true') {
            $value = true;
        }
        $arrangement->getMeta($meta_key)->set($value);
    }
}

// Maks antall deltagere
if (isset($_POST['maksantall'])) {

    // Ingen begrensning
    if($_POST['maksantall'] == 'false') {
        $arrangement->setMaksAntallDeltagere(null);
    }
    else if(isset($_POST['maks_antall_deltagere'])) {
        if((int)$_POST['maks_antall_deltagere'] >= $arrangement->getAntallPersoner()) {
            $arrangement->setMaksAntallDeltagere((int)$_POST['maks_antall_deltagere']);
        }
    }
}

// Utvidet GUI-type
if(isset($_POST['gui_type'])) {
    $arrangement->setGuiType($_POST['gui_type'] == 'true' ? 1 : 0);
}

if($arrangement->getEierType() == 'land') {
    if(isset($_POST['kvote_deltakere'])) {
        $arrangement->getMeta('kvote_deltakere')->set((int)$_POST['kvote_deltakere']);
        $arrangement->getMeta('kvote_ledere')->set((int)$_POST['kvote_ledere']);
        $arrangement->getMeta('avgift_ordinar')->set((int)$_POST['avgift_ordinar']);
        $arrangement->getMeta('avgift_subsidiert')->set((int)$_POST['avgift_subsidiert']);
        $arrangement->getMeta('avgift_reise')->set((int)$_POST['avgift_reise']);
    }
}

// LAGRE
try {
    Write::save($arrangement);
    UKMmonstring::getFlashbag()->add(
        'success',
        'Arrangement-info er lagret!'
    );
} catch (Exception $e) {
    UKMmonstring::getFlashbag()->add(
        'danger',
        'Kunne ikke lagre arrangement-info. Systemet sa: ' . $e->getMessage() . ' (' . $e->getCode() . ')'
    );
}

// Oppdater personer som venter i venteliste
if (isset($_POST['maksantall'])) {
    $venteliste = $arrangement->getVenteliste();
    $venteliste->updatePersoner();
}

// reload innslagTyper (i tilfelle man lagrer "blankt")
$arrangement->resetInnslagTyper();
