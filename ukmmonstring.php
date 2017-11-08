<?php  
/* 
Plugin Name: UKM M&oslash;nstring
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://www.ukm-norge.no
*/
//require_once('UKM/inc/ukmlog.inc.php');

require_once('UKM/monstring.class.php');
## HOOK MENU AND SCRIPTS
if(is_admin()) {
	global $blog_id;
	if( in_array( get_option('site_type'), array('kommune','fylke','land')) ) {
		add_action('UKM_admin_menu', 'UKMMonstring_menu');
		add_action('UKMWPDASH_shortcuts', 'UKMMonstring_dash_shortcut', 10);
	}

	// Kun gjør dette dersom vi er i november, slutt ved nyttår
	if( 'kommune' == get_option('site_type') && ( (int)date('m') > 10 ) ) {
		add_filter('UKMWPDASH_messages', 'UKMmonstring_messages');
	}

	add_action('wp_ajax_UKMmonstring_save_kontaktpersoner', 'UKMmonstring_save_kontaktpersoner');
	
	add_action('network_admin_menu', 'UKMmonstring_network_menu');
	add_filter('UKMWPNETWDASH_messages', 'UKMmonstring_network_messages');
}

function UKMmonstring_dash_shortcut( $shortcuts ) {	
	$shortcut = new stdClass();
	$shortcut->url = 'admin.php?page=UKMMonstring';
	$shortcut->title = get_option('site_type') == 'fylke' ? 'Min m&oslash;nstring' : 'M&oslash;nstring';
	$shortcut->icon = '//ico.ukm.no/hus-menu.png';
	$shortcuts[] = $shortcut;
	
	return $shortcuts;
}

## Legg til varsel på forsiden dersom mønstringen mangler noe data.
function UKMmonstring_messages( $MESSAGES ) {
	$monstring = new monstring( get_option('pl_id') );

	if(!$monstring->registered()) {
		$MESSAGES[] = array('level' 	=> 'alert-error',
							'header' 	=> 'Du har ikke registrert mønstringen din!',
							'link' 		=> 'admin.php?page=UKMMonstring',
							'body' 	=> 'Velg "Mønstring" i menyen til venstre for å legge til informasjonen som mangler'
							);
	}

	return $MESSAGES;
}

## CREATE A MENU
function UKMMonstring_menu() {
	global $UKMN;
	$name = get_option('site_type') == 'fylke' ? 'Min m&oslash;nstring' : 'M&oslash;nstring';
	UKM_add_menu_page('monstring', $name, $name, 'editor', 'UKMMonstring', 'UKMMonstring', '//ico.ukm.no/hus-menu.png',1);
	#add_submenu_page('UKMMonstring', 'Videresendingsinformasjon', 'Infotekst om videresending', 'editor', 'UKMvideresending_info', 'UKMmonstring_videresending_info');

	// Legg til side for å redigere forsideinformasjonen.
	if(get_option('site_type') == 'fylke' || get_option('site_type') == 'kommune') {
		UKM_add_submenu_page(
			'UKMMonstring', 
			"Din forside", 
			"Din forside", 
			'editor', 
			'UKMMonstring_forside', 
			'UKMMonstring_forside'
		);
	}

	if(get_option('site_type') == 'fylke') {
		UKM_add_submenu_page(	'UKMMonstring', 
								'Infotekst om videresending', 
								'Infotekst om videresending', 
								'editor', 
								'UKMmonstring_videresending_info',
								'UKMmonstring_videresending_info'
							);
	}
	
	UKM_add_scripts_and_styles( 'UKMMonstring', 'UKMMonstring_script' );

}

## INCLUDE SCRIPTS
function UKMMonstring_script() {
	wp_enqueue_media();
	
	wp_enqueue_script('UKMMonstring_script',  plugin_dir_url( __FILE__ )  . 'monstring.script.js' );
	wp_enqueue_style( 'UKMMonstring_style', plugin_dir_url( __FILE__ ) .'monstring.style.css');
	
	wp_enqueue_script('bootstrap_js');
	wp_enqueue_style('bootstrap_css');
}

## SHOW STATS OF PLACES
function UKMMonstring() {
	$_CONTROLLER = 'form';
	
	if($_SERVER['REQUEST_METHOD']==='POST') {
		if(isset($_POST['c_id']))
			require_once('contact.save.php');
		else
			require_once('form.save.php');
	}

	if($_CONTROLLER == 'contact') {
		require_once('contact.controller.php');
		echo TWIG('contact.twig.html', $infos, dirname(__FILE__));
	} else {
		require_once('form.controller.php');
	
		if(isset($_MESSAGE))
			$infos['message'] = $_MESSAGE;
		
		echo TWIG('monstring.twig.html', $infos, dirname(__FILE__));
	}
}

## Move front-page stuff to separate file.
function UKMmonstring_forside() {
	require_once('forside.controller.php');
	UKMmonstring_forside_main();
}

function UKMmonstring_videresending_info() {
	$option_name = 'videresending_info_pl'.get_option('pl_id');
	if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		$TWIG['saved'] = update_site_option($option_name, $_POST['videresending_editor'] );
	}
	$TWIGdata = array('UKM_HOSTNAME' => UKM_HOSTNAME);
	echo TWIG('videresending_pre_editor.html.twig', $TWIGdata, dirname(__FILE__) );
	wp_editor( stripslashes(get_site_option($option_name)), 'videresending_editor', $settings = array() );
	echo TWIG('videresending_post_editor.html.twig', $TWIGdata, dirname(__FILE__) );
}

function UKMmonstring_save_kontaktpersoner() {
	require_once('order.save.php');
	die();
}

/**
	NETWORK ADMIN FUNCTIONS
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
	// I sesong, sjekk antall uregistrerte mønstringer
	if( in_array( (int)date('m'), array(11,12,1,2) ) ) {
		require_once('UKM/monstringer.class.php');
		$monstringer = new monstringer( get_site_option('season') );
		if( 15 < $monstringer->antall_uregistrerte() ) {
			$messages[] = array(
				'level' 	=> 'alert-warning',
				'module'	=> 'System',
				'header'	=> 'Det er '.$monstringer->antall_uregistrerte() .' uregistrerte mønstringer ',
				'link'		=> 'admin.php?page=UKMrapport_admin&network=monstringer_uregistrert'
			);
		}
	}
	return $messages;
}

function UKMmonstring_network_menu() {
	$page = add_menu_page('Mønstringer', 'Mønstringer', 'superadmin', 'UKMmonstring_network_admin','UKMmonstring_network_admin', '//ico.ukm.no/hus-menu.png',23);
	$subpage1 = add_submenu_page( 'UKMmonstring_network_admin', 'Opprett mønstring', 'Opprett mønstring', 'superadmin', 'UKMmonstring_network_admin_rome_opprett', 'UKMmonstring_network_admin_rome_opprett' );
	$subpage2 = add_submenu_page( 'UKMmonstring_network_admin', 'Avlys mønstring', 'Avlys mønstring', 'superadmin', 'UKMmonstring_network_admin_rome_avlys', 'UKMmonstring_network_admin_rome_avlys' );
	$subpage3 = add_submenu_page( 'UKMmonstring_network_admin', 'Legg til kommune', 'Legg til kommune', 'superadmin', 'UKMmonstring_network_admin_rome_legg_til', 'UKMmonstring_network_admin_rome_legg_til' );
	$subpage4 = add_submenu_page( 'UKMmonstring_network_admin', 'Trekk ut kommune', 'Trekk ut kommune', 'superadmin', 'UKMmonstring_network_admin_rome_trekk_ut', 'UKMmonstring_network_admin_rome_trekk_ut' );
	$subpage5 = add_submenu_page( 'UKMmonstring_network_admin', 'Opprett sesong', 'Opprett sesong', 'superadmin', 'UKMmonstring_network_admin_ny_sesong', 'UKMmonstring_network_admin_ny_sesong' );


	add_action( 'admin_print_styles-' . $page, 	'UKMmonstring_network_script' );
	for($i=0;$i<5;$i++) {
		$var = 'subpage'.$i;
		add_action( 'admin_print_styles-' . $$var, 'UKMmonstring_network_script' );
	}
}

function UKMmonstring_network_script() {
	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
}