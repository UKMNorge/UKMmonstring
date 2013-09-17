<?php
################################################################################################################################################################
################################################################################################################################################################
### FUNCTION TO SET UP THE GUI OF THE PLACE
################################################################################################################################################################
################################################################################################################################################################
function UKMMonstring_form($user) {
	global $lang;
		
	$place = new monstring(get_option('pl_id'));
	
	$target = get_option('siteurl').'/wp-admin/admin.php?'.$_SERVER['QUERY_STRING'];
	
	##INFO ABOUT THE PLACE
	$season = UKMN_config('smartukm_season');
	
	UKM_loader('private');
	
	$lockMSG = '<h3>Beklager, du kan ikke endre m&oslash;nstringen</h3>'
				.'Endring av m&oslash;nstring er ikke tilgjengelig fra 1. mai til 1. november grunnet klargj&oslash;ring av neste sesong';
	if(UKM_private()){
		if((int)date("m") > 4 && (int)date("m") < 11 && !isset($_GET['force']))
			echo $lockMSG . '<h3>Grunnet din IP fungerer modulen likevel</h3>';
	} else {
		if((int)date("m") > 4 && (int)date("m") < 11 && !isset($_GET['force']))
			return $lockMSG;
	}
	################# PLACE
	$form_place = new form('place_place');
	## PLACE NAME
	if($place->get('type')=='kommune') # IF KOMMUNE
		$form_place->input($lang['upl_name'], "pl_name", $place->get('pl_name'), $lang['upl_name_help']);
	else # IF FYLKE OR FESTIVAL
		$form_place->noe($lang['upl_name'], $place->get('pl_name'));
	## PLACE PLACE
	$form_place->input($lang['upl_where'], "pl_place", $place->get('pl_place'), $lang['upl_where_help']);
	
	################# TIME
	$form_time = new form('place_time');
	## PLACE START AND STOP
	$form_time->datoTid($lang['upl_start'], "date_from", $place->get('pl_start'), $season, $season, $lang['upl_start_help']);
	$form_time->datoTid($lang['upl_stop'], "date_to", $place->get('pl_stop'), $season, $season, $lang['upl_stop_help']);
	
	################# DEADLINE
	$form_deadline = new form('place_deadline');
	
	## DEADLINES
	if ($place->get('type')=='fylke') {
		$form_deadline->datoTid($lang['upl_deadline'], "deadline", $place->get('pl_deadline'), $season, $season, $lang['upl_deadline_help']);
		$form_deadline->select($lang['upl_fylke'], 'pl_fylke', UKMN_fylker(), $place->get('pl_fylke'));
	} else {
		$form_deadline->datoTid($lang['upl_deadline1'], "deadline", $place->get('pl_deadline'), $season-1, $season+1, $lang['upl_deadline1_help']);
		$form_deadline->datoTid($lang['upl_deadline2'], "deadline2", $place->get('pl_deadline2'), $season-1, $season+1, $lang['upl_deadline2_help']);
	}

	return '<form id="form_myplace" method="post" action="'.$target.'" class="validate">'
		.'	<div id="hugesubmit_monstring"><div id="lagre">Lagre</div></div>'

		.  UKMN_fieldset($lang['time_and_place'], 
						 '<table><tr>'
						.'<td valign="top" style="border-bottom:none;" width="50%">'.$form_place->run().'</td>'
						.'<td valign="top" style="border-bottom:none;">'.$form_time->run().'</td>'
						.'</tr></table>'
						, '750px;')
						
		.  UKMN_fieldset($lang['offers_and_deadlines'], 
						 '<table><tr>'
						.'<td valign="top" style="border-bottom:none;" width="50%">'.UKMMonstring_offers($place).'</td>'
						.'<td valign="top" style="border-bottom:none;">'.$form_deadline->run().'</td>'
						.'</tr></table>'
						, '750px;')

		.  UKMN_fieldset($lang['contactperson'], 
						'<table><tr>'
						.'<td valign="top" style="border-bottom:none;" width="70%">'
						.'<span style="font-size: 11px;font-style: italic;font-family: \'Lucida Grande\',Verdana,Arial,\'Bitstream Vera Sans\',sans-serif;color: #666666;">'.$lang['contactperson_help'].'</span>'
						.UKMMonstring_contacts($place)
						. '<p class="submit">'
						. '<input type="button" class="button" name="add_contact_p" id="add_contact_p" value="'.$lang['add_contactP'].'" '
						.   'onclick="window.location.href=\'?page=UKMMonstring&contact=new\'" />'
						.'</p>'
						.'</td>'
						.'<td valign="top" style="border-bottom: none;">'
						.UKMMonstring_activeContacts($place)
						.'</td>'
						.'</tr>'
						.'</table>', '750px')
						
/*
		.  UKMN_fieldset($lang['files'], 
						UKMMonstring_files($place)
						, '750px')
*/
						
		.  ($place->get('type') == 'kommune'
			?	UKMN_fieldset($lang['geography'], 
							 UKMMonstring_places($place)
							, '750px;')
			: '')
						
		.  '<p class="submit"><input type="submit" class="button" name="place_submit" id="place_submit" value="'.$lang['save'].'" /></p>'
		.  '</form>';
}

################################################################################################################################################################
################################################################################################################################################################
### GENERATE A TABLE OF GEOGRAPHICAL RELATIONS FOR A KOMMUNE PLACE
################################################################################################################################################################
################################################################################################################################################################
function UKMMonstring_places($place) {
	global $lang;
	$return = '<table cellspacing="2" cellpadding="2" class="widefat">'
			. '<tr>'
			. '<td><em>'.$lang['about_geo'].'</em></td>'
			. '</tr>'
			;
			
	$kommuner = $place->get('kommuner');
	
	foreach($kommuner as $i => $k)
		$return .= '<tr>'
				.  '<td valign="middle">'.$k['name'].'</td>'
				.  '<td>'
				#.  ($user['user_type']=='user' ? $lang['av_UKMN'] : (UKMN_ico('remove',20)))
				.  '</td>'
				.  '</tr>';	
	$return .= '</table>';	

	return $return;
}

################################################################################################################################################################
################################################################################################################################################################
### FUNCTION TO CREATE A TABLE TO SHOW ALL THE OFFERS OF THE PLACE
################################################################################################################################################################
################################################################################################################################################################
function UKMMonstring_offers($place) {
	$all_bts = $place->getAllBandTypes();

	$return = '<table cellspacing="2" cellpadding="2" class="widefat">'
			. '<thead>'
			. '<tr>'
			. '<th>'.$lang['upl_offer'].'</th>'
			. '<th colspan="2">'.$lang['upl_choose'].'</th>'
			. '</tr>'
			. '</thead>';
	foreach($all_bts as $i => $bt) {
		$allowed = $bt['allowed'] ? ' checked="checked"' : '';
		$notallowed = $bt['allowed'] ? '' : ' checked="checked"';
		if($bt['bt_id'] < 4) {
			$active = ' disabled="disabled"';
#			$link = $linkend = $action = '';
		} else {
			$active = '';
			#$action = 'UKMN_AJAX(\'UKMMonstring/monstring.ajax.php\', \'action|#action||pl_id|'.$place->get('pl_id').'||bt_id|'.$bt['bt_id'].'\');';
#			$link = '<a href="javascript:'.$action.''
#					. '" style="text-decoration:none; color: #000;">'
#					;
#			$linkend = '</a>';
		}
		$return .= '<tr style="height: 25px;">'
				.  '<td>'.$bt['bt_name'] . '</td>'
				.  '<td>'
#				.  str_replace('#action','offerAdd',$link)
				#.  '<label><input type="radio" onclick="'.str_replace('#action','offerAdd',$action).'" value="ja" name="pl_bt_'.$bt['bt_id'].'" '.$allowed.$active.' /> ja</label>'
				.  '<label><input type="radio" class="bandtypesallowed" value="true" name="'.$bt['bt_id'].'" '.$allowed.$active.' /> ja</label>'
#				.  $linkend
				.  '</td>'
				.  '<td>'
#				.  str_replace('#action','offerRemove',$link)
				#.  '<label><input type="radio" onclick="'.str_replace('#action','offerRemove',$action).'"  value="ja" name="pl_bt_'.$bt['bt_id'].'" '.$notallowed.$active.'/> nei</label>'
				.  '<label><input type="radio" class="bandtypesallowed" value="false" name="'.$bt['bt_id'].'" '.$notallowed.$active.' /> nei</label>'

#				.  $linkend
				.  '</td>'
				.  '</tr>';	
	}
	$return .= '</table>';
	return  $return;	
}


################################################################################################################################################################
################################################################################################################################################################
### FUNCTION TO LIST CONTACT PERSONS OF THE PLACE
################################################################################################################################################################
################################################################################################################################################################
function UKMMonstring_activeContacts($place) {
	if($place->get('type') != 'kommune')
		return ;
	$return = '<div style="line-height: 20px;">'
			.  '<strong>Den &oslash;verste kontaktpersonen er hovedkontakt<br />(klikk og dra hvis du vil endre rekkef&oslash;lgen):</strong>'
			.  '<br /><br />'
			.  '<span id="main_contacts">';

	$contacts = $place->kontakter_pamelding();
	if(is_array($contacts)) {
		foreach($contacts as $kommune => $c)
			$return .= '<strong>'.$kommune.': </strong>'
					.  $c->get('name')
					.  '<br />';
	}
	$return .= '</span>'
			 . '</div>';
	return $return;
}
################################################################################################################################################################
################################################################################################################################################################
### FUNCTION TO LIST CONTACT PERSONS OF THE PLACE
################################################################################################################################################################
################################################################################################################################################################
function UKMMonstring_contacts($place) {
	global $lang;

	$contacts = $place->kontakter();
	if(is_array($contacts)) {
		$return = '<input type="hidden" name="this_place_id" id="this_place_id" value="'.get_option('pl_id').'" />'
				. '<ul id="sortable">';
		foreach($contacts as $id => $c) {
			$edit 			= '<div style="float:left; margin: 2px; margin-left: 10px; margin-right: 10px;">'
							. '<a href="?page=UKMMonstring&contact='.$c->get('id').'">'
					  		. UKMN_icoButton('pencil',16,$lang['edit'])
					  		. '</a>'
				  			. '</div>';
			$deleteAction = 'UKMN_AJAX(\'UKMMonstring/monstring.ajax.php\', \'action|deleteContact||pl_id|'.$place->get('pl_id').'||rel_id|'.$c->get('rel_id').'||c_id|'.$c->get('id').'\',\'eval\');';
			$delete			= '<div style="float:right; margin: 2px; margin-left: 5px; margin-right: 10px;">'
							. '<a href="javascript:'.$deleteAction.'">'
					  		. UKMN_icoButton('trash',16,'fjern')
					  		. '</a>'
				  			. '</div>';

			$nameandtitle 	= '<div style="float:left; margin-right: 10px;">'
					  		. '<strong>'. $c->get('name') . '</strong>'
							. '<br />'
							. $c->get('title')
							. '<br />'
							. $c->get('kommunenavn')
				  			. '</div>';
			$phoneemail 	= '<div style="float:right; margin-right: 10px; text-align:right;">'
					  		. $c->get('tlf')
							. '<br />'
							. $c->get('email')
					  		. '</div>';
				  						  		 
			$return .= '<li class="ui-state-default" id="'.$c->get('id').'" style="line-height: 20px; vertical-align: center;">'
					 . $delete
					 . $edit
					 . $nameandtitle
					 . $phoneemail
					 . '<br clear="all" />'
					 . '</li>';
		}	
		$return .= '</ul>';
	}
	return $return;
}

################################################################################################################################################################
################################################################################################################################################################
### FUNCTION TO LIST CONTACT PERSONS OF THE PLACE
################################################################################################################################################################
################################################################################################################################################################
function UKMMonstring_files($place) {
	$form_files = new form('place_files');
	$form_files->janei('Skal deltakerne kunne laste opp filer direkte i p&aring;meldingsprosessen?',
						'pl_fileupload', $place->get('pl_fileupload'));
	return 'Vi &oslash;nsker &aring; innf&oslash;re en opplastingsfunksjon for deltakerne, slik at de kan laste opp MP3-filer o.l '
		.  'som skal brukes til innslaget deres direkte i p&aring;meldingssystemet. Forel&oslash;pig vil ikke denne funksjonen inneb&aelig;re noen '
		.  'endringer, men brukes til &aring; sjekke interessen for en slik opplastingsmulighet.'
		#.  '<br /><br />'
		.  'Hvis funksjonen blir integrert i &aring;r, vil alle bli varslet om dette'
		.  $form_files->run();
}
?>