<?php
$pl = new monstring(get_option('pl_id'));

$kontakter = $pl->kontakter();
foreach($kontakter as $kontakt) {
	$kontaktpersoner[] = array('id' => $kontakt->get('id'),
							   'name' => $kontakt->get('name'),
							   'kommune' => $kontakt->get('kommunenavn'),
							   'image' => $kontakt->get('picture'),
							   'email' => $kontakt->get('email'),
							   'phone' => $kontakt->get('tlf'),
							   'title' => $kontakt->get('title'),
							   );
}



$contacts = $pl->kontakter_pamelding();
foreach($contacts as $kommune => $c)
	$hovedkontakter[] = array('kommune' => $kommune,
							  'name' => $c->get('name')
							  );

$konferansier = $nettredaksjon = $arrangor = $sceneteknikk = $matkultur = false;
if(!$pl->registered()) {
	$konferansier = $nettredaksjon = $arrangor = $sceneteknikk = true;
} else {
	$bt = $pl->getBandTypes();	
	foreach($bt as $btid => $btname) {
		switch( $btid ) {
			case '4':
				$konferansier = true;
				break;
			case '5':
				$nettredaksjon = true;
				break;
			case '6':
				$matkultur = true;
				break;
			case '8':
				$arrangor = true;
				break;
			case '9':
				$sceneteknikk = true;
				break;
		}
	}
}


$infos = array('name' => $pl->get('pl_name'),
			   'place' => $pl->get('pl_place'),
			   'start' => $pl->get('pl_start'),
			   'stop' => $pl->get('pl_stop'),
			   'deadline1' => $pl->get('pl_deadline'),
			   'deadline2' => $pl->get('pl_deadline2'),
			   'tilbud_konferansier' => $konferansier,
			   'tilbud_nettredaksjon' => $nettredaksjon,
			   'tilbud_arrangor' => $arrangor,
			   'tilbud_sceneteknikk' => $sceneteknikk,
			   'tilbud_matkultur' => $matkultur,
			   'kommuner' => $pl->get('kommuner'),
			   'site_type' => get_option('site_type'),
			   'season' => get_option('season'),
			   'kontaktpersoner' => $kontaktpersoner,
			   'hovedkontakter' => $kontaktpersoner,
			   'plugin_path' => plugin_dir_url( __FILE__ ),
			  );
?>