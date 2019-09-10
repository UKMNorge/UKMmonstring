<?php
/**
 * SLETT EN KONTAKTPERSON
 */

use UKMNorge\Arrangement\Arrangement;

require_once('UKM/kontakt.class.php');
require_once('UKM/write_kontakt.class.php');
require_once('UKM/Arrangement/Arrangement.php');
require_once('UKM/write_monstring.class.php');

try {
    $arrangement = new Arrangement( get_option('pl_id') );

    $kontakt = new kontakt_v2( $_POST['id'] );
    $arrangement->getKontaktpersoner()->fjern( $kontakt );
    write_monstring::save( $arrangement );
    write_kontakt::delete( $kontakt );

    UKMmonstring::addResponseData('success', true);
} catch( Exception $e ) {
    UKMmonstring::addResponseData('success', true);
    UKMmonstring::addResponseData(
        'melding',
        'Kunne ikke slette kontaktperson. Systemet sa: '. $e->getMessage()
    );
}