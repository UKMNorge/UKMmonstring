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

$arrangement = UKMmonstring::getArrangement();

$admin_kontakter = [];
foreach( $arrangement->getKontaktpersoner()->getAll() as $kontakt ) {
    if( is_numeric( $kontakt->getAdminId() ) ) {
        $admin_kontakter[] = $kontakt->getAdminId();
    }
}

UKMmonstring::addViewData(
    'admin_kontakter', 
    $admin_kontakter
);