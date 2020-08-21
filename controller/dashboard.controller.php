<?php

use UKMNorge\Arrangement\Arrangement;

$arrangement = new Arrangement(intval(get_option('pl_id')));

/* MESSAGES */
$messages = apply_filters('UKMWPDASH_messages', []);
foreach($messages as $key => $message) {
	if($message['level'] == 'alert-error')
		$message[$key]['level'] = 'alert-danger';
}
UKMmonstring::addViewData('messages', $messages );

/* KALENDER */
UKMmonstring::addViewData('kalender', apply_filters('UKMWPDASH_calendar',[]));

/* ANTALL PÅMELDTE (arrangement satt i hoved-controller for mønstring) */
$antall_innslag = 0;
$antall_personer = 0;
foreach( $arrangement->getInnslag()->getAll() as $innslag ) {
    $antall_innslag++;
    $antall_personer += $innslag->getPersoner()->getAntall();
}
UKMmonstring::addViewData('antall', ['personer' => $antall_personer, 'innslag' => $antall_innslag]);