<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Videresending\Avsender;
use UKMNorge\Arrangement\Write;
use UKMNorge\Google\StaticMap;
use UKMNorge\Innslag\Typer\Typer;
use UKMNorge\Wordpress\Blog;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement( intval(get_option('pl_id')) );

// ENDRE SYNLIGHET
if (isset($_POST['goTo']) && $_POST['goTo'] == 'synlig') {
    $arrangement->setSynlig($_POST['goToId'] == 'true');

    UKMmonstring::getFlashbag()->add(
        'info',
        'Arrangementet er nå ' . ($_POST['goToId'] == 'true' ? 'synlig på' : 'skjult fra') . ' UKM.no. ' .
            '<a href="?page=' . $_GET['page'] . '" class="goTo" data-action="synlig" data-id="' . ($_POST['goToId'] == 'true' ? 'false' : 'true') . '">Angre</a>'
    );
}
if (isset($_POST['goTo']) && $_POST['goTo'] == 'pamelding') {
    if ($_POST['goToId'] == 'true') {
        $arrangement->setPamelding('apen');
        UKMmonstring::getFlashbag()->add(
            'success',
            'Arrangementet tar nå i mot påmelding fra deltakere.'
        );
    } else {
        $arrangement->setPamelding('ingen');
        UKMmonstring::getFlashbag()->add(
            'warning',
            'Arrangementet tar ikke lenger i mot påmelding fra deltakere! All påmelding må skje via videresending. ' .
                '<br />' .
                'Sjekk at du har oppgitt riktig på hvem som kan sende videre til ditt arrangement nederst på denne siden'
        );
    }
}


// START
if (isset($_POST['start'])) {
    $start = DateTime::createFromFormat(
        'd.m.Y-H:i',
        $_POST['start'] . '-' . $_POST['start_time']
    );
    if (is_bool($start)) {
        UKMmonstring::getFlashbag()->add(
            'danger',
            'Ugyldig starttidspunkt ble dessverre ikke lagret. Prøv igjen.'
        );
    } else {
        $arrangement->setStart($start->getTimestamp());
    }
}

// STOP
if (isset($_POST['stop'])) {
    $stop = DateTime::createFromFormat(
        'd.m.Y-H:i',
        $_POST['stop'] . '-' . $_POST['stop_time']
    );
    if (is_bool($stop)) {
        UKMmonstring::getFlashbag()->add(
            'danger',
            'Ugyldig stopptidspunkt ble dessverre ikke lagret. Prøv igjen.'
        );
    } else {
        $arrangement->setStop($stop->getTimestamp());
    }
}

// FRIST 1
if (isset($_POST['frist_1'])) {
    $frist1 = DateTime::createFromFormat(
        'd.m.Y-H:i:s',
        $_POST['frist_1'] . '-23:59:59'
    );
    if (is_bool($frist1)) {
        UKMmonstring::getFlashbag()->add(
            'danger',
            $arrangement->harPamelding() ?
                'Ugyldig påmeldingsfrist for å vise frem noe ble dessverre ikke lagret. Prøv igjen.' : 'Ugyldig dato for åpning av videresending ble dessverre ikke lagret. Prøv igjen.'
        );
    } else {
        $arrangement->setFrist1($frist1->getTimestamp());
    }
}

// FRIST 2
// For kommuner er frist 2 påmeldingsfrist for "bidra med noe-innslag"
if (isset($_POST['frist_2'])) {
    $frist2_time = $arrangement->getType() == 'kommune' ? '23:59:59' : '08:00:00';
    $frist2 = DateTime::createFromFormat(
        'd.m.Y-H:i:s',
        $_POST['frist_2'] . '-' . $frist2_time
    );
    if (is_bool($frist2)) {
        UKMmonstring::getFlashbag()->add(
            'danger',
            $arrangement->harPamelding() ?
                'Ugyldig påmeldingsfrist for å bidra som noe ble dessverre ikke lagret. Prøv igjen.' : 'Ugyldig frist for videresending ble dessverre ikke lagret. Prøv igjen.'
        );
    } else {
        $arrangement->setFrist2($frist2->getTimestamp());
    }
}

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


// TYPER INNSLAG SOM TILLATES
$arrangement->getInnslagtyper()->getAll(); // laster de inn
foreach (Typer::getAllTyper() as $tilbud) {
    if (!isset($_POST['tilbud_' . $tilbud->getKey()])) {
        try {
            $arrangement->getInnslagtyper()->fjern(Typer::getByName($tilbud->getKey()));
        } catch (Exception $e) {
            if ($e->getCode() != 110001) {
                throw $e;
            }
        }
    } else {
        $arrangement->getInnslagtyper()->leggTil(Typer::getByName($tilbud->getKey()));
    }
}
if ($arrangement->erArrangement()) {
    $arrangement->getInnslagTyper()->leggTil(Typer::getByName('enkeltperson'));
} elseif ($arrangement->getInnslagTyper()->getAntall() == 0) {
    UKMmonstring::getFlashbag()->info(
        'Det er ikke mulig å ha et arrangement med påmelding uten noen tillatte kategorier. ' .
            'Vi har derfor åpnet for standard-kategoriene, men du må gjerne redigere de.'
    );
}

// VIDERESENDING
if (isset($_POST['tillatVideresending'])) {
    $arrangement->setHarVideresending($_POST['tillatVideresending'] == 'true');
}

// VIDERESENDING: Skjema
$arrangement->setHarSkjema($_POST['vilHaSkjema'] == 'true');

// Hvis arrangementet tar i mot videresending, lagre hvem fra
if ($arrangement->harVideresending()) {
    if (isset($_POST['avsender']) && is_array($_POST['avsender'])) {
        foreach ($_POST['avsender'] as $pl_id) {
            $avsender = new Avsender((int) $pl_id, $arrangement->getId());
            $arrangement->getVideresending()->leggTilAvsender($avsender);
        }
    }
}

// Skal noen ting nomineres?
if (isset($_POST['benyttNominasjon'])) {
    if ($_POST['benyttNominasjon'] == 'true') {
        $arrangement->setHarNominasjon(true);

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
    'kvote_deltakere',
    'kvote_ledere',
    'pris_hotelldogn',
    'avgift_ordinar',
    'avgift_subsidiert',
    'avgift_reise',
    'navn_deltakerovernatting'
];

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


try {
    Write::save($arrangement);
    UKMmonstring::getFlashbag()->add(
        'success',
        'Arrangement-info er lagret!'
    );
} catch (Exception $e) {
    debug_print_backtrace();
    UKMmonstring::getFlashbag()->add(
        'danger',
        'Kunne ikke lagre arrangement-info. Systemet sa: ' . $e->getMessage() . ' (' . $e->getCode() . ')'
    );
}

// reload innslagTyper (i tilfelle man lagrer "blankt")
$arrangement->resetInnslagTyper();
