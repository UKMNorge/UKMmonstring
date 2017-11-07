<?php
function getSeasonsData() {
	// OBJEKTER
	$seasons = new stdClass();
	$seasons->active = new stdClass();
	$seasons->new = new stdClass();
	$seasons->new->frist = new stdClass();
	
	// Hvilken måned starter ny sesong
	$seasons->startMonth = 9;
	
	// AKTIV SESONG
	$seasons->active->year	= (int) date("Y"); 	# INT aktiv sesong
	# Førsste dag i aktiv sesong
	$startDate 				= '01.'. $seasons->startMonth .'.'. (($seasons->active->year)-1);
	$seasons->active->start = DateTime::createFromFormat('d.n.Y H:i:s', $startDate.'00:00:00');
	# Siste dag i aktiv sesong
	$daysInStopMonth		= cal_days_in_month(
								CAL_GREGORIAN, 
								( $seasons->startMonth )-1,
								$seasons->active->year
							);
	$stopDate 				= $daysInStopMonth .'.'. $seasons->startMonth .'.'. $seasons->active->year;
	$seasons->active->stop	= DateTime::createFromFormat('d.n.Y H:i:s', $stopDate.'23:59:59');
	
	// NY SESONG
	$seasons->new->year			= ( $seasons->active->year )+ 1;
	# Påmeldingsfrist lokalmønstring
	$seasons->new->frist->lokal = getSeasonsDataFristLokal( $seasons->new->year );
	# Videresendingsfrist fylkesmønstring
	$seasons->new->frist->fylke	= getSeasonsDataFristFylke( $seasons->new->year );
	return $seasons;
}

function getSeasonsDataFristLokal( $season ) {
	$fristLokal 				= '01.01.'. $season;
	return DateTime::createFromFormat('d.n.Y H:i:s', $fristLokal.'23:59:59');
}
function getSeasonsDataFristFylke( $season ) {
	$fristLokal 				= '01.03.'. $season;
	return DateTime::createFromFormat('d.n.Y H:i:s', $fristLokal.'23:59:59');
}