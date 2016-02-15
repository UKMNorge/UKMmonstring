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
	if($blog_id != 1)
		add_action('UKM_admin_menu', 'UKMMonstring_menu');

	add_action('wp_ajax_UKMmonstring_save_kontaktpersoner', 'UKMmonstring_save_kontaktpersoner');
	add_action('UKMWPDASH_shortcuts', 'UKMMonstring_dash_shortcut');
}

function UKMmonstring_dash_shortcut( $shortcuts ) {	
	$shortcut = new stdClass();
	$shortcut->url = 'admin.php?UKMMonstring';
	$shortcut->title = get_option('site_type') == 'fylke' ? 'Min m&oslash;nstring' : 'M&oslash;nstring';
	$shortcut->icon = 'http://ico.ukm.no/hus-menu.png';
	$shortcuts[] = $shortcut;
	
	return $shortcuts;
}
## CREATE A MENU
function UKMMonstring_menu() {
	global $UKMN;
	$name = get_option('site_type') == 'fylke' ? 'Min m&oslash;nstring' : 'M&oslash;nstring';
	UKM_add_menu_page('monstring', $name, $name, 'editor', 'UKMMonstring', 'UKMMonstring', 'http://ico.ukm.no/hus-menu.png',1);
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

function UKMmonstring_save_kontaktpersoner() {
	require_once('order.save.php');
	die();
}