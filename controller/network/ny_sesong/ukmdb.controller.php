<?php
/**
 * NY UKM-SESONG
 * OBS: Scriptet krever at du gjør dette FØR ny sesong har startet.
 *      Typisk skal denne prosessen kjøres i august, og kan kreve
 *      tilpasninger hvis den skal fungere senere.
**/
error_reporting(E_ALL ^ E_DEPRECATED);
require_once( PLUGIN_DIR . 'functions/network/ny_sesong/ukmdb.functions.php');
require_once('UKM/logger.class.php');
require_once('UKM/monstringer.class.php');
require_once('UKM/write_monstring.class.php');

/**
 *
 * HJELPEVARIABEL $seasons
 *
 * $seasons->startMonth			INT Måned påmeldingen starter
 *
 * $seasons->active->year		INT		 Aktiv sesong: år
 * $seasons->active->start		DATETIME Aktiv sesong: første dag i sesongen (00:00:00)
 * $seasons->active->stop		DATETIME Aktiv sesong: siste dag i sesongen (23:59:59)
 *
 * $seasons->new->year			INT		 Ny sesong: år
 * $seasons->new->frist->lokal	DATETIME Ny sesong: standard-dato frist lokalmønstring
 * $seasons->new->frist->fylke	DATETIME Ny sesong: standard-dato frist fylkesmønstring
 * 
**/ 
$seasons = getSeasonsData();
$TWIGdata['seasons'] 		= $seasons;
$TWIGdata['monstringer'] 	= stat_monstringer_v2::getAllBySesong( $seasons->active->year );

/**
 * START PROSESSEN (STILL A WAY BACK)
 * elseif starter genereringen
 * else viser en info-side
**/
if( isset($_GET['init']) && $_GET['init'] == 'start' ){
	$VIEW = 'ukmdb-confirm';
}
/**
 * KJØR PROSESSEN (NO WAY BACK)
**/
elseif( isset($_GET['init']) && $_GET['init'] == 'do' ) {
	// Initer UKMlogger
	UKMlogger::setID( 'wordpress', get_current_user_id(), 0);
	
	echo TWIG( 'network/ny_sesong/ukmdb-do-start.html.twig', $TWIGdata, PLUGIN_DIR, true);

	foreach( $TWIGdata['monstringer'] as $monstring_active ) {
		$renderData = [
						'success' => false,
						'monstring' => $monstring_active,
					];
		$datoer 	= new stdClass();
		$geografi 	= new stdClass();
		switch( $monstring_active->getType() ) {
			case 'kommune':
				$datoer->frist 	= $seasons->new->frist->lokal;
				$geografi		= $monstring_active->getKommuner()->getAll();
				if( !is_array( $geografi ) ) {
					$renderData['success'] = false;
					$renderData['error'] = 'mangler_kommune';
					echo TWIG( 'network/ny_sesong/ukmdb-do-status.html.twig', $renderData, PLUGIN_DIR, true);
			break;

					continue;
				}
				break;
			case 'fylke':
				$datoer->frist 		= $seasons->new->frist->fylke;
				$geografi			= $monstring_active->getFylke();
				break;
			case 'land':
				$datoer->frist		= $seasons->new->frist->fylke;
				break;
		}

		$monstring_new = write_monstring::create(
							$monstring_active->getType(),	// type
							$seasons->new->year,			// sesong
							$monstring_active->getNavn(),	// navn
							$datoer,						// datoer
							$geografi						// geografi
						);

		foreach( $monstring_active->getKontaktpersoner()->getAll() as $kontakt ) {
			$monstring_new->addKontaktperson( $kontakt );
		}
		
		/**
		 * Sett hvilket skjema som skal brukes for videresending.
		 * Gjelder kun fylke da UKM-festivalen bruker hardkodet skjema
		**/
		if( $monstring_new->getType() == 'fylke' ) {
			$monstring_new->setSkjema( $monstring_active->getSkjema() );
		}
		$monstring_new->setSted( $monstring_active->getSted() );
		$monstring_new->save();
		
		/**
		 * Lagre relasjon mellom gammel og ny mønstring
		 * Brukes nok ikke, men lagres likevel
		 * For old times sake, eh?
		**/
		$qry = new SQLins('smartukm_rel_pl_pl');
		$qry->add('pl_old', $monstring_active->getId());
		$qry->add('pl_new', $monstring_new->getId());
		$qry->add('season', $seasons->new->year);
		#echo $qry->debug();
		$qry->run();

		$renderData['success'] = true;
		echo TWIG( 'network/ny_sesong/ukmdb-do-status.html.twig', $renderData, PLUGIN_DIR, true);
	}
	$VIEW = 'ukmdb-do-stop';
}
/**
 * VIS INFO OM PROSESSEN
 *
 * Vises hvis $_GET['init'] ikke er satt.
 * Praktisk kun for å forhindre tilfeldig start av oppsett
 *
**/
else {
	$VIEW = 'ukmdb-start';
}