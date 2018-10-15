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
	$kommune = new kommune( $_GET['k_id'] );
	$monstring = new monstring_v2( $_GET['pl_id'] );	
	$rapport = new stdClass();

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
				'Kan ikke legge til kommunen, da bloggen for mønstring ikke finnes ' .
				'('. $rapport->from->path.' eksisterer ikke). '.
				'Opprett bloggen og prøv igjen '
			);
		}
		
		/**
		 * "Legg til" kommune og sorter kommune-array
		**/
		// Sorter kommuner
		$kommuner_ny = $monstring->getKommuner()->getAll();
		$kommuner_ny[] = $kommune;
		
		$kommuner_alpha = [];
		foreach( $kommuner_ny as $alphaloop_kommune ) {
			$kommuner_alpha[ $alphaloop_kommune->getNavn() ] = $alphaloop_kommune;
		}
		ksort( $kommuner_alpha );
		
		/**
		 * Beregn navn og URL for ny mønstring
		**/
		$rapport->to->navn = '';
		$rapport->to->path = '/';
		$loop = new stdClass();
		$loop->total = sizeof( $kommuner_alpha );
		$loop->count = 0;
		$loop->kommuner_ny = [];
		foreach( $kommuner_alpha as $loop_kommune ) {
			$loop->count++;
				
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

		$rapport->to->eksisterer = write_wp_blog::eksisterer( $rapport->to->path );
		if( $rapport->to->eksisterer ) {
			$rapport->to->blog_id = write_wp_blog::getIdByPath( $rapport->to->path );
			$rapport->to->delPath = '/'. $rapport->to->blog_id .'-'. trim( $rapport->to->path, '/' ) .'/';
		} 
		
		if( isset( $_GET['confirmed'] ) && $_GET['confirmed'] == 'true' ) {
			// Initer UKMlogger
			UKMlogger::setID( 'wordpress', get_current_user_id(), $monstring->getId() );

			try {
				// Opprett temp-bloggen hvis den ikke eksisterer (mest sannsynlig) 
				// (create oppdaterer den med mønstringsobjektet)
				if( !$rapport->temp->eksisterer ) {
					$rapport->temp->blog_id = write_wp_blog::create( $monstring, $rapport->temp->path );
				}
				// Oppdater eksisterende temp-blogg så den stemmer med mønstringsobjektet før kommunen legges til
				else {
					write_wp_blog::updateBlogFromMonstring( $monstring, $rapport->temp->path );
				}
				// Merk temp-bloggen som splittet
				$rapport->flytt = write_wp_blog::flytt( $rapport->temp->blog_id, $monstring );
			
				// Fjern kommune-brukeren fra fra-bloggen (fellesmønstringen)
				$rapport->from->bruker = write_wp_blog::leggTilKommunebrukerTilBlogg( $kommune, $rapport->from->blog_id );
				
				// Fjern kommunen fra mønstringen
				$monstring->getKommuner()->leggTil( $kommune );
				
				// Oppdater mønstringens navn og path
				$monstring->setNavn( $rapport->to->navn );
				$monstring->setPath( $rapport->to->path );
				write_monstring::save( $monstring );
				// Oppdater fra-bloggen (fellesmønstringen) så den stemmer med mønstringsobjektet kommunen forlater
				write_wp_blog::updateBlogFromMonstring( $monstring, $rapport->from->path );

				// Slett bloggen som i dag ligger på to-path
				if( $rapport->to->eksisterer ) {
					write_wp_blog::moveBlog( $rapport->to->path, $rapport->to->delPath );
					//write_wp_blog::deleteBlog( $blog_id, $rapport->to->delPath );
				}
				// Flytt dagens blogg til ny URL, og temp-blogg til dagens URL
				$rapport->flipp = write_wp_blog::flippBlogger( $rapport->from->path, $rapport->to->path, $rapport->temp->path );
				$VIEW = 'legg_til-success';
			} catch( Exception $e ) {
				$rapport->success = false;
				$rapport->error = $e->getMessage();
				$VIEW = 'legg_til-error';
			}
		} else {
			$VIEW = 'legg_til-confirm';
			$rapport->success = true;
		}
	} catch( Exception $e ) {
		$VIEW = 'legg_til-error';
		$rapport->success = false;
		$rapport->error = $e->getMessage();
	}

	
	$TWIGdata['kommune'] = $kommune;	
	$TWIGdata['monstring'] = $monstring;
	$TWIGdata['rapport'] = $rapport;
}
/**
 * VIS LISTE OVER FELLESMØNSTRINGER
**/
elseif( isset( $_GET['k_id'] ) ) {
	$kommune = new kommune( $_GET['k_id'] );
	$TWIGdata['kommune'] = $kommune;
	$TWIGdata['monstringer'] = stat_monstringer_v2::getAllBySesong( get_site_option('season') );
}
/**
 * VIS LISTE OVER KOMMUNER UTEN MØNSTRING
**/
else {
	$TWIGdata['kommuner'] = monstringer_v2::getAlleKommunerUtenMonstring( get_site_option('season') );	
}