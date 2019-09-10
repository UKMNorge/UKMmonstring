<?php

require_once('UKM/Arrangement/Arrangement.php');
require_once('UKM/Meta/Write.php');

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Meta\Write as WriteMeta;

$arrangement = new Arrangement( get_option('pl_id') );
$tekst = $arrangement->getMeta('infotekst_videresending')->set( nl2br($_POST['videresending_editor']));

try {
    WriteMeta::set( $tekst );

    UKMmonstring::getFlashbag()->add(
        'success',
        'Informasjonsteksten er lagret.'
    );
} catch( Exception $e ) {
    UKMmonstring::getFlash()->add(
        'danger',
        'Kunne ikke lagre informasjonstekst. Systemet sa: '. $e->getMessage()
    );
}