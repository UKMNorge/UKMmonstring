<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Arrangementer;
use UKMNorge\Arrangement\Kommende;
use UKMNorge\Innslag\Typer\Typer;
use UKMNorge\Log\Logger;

date_default_timezone_set('Europe/Oslo');

require_once('UKM/Autoloader.php');
$arrangement = new Arrangement(intval(get_option('pl_id')));


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
            
            
            /*
                case 'infotekst':
                UKMmonstring::setAction('infotekst');
                UKMmonstring::includeActionController();
                do_action('admin_print_footer_scripts');

                die();
                break;
            */

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
    if ($arrangement->erFerdig()) {
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

switch ($arrangement->getType()) {
    case 'kommune':
        UKMmonstring::addViewData(
            'arrangementer_av_kommunen',
            Kommende::byEier(
                $arrangement->getEier()
            )->getAll()
        );
    case 'fylke':
        UKMmonstring::addViewData(
            'arrangementer_av_fylket',
            Kommende::byEier(
                $arrangement->getFylke()
            )->getAll()
        );

        UKMmonstring::addViewData(
            'arrangementer_i_fylket',
            Arrangementer::filterSkipEier(
                $arrangement->getEierObjekt(),
                Kommende::iFylke(
                    $arrangement->getFylke()
                )->getAll()
            )
        );
        if ($arrangement->getType() == 'kommune') {
            break;
        }
    case 'land':
        $fylke_arrangement = [];
        $fylke_monstring = [];
        $andre_arrangement = [];
        foreach (Kommende::getAllCollection()->getAll() as $mottaker) {
            if ($mottaker->getEierType() == 'fylke' && $mottaker->erMonstring()) {
                $fylke_monstring[] = $mottaker;
            } elseif ($mottaker->getEierType() == 'fylke') {
                $fylke_arrangement[] = $mottaker;
            } else {
                $andre_arrangement[] = $mottaker;
            }
        }

        UKMmonstring::addViewData('arrangementer', $andre_arrangement);
        UKMmonstring::addViewData('arrangementer_fylke', $fylke_arrangement);
        UKMmonstring::addViewData('arrangementer_fylke_monstring', $fylke_monstring);
        break;
}

$antall_per_type = [];
foreach (Typer::getAlleInkludertSkjulteTyper() as $type) {
    $antall_personer = 0;
    foreach ($arrangement->getInnslag()->getAllByType($type) as $innslag) {
        $antall_personer += $innslag->getPersoner()->getAntall();
    }
    $antall_per_type[$type->getKey()] = $antall_personer;
}

UKMmonstring::addViewData('pameldte_per_type', $antall_per_type);
UKMmonstring::include('controller/dashboard.controller.php');
