<?php
/**
 * SLETT EN KONTAKTPERSON
 */

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Kontaktperson\Write as WriteKontaktperson;
use UKMNorge\Arrangement\Write;
use UKMNorge\Innslag\Context\Kontaktperson;

require_once('UKM/Autoloader.php');

try {
    $arrangement = new Arrangement( get_option('pl_id') );

    $kontakt = new Kontaktperson( $_POST['id'] );
    $arrangement->getKontaktpersoner()->fjern( $kontakt );
    Write::save( $arrangement );
    WriteKontaktperson::delete( $kontakt );

    UKMmonstring::addResponseData('success', true);
} catch( Exception $e ) {
    UKMmonstring::addResponseData('success', true);
    UKMmonstring::addResponseData(
        'melding',
        'Kunne ikke slette kontaktperson. Systemet sa: '. $e->getMessage()
    );
}