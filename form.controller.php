<?php
$pl = new monstring(get_option('pl_id'));

$kontakter = $pl->kontakter();
foreach($kontakter as $kontakt) {
	$kontaktpersoner[] = array('name' => $kontakt->get('name'),
							   'kommune' => $kontakt->get('kommunenavn'),
							   'image' => $kontakt->get('picture'),
							   'email' => $kontakt->get('email'),
							   'phone' => $kontakt->get('tlf'),
							   'title' => $kontakt->get('title'),
							   );
}

$infos = array('name' => $pl->get('pl_name'),
			   'place' => $pl->get('pl_place'),
			   'start' => $pl->get('pl_start'),
			   'stop' => $pl->get('pl_stop'),
			   'deadline1' => $pl->get('pl_deadline'),
			   'deadline2' => $pl->get('pl_deadline2'),
			   'tilbud_konferansier' => true,
			   'tilbud_nettredaksjon' => true,
			   'tilbud_arrangor' => true,
			   'tilbud_sceneteknikk' => true,
			   'tilbud_matkultur' => true,
			   'kommuner' => $pl->get('kommuner'),
			   'site_type' => get_option('site_type'),
			   'season' => get_option('season'),
			   'kontaktpersoner' => $kontaktpersoner,
			   'plugin_path' => plugin_dir_url( __FILE__ ),
			  );
?>