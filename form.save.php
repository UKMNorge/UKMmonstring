<?php
$place = new monstring(get_option('pl_id'));
switch(get_option('site_type')) {
	case 'land':
	break;
	case 'fylke':
/*
		$fylke = new SQL("SELECT `name` FROM `smartukm_fylke` WHERE `id` = '#fylke'",
						array('fylke'=>$_POST['pl_fylke']));
		$fylke = $fylke->run('field', 'name');
		$_POST['pl_name'] = utf8_encode($fylke);
		$place->update('pl_name', 'pl_name');
		$place->update('pl_fylke', 'pl_fylke');
*/
	break;
	default:
		$place->update('pl_name', 'pl_name');
		$_POST['pl_deadline2'] = autocorrectDeadline(getDatePickerTime('deadline2'));
		$place->update('pl_deadline2');
	break;
}

$place->update('pl_place', 'pl_place');

$_POST['date_from'] = getDatePickerTime('date_from');

$place->update('pl_start', 'date_from');
$_POST['date_to'] = getDatePickerTime('date_to');
$place->update('pl_stop', 'date_to');

$_POST['deadline'] = autocorrectDeadline(getDatePickerTime('deadline'));
$place->update('pl_deadline','deadline');

##############################################################
## ADDED 01.12.2011
## INSERT RELATIONS BETWEEN PLACE AND STANDARD TYPES OF BANDS
## THE REST IS HANDLED BY AJAX
$plid = get_option('pl_id');
for($id=1; $id<4; $id++) {
	if(empty($id)||empty($plid)) continue;
	$del = new SQLdel('smartukm_rel_pl_bt', array('pl_id'=>$plid, 'bt_id'=>$id));
	$del = $del->run();
	#echo $del->debug() . '<br />';
	$ins = new SQLins('smartukm_rel_pl_bt');
	$ins->add('pl_id', $plid);
	$ins->add('bt_id', $id);
	$ins = $ins->run();
	#echo $ins->debug();
}



$offers = array(4,5,6,8,9);

foreach($offers as $offer) {
	if(!isset($_POST['tilbud_'. $offer]))
		continue;

	if(empty($id)||empty($plid)) continue;
	$del = new SQLdel('smartukm_rel_pl_bt', array('pl_id'=>$plid, 'bt_id'=>$offer));
	$del = $del->run();

	if($_POST['tilbud_'. $offer] == 'true') {
		$ins = new SQLins('smartukm_rel_pl_bt');
		$ins->add('pl_id', $plid);
		$ins->add('bt_id', $offer);
		$ins = $ins->run();
	}
}

if($_POST['takemeto'] != 'home') {
	$to = explode('=', $_POST['takemeto']);
	if($to[0] == 'contact') {
		$newURL = $_SERVER['REDIRECT_URL']. '?page=UKMMonstring&contact='. $to[1];
		
		$_CONTROLLER = 'contact';
		$_GET['contact'] = $to[1];
	} elseif($to[0] == 'delete') {
		$id = (int) $to[1];
		if($id > 0) {
			$del = new SQLdel('smartukm_rel_pl_ab', array('pl_id'=>get_option('pl_id'), 'ab_id'=>$id));
			$res = $del->run();
			$_MESSAGE = array('success' => $res ? true : false, 'body' => 'Kontaktpersonen ble '. (!$res ? 'IKKE' : '').' slettet fra din mønstring');
		} else
			$_MESSAGE = array('success' => false, 'body' => 'En feil oppsto. Kontakt UKM Norge hvis du ikke får slettet kontaktpersonen');
	}
} else {
	$place = new monstring($place->get('pl_id'));
	if($place->get('pl_start') > $place->get('pl_stop')) {
		$_MESSAGE = array('successs' => false,
						  'title' => 'Dette blir vel feil?',
						  'body' => 'Alle endringene dine er lagret, MEN vi ser at mønstringen slutter før den starter. Rett opp dette i skjemaet og lagre på nytt'
						  );
	} elseif ($place->get('pl_deadline') > $place->get('pl_start') || $place->get('pl_deadline2') > $place->get('pl_start')) {
		$_MESSAGE = array('successs' => false,
						  'title' => 'Dette blir vel feil?',
						  'body' => 'Alle endringene dine er lagret, MEN vi ser at påmeldingsfrist er satt etter mønstringen starter. Sjekk datoer i skjemaet nedenfor.'
						  );
	}
}

#######################################################################
## HENTER INN ET DATEPICKER-TIDSPUNKT								 ##
#######################################################################
function getDatePickerTime($postname) {
	$vals = explode('.', $_POST[$postname.'_datepicker']);
	
	$v = array('d'=>$vals[0],
		  	   'm'=>$vals[1],
			   'y'=>$vals[2],
			   'h'=>$_POST[$postname.'_time'],
			   'i'=>$_POST[$postname.'_min']
				 );
	return mktime($v['h'],$v['i'],0,$v['m'],$v['d'],$v['y']);
}

#######################################################################
## CHECK IF A DEADLINE IS VALID OR NOT								 ##
#######################################################################
function autocorrectDeadline($deadline) {
	#return $deadline;
	$season = UKMN_config('smartukm_season');
	
	$month	= date("m", $deadline);
	$year	= date("Y", $deadline);
	$day 	= date("d", $deadline);
	$hour	= date("H", $deadline);
	$minute	= date("i", $deadline);
	
	## IF THE ACTUAL SEASON YEAR, AND DEADLINE IS AFTER JUNE, IT MUST BE ONE YEAR TOO MUCH
	if($year == $season && $month > 6)
		$deadline = mktime($hour, $minute, 0, $month, $day, ($year-1));
	## IF IT IS LAST YEAR, AND THE DEADLINE IS BEFORE MAY, IT MUST BE ONE YEAR TOO LITTLE
	elseif($year == ($season-1) && $month < 5)
		$deadline = mktime($hour, $minute, 0, $month, $day, ($year+1));
	
	return $deadline;
}
?>