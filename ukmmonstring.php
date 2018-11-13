<?php  
/* 
Plugin Name: UKM M&oslash;nstring
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://www.ukm-norge.no
*/

/**
 * ALL HOOOKS FOR mønstring, mønstringer, network admin mønstringer
 */
if(is_admin()) {
	if( in_array( get_option('site_type'), array('kommune','fylke','land')) ) {
		add_action('UKM_admin_menu', 'UKMMonstring_menu');
	}
	if(get_option('site_type') == 'fylke') {
		add_action('UKM_admin_menu', 'UKMmonstringer_menu',100);
	}
	
	// Kun gjør dette dersom vi er i november, slutt ved nyttår
	add_filter('UKMWPDASH_messages', 'UKMmonstring_messages');
		
	add_action('network_admin_menu', 'UKMmonstring_network_menu');
	add_filter('UKMWPNETWDASH_messages', 'UKMmonstring_network_messages');
}

/**
 * UKM MØNSTRING
 * Alt om min mønstring (lokal, fylke, land)
 */

// MENY
function UKMMonstring_menu() {
	global $UKMN;
	$name = get_option('site_type') == 'fylke' ? 'Min m&oslash;nstring' : 'M&oslash;nstring';
	UKM_add_menu_page(
		'monstring', 
		$name,
		$name, 
		'editor', 
		'UKMMonstring', 
		'UKMMonstring', 
		'//ico.ukm.no/hus-menu.png'
		,1
	);
	
	if(get_option('site_type') == 'fylke' || get_option('site_type') == 'kommune') {
		UKM_add_submenu_page(
			'UKMMonstring', 
			"Din forside", 
			"Din forside", 
			'editor', 
			'UKMnettside_info', 
			'UKMnettside_info'
		);
	}
	
	UKM_add_scripts_and_styles( 'UKMMonstring', 'UKMMonstring_script' );
}

// SCRIPTS AND STYLES
function UKMMonstring_script() {
	wp_enqueue_media();
	
	wp_enqueue_script('UKMMonstring_script',  plugin_dir_url( __FILE__ )  . 'monstring.script.js' );
	wp_enqueue_style( 'UKMMonstring_style', plugin_dir_url( __FILE__ ) .'monstring.style.css');
	
	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
}

// STORAGE KEYS for mønstringsinfo i site_meta
function UKMMonstring_sitemeta_storage() {
	return [
		'avgift_subsidiert',
		'avgift_ordinar',
		'avgift_reise',
		'hotelldogn_pris',
		'kvote_ledere',
		'kvote_deltakere',
		'info1',
		'nominasjon_frister'
	];
}

// GUI MØNSTRING
function UKMMonstring() {
	require_once('UKM/innslag_typer.class.php');
    require_once('UKM/monstring.class.php');
    $monstring = new monstring_v2( get_option('pl_id') );
	$TWIGdata = [];

	if( !is_super_admin() && date('y') > 6 && (int)$monstring->getSesong() <= (int)date('Y') ) {
		echo TWIG('vent-til-ny-sesong.html.twig', $TWIGdata, dirname(__FILE__));
		return;
	} elseif( date('y') > 6 && (int)$monstring->getSesong() <= (int)date('Y') ) {
		$TWIGdata['melding'] = new stdClass();
		$TWIGdata['melding']->success = false;
		$TWIGdata['melding']->text = 
			'Redigering er kun mulig fordi du er logget inn som superadmin. '.
			'Vanlige brukere får ikke opp skjema, da de venter på at ny sesong skal settes opp.';
	}

	$CONTROLLER = isset( $_GET['kontakt'] ) ? 'kontakt' : 'monstring';	
	// Might modify $CONTROLLER
	require_once('controller/monstring.save.php');

	require_once('controller/'. $CONTROLLER .'.controller.php');
	echo TWIG( $CONTROLLER .'.html.twig', $TWIGdata, dirname(__FILE__));
}

// DASHBOARD MESSAGE Legg til varsel på forsiden dersom mønstringen ikke er registrert.
function UKMmonstring_messages( $MESSAGES ) {
	$pl_id = get_option('pl_id');
	if( !is_numeric( $pl_id ) ) {
		return $MESSAGES;
	}

	$monstring = new monstring_v2( $pl_id );

	if(!$monstring->erRegistrert()) {
		$MESSAGES[] = array('level' 	=> 'alert-error',
							'header' 	=> 'Du må registrere mønstringen din',
							'link' 		=> 'admin.php?page=UKMMonstring',
							'body' 		=> 'Velg "Mønstring" i menyen til venstre for å legge til informasjonen som mangler'
							);
	}

	return $MESSAGES;
}




/**
 * MØNSTRINGER
 * Fylkenes oversikt over sine lokalmønstringer
 */
function UKMmonstringer_menu() {
	UKM_add_menu_page('resources','Lokal-mønstringer', 'Lokal-mønstringer', 'editor', 'UKMmonstringer', 'UKMmonstringer', '//ico.ukm.no/mapmarker-bubble-blue-menu.png',20);
	UKM_add_scripts_and_styles('UKMmonstringer', 'UKMmonstringer_script' );
}

// DASHBOARD MESSAGE for fylkeskontakten. Oversikt antall uregistrerte
function UKMmonstringer_dash( $MESSAGES ) {
	if( get_option('site_type') != 'fylke' ) {
		return $MESSAGES;
	}

	$unregistered = 0;
	
	if( ((int) date('Y') == (int) (get_option('season')-1)) && ((int) date('m') > 10 ) ) {
		$monstring = new monstring( get_option('pl_id') );
		
		$monstringer = $monstring->hent_lokalmonstringer();
		$monstringer = array_unique( $monstringer );
		
		foreach( $monstringer as $plid ) {
			$pl = new monstring( $plid );
			if( !$pl->registered() && $pl->g('pl_name') != 'Gjester' )
				$unregistered++;
		}	
	}
	
	if($unregistered > 0)
		$MESSAGES[] = array(
			'link'		=> '?page=UKMmonstringer',
			'level' 	=> 'alert-error',
			'header' 	=> $unregistered . ' av dine lokalmønstringer er ikke registrert!',
			'body' 	=> 'Velg "lokalmønstringer" i menyen til venstre for å se hvilke'
		);
	elseif($is_showtime) {
		$MESSAGES[] = array('level' 	=> 'alert-success',
							'header' 	=> 'Alle dine lokalmønstringer registrert!',
							'body' 	=> 'Det liker vi!'
							);
	}
	return $MESSAGES;
}

// GUI MØNSTRINGER i fylket
function UKMmonstringer() {
	require_once('UKM/monstringer.class.php');
	$TWIGdata = [];
	$monstring = new monstring_v2( get_option('pl_id') );
	$monstringer = stat_monstringer_v2::getAllByFylke( $monstring->getFylke(), get_option('season') );
	
	$emails = '';
	foreach( $monstringer as $lokalmonstring ) {
		foreach( $lokalmonstring->getKontaktpersoner()->getAll() as $kontakt ) {
			$epost = $kontakt->getEpost();
			if( !empty( $epost ) ) {
				$emails .= $epost .';';
			}
		}
	}
	
	$TWIGdata['monstring'] = $monstring;
	$TWIGdata['lokalmonstringer'] = $monstringer;
	$TWIGdata['mailtoall'] = $emails;
	echo TWIG('monstringer.html.twig', $TWIGdata , dirname(__FILE__));
}

// SCRIPTS AND STYLES
function UKMmonstringer_script() {
	UKMmonstring_network_script();
	wp_enqueue_script('UKMMonstring_script',  plugin_dir_url( __FILE__ )  . 'monstring.script.js' );
	wp_enqueue_style( 'UKMMonstring_style', plugin_dir_url( __FILE__ ) .'monstring.style.css');
}




/**
 *	NETWORK ADMIN FUNCTIONS
**/
function UKMmonstring_network_admin_ny_sesong() {
	return UKMmonstring_network_admin( 'ny_sesong' );
}

function UKMmonstring_network_admin_rome_opprett() {
	$_GET['action'] = 'opprett';
	return UKMmonstring_network_admin( 'romerriket' );
}
function UKMmonstring_network_admin_rome_avlys() {
	$_GET['action'] = 'avlys';
	return UKMmonstring_network_admin( 'romerriket' );
}
function UKMmonstring_network_admin_rome_legg_til() {
	$_GET['action'] = 'legg_til';
	return UKMmonstring_network_admin( 'romerriket' );
}
function UKMmonstring_network_admin_rome_trekk_ut() {	
	$_GET['action'] = 'trekk_ut';
	return UKMmonstring_network_admin( 'romerriket' );
}

function UKMmonstring_network_admin_undersider() {
	$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : 'undersider';
	return UKMmonstring_network_admin('wordpress');
}

function UKMmonstring_network_admin( $page='' ) {
	define('PLUGIN_DIR', dirname( __FILE__ ).'/' );
	$TWIGdata = ['UKM_HOSTNAME' => UKM_HOSTNAME];

	$folder = '/network/'. ( !empty( $page ) ? basename($page) .'/' : '' );

	$action = isset($_GET['action']) ? $_GET['action'] : 'home';
	$VIEW = $action;
	require_once('controller/'. $folder . $action .'.controller.php');

	echo TWIG( $folder . $VIEW .'.html.twig', $TWIGdata, dirname(__FILE__), true);
}

function UKMmonstring_network_messages( $messages ) {
	// Etter juli må ny sesong settes opp
	if( 7 < (int)date('m') && get_site_option('season') == date('Y') ) {
		$messages[] = array(
			'level' 	=> 'alert-danger',
			'module'	=> 'System',
			'header'	=> 'NY SESONG MÅ SETTES OPP!',
			'link'		=> 'admin.php?page=UKMsystemtools_ny_sesong'
		);
	}
	
	return $messages;
}

function UKMmonstring_network_menu() {
	$page = add_menu_page('Mønstringer', 'Mønstringer', 'superadmin', 'UKMmonstring_network_admin','UKMmonstring_network_admin', '//ico.ukm.no/hus-menu.png',23);
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Opprett mønstring', 'Opprett mønstring', 'superadmin', 'UKMmonstring_network_admin_rome_opprett', 'UKMmonstring_network_admin_rome_opprett' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Avlys mønstring', 'Avlys mønstring', 'superadmin', 'UKMmonstring_network_admin_rome_avlys', 'UKMmonstring_network_admin_rome_avlys' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Legg til kommune', 'Legg til kommune', 'superadmin', 'UKMmonstring_network_admin_rome_legg_til', 'UKMmonstring_network_admin_rome_legg_til' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Trekk ut kommune', 'Trekk ut kommune', 'superadmin', 'UKMmonstring_network_admin_rome_trekk_ut', 'UKMmonstring_network_admin_rome_trekk_ut' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Opprett sesong', 'Opprett sesong', 'superadmin', 'UKMmonstring_network_admin_ny_sesong', 'UKMmonstring_network_admin_ny_sesong' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Kontroller undersider', 'Kontroller undersider', 'superadmin', 'UKMmonstring_network_admin_undersider', 'UKMmonstring_network_admin_undersider' );


	add_action( 'admin_print_styles-' . $page, 	'UKMmonstring_network_script' );
	foreach( $subpages as $page ) {
		add_action( 'admin_print_styles-' . $page, 'UKMmonstring_network_script' );
	}
}

function UKMmonstring_network_script() {
	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
}