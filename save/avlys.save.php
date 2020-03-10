<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Meta\Write as WriteMeta;

$arrangement = new Arrangement( intval(get_option('pl_id')));

$meta = $arrangement->getMeta('avlys');
$meta->set( str_replace('avlys_', '', $_POST['status']));

$meta_status_kort = $arrangement->getMeta('avlys_status_kort');
$meta_status_lang = $arrangement->getMeta('avlys_status_lang');

switch( $_POST['status'] ) {
    case 'gjennomforer':
    break;

    case 'avlys_videresending_kanskje';
        $meta_status_kort->set($_POST['avlys_videresending_kanskje_status_kort']);
        $meta_status_lang->set($_POST['avlys_videresending_kanskje_status_lang']);
    break;
}

if( $_POST['status'] == 'gjennomforer' ) {
    try {
        WriteMeta::delete($meta);
    } catch( Exception $e ) {
        if( $e->getCode() == 523001) {
            // Do nothing. Kan ikke slette ≈ slettet 
        }
    }
} else {
    WriteMeta::set($meta);
    WriteMeta::set($meta_status_kort);
    WriteMeta::set($meta_status_lang);
}