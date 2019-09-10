<?php

/**
 * LAGRE ENDRINGER ELLER OPPRETT KONTAKTPERSON
 */

require_once('UKM/write_kontakt.class.php');
require_once('UKM/write_monstring.class.php');

if ($_POST['id'] == 'new') {
    $kontakt = write_kontakt::create(
        $_POST['fornavn'],
        $_POST['etternavn'],
        $_POST['telefon']
    );
} else {
    $kontakt = new kontakt_v2($_POST['id']);
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
    write_kontakt::save($kontakt);

    if ($_POST['id'] == 'new') {
        $arrangement->getKontaktpersoner()->leggTil($kontakt);
        write_monstring::save($arrangement);
        UKMmonstring::getFlashbag()->add(
            'success',
            'Kontaktpersonen er lagret!'
        );
    }
} catch (Exception $e) {
    UKMmonstring::getFlashbag()->add(
        'danger',
        'Kunne ikke lagre kontaktperson. Systemet sa: ' .
            $e->getMessage() . ' (' . $e->getCode() . ')'
    );
}
