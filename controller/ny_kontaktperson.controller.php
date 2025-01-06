<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\OAuth2\ArrSys\HandleAPICallWithAuthorization;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Nettverk\OmradeKontaktperson;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement(intval((get_option('pl_id'))));
$eierOmrade = $arrangement->getEierOmrade();

$tilgang = 'arrangement_i_kommune_fylke'; // Har tilgang til arrangement eller kommuner eller fylket arrangementet er opprettet id
$tilgangAttribute = $arrangement->getId();

$handleCall = new HandleAPICallWithAuthorization([], [], ['GET'], false, false, $tilgang, $tilgangAttribute);

$omradeKontaktperson = OmradeKontaktperson::createEmpty();


UKMnettverket::addViewData('omradekontaktperson', $omradeKontaktperson);
UKMnettverket::addViewData('omrade', $eierOmrade);
