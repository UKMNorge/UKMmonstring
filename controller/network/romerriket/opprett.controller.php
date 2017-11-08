<?php
error_reporting(E_ALL ^ E_DEPRECATED);
require_once( PLUGIN_DIR . 'functions/network/ny_sesong/ukmdb.functions.php');
require_once('UKM/monstringer.class.php');
require_once('UKM/write_monstring.class.php');
require_once('UKM/write_wp_blog.class.php');

$season = (int) get_site_option('season');
$TWIGdata['season'] = $season;

if( isset( $_GET['create'] ) && is_numeric( $_GET['create'] ) ) {
	$VIEW = 'opprett-success';
	$rapport = new stdClass();

	try {
		// Initer UKMlogger
		UKMlogger::setID( 'wordpress', get_current_user_id(), 0);

		// Forbered data
		$kommune = new kommune( $_GET['create'] );
		$datoer = new stdClass();
		$datoer->frist = getSeasonsDataFristLokal( $season );
	
		/**
		 *
		 * MØNSTRING: OPPRETT OG LAGRE I DATABASE
		 *
		**/
		$monstring = write_monstring::create(
								'kommune',				// type
								$season,				// sesong
								$kommune->getNavn(),	// navn
								$datoer,				// datoer
								array( $kommune )		// geografi
							);
		$rapport->monstring = $monstring;
		// Dobbeltsjekk at vi har gyldig mønstringsobjekt
		if( !is_object( $monstring ) || !is_numeric( $monstring->getId() ) ) {
			throw new Exception( 'Opprett mønstring ga ikke et mønstrings-objekt med ID tilbake. Ukjent feil.' );
		} 

		/**
		 *
		 * BLOGG: OPPRETT / OPPDATER
		 *
		**/
		$path = write_wp_blog::getPathFromMonstring( $monstring );
		$rapport->path = $path;
		// Blogg finnes ikke, opprett
		if( !write_wp_blog::eksisterer( $path ) ) {
			$rapport->eksisterer = false;
			try {
				$rapport->blog_id = write_wp_blog::create( $monstring );
			} catch( Exception $e ) {
				throw new Exception(
					'OBS: DATABASE-ENDRING OBLIGATORISK. VARLE SYSTEM-ADMIN! IKKE PRØV IGJEN!!' ."\r\n".
					'Mønstringen er opprettet, men systemet klarte ikke å opprette en blogg.' ."\r\n".
					$e->getMessage()
				);
			}
		} 
		// Blogg finnes 
		else {
			$rapport->eksisterer = true;
			$rapport->blog_id = write_wp_blog::getIdByPath( $path );
			try {
				write_wp_blog::updateBlogFromMonstring( $monstring );
			} catch( Exception $e ) {
				throw new Exception(
					'OBS: DATABASE-ENDRING OBLIGATORISK. VARSLE SYSTEM-ADMIN! IKKE PRØV IGJEN!!'. "\r\n".
					$e->getMessage()
				);
			}
		}
		
		/**
		 * 
		 * BRUKERE: Legg til
		 *
		**/
		$rapport->brukere = write_wp_blog::leggTilFylkesbrukereTilBlogg( $monstring, $rapport->blog_id );
		foreach( $monstring->getKommuner()->getAll() as $kommune ) {
			$rapport->brukere[] = write_wp_blog::leggTilKommunebrukerTilBlogg( $kommune, $rapport->blog_id, 'userObjects' );
		}
		
	} catch( Exception $e ) {
		$VIEW = 'opprett-error';
		$TWIGdata['error'] = $e->getMessage();
	}
	$TWIGdata['rapport'] = $rapport;
} else {
	$TWIGdata['kommuner'] = monstringer_v2::getAlleKommunerUtenMonstring( get_site_option('season') );	
}