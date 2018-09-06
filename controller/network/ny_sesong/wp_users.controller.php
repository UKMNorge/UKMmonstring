<?php
require_once('UKM/write_wp_blog.class.php');
require_once('UKM/write_wp_user.class.php');
require_once('UKM/inc/password.inc.php');
require_once('UKM/fylker.class.php');
global $wpdb;

$brukere = [];
$brukertyper = array('' => '@ukm.no', 'urg-' => '@urg.ukm.no');

foreach( write_wp_blog::getFylkesbrukere() as $fylkeId => $fylkedata ) {
	foreach( $fylkedata as $brukernavn => $epost ) {
		$rapport = new stdClass();
		$rapport->success = true;
		$rapport->brukernavn = $brukernavn;
	
		$bruker = new write_wp_UKM_user( $brukernavn, 'username' );
	
		// Bruker finnes ikke i klartekstabellen
		if( !$bruker->exists() ) {
			try {
				/**
				 * Opprett bruker.
				 * write_wp_UKM_user::create vil selv sjekke om brukeren 
				 * finnes i wordpress med gitt brukernavn, og deretter
				 * opprette / koble objektene
				**/
				$fylke_id = fylker::getByLink( str_replace('urg-','',$brukernavn) )->getId();
				$bruker = write_wp_UKM_user::create( 
												$brukernavn, 								// Username
												$epost,										// Email
												UKM_ordpass(),								// Password
												$fylke_id,									// Fylke
												0,											// Kommune
												false										// WP_ID
											);
				$rapport->status = 'Bruker opprettet';
			} catch( Exception $e ) {
				$rapport->success = false;
				$rapport->error = $e->getMessage();
			}
		} else {
			$rapport->status = 'Bruker eksisterer, passord oppdatert';
		}
		$brukere[] = $rapport;
		
		if( $rapport->success ) {
			$passord = UKM_ordpass();
			$rapport->passord = $passord;
			$bruker->setPassord( $passord );
		
			$bruker->setLock( true );
			
			write_wp_blog::fjernBrukerFraBlogg( $bruker->getWPID(), 1 );
		}
	}
}
$TWIGdata['brukere'] = $brukere;