
<?php
date_default_timezone_set('Europe/Oslo');

require_once('UKM/logger.class.php');
require_once('UKM/kontakt.class.php');
require_once('UKM/write_kontakt.class.php');
require_once('UKM/write_monstring.class.php');

global $current_user;
UKMlogger::setId( 'wordpress', $current_user->ID, get_option('pl_id') );

$response = new stdClass();
$response->success = true;

/**
 * SLETT EN KONTAKTPERSON
 */
if( isset( $_GET['slett_kontakt'] ) ) {    
    try {
        $kontakt = new kontakt_v2( $_GET['slett_kontakt'] );
        $monstring->getKontaktpersoner()->fjern( $kontakt );
        write_monstring::save( $monstring );
        write_kontakt::delete( $kontakt );
        $response->text = 'Kontaktpersonen er slettet!';
    } catch( Exception $e ) {
        $response->success = false;
        $response->text = 'Kunne ikke slette kontaktperson. Systemet sa: '. $e->getMessage();
    }
    $TWIGdata['melding'] = $response;
}

/**
 * LAGRE ENDRINGER I MØNSTRING
 */
if( $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['type'] == 'monstring' ) {
    $start = DateTime::createFromFormat('d.m.Y-H:i', $_POST['start'].'-'.$_POST['start_time']);
    $stop = DateTime::createFromFormat('d.m.Y-H:i', $_POST['stop'].'-'.$_POST['stop_time']);
    $frist1 = DateTime::createFromFormat('d.m.Y-H:i:s', $_POST['frist_1'].'-23:59:59');
    
    // For kommuner er frist 2 påmeldingsfrist for "bidra med noe-innslag"
    if( $monstring->getType() == 'kommune' ) {
        $frist2 = DateTime::createFromFormat('d.m.Y-H:i:s', $_POST['frist_2'].'-23:59:59');
    }
    // For fylker og nasjonalt er frist 2 dato for videresendingsåpning
    else {
        $frist2 = DateTime::createFromFormat('d.m.Y-H:i:s', $_POST['frist_2'].'-08:00:00');
    }

    if( isset($_POST['navn'] ) ) {
        $monstring->setNavn( $_POST['navn'] );
        global $blog_id;
        update_option( 'blogname', $_POST['navn']);
        update_option('blogdescription', ($monstring->getType() == 'fylke' ? '' : 'UKM ') . $_POST['navn']);
    }

    $monstring->setSted( $_POST['sted'] );
    $monstring->setStart( $start->getTimestamp() );
    $monstring->setStop( $stop->getTimestamp() );
    $monstring->setFrist1( $frist1->getTimestamp() );
    $monstring->setFrist2( $frist2->getTimestamp() );

    $monstring->getInnslagtyper()->getAll(); // laster de inn
    foreach( ['konferansier','nettredaksjon','arrangor','matkultur'] as $tilbud ) {
        if( !isset( $_POST['tilbud_'. $tilbud] ) ) {
            try {
                $monstring->getInnslagtyper()->fjern( innslag_typer::getByName($tilbud) );
            } catch( Exception $e ) {
                if( $e->getCode() != 110001 ) {
                    throw $e;
                }
            }
        } else {
            $monstring->getInnslagtyper()->leggTil( innslag_typer::getByName($tilbud) );
        }
    }

    try {
        write_monstring::save( $monstring );
        $response->text = 'Endringene er lagret!';
    } catch( Exception $e ) {
        $response->success = false;
        $response->text = 'Kunne ikke lagre. Systemet sa: '. $e->getMessage();
    }
    $TWIGdata['melding'] = $response;

    if( $_POST['goTo'] != 'monstring' ) {
        $CONTROLLER = 'kontakt';
        $_GET['kontakt'] = $_POST['goTo'];
    }


    if( $monstring->getType() == 'land' ) {
        foreach( UKMMonstring_sitemeta_storage() as $key ) {
			if( isset( $_POST[$key] ) ) {
				update_site_option('UKMFvideresending_'.$key.'_'.$monstring->getSesong(), $_POST[$key]);
			}
		}
    }
}

/**
 * LAGRE ENDRINGER ELLER OPPRETT KONTAKTPERSON
 */
if( $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['type'] == 'kontakt' ) {
    if( $_POST['id'] == 'new' ) {
        $kontakt = write_kontakt::create( $_POST['fornavn'], $_POST['etternavn'], $_POST['telefon'] );
    } else {
        $kontakt = new kontakt_v2( $_POST['id'] );
        $kontakt->setFornavn( $_POST['fornavn'] );
        $kontakt->setEtternavn( $_POST['etternavn'] );
        $kontakt->setTelefon( $_POST['telefon'] );
    }

    $kontakt->setTittel( $_POST['tittel'] );
    $kontakt->setEpost( $_POST['epost'] );
    $kontakt->setBilde( $_POST['image'] );
    $kontakt->setFacebook( $_POST['facebook'] );
    
    // Lagre kontaktpersonen i databasen
    try {
        write_kontakt::save( $kontakt );
    } catch( Exception $e ) {
        $response->success = false;
        $response->text = 'Kunne ikke lagre kontaktperson. Systemet sa: '. $e->getMessage() .' ('. $e->getCode() .')';
    }

    // Relater kontaktpersonen til mønstringen
    if( $response->success ) {
        try {
            $monstring->getKontaktpersoner()->leggTil( $kontakt );
            write_monstring::save( $monstring );
            $response->text = 'Kontaktpersonen er lagret!';
            $response->success = true;
        } catch( Exception $e ) {
            $response->success = false;
            $response->text = 'Kunne ikke legge til kontaktperson. Systemet sa: '. $e->getMessage() .' ('. $e->getCode() .')';
        }
    }

    $TWIGdata['melding'] = $response;
}
