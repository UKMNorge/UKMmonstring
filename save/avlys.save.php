<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Meta\Write as WriteMeta;

$arrangement = new Arrangement( intval(get_option('pl_id')));

// Set overordnet status
$meta = $arrangement
    ->getMeta('avlys')
    ->set( str_replace('avlys_', '', $_POST['status']));


// Fjern status hvis det gjennomføres
if( $_POST['status'] == 'gjennomforer' ) {
    try {
        WriteMeta::delete($meta);
    } catch( Exception $e ) {
        if( $e->getCode() == 523001) {
            // Do nothing. Kan ikke slette ≈ slettet 
        }
    }
}
// Oppdater tekster og status
else {
    WriteMeta::set($meta);
    WriteMeta::set(
        $arrangement
        ->getMeta('avlys_status_kort')
        ->set($_POST[$_POST['status'].'_status_kort'])
    );

    WriteMeta::set(
        $arrangement
        ->getMeta('avlys_status_lang')
        ->set($_POST[$_POST['status'].'_status_lang'])
    );
}