<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Skjema\Write as SkjemaWrite;
use UKMNorge\Arrangement\Videresending\Avsender;
use UKMNorge\Arrangement\Write;
use UKMNorge\Innslag\Typer\Typer;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement(intval(get_option('pl_id')));
$metadata = [];


// VIDERESENDING
if (isset($_POST['videresending'])) {
    $arrangement->setHarVideresending($_POST['videresending'] == 'true');
}

// TIDSPUNKTER (start, stop, påmeldingsfrister)
$tidspunkter = [
    'pl_forward_start' => 'Når påmeldingen åpner ',
    'pl_forward_stop' => 'Når påmeldingen stenger '
];
$funksjoner = [
    'pl_forward_start' => 'setVideresendingApner',
    'pl_forward_stop' => 'setVideresendingStenger'
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
            $arrangement->$funksjon($tidspunkt);
        }
    }
}

// AVSENDERE (avsendere som ikke legges til her fjernes automatisk)
if (isset($_POST['avsender']) && is_array($_POST['avsender'])) {
    foreach ($_POST['avsender'] as $pl_id) {
        $avsender = new Avsender((int) $pl_id, $arrangement->getId());
        $arrangement->getVideresending()->leggTilAvsender($avsender);
    }
}


// Skal noen ting nomineres?
if (isset($_POST['benyttNominasjon'])) {
    $arrangement->setHarNominasjon($_POST['benyttNominasjon'] == 'true');
    if ($_POST['benyttNominasjon'] == 'true') {
        // Lagre alle "nominer_$something"-settings
        $count = 0;
        foreach ($_POST as $post_key => $post_value) {
            if (strpos($post_key, 'nominer_') === 0) {
                $har_nominering = $post_value == 'true';
                if ($har_nominering) {
                    $count++;
                }
                $type = Typer::getByKey(str_replace('nominer_', '', $post_key));
                $arrangement->setHarNominasjonFor($type, $har_nominering);
            }
        }
        if ($count == 0) {
            $arrangement->setHarNominasjon(false);
            UKMmonstring::getFlashbag()->add(
                'warning',
                'Nominasjon ble slått av, da du ikke valgte noen typer innslag som krever nominasjon.'
            );
        }
    } else {
        $arrangement->setHarNominasjon(false);
    }
}

// Finnes det en nominasjonsinformasjon som skal lagres?
if (isset($_POST['nominasjon_informasjon'])) {
    $arrangement->setNominasjonInformasjon($_POST['nominasjon_informasjon']);
}


// Lagre diverse metadata hvis vi har tilgang på de
$metadata = [
    'harLederskjema',
    'ledere_per_deltakere',
    'ledere_per_deltakere_deltakere',
    'pris_hotelldogn',
    'navn_deltakerovernatting',
    #'kvote_deltakere',
    #'kvote_ledere',
    #'avgift_ordinar',
    #'avgift_subsidiert',
    #'avgift_reise'
];


// SKJEMA: for arrangement som videresender
if (isset($_POST['vilHaSkjema'])) {
    $arrangement->setHarSkjema($_POST['vilHaSkjema'] == 'true');
    if($_POST['vilHaSkjema'] == 'true') {
        try {
            $skjema = $arrangement->getSkjema();
        } catch (Exception $e) {
            // Fant ikke skjema
            if ($e->getCode() == 151002) {
                $skjema = SkjemaWrite::createForArrangement($arrangement);
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

// LAGRE
try {
    Write::save($arrangement);
    UKMmonstring::getFlashbag()->add(
        'success',
        'Detaljer om videresending er lagret!'
    );
} catch (Exception $e) {
    UKMmonstring::getFlashbag()->add(
        'danger',
        'Kunne ikke lagre videresendingsdetaljer. Systemet sa: ' . $e->getMessage() . ' (' . $e->getCode() . ')'
    );
}