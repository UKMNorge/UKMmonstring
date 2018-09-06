<?php
$pl = new monstring(get_option('pl_id'));
$kontaktpersoner = array();
$kontakter = $pl->kontakter();
if(!is_array($kontakter) && $pl->registered()) {
	$_MESSAGE = array('success' => false,
					  'title' => 'Kontaktperson mangler',
					  'body' => 'Legg til kontaktperson nedenfor');
} elseif(is_array($kontakter))
	$fylkemail = $pl->get('url').'@ukm.no';
	$urgmail = $pl->get('url').'@urg.ukm.no';

	if(is_array($kontakter))
	foreach($kontakter as $kontakt) {
		#$deleteable =  $fylkemail != $kontakt->get('email') && $urgmail != $kontakt->get('email');
		$deleteable = 'false' == $kontakt->get('system_locked');
		$kontaktpersoner[] = array('id' => $kontakt->get('id'),
								   'name' => $kontakt->get('name'),
								   'kommune' => $kontakt->get('kommunenavn'),
								   'image' => $kontakt->get('picture'),
								   'email' => $kontakt->get('email'),
								   'phone' => $kontakt->get('tlf'),
								   'title' => $kontakt->get('title'),
								   'deleteable' => $deleteable,
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
			case '10':
				$ressurs = true;
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
			   'tilbud_ressurs' => $ressurs,
			   'kommuner' => $pl->get('kommuner'),
			   'site_type' => get_option('site_type'),
			   'season' => get_option('season'),
			   'kontaktpersoner' => $kontaktpersoner,
			   'hovedkontakter' => $hovedkontakter,
			   'plugin_path' => plugin_dir_url( __FILE__ ),
			  );

if( $infos['site_type'] == 'kommune' ) {
	$infos['fellesmonstring'] = $pl->fellesmonstring();
} else {
	$infos['fellesmonstring'] = false;
}
$season = get_option('season');#($month > 7) ? date('Y')+1 : date('Y');	
$infos['UKMFvideresending_info1'] = get_site_option('UKMFvideresending_info1_'.$season);
$infos['UKMFvideresending_nominasjon_ua'] = get_site_option('UKMFvideresending_nominasjon_ua_'.$season);
$infos['UKMFvideresending_nominasjon_ukmmedia'] = get_site_option('UKMFvideresending_nominasjon_ukmmedia_'.$season);
$infos['UKMFvideresending_nominasjon_konf'] = get_site_option('UKMFvideresending_nominasjon_konf_'.$season);
$infos['UKMFvideresending_nominasjon_frister'] = get_site_option('UKMFvideresending_nominasjon_frister');

$videresendingsfelter = array('hotelldogn_pris', 'kvote_ledere', 'kvote_deltakere', 'ledermiddag_avgift', 'ledermiddag_dag','ledermiddag_tid','ledermiddag_sted');
foreach( $videresendingsfelter as $key ) {
	$infos[ $key ] = get_site_option('UKMFvideresending_'.$key.'_'.$season, $_POST[$key]);
}

$infos['infotekst_url'] = menu_page_url('UKMmonstring_videresending_info', false);
#$infos['infotekst_url'] = 'admin.php?page=UKMmonstring_videresending_info';

?>