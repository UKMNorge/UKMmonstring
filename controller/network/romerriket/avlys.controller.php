<?php
error_reporting(E_ALL ^ E_DEPRECATED);
require_once( PLUGIN_DIR . 'functions/network/ny_sesong/ukmdb.functions.php');
require_once('UKM/monstringer.class.php');
require_once('UKM/write_monstring.class.php');
require_once('UKM/write_wp_blog.class.php');

$season = (int) get_site_option('season');
$TWIGdata['season'] = $season;

if( isset( $_GET['avlys'] ) && is_numeric( $_GET['avlys'] ) ) {
	// Kalkuler innslag som mister mønstringen sin
	$monstring = new write_monstring( $_GET['avlys'] );	
	$init = false;
	try {
		$pavirkede = $monstring->getInnslag()->getAntall();
		$TWIGdata['pavirkede'] = $pavirkede;
		$init = true;
	} catch( Exception $e ) {
		$VIEW = 'avlys-error';
		$TWIGdata['rapport'] = array(
			'error' => 'Mønstringen ser ut til å være helt/delvis avlyst allerede ('. $e->getMessage() .' )'
		);
	}

	// Sjekk at vi har en gyldig mønstring med oss før vi kjører på.
	if( $init ) {
		// Initer UKMlogger
		UKMlogger::setID( 'wordpress', get_current_user_id(), $monstring->getId() );
		/**
		 * Mønstringen har ingen innslag påmeldt, kjør på!
		**/
		if( $pavirkede == 0 || ( $pavirkede > 0 && isset( $_GET['confirmed'] ) && $_GET['confirmed'] == true ) ) {
			try {
				$TWIGdata['rapport'] = write_wp_blog::avlys( $monstring );
				$TWIGdata['rapport']->viaConfirmation = isset( $_GET['confirmed'] );
				$VIEW = 'avlys-success';
			} catch( Exception $e ) {
				$VIEW = 'avlys-error';
				$TWIGdata['rapport'] = new stdClass();
				$TWIGdata['rapport']->error = $e->getMessage();
			}
		}
		/**
		 * Sjekk med brukeren - mønstringen har påmeldte!
		**/
		else {
			$VIEW = 'avlys-confirm';
		}
	}
} else {
	$TWIGdata['monstringer'] = stat_monstringer_v2::getAllBySesong( get_site_option('season') );
}