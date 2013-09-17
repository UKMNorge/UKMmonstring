<?php
date_default_timezone_set('Europe/Oslo');

##############################################################################################################################
## KONTAKTPERSON-LAGRING
##############################################################################################################################
if(isset($_POST['kontaktperson_submit'])) {
	
	if(is_numeric($_POST['c_id']) && $_POST['c_id']!=0)
		$sql = new SQLins('smartukm_contacts', array('id'=>$_POST['c_id']));
	else	
		$sql = new SQLins('smartukm_contacts');
	
	$sql->add('firstname', ucwords($_POST['firstname']));
	$sql->add('lastname', ucwords($_POST['lastname']));
	$sql->add('name', ucwords($_POST['firstname']. ' ' . $_POST['lastname']));
	
	$sql->add('tlf', (int) str_replace(' ','', $_POST['tlf']));
	$sql->add('email', $_POST['email']);
	
	if(isset($_POST['kommune']))
		$sql->add('kommune', $_POST['kommune']);
	$sql->add('title', $_POST['title']);
	$sql->add('adress', $_POST['address']);
	$sql->add('postalcode', $_POST['postalcode_nr']);
	$sql->add('poststed', UKMN_poststed($_POST['postalcode_nr']));
	
	$sql->add('picture', $_POST['upload_image']);
	$sql->add('facebook', $_POST['facebook']);
		
	$res = $sql->run();
		
	if($res && !(is_numeric($_POST['c_id']) && $_POST['c_id']!=0)) {
		$rel = new SQLins('smartukm_rel_pl_ab');
		$rel->add('pl_id', get_option('pl_id'));
		$rel->add('ab_id', $sql->insid());
		$rel->add('order', time());
#		echo $rel->debug();
		$relres = $rel->run();
	}

#	if($_SERVER['REMOTE_ADDR']=='188.113.120.119')
#		echo $sql->debug();

	if($res!==false&&$res!=-1)
	    echo '<div class="updated"><p>Kontaktperson lagret!</p></div>';
	else
	    echo '<div class="error"><p><strong>Beklager, en feil har oppst&aring;tt!</strong> <br />Kontaktperson ble ikke lagret, vennligst pr&oslash;v igjen</p></div>';
	


##############################################################################################################################
## PLACE-LAGRING
##############################################################################################################################
}elseif(isset($_POST['place_submit'])) {
	###############
	### OBS OBS
	### FLYTTET LAGRING TIL OBJEKTET 04.09.2012
	
	$place = new monstring(get_option('pl_id'));
	switch(get_option('site_type')) {
		case 'land':
		break;
		case 'fylke':
			$fylke = new SQL("SELECT `name` FROM `smartukm_fylke` WHERE `id` = '#fylke'",
							array('fylke'=>$_POST['pl_fylke']));
			$fylke = $fylke->run('field', 'name');
			$_POST['pl_name'] = utf8_encode($fylke);
			$place->update('pl_name', 'pl_name');
			$place->update('pl_fylke', 'pl_fylke');
		break;
		default:
			$place->update('pl_name', 'pl_name');
			$place->update('pl_kommune', mktime());
			$place->update('pl_fylke', 0);
			$_POST['pl_deadline2'] = autocorrectDeadline(getDatePickerTime('deadline2'));
			$place->update('pl_deadline2');
		break;
	}
	
	$place->update('pl_place', 'pl_place');
	$place->update('pl_fileupload', 'pl_fileupload');
	
	$_POST['date_from'] = getDatePickerTime('date_from');
	$place->update('pl_start', 'date_from');
	$_POST['date_to'] = getDatePickerTime('date_to');
	$place->update('pl_stop', 'date_to');

	$_POST['deadline'] = autocorrectDeadline(getDatePickerTime('deadline'));
	$place->update('pl_deadline','deadline');


/*
	$sql = new SQLins('smartukm_place', array('pl_id'=>get_option('pl_id')));
	#$sql->add('season', UKMN_config('smartukm_season'));
	#### SAVE SPESIFIC PARTS FOR LAND, FYLKE OR KOMMUNE
	switch(get_option('site_type')) {
		case 'land':
			$sql->add('pl_form', $_POST['pl_form']);
		break;
		case 'fylke':
			## ADD FYLKE ID AND NAME OF PLACE
			$fylke = new SQL("SELECT `name` FROM `smartukm_fylke` WHERE `id` = '#fylke'",
							array('fylke'=>$_POST['pl_fylke']));
			$fylke = $fylke->run('field', 'name');
			$sql->add('pl_name', utf8_encode($fylke));
			$sql->add('pl_fylke', $_POST['pl_fylke']);
			$sql->add('pl_form', $_POST['pl_form']);
		break;
		default:
			## SET THE PL NAME AND KOMMUNE TO KOMMUNE-VALUES
			$sql->add('pl_name', $_POST['pl_name']);
			$sql->add('pl_kommune', mktime());
			$sql->add('pl_fylke', 0);
			$sql->add('pl_deadline2', autocorrectDeadline(getDatePickerTime('deadline2')));
		break;
	}
	#### ADD COMMON VALUES
	$sql->add('pl_place', $_POST['pl_place']);
	$sql->add('pl_fileupload', $_POST['pl_fileupload']);
	$sql->add('pl_start', getDatePickerTime('date_from'));
	$sql->add('pl_stop', getDatePickerTime('date_to'));
	$sql->add('pl_deadline', autocorrectDeadline(getDatePickerTime('deadline')));

#	$res = $sql->debug();
	$res = $sql->run();
*/
	if($res!==false)
		    echo '<div class="updated"><p>M&oslash;nstringsdetaljer lagret!</p></div>';
	else
		    echo '<div class="error"><p><strong>Beklager, en feil har oppst&aring;tt!</strong> <br />M&oslash;nstringsdetaljer ble ikke lagret, vennligst pr&oslash;v igjen</p></div>';
		    
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
	
}
##############################################################################################################################


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