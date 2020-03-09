<?php

/**
 * LAGRE ENDRINGER ELLER OPPRETT KONTAKTPERSON
 */

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Kontaktperson\Kontaktperson;
use UKMNorge\Arrangement\Kontaktperson\Write;
use UKMNorge\Arrangement\Write as ArrangementWrite;

require_once('UKM/Autoloader.php');
$arrangement = new Arrangement( intval(get_option('pl_id')) );

if ($_POST['id'] == 'new') {
    $kontakt = Write::create(
        $_POST['fornavn'],
        $_POST['etternavn'],
        $_POST['telefon']
    );
} else {
    $kontakt = new Kontaktperson((int)$_POST['id']);
    $kontakt->setFornavn($_POST['fornavn']);
    $kontakt->setEtternavn($_POST['etternavn']);
    $kontakt->setTelefon($_POST['telefon']);
}

$kontakt->setTittel($_POST['tittel']);
$kontakt->setEpost($_POST['epost']);
$kontakt->setBilde($_POST['image']);
$kontakt->setFacebook($_POST['facebook']);

// Lagre kontaktpersonen i databasen
try {
    Write::save($kontakt);

    if ($_POST['id'] == 'new') {
        $arrangement->getKontaktpersoner()->leggTil($kontakt);
        ArrangementWrite::save($arrangement);
        UKMmonstring::getFlashbag()->add(
            'success',
            'Kontaktpersonen er lagret!'
        );
    }
} catch (Exception $e) {
    $arrangement->getKontaktpersoner()->fjern($kontakt);
    UKMmonstring::getFlashbag()->add(
        'danger',
        'Kunne ikke lagre kontaktperson. Systemet sa: ' .
            $e->getMessage() . ' (' . $e->getCode() . ')'
    );
}
