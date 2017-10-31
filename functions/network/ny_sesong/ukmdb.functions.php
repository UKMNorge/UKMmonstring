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
	$fristLokal 				= '01.01.'. $seasons->new->year;
	$seasons->new->frist->lokal	= DateTime::createFromFormat('d.n.Y H:i:s', $fristLokal.'23:59:59');
	# Videresendingsfrist fylkesmønstring
	$fristFylke 				= '01.03.'. $seasons->new->year;
	$seasons->new->frist->fylke	= DateTime::createFromFormat('d.n.Y H:i:s', $fristFylke.'23:59:59');
	return $seasons;
}