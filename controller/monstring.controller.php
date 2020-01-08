<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Arrangementer;
use UKMNorge\Arrangement\Eier;
use UKMNorge\Google\StaticMap;
use UKMNorge\Arrangement\Load as LoadArrangement;
use UKMNorge\Innslag\Typer\Typer;

date_default_timezone_set('Europe/Oslo');

require_once('UKM/Autoloader.php');
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

    // LAGRE ENDRINGER I INFOTEKST
    if( isset($_POST['type']) && $_POST['type'] == 'infotekst' ) {
        require_once('infotekst.save.php');
    }  

    // LAGRE ENDRINGER I SKJEMA
    if( isset($_POST['type']) && $_POST['type'] == 'skjema' ) {
        require_once('skjema.save.php');
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
                do_action('admin_print_footer_scripts');

                die();
                break;
            case 'skjema':
                UKMmonstring::setAction('skjema');
                UKMmonstring::includeActionController();
                break;
        }
    }
}

/* DEVELOPMENT */
/*
echo '<pre>';var_dump($_POST);echo '</pre>';
UKMmonstring::setAction('skjema');
UKMmonstring::includeActionController();
*/

/* HENT INN VIEW-DATA */

// Be brukeren om å vente til vi har satt opp ny sesong
if (!is_super_admin() && date('m') > 6 && (int) $arrangement->getSesong() <= (int) date('Y')) {
    UKMmonstring::setAction('vent-til-ny-sesong');
} else {

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
            'innslag_typer' => Typer::getAllTyper(),
            'is_superadmin' => is_super_admin(),
            'GOOGLE_API_KEY' => defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : ''
        ]
    );
}

switch( $arrangement->getType() ) {
    case 'kommune':
        UKMmonstring::addViewData(
            'arrangementer_av_kommunen',
            LoadArrangement::byEier(
                $arrangement->getSesong(),
                $arrangement->getEier()
            )->getAll()
        );
    case 'fylke':
        UKMmonstring::addViewData(
            'arrangementer_av_fylket',
            LoadArrangement::byEier(
                $arrangement->getSesong(),
                $arrangement->getFylke()
            )->getAll()
        );

        UKMmonstring::addViewData(
            'arrangementer_i_fylket',
            Arrangementer::filterSkipEier(
                $arrangement->getEier(),
                LoadArrangement::iFylke(
                    $arrangement->getSesong(),
                    $arrangement->getFylke()
                )->getAll()
            )
        );
    break;
}

UKMmonstring::include('controller/dashboard.controller.php');