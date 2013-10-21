<?php
	if($_POST['c_id']=='new')
		$sql = new SQLins('smartukm_contacts');
	else
		$sql = new SQLins('smartukm_contacts', array('id'=>$_POST['c_id']));
	
	$sql->add('firstname', ucwords($_POST['firstname']));
	$sql->add('lastname', ucwords($_POST['lastname']));
	$sql->add('name', ucwords($_POST['firstname']. ' ' . $_POST['lastname']));
	
	$sql->add('tlf', (int) str_replace(' ','', $_POST['phone']));
	$sql->add('email', $_POST['email']);
	
	if(isset($_POST['kommune']))
		$sql->add('kommune', $_POST['kommune']);
	$sql->add('title', $_POST['title']);
	$sql->add('adress', $_POST['address']);
	$sql->add('postalcode', $_POST['postal']);
//	$sql->add('poststed', UKMN_poststed($_POST['postal']));
	
	$sql->add('picture', $_POST['image']);
	$sql->add('facebook', $_POST['facebook']);
		
	$res = $sql->run();
		
	if($res && $_POST['c_id']=='new') {
		$rel = new SQLins('smartukm_rel_pl_ab');
		$rel->add('pl_id', get_option('pl_id'));
		$rel->add('ab_id', $sql->insid());
		$rel->add('order', time());
		$relres = $rel->run();
	}

	if( isset($_POST['c_id']) && is_numeric( $_POST['c_id'] ) ) {
		require_once('UKM/kontakt.class.php');
		
		$kontakt = new kontakt( $_POST['c_id'] );
		$locked = $kontakt->g('system_locked');
		
		if($locked == 'true') {
			$email = $kontakt->g('email');
			require_once(plugin_dir_path(__FILE__).'../UKMkart/config.php');
			require_once(plugin_dir_path(__FILE__).'../UKMkart/functions.inc.php');
			$UKMkart_GD_LOG = array();
			$UKMkart_GD_LOG_GROUP = '';

			// Re-gen URG-kart (UKMkart-module)
			if(strpos( $email, '@urg.ukm.no') ) {
				require_once(plugin_dir_path(__FILE__).'../UKMkart/controller_urg.inc.php');
			} elseif ( strpos( $email , '@ukm.no') ) {
				require_once(plugin_dir_path(__FILE__).'../UKMkart/controller_ukm.inc.php');
			}
		}
	}


	if($res!==false&&$res!=-1)
		$_MESSAGE = array('success' => true, 'body' => 'Kontaktperson lagret');
	else
	    $_MESSAGE = array('success' => false, 'body' => 'Lagret ikke kontaktinformasjon');



