<?php

/**
 * OVERTA EN KONTAKTPERSON
 */

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Nettverk\OmradeKontaktpersoner;
use UKMNorge\Nettverk\WriteOmradeKontaktperson;


require_once('UKM/Autoloader.php');

try {
    $arrangement = new Arrangement(get_option('pl_id'));
    $okp = OmradeKontaktpersoner::getById($_POST['id']);
    
    $omrade = new Omrade('monstring', $arrangement->getId());

    // Ta over kontaktpersonen
    WriteOmradeKontaktperson::overtaOmradekontaktperson($okp, $omrade);

    UKMmonstring::addResponseData('success', true);
} catch (Exception $e) {
    UKMmonstring::addResponseData('success', true);
    UKMmonstring::addResponseData(
        'melding',
        'Kunne ikke overta kontaktperson. Systemet sa: ' . $e->getMessage()
    );
}