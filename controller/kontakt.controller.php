<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\OAuth2\ArrSys\HandleAPICallWithAuthorization;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Nettverk\OmradeKontaktperson;
use UKMNorge\Nettverk\WriteOmradeKontaktperson;
use UKMNorge\Nettverk\OmradeKontaktpersoner;
use UKMNorge\OAuth2\ArrSys\AccessControlArrSys;

require_once('UKM/Autoloader.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arrangement = new Arrangement(intval((get_option('pl_id'))));
    $omrade = $arrangement->getEierOmrade();

    $tilgang = 'kommune_eller_fylke'; // Har tilgang til kommunen eller fylket
    $tilgangAttribute = $omrade->getForeignId();

    $handleCall = new HandleAPICallWithAuthorization(['okpId', 'fornavn', 'mobil', 'etternavn'], ['epost', 'beskrivelse', 'deletedProfileImage'], ['POST'], false, false, $tilgang, $tilgangAttribute);

    $id = $handleCall->getArgument('okpId');
    $fornavn = $handleCall->getArgument('fornavn');
    $etternavn = $handleCall->getArgument('etternavn');
    $mobil = $handleCall->getArgument('mobil');
    $epost = $handleCall->getOptionalArgument('epost');
    $beskrivelse = $handleCall->getOptionalArgument('beskrivelse') ?? '';
    $deletedProfileImage = $handleCall->getOptionalArgument('deletedProfileImage') == 'true' ? true : false;

    try {
        $okp = OmradeKontaktpersoner::getByMobil($mobil);
        $okp->setFornavn($fornavn);
        $okp->setEtternavn($etternavn);
        $okp->setEpost($epost);
        $okp->setBeskrivelse($beskrivelse);
        
        WriteOmradeKontaktperson::uploadProfileImage($_FILES['profile_picture'], $okp, $deletedProfileImage);
        WriteOmradeKontaktperson::editOmradekontaktperson($okp);
    } catch(Exception $e) {
        HandleAPICallWithAuthorization::sendError($e->getMessage(), 400);
    }

    echo '<script>window.location.href = "?";</script>';
    exit();
}
else {
    $okpId = HandleAPICallWithAuthorization::getArgumentBeforeInit('okpId', 'GET');
    $okp = OmradeKontaktpersoner::getById($okpId);

    $omrade = new Omrade($okp->getEierOmradeType(), $okp->getEierOmradeId());
    if(!AccessControlArrSys::hasOmradeAccess($omrade)) {
        UKMnettverket::addViewData('omrade', $omrade);
        UKMnettverket::addViewData('tilgang', false);
    }
    else {
        showUser($okp);
    }
}

function showUser(OmradeKontaktperson $okp) {
    $omrade = new Omrade($okp->getEierOmradeType(), $okp->getEierOmradeId());

    UKMnettverket::addViewData('tilgang', true);
    UKMnettverket::addViewData('omradekontaktperson', $okp);
    UKMnettside::addViewData('omrade', $omrade);

    UKMmonstring::addViewData(
        'kontakt',
        $okp
    );

}