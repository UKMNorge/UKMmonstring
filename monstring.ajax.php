<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
/* UKM LOADER */ if(!defined('UKM_HOME')) define('UKM_HOME', '/home/ukmno/public_html/UKM/'); require_once(UKM_HOME.'loader.php');
require_once('/home/ukmno/public_html/UKM/inc/sql.inc.var.php');
require_once('/home/ukmno/public_html/UKM/inc/sql.inc.php');
UKM_loader('ukmlog');

if(isset($_GET['fakeGET'])) {
	$gets = explode('||',$_GET['fakeGET']);
	for($i=0; $i<sizeof($gets); $i++) {
		$temp = explode('|',$gets[$i]);
		$_GET[$temp[0]] = $temp[1];
	}
	unset($_GET['fakeGET']);
}

switch($_GET['action']) {
	####################
	## REMOVE A CONTACT FROM A PLACE
	case 'deleteContact':
		if(isset($_GET['pl_id']) && isset($_GET['rel_id'])) {
			$del = new SQLdel('smartukm_rel_pl_ab', array('pl_id'=>$_GET['pl_id'], 'pl_ab_id'=>$_GET['rel_id']));
			$delRes = $del->run();
			
			die("document.getElementById('".$_GET['c_id']."').style.display = 'none';document.getElementById('".$_GET['c_id']."').id = 0;");
		} else {
			die('Beklager, kontakten ble ikke fjernet grunnet feil');
		}
		break;

	case 'listMainContacts':
		UKM_loader('api/monstring.class');
		$place = new monstring($_GET['pl_id']);
	
		$contacts = $place->kontakter_pamelding();
		foreach($contacts as $kommune => $c)
			$return .= '<strong>'.$kommune.': </strong>'
					.  $c->get('name')
					. '<br />'
					;
					
		die('document.getElementById(\'main_contacts\').innerHTML = \''.$return.'\';');
	break;
}
?>