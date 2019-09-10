<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Google\StaticMap;

date_default_timezone_set('Europe/Oslo');

require_once('UKM/Arrangement/Arrangement.php');
$arrangement = new Arrangement( get_option('pl_id') );

/* LAGRE ENDRINGER */
if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    require_once('UKM/logger.class.php');
    UKMlogger::initWP(get_option('pl_id'));

    // LAGRE ENDRINGER I MØNSTRING
    if( isset($_POST['type']) && $_POST['type'] == 'monstring' ) {
        require_once('monstring.save.php');
    }

    // LAGRE ENDRINGER I KONTAKTPERSON
    if( isset($_POST['type']) && $_POST['type'] == 'kontakt' ) {
        require_once('kontakt.save.php');
    } 

    // LAGRE ENDRINGER I KONTAKTPERSON
    if( isset($_POST['type']) && $_POST['type'] == 'infotekst' ) {
        require_once('infotekst.save.php');
    }  

    // "REDIRECT" TIL KONTAKT ETTER SAVE
    if ($_POST['goTo'] ) {
        switch( $_POST['goTo'] ) {
            case 'kontakt':
                $_GET['kontakt'] = $_POST['goToId'];
                UKMmonstring::setAction('kontakt');
                UKMmonstring::includeActionController();
                break;
            case 'infotekst':
                UKMmonstring::setAction('infotekst');
                UKMmonstring::includeActionController();
                die();
                break;
        }
    }
}


/* HENT INN VIEW-DATA */

// Be brukeren om å vente til vi har satt opp ny sesong
if (!is_super_admin() && date('m') > 6 && (int) $arrangement->getSesong() <= (int) date('Y')) {
    UKMmonstring::setAction('vent-til-ny-sesong');
} else {
    require_once('UKM/innslag_typer.class.php');

    if( date('m') > 6 && (int) $arrangement->getSesong() <= (int) date('Y') ) {
        UKMmonstring::getFlashbag()->add(
            'warning',
            'Redigering av arrangement er kun mulig fordi du er superadmin. '.
            'For alle andre er redigering stengt i påvente av at ny sesong skal settes opp.'
        );
    }

    UKMmonstring::addViewData(
        [
            'arrangement' => $arrangement,
            'innslag_typer' => innslag_typer::getAllTyper(),
            'is_superadmin' => is_super_admin(),
            'GOOGLE_API_KEY' => GOOGLE_API_KEY
        ]
    );
}


