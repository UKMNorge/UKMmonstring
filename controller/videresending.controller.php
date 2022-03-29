<?php

use UKMNorge\Arrangement\Aktuelle;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Arrangementer;
use UKMNorge\Innslag\Typer\Typer;
use UKMNorge\Log\Logger;

date_default_timezone_set('Europe/Oslo');

require_once('UKM/Autoloader.php');
$arrangement = new Arrangement(intval(get_option('pl_id')));


/* LAGRE ENDRINGER */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    Logger::initWP($arrangement->getId());

    UKMmonstring::require('save/videresending.save.php');

    // LAGRE ENDRINGER I INFOTEKST
    if (isset($_POST['type'])) {
        switch ($_POST['type']) {
            case 'infotekst':
                UKMmonstring::require('save/infotekst.save.php');
                break;
            // LAGRE ENDRINGER I (PERSON-)SKJEMA
            case 'skjema':
                UKMmonstring::require('save/skjema.save.php');
                break;
        }
    }

    if (isset($_POST['goTo'])) {
        switch ($_POST['goTo']) {
            case 'skjema':
                UKMmonstring::setAction('skjema');
                #UKMmonstring::includeActionController();
                break;
            case 'infotekst':
                UKMmonstring::setAction('infotekst');
                UKMmonstring::includeActionController();
                do_action('admin_print_footer_scripts');

                die();
        }
    }
}

// Laster inn på nytt for å få med alle endringer
$arrangement = new Arrangement($arrangement->getId());

/* HENT ANDRE ARRANGEMENT (SOM KAN VIDERESENDE HIT) */
switch ($arrangement->getType()) {
    case 'kommune':
        UKMmonstring::addViewData(
            'arrangementer_av_kommunen',
            Aktuelle::byEier(
                $arrangement->getEier()
            )->getAll()
        );
    case 'fylke':
        UKMmonstring::addViewData(
            'arrangementer_av_fylket',
            Aktuelle::byEier(
                $arrangement->getFylke()
            )->getAll()
        );

        UKMmonstring::addViewData(
            'arrangementer_i_fylket',
            Arrangementer::filterSkipEier(
                $arrangement->getEierObjekt(),
                Aktuelle::iFylke(
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
        foreach (Aktuelle::getAllCollection()->getAll() as $mottaker) {
            if ($mottaker->getEierType() == 'fylke' && $mottaker->erMonstring()) {
                $fylke_monstring[] = $mottaker;
            } elseif ($mottaker->getEierType() == 'fylke') {
                $fylke_arrangement[] = $mottaker;
            } else {
                $stop = (int)$mottaker->getStop()->format('Y');
                if($stop > (int)date("Y")-3) {
                    $andre_arrangement[] = $mottaker;
                }
            }
        }

        UKMmonstring::addViewData('arrangementer', $andre_arrangement);
        UKMmonstring::addViewData('arrangementer_fylke', $fylke_arrangement);
        UKMmonstring::addViewData('arrangementer_fylke_monstring', $fylke_monstring);
        break;
}

UKMmonstring::addViewData(
    [
        'arrangement' => $arrangement,
        'innslag_typer' => Typer::getAllTyper(),
        'is_superadmin' => is_super_admin()
    ]
);
