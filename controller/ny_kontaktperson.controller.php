<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\OAuth2\ArrSys\HandleAPICallWithAuthorization;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Nettverk\OmradeKontaktperson;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement(intval((get_option('pl_id'))));
$omrade = $arrangement->getEierOmrade();

$tilgang = 'kommune_eller_fylke'; // Har tilgang til kommunen eller fylket
$tilgangAttribute = $omrade->getForeignId();

$handleCall = new HandleAPICallWithAuthorization([], [], ['GET'], false, false, $tilgang, $tilgangAttribute);

$omradeKontaktperson = OmradeKontaktperson::createEmpty();


UKMnettverket::addViewData('omradekontaktperson', $omradeKontaktperson);
UKMnettverket::addViewData('omrade', $omrade);
