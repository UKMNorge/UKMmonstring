<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Log\Logger;

date_default_timezone_set('Europe/Oslo');

require_once('UKM/Autoloader.php');
$arrangement = new Arrangement(intval(get_option('pl_id')));


/* LAGRE ENDRINGER */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    Logger::initWP($arrangement->getId());

    // LAGRE ENDRINGER I INFOTEKST
    if (isset($_POST['type'])) {
        switch ($_POST['type']) {
            case 'infotekst':
                UKMmonstring::require('save/infotekst.save.php');
                break;
        }
    }

    if (isset($_POST['goTo'])) {
        switch ($_POST['goTo']) {
            case 'skjema':
                UKMmonstring::setAction('skjema');
                #UKMmonstring::includeActionController();
                break;
        }
    }
}


UKMmonstring::addViewData(
    [
        'arrangement' => $arrangement,
        'is_superadmin' => is_super_admin()
    ]
);
