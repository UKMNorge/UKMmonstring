<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\UKMFestival;
use UKMNorge\Arrangement\Arrangementer;
use UKMNorge\Arrangement\Kommende;
use UKMNorge\Innslag\Typer\Typer;
use UKMNorge\Log\Logger;
use UKMNorge\Geografi\Fylker;


date_default_timezone_set('Europe/Oslo');

require_once('UKM/Autoloader.php');
$arrangement = new Arrangement(intval(get_option('pl_id')));

// Hvis arrangement er UKM Festivalen (type 'land'), opprett UKMFestival klasse
if($arrangement->getEierType() == 'land') {
    $arrangement = new UKMFestival(intval(get_option('pl_id')));
    $arrangement->getOvernattingGrupper();
}


/* LAGRE ENDRINGER */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    Logger::initWP($arrangement->getId());

    if (isset($_POST['type'])) {
        switch ($_POST['type']) {
            // ARRANGEMENT
            case 'monstring':
                UKMmonstring::require('save/monstring.save.php');
                break;
            // KONTAKTPERSON
            case 'kontakt':
                UKMmonstring::require('save/kontakt.save.php');
                break;
            // LAGRE ENDRINGER I (PERSON-)SKJEMA
            case 'skjema':
                UKMmonstring::require('save/skjema.save.php');
                break;
            // KORONA-INFO / AVLYS-INFO
            case 'avlys':
                UKMmonstring::require('save/avlys.save.php');
                break;
        }
    }

    // "REDIRECT" TIL KONTAKT/INFOTEKST/SKJEMA ETTER SAVE
    if ($_POST['goTo']) {
        switch ($_POST['goTo']) {
            case 'kontakt':
                $_GET['kontakt'] = $_POST['goToId'];
                UKMmonstring::setAction('kontakt');
                UKMmonstring::includeActionController();
                break;
            case 'avlys':
                UKMmonstring::setAction('avlys');
                UKMmonstring::includeActionController();
                break;
            case 'personskjema':
                UKMmonstring::setAction('skjema_person');
                UKMmonstring::includeActionController();
                break;
        }
    } else {
        if ($arrangement->erFerdig()) {
            UKMmonstring::setAction('ferdig');
        }
    }
    // Reload for å få med alle endringer
    $arrangement = new Arrangement($arrangement->getId());
} else {
    if ($arrangement->erFerdig() && !is_super_admin()) {
        UKMmonstring::setAction('ferdig');
    }
}


// DEV
#UKMmonstring::setAction('kontakt');
#UKMmonstring::includeActionController();
// DEV


/* HENT INN VIEW-DATA */
UKMmonstring::addViewData(
    [
        'arrangement' => $arrangement,
        'innslag_typer' => Typer::getAllTyper(),
        'is_superadmin' => is_super_admin(),
        'GOOGLE_API_KEY' => defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : ''
    ]
);

$antall_per_type = [];
$innslag_arr = [];
foreach (Typer::getAlleInkludertSkjulteTyper() as $type) {
    $antall_personer = 0;
    foreach ($arrangement->getInnslag()->getAllByType($type) as $innslag) {
        $antall_personer += $innslag->getPersoner()->getAntall();
        $innslag->getPersoner()->getAll();
        $innslag_arr[] = $innslag;
    }
    $antall_per_type[$type->getKey()] = $antall_personer;
}

// foreach($innslag_arr as $inn) {
//     echo '<pre>';
//     var_dump($inn->personer_collection);
//     echo '<pre>';
//     echo '<hr>';
// }

// die;


UKMmonstring::addViewData('pameldte_per_type', $antall_per_type);
UKMmonstring::addViewData('fylker', Fylker::getAll());
UKMmonstring::addViewData('innslag_liste', $innslag_arr); // Innslag liste og IKKE innslag type liste
UKMmonstring::include('controller/dashboard.controller.php');
