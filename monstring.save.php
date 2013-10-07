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
}