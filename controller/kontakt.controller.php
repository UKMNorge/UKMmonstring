<?php

require_once('UKM/kontakt.class.php');

if( is_numeric( $_GET['kontakt'] ) ) {
    $kontakt = new kontakt_v2( $_GET['kontakt'] );
    UKMmonstring::addViewData(
        'kontakt',
        $kontakt
    );
}