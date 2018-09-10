<?php
add_submenu_page('index.php', 'Nettred.brukere', ' Nettred.brukere', 'editor', 'createUsers_gui','createUsers_gui', 1000);

function createUsers_gui() {
	require_once('monstring.users.php');
	echo '<h1>Opprett nettredaksjonsbrukere</h1>';
	//echo 'DEVELOPER: Funksjonen befinner seg for øyeblikket i ukmmonstring.php';
	if( isset( $_GET['generateUsers'] ) )
		generateUsers();
	
	$query = 'SELECT * FROM ukmno_generatedusers WHERE `pl_id` = '.get_option('pl_id') .' ORDER BY `lastname`';
	$statement = new SQL($query);
	$result = $statement->run();
	
	if (SQL::numRows($result) == 0) {
		echo 'Sleng på &generateUsers i adresselinjen for å opprette brukere.<br /><br />';
		return;
	}
	
	echo '<table width="600px" border="0"><tr><th>Navn</th><th>Brukernavn</th><th>Passord</th></tr>';
	while ($row = SQL::fetch($result)) {
		echo '<tr><td>'.$row['lastname'].', '.$row['firstname'].'</td>
				<td>'.$row['username'].'</td>
				<td>'.$row['password'].'</td></tr>';
	}
	echo '</table><br /><br />';
	echo SQL::numRows($result).' brukere';
}


function generateUsers() {
	
	$m = new monstring(get_option('pl_id'));
	$innslag = $m->innslag_btid();
	
	$count = 0;
	
	foreach($innslag as $band_type => $bands) {
	
		if( $band_type == 2 OR $band_type == 5 ) { 
		
			foreach($bands as $band) {
				
				$inn = new innslag($band['b_id']);
				$inn->videresendte($m->g('pl_id'));
				$deltakere = $inn->personer();
				
				foreach( $deltakere as $deltaker ) {
					
					$username = $deltaker['p_firstname'].'_'.$deltaker['p_lastname'];					
					$username = str_replace(' ', '', $username);
					$username = strtolower($username);
					$username = str_replace('æ', 'e', $username);
					$username = str_replace('ø', 'o', $username);
					$username = str_replace('å', 'a', $username);
					
					$email    = $username . '@nettredaksjon.ukm.no';
					
					UKM_loader('password');
					$password = UKM_ordpass();
										
					$query = 'SELECT * FROM ukmno_generatedusers WHERE `username` = "'.$username.' AND `pl_id` = '.$m->g('pl_id');
					$statement = new SQL($query);
					$result = $statement->run('array');
					
					if( count($result)>1 ) {
						// USER ALREADY EXISTS FOR THIS PL_ID, SKIP
					}
					else {
						$sql = new SQLins('ukmno_generatedusers');
						
						$sql->add('firstname', $deltaker['p_firstname']);
						$sql->add('lastname', $deltaker['p_lastname']);
						$sql->add('username', $username);
						$sql->add('password', $password);
						$sql->add('pl_id', $m->g('pl_id'));
						
						$sql->run();
						
						++$count;
						
						if( username_exists($username) ) {
							// USER ALREADY EXISTS
						} else {
//							wp_create_user( $username, $password, $email );
							$res = wp_insert_user(array('user_pass'=>$password, 
									'user_login'=>$username, 
									'first_name'=>$deltaker['p_firstname'],
									'last_name'=>$deltaker['p_lastname'],
									'displayname'=>$deltaker['p_firstname'].' '.$deltaker['p_lastname'],
									'user_email'=>$email,
									'role'=>'contributor'));
						}
					}
					
				}
							
			}
		
		}
	
	}
	
	echo '<br />Brukere opprettet!';
	
}
?>
