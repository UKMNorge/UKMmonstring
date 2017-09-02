<?php
/**
 * Oppretter forside-editor-siden. 
 * Noted issues:
 * - For å starte å skrive må man trykke på den ene tomme linjen, ikke det andre blanket området  - uten at det finnes en blinkende karet som hjelper deg med å se den.
 * 
 */
function UKMmonstring_forside_main() {
	UKMMonstring_script();

	$monstring = new monstring_v2(get_option('pl_id'));
	$TWIGdata = array('UKM_HOSTNAME' => UKM_HOSTNAME, 'monstringsLink' => $monstring->getLink());
	$forside = get_page_by_title("Forside");

	$content = null;
	if( null != $forside ) {
		$content = $forside->post_content;
	}
	
	// Hvis brukeren har trykt lagre, og det ikke er en tom side.
	if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['forside_editor']) ) {
		// Hvis vi har sendt inn data, vis det i editoren uavhengig om vi får til å lagre det eller ikke, sånn at folk  slipper å miste endringer.
		$content = $_POST['forside_editor'];
		$new_content = array
			(
				'post_title' => "Forside",
				'post_type' => 'page',
				'post_content' =>  $_POST['forside_editor'],
				'post_status' => 'publish'
			);
		if( NULL == $forside ) {
			// Ingen informasjon er lagret tidligere, opprett siden.
			$front = wp_insert_post($new_content, true);
			if( null == $front || is_wp_error($front) ){
				$TWIGdata['errors'][] = is_wp_error($front) ? "Klarte ikke å lagre innhold som ny side! Feilmelding: ". $front->get_error_message($code): "Klarte ikke å lagre innhold som ny side!";
			} else {
				$TWIGdata['saved'] = "Opprettet ny forside! ID: ".$front;
			}
		} else {
			// Eller oppdater den vi har fra før
			$new_content['ID'] = $forside->ID;
			$front = wp_update_post($new_content, true);
			if( null == $front || is_wp_error($front) ) {
				$TWIGdata['errors'][] = is_wp_error($front) ? "Klarte ikke å lagre oppdatert innhold! Feilmelding: ". $front->get_error_message($code): "Klarte ikke å oppdatere forsiden!";	
			} else {
				$TWIGdata['saved'] = "Oppdaterte forsiden din! ID: ".$front;
			}
		}

		// Dersom vi ikke har en nyhetsside, opprett den
		$nyheter = opprettNyhetsside("Alle nyheter");
		if( !is_numeric($nyheter) ) {
			$TWIGdata['errors'][] = $nyheter;
		}
		// Oppdater innstillingene
		if(is_numeric($front) && is_numeric($nyheter)) 
			updateWPFrontPageSettings($front, $nyheter);
		else {
			$TWIGdata['errors'][] = "Kan ikke oppdatere forsideinnstillingene - feil med forside eller nyhetsside.";
		}

		// Last inn innholdet på nytt dersom lagring funka - for å laste inn bilder skikkelig.
		if ( empty($TWIGdata['errors']) ) {
			$forside = get_page_by_title("Forside");
			$content = $forside->post_content;
		}
	}

	

	echo TWIG('forside_pre_editor.html.twig', $TWIGdata, dirname(__FILE__) );
	wp_editor($content, 'forside_editor', $settings = array() );
	echo TWIG('forside_post_editor.html.twig', $TWIGdata, dirname(__FILE__) );
}

// Oppretter nyhetsside dersom denne ikke allerede eksisterer
// Returns the ID if successful, or a String with an error message if not.
function opprettNyhetsside($name) {
	$nyhetsside = get_page_by_title($name);
	
	// Hvis siden finnes er det OK.
	if( null != $nyhetsside ) 
		return $nyhetsside->ID;
	
	$new_content = array
			(
				'post_title' => $name,
				'post_type' => 'page',
				'post_content' =>  '',
				'post_status' => 'publish'
			);
	$postID = wp_insert_post($new_content, true);
	if( is_wp_error($postID) ){
		$error = is_wp_error($postID) ? "Klarte ikke å lagre innhold som ny side! Feilmelding: ". $postID->get_error_message($code): "Klarte ikke å lagre innhold som ny side!";
		return $error;
	}
	return $postID;
}

// Oppdaterer Wordpress-innstillingene for forside og nyhetsside.
function updateWPFrontPageSettings($frontID, $postsID) {
	update_option( 'page_on_front', $frontID );
	update_option( 'show_on_front', 'page' );

	update_option( 'page_for_posts', $postsID );
}

?>