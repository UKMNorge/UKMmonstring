<?php
error_reporting(E_ALL ^ E_DEPRECATED);
require_once( PLUGIN_DIR . 'functions/network/ny_sesong/ukmdb.functions.php');
require_once('UKM/monstringer.class.php');
require_once('UKM/write_monstring.class.php');
require_once('UKM/write_wp_blog.class.php');

/**
 * TREKK UT EN KOMMUNE FRA GITT MØNSTRING
**/
if( isset( $_GET['pl_id'] ) && isset( $_GET['k_id'] ) ) {

	$monstring = new write_monstring( $_GET['pl_id'] );	
	$kommune = new kommune( $_GET['k_id'] );
	$rapport = new stdClass();
	
	// Kalkuler innslag som mister mønstringen sin
	try {
		$pavirkede = sizeof( $monstring->getInnslag()->getAllByKommune( $kommune ) );
		$TWIGdata['pavirkede'] = $pavirkede;
		$init = true;
	} catch( Exception $e ) {
		$init = false;
		$VIEW = 'trekk_ut-error';
		$rapport = array(
			'error' => 'Mønstringen ser ut til å være helt/delvis avlyst ('. $e->getMessage() .' )'
		);
	}

	if( $init ) {
		try {
			$rapport->from	= new stdClass();
			$rapport->temp	= new stdClass();
			$rapport->to 	= new stdClass();
			
			/**
			 * Beregn paths og sjekk at disse er gyldige
			**/
			$rapport->from->path = write_wp_blog::getPathFromMonstring( $monstring );
			$rapport->temp->path = '/temp-'. substr( $rapport->from->path, 1);
			
			write_wp_blog::controlPath( $rapport->from->path, 'From-path' );
			write_wp_blog::controlPath( $rapport->from->path, 'Temp-path' );
			
			/**
			 * Sjekk om fra- og temp-blogg eksisterer
			**/
			// FROM
			$rapport->from->eksisterer = write_wp_blog::eksisterer( $rapport->from->path );
			if( $rapport->from->eksisterer ) {
				$rapport->from->blog_id = write_wp_blog::getIdByPath( $rapport->from->path );
			}
			// TEMP
			$rapport->temp->eksisterer = write_wp_blog::eksisterer( $rapport->temp->path );
			if( $rapport->temp->eksisterer ) {
				$rapport->temp->blog_id = write_wp_blog::getIdByPath( $rapport->temp->path );
			} 
			
			if( !$rapport->from->eksisterer ) {
				throw new Exception(
					'Kan ikke trekke ut kommunen, da bloggen for mønstring ikke finnes ' .
					'('. $rapport->from->path.' eksisterer ikke). '.
					'Oppdater mønstringsobjektet (i databasen) til å stemme med riktig path for bloggen, '. 
					'og prøv igjen'
				);
			}
			
			/**
			 * Beregn navn og URL for ny mønstring
			**/
			$rapport->to->navn = '';
			$rapport->to->path = '/';
			$loop = new stdClass();
			$loop->total = $monstring->getKommuner()->getAntall();
			$loop->count = 0;
			$loop->kommuner_ny = [];
			
			foreach( $monstring->getKommuner()->getAll() as $loop_kommune ) {
				$loop->count++;
					
				if( $loop_kommune->getId() == $kommune->getId() ) {
					continue;
				}
			
				// Første kommune i løkka
				if( empty( $rapport->to->navn ) ) {
					$rapport->to->navn .= $loop_kommune->getNavn();
				}
				// Siste kommune i løkka
				elseif( $loop->count == $loop->total ) {
					$rapport->to->navn .= ' og '. $loop_kommune->getNavn();
				}
				// Øvrige kommuner i løkka
				else {
					$rapport->to->navn .= ', '. $loop_kommune->getNavn();
				}
				$loop->kommuner_ny[] = $loop_kommune;
			}
			$rapport->to->path = 
				'/'.
				write_monstring::generatePath(
					$monstring->getType(),
					$loop->kommuner_ny,
					$monstring->getSesong(),
					true
				).
				'/'
			;
			write_wp_blog::controlPath( $rapport->to->path, 'To-path' );
			
			// TEMP
			$rapport->to->eksisterer = write_wp_blog::eksisterer( $rapport->to->path );
			if( $rapport->to->eksisterer ) {
				$rapport->to->blog_id = write_wp_blog::getIdByPath( $rapport->to->path );
				$rapport->to->delPath = '/'. $rapport->to->blog_id .'-'. trim( $rapport->to->path, '/' ) .'/';
			} 

			$rapport->success = true;
		} catch( Exception $e ) {
			$rapport->success = false;
			$rapport->error = $e->getMessage();
		}
		
		/**
		 * Kjør på - trekk ut kommunen (NO WAY BACK)
		 *
		 * Brukeren har godkjent 
		 * - at eventuelle deltakere blir mønstringsløse
		 * - endringene som skal gjennomføres
		**/
		if( $rapport->success != false && isset( $_GET['confirmed'] ) && $_GET['confirmed'] == true ) {
			// Initer UKMlogger
			UKMlogger::setID( 'wordpress', get_current_user_id(), $monstring->getId() );

			try {
				// Opprett temp-bloggen hvis den ikke eksisterer (mest sannsynlig) 
				// (create oppdaterer den med mønstringsobjektet)
				if( !$rapport->temp->eksisterer ) {
					$rapport->temp->blog_id = write_wp_blog::create( $monstring, $rapport->temp->path );
				}
				// Oppdater eksisterende temp-blogg så den stemmer med mønstringsobjektet kommunen forlater
				else {
					write_wp_blog::updateBlogFromMonstring( $monstring, $rapport->temp->path );
				}
				// Merk temp-bloggen som splittet
				$rapport->splitt = write_wp_blog::splitt( $rapport->temp->blog_id, $monstring );
			
				// Fjern kommune-brukeren fra fra-bloggen (fellesmønstringen)
				$rapport->from->bruker = write_wp_blog::fjernKommuneBrukerFraBlogg( $kommune, $rapport->from->blog_id );
				
				// Fjern kommunen fra mønstringen
				$monstring->fjernKommune( $kommune );
				
				// Oppdater mønstringens navn og path
				$monstring->setNavn( $rapport->to->navn );
				$monstring->setPath( $rapport->to->path );
				$monstring->save();
				// Oppdater fra-bloggen (fellesmønstringen) så den stemmer med mønstringsobjektet kommunen forlater
				write_wp_blog::updateBlogFromMonstring( $monstring, $rapport->from->path );
		
				// Slett bloggen som i dag ligger på to-path
				if( $rapport->to->eksisterer ) {
					write_wp_blog::moveBlog( $rapport->to->path, $rapport->to->delPath );
					//write_wp_blog::deleteBlog( $blog_id, $rapport->to->delPath );
				}
				// Flytt dagens blogg til ny URL, og temp-blogg til dagens URL
				$rapport->flipp = write_wp_blog::flippBlogger( $rapport->from->path, $rapport->to->path, $rapport->temp->path );
				$VIEW = 'trekk_ut-success';
			} catch( Exception $e ) {
				$rapport->success = false;
				$rapport->error = $e->getMessage();
				$VIEW = 'trekk_ut-error';
			}
		} elseif( !$rapport->success ) {
			$VIEW = 'trekk_ut-error';
		}
		// Vi har alt vi trenger, unntatt en bekreftelse fra brukeren
		else {
			$VIEW = 'trekk_ut-confirm';
		}
	}
	/**
	 * Mønstringen ser ut til å være helt/delvis avlyst 
	**/
	else {
		$VIEW = 'trekk_ut-confirm';

	}
	$TWIGdata['kommune'] = $kommune;
	$TWIGdata['monstring'] = $monstring;
	$TWIGdata['rapport'] = $rapport;
}
/**
 * VIS LISTE OVER FELLESMØNSTRINGER
**/
else {
	$TWIGdata['monstringer'] = stat_monstringer_v2::getAllBySesong( get_site_option('season') );
}