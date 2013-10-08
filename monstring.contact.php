<?php
function contactForm() {
	require_once('UKM/kontakt.class.php');
	$c = new kontakt((int)$_GET['contact']);
	$m = new monstring(get_option('pl_id'));
	
	if(is_numeric($_GET['contact']))
		echo '<h2>Rediger '.$c->get('name').'</h2>';
	else
		echo '<h2>Legg til ny kontaktperson</h2>';
		
	$v = new Form('kontaktperson_venstre');
	$h = new Form('kontaktperson_hoyre');

	$v->hidden('c_id', $c->get('id'));
	
	$v->input('Fornavn', 'firstname', $c->get('firstname'));
	$v->input('Etternavn', 'lastname', $c->get('lastname'));
	$h->telefon('Telefon', 'tlf', $c->get('tlf'), 'Helst mobil, spesielt hos hovedkontakt');
	$h->input('E-post', 'email', $c->get('email'));
	
	$return = UKMN_fieldset('Kontaktinfo',
							'<div style="width: 375px; float: right;">'.$h->run().'</div>'
							.$v->run()
							,'750px;');

	$v = new Form('kontaktperson_venstre');
	$h = new Form('kontaktperson_hoyre');
	
	if($m->get('type')=='kommune')
		$v->select('Kommune', 'kommune', $m->kommuneArray(), $c->get('kommune'));
	
	$v->input('Tittel', 'title', $c->get('title'));
	$v->input('Facebook', 'facebook', $c->get('facebook'), 
			 'Full adresse til din facebook-profil. Kopier adressen ved &aring; '
			.'h&oslash;yreklikke p&aring; ditt eget navn, og velge &quotkopier lenkeadresse&quot;'
			.'<br />'
			.'Adressen skal se ca slik ut:'
			.'<br />'
			.' &nbsp;  &nbsp; <u>http://facebook.com/UKMNorge</u>');
	$h->bilde('Bilde', 'picture', $c->get('image'), 'HUSK: Klikk &quot;Sett inn i innlegg&quot;', $c->defaultImage());
	

	
	$v->input('Adresse', 'address', $c->get('address'));
	$v->postnummer('Postnummer og -sted', 'postalcode', $c->get('postalcode'));
	
	$return .= UKMN_fieldset('Kontaktdetaljer',
							'<div style="width: 375px; float: right;">'.$h->run().'</div>'
							.$v->run()
							,'750px;');
							
							
	return  '<form action="?page=UKMMonstring" method="POST" id="kontaktSkjema">'
			.  $return
			.  '<p class="submit">'
			.  '<input type="submit" value="Lagre kontaktperson" name="kontaktperson_submit" />'
			.  ' <em>eller</em> '
			.  '<a href="?page=UKMMonstring">g&aring; tilbake til m&oslash;nstringssiden</a>'
			.  '</p>'
			.  '</form>';

}


?>