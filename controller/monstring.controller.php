<?php
date_default_timezone_set('Europe/Oslo');

require_once('UKM/monstring.class.php');
require_once('UKM/innslag_typer.class.php');

$monstring = new monstring_v2( get_option('pl_id') );

$TWIGdata['monstring'] = $monstring;
$TWIGdata['innslag_typer'] = innslag_typer::getAllTyper();

if( $monstring->getType() == 'land' ) {
    foreach( UKMMonstring_sitemeta_storage() as $key ) {
        $TWIGdata[ $key ] = get_site_option( 'UKMFvideresending_'.$key.'_'.$monstring->getSesong() );
    }
}
