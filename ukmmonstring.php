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
		add_action('admin_menu', 'UKMMonstring_menu',200);
}

## CREATE A MENU
function UKMMonstring_menu() {
	global $UKMN;
	$page = add_menu_page('M&oslash;nstring', 'M&oslash;nstring', 'editor', 'UKMMonstring', 'UKMMonstring', 'http://ico.ukm.no/hus-menu.png',199);
	add_action( 'admin_print_styles-' . $page, 'UKMMonstring_script' );

}

## INCLUDE SCRIPTS
function UKMMonstring_script() {
	wp_enqueue_script('UKMMonstring_script',  plugin_dir_url( __FILE__ )  . 'monstring.script.js' );
	wp_enqueue_style( 'UKMMonstring_style', plugin_dir_url( __FILE__ ) .'monstring.style.css');
}

## SHOW STATS OF PLACES
function UKMMonstring() {
	echo TWIG('monstring.twig.html',array(), dirname(__FILE__));
}

/*
function UKMMonstring_allow(){
	$plid = (int)get_option('pl_id');
	$type = (int)$_POST['type'];
	if($plid == 0 || $type == 0)
		die('Ugyldig input!');
	
	if($_POST['setting'] == 'true') {
		$del = new SQLdel('smartukm_rel_pl_bt', array('pl_id'=>$plid, 'bt_id'=>$type));
		$delRes = $del->run();
			
		$ins = new SQLins('smartukm_rel_pl_bt');
		$ins->add('pl_id', $plid);
		$ins->add('bt_id', $type);
		$ins->run();

		UKMlog('smartukm_rel_pl_bt','bt'.$type, 'setting');
		
		die();
	} else {
		$del = new SQLdel('smartukm_rel_pl_bt', array('pl_id'=>$plid, 'bt_id'=>$type));
		$delRes = $del->run();
		
		UKMlog('smartukm_rel_pl_bt','bt'.$type, 'setting');
	}
	die();
}
*/


/*
	
	if(isset($_GET['contact'])) {
		require_once('UKM/kontakt.class.php');
		require_once('monstring.contact.php');
		echo contactForm();
		return;
	}
	
	UKM_loader('form|toolkit');
	require_once('monstring.form.php');
	require_once('monstring.lang.php');
	require_once('monstring.save.php');

	echo '<div class="wrap">'.UKMMonstring_form($place, $user).'</div>';

	
}
*/
/*

function my_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_register_script('UKMcontactImageupload', plugin_dir_url( __FILE__ ).'monstring.image.js', array('jquery','media-upload','thickbox'));
	wp_enqueue_script('UKMcontactImageupload');
}

function my_admin_styles() {
	wp_enqueue_style('thickbox');
}

if(isset($_GET['contact'])) {
	add_action('admin_print_scripts', 'my_admin_scripts');
	add_action('admin_print_styles', 'my_admin_styles');
}
function filter_iste($html, $id, $caption, $title, $align, $url, $size, $alt) {
	var_dump($html);
    return $html;
}


*/