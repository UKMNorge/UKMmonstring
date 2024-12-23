<?php

/**
 * SLETT EN KONTAKTPERSON
 */

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Kontaktperson\Kontaktperson;
use UKMNorge\Arrangement\Kontaktperson\Write as WriteKontaktperson;
use UKMNorge\Arrangement\Write;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Nettverk\OmradeKontaktpersoner;
use UKMNorge\Nettverk\WriteOmradeKontaktperson;

require_once('UKM/Autoloader.php');

try {
    $arrangement = new Arrangement(get_option('pl_id'));
    $okp = OmradeKontaktpersoner::getById($_POST['id']);
    
    $arrangementOmrade = new Omrade('monstring', $arrangement->getId());
    WriteOmradeKontaktperson::removeFromOmrade($okp, $arrangementOmrade);

    UKMmonstring::addResponseData('success', true);
} catch (Exception $e) {
    UKMmonstring::addResponseData('success', true);
    UKMmonstring::addResponseData(
        'melding',
        'Kunne ikke slette kontaktperson. Systemet sa: ' . $e->getMessage()
    );
}
