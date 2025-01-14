<?php

// Opprett OmradeKontaktperson og legg til i området
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\OAuth2\ArrSys\HandleAPICallWithAuthorization;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Nettverk\OmradeKontaktperson;
use UKMNorge\Nettverk\WriteOmradeKontaktperson;
use UKMNorge\Nettverk\OmradeKontaktpersoner;

require_once('UKM/Autoloader.php');


$arrangement = new Arrangement(intval((get_option('pl_id'))));
$eierOmrade = $arrangement->getEierOmrade();
$arrangementOmrade = new Omrade('monstring', $arrangement->getId());

$tilgang = 'arrangement_i_kommune_fylke'; // Har tilgang til arrangement eller kommuner eller fylket arrangementet er opprettet id
$tilgangAttribute = $arrangement->getId();

// For å legge til en kontaktperson i området, trenger ikke tilgang til området siden alle kan bruke same kontaktpersoner
$foundMobil = null;
try {
    $foundMobil = HandleAPICallWithAuthorization::getArgumentBeforeInit('foundMobil', 'POST');
} catch(Exception $e) {
    // Kan skje når brukeren går tilbake, da sendes ikke foundMobil
    echo '<script>window.location.href = "?";</script>';
    exit();
}
if($foundMobil != 'null') {
    $okp = OmradeKontaktpersoner::getByMobil($foundMobil);
    connectOkpToOmrade($okp, $arrangementOmrade);
}
// Brukeren finnes ikke, opprett og legg til i området
else {   
    $handleCall = new HandleAPICallWithAuthorization(['fornavn', 'etternavn', 'mobil', 'epost', 'foundMobil'], ['beskrivelse'], ['POST'], false, false, $tilgang, $tilgangAttribute);

    $fornavn = $handleCall->getArgument('fornavn');
    $etternavn = $handleCall->getArgument('etternavn');
    $mobil = $handleCall->getArgument('mobil');
    $epost = $handleCall->getArgument('epost');
    $beskrivelse = $handleCall->getOptionalArgument('beskrivelse') ?? '';

    // Check mobil
    if(!preg_match('/^\d{8}$/', $mobil)) {
        HandleAPICallWithAuthorization::sendError('Mobilnummeret må være 8 siffer og kun tall', 400);
    }

    $okp = new OmradeKontaktperson(['id' => -1, 'fornavn' => $fornavn, 'etternavn' => $etternavn, 'mobil' => $mobil, 'epost' => $epost, 'beskrivelse' => $beskrivelse, 'eier_omrade_id' => $arrangement->getId(), 'eier_omrade_type' => 'monstring']);
    // Upload profile image
    WriteOmradeKontaktperson::uploadProfileImage($_FILES['profile_picture'], $okp, false);

    connectOkpToOmrade($okp, $arrangementOmrade);
}


function connectOkpToOmrade(OmradeKontaktperson $okp, Omrade $omrade) {
    try {
        WriteOmradeKontaktperson::leggTilOmradeKontaktperson($omrade, $okp);
    } catch(Exception $e) {
        HandleAPICallWithAuthorization::sendError($e->getMessage(), 400);
    }

    // echo '<script>window.location.href = "?page=UKMnettverket_'. $omrade->getType() .'&omrade='. $omrade->getForeignId() .'&type='. $omrade->getType() .'";</script>';
    echo '<script>window.location.href = "?";</script>';

    exit();
}
