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
	echo 'TILBUD '. $offer;
	if(!isset($_POST['tilbud_'. $offer]))
		continue;

	if(empty($id)||empty($plid)) continue;
	echo 'delete';
	$del = new SQLdel('smartukm_rel_pl_bt', array('pl_id'=>$plid, 'bt_id'=>$id));
	$del = $del->run();

	if($_POST['tilbud_'. $offer] == 'true') {
		echo 'insert';
		$ins = new SQLins('smartukm_rel_pl_bt');
		$ins->add('pl_id', $plid);
		$ins->add('bt_id', $id);
		echo $ins->debug();
		$ins = $ins->run();
	} else 
		echo 'NOinsert';
}













// SAVE OFFERS HERE




























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