<?php 
require_once('UKM/kontakt.class.php');

if(is_numeric($_GET['contact'])) {
	$contact = new kontakt($_GET['contact']);
	
	$infos = array('id'		=> $contact->get('id'),
				   'firstname' => $contact->get('firstname'),
				   'lastname' => $contact->get('lastname'),
				   'phone' => $contact->get('tlf'),
				   'email' => $contact->get('email'),
				   'title' => $contact->get('title'),
				   'facebook' => $contact->get('facebook'),
				   'kommune' => $contact->get('kommune'),
				   'address' => $contact->get('address'),
				   'postal' => $contact->get('postalcode'),
				   'image' => $contact->get('image'),
				   'season' => get_option('season'),
				   );
} else {
	$infos['id'] = 'new';
}

$pl = new monstring(get_option('pl_id'));

$infos['kommuner'] = $pl->get('kommuner');
$infos['is_fellesmonstring'] = $pl->fellesmonstring();
$infos['site_type']	= get_option('site_type');

?>