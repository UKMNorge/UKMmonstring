<?php
error_reporting(E_ALL ^ E_DEPRECATED);
require_once( PLUGIN_DIR . 'functions/network/ny_sesong/ukmdb.functions.php');
require_once('UKM/write_wp_user.class.php');
require_once('UKM/write_wp_blog.class.php');
require_once('UKM/monstringer.class.php');
require_once('UKM/inc/password.inc.php');

$fylkesbrukere		 	= write_wp_blog::getFylkesbrukere();
$seasons 				= getSeasonsData();
$rapporter				= [];
$TWIGdata['seasons']	= $seasons;

/**
 * Vi har et intervall, oppdater/opprett blogger
**/
if( isset( $_GET['start'] ) && is_numeric( $_GET['start'] ) ) {

	/**
	 *
	 * Pass på at vi er på riktig sted i loopen
	 *
	**/
	$loop 					= new stdClass();
	$loop->count			= 0;
	$loop->blogger_per_load = 40;
	$loop->start			= abs( $_GET['start'] );
	$loop->stop				= $loop->start + $loop->blogger_per_load;
	$loop->next 			= new stdClass();
	$loop->next->start		= $loop->stop + 1; // testet at +1 er riktig!
	$loop->next->stop		= $loop->next->start + $loop->blogger_per_load;

	if( $loop->start > 0 ) {
		echo 'Hopper over: ';
	}
	// Loop alle mønstringer
	foreach( stat_monstringer_v2::getAllBySesong( $seasons->new->year ) as $monstring ) {
		$loop->count++;
		# Hopp over de vi har tatt
		if( $loop->count < $loop->start ) {
			echo $loop->count .', ';
			continue;
		}
		# Stopp for å unngå timeout
		if( $loop->count > $loop->stop ) {
			break;
		}
		
		/**
		 *
		 * OPPDATER BLOGGEN
		 *
		**/
		$rapport = new stdClass();
		$rapport->success	= false;
		$rapport->monstring = $monstring;

		$path = write_wp_blog::getPathFromMonstring( $monstring );;

		// Blogg finnes ikke, opprett
		if( !write_wp_blog::eksisterer( $path ) ) {
			$rapport->eksisterer = false;
			try {
				$blog_id = write_wp_blog::create( $monstring );
				$rapport->success = true;
				$rapport->blog_id = $blog_id;
			} catch( Exception $e ) {
				$rapport->error = 'Kunne ikke opprette blogg: '. $e->getMessage();
			}
		} 
		// Blogg finnes 
		else {
			$rapport->eksisterer = true;
			$blog_id = write_wp_blog::getIdByPath( $path );
			$rapport->blog_id = $blog_id;
			try {
				write_wp_blog::updateBlogFromMonstring( $monstring );
				$rapport->success = true;
			} catch( Exception $e ) {
				$rapport->error = 'Kunne ikke oppdatere blogg: '. $e->getMessage();
			}
		}
		
		$rapport->brukere = write_wp_blog::leggTilFylkesbrukereTilBlogg( $monstring, $blog_id );
		
		/**
		 * 
		 * LOKALMØNSTRINGER
		 *
		**/
		if( $monstring->getType() == 'kommune' ) {
			$kommuner_brukere = [];
			foreach( $monstring->getKommuner()->getAll() as $kommune ) {
				$kommuner_brukere[] = write_wp_blog::leggTilKommunebrukerTilBlogg( $kommune, $blog_id, 'userObjects' );
			}

			// Oppdater listen over alle brukere
			// Hvis fylkesbrukere ikke er lagt til (har noen ganger skjedd)
			// brukes kommune-brukere alene
			if( is_array( $kommuner_brukere ) && is_array( $rapport->brukere ) ) {
				$rapport->brukere = array_merge( $rapport->brukere, $kommuner_brukere );
			} elseif( is_array( $kommuner_brukere ) ) {
				$rapport->brukere = $kommuner_brukere;
			}
			
			/**
			 * 
			 * Sett nye passord på alle kommunebrukere
			 *
			**/
			foreach( $kommuner_brukere as $brukerRapport ) {
				$bruker = $brukerRapport['object'];
				$bruker->setPassord( UKM_ordpass() );
				$kommuner_brukernavn[ $bruker->getNavn() ] = $bruker->getEpost();
			}
		}
		
		/**
		 * 
		 * FYLKESMØNSTRINGER
		 *
		**/
		if( $monstring->getType() == 'fylke' ) {
			// Noen fylker skal vi ikke jobbe så mye med (jukse-fylker brukt av systemet)
			try {
				$fylke_urlname = $monstring->getFylke()->getURLsafe();
				if( !isset( $fylkesbrukere[ $fylke_urlname ] ) ) {
					$fylke_urlname = false;
				}
			} catch( Exception $e ) {
				$fylke_urlname = false;
			}

			if( $fylke_urlname ) {
				foreach( $fylkesbrukere[ $fylke_urlname ] as $username => $email ) {
					$bruker = wp_UKM_user::getWPUser( $username, 'username' );
					if( $bruker !== false && $bruker->exists() ) {
						update_user_meta( $bruker->ID, 'primary_blog', $blog_id);
						$rapport->brukere[] = array(
							'success'	=> true,
							'rolle'		=> $blog_id,
							'brukernavn'=> 'Primary_blog: '. $username,
						);
					} else {
						$rapport->brukere[] = array(
							'success'	=> false,
							'error'		=> $blog_id,
							'brukernavn'=> 'Primary_blog: '. $username,
						);
					}
				}
				
			}
		}
		
		/**
		 * Oppsummering om bloggen
		**/
		$rapporter[] = $rapport;
	}

	




	$TWIGdata['the_loop']	= $loop;
	$TWIGdata['rapport']	= $rapporter;
	// TODO: LOOP ALLE SIDER OG DEAKTIVER / FIX DE SOM IKKE ER I AKTIV SESONG
} else {
	$VIEW = 'wp_blogs-confirm';
}