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
        add_action('admin_menu', 'UKMMonstring_menu');
        add_action('wp_ajax_UKMmonstring_save_kontaktpersoner', 'UKMmonstring_save_kontaktpersoner');
	}
	
	// Kun gjør dette dersom vi er i november, slutt ved nyttår
	add_filter('UKMWPDASH_messages', 'UKMmonstring_messages');
		
	add_action('network_admin_menu', 'UKMmonstring_network_menu');
}

/**
 * UKM MØNSTRING
 * Alt om min mønstring (lokal, fylke, land)
 */

// MENY
function UKMMonstring_menu() {
	$page = add_menu_page(
		'Arrangmentet',
		'Arrangementet', 
		'editor', 
		'UKMmonstring', 
		'UKMMonstring', 
		'dashicons-buddicons-groups',#'//ico.ukm.no/settings-menu.png',
		4
	);
	add_action( 'admin_print_styles-' . $page, 'UKMMonstring_script' );
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

	if( !is_super_admin() && date('m') > 6 && (int)$monstring->getSesong() <= (int)date('Y') ) {
		echo TWIG('vent-til-ny-sesong.html.twig', $TWIGdata, dirname(__FILE__));
		return;
	} elseif( date('m') > 6 && (int)$monstring->getSesong() <= (int)date('Y') ) {
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

function UKMmonstring_save_kontaktpersoner() {
    require_once('ajax/order.save.php');
	die();
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


/**
 *	NETWORK ADMIN FUNCTIONS
**/
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


function UKMmonstring_network_menu() {
	$page = add_menu_page(
		'Mønstringer',
		'Mønstringer',
		'superadmin',
		'UKMmonstring_network_admin',
		'UKMmonstring_network_admin',
		'dashicons-buddicons-groups',#//ico.ukm.no/hus-menu.png',
		23
	);
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Opprett mønstring', 'Opprett mønstring', 'superadmin', 'UKMmonstring_network_admin_rome_opprett', 'UKMmonstring_network_admin_rome_opprett' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Avlys mønstring', 'Avlys mønstring', 'superadmin', 'UKMmonstring_network_admin_rome_avlys', 'UKMmonstring_network_admin_rome_avlys' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Legg til kommune', 'Legg til kommune', 'superadmin', 'UKMmonstring_network_admin_rome_legg_til', 'UKMmonstring_network_admin_rome_legg_til' );
	$subpages[] = add_submenu_page( 'UKMmonstring_network_admin', 'Trekk ut kommune', 'Trekk ut kommune', 'superadmin', 'UKMmonstring_network_admin_rome_trekk_ut', 'UKMmonstring_network_admin_rome_trekk_ut' );
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