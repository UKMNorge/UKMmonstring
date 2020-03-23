<?php

use UKMNorge\Arrangement\Kontaktperson\Kontaktperson;

require_once('UKM/Autoloader.php');

if( is_numeric( $_GET['kontakt'] ) ) {
    $kontakt = new Kontaktperson( intval($_GET['kontakt']) );
    UKMmonstring::addViewData(
        'kontakt',
        $kontakt
    );
}