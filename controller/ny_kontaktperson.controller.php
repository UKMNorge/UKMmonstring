<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\OAuth2\ArrSys\HandleAPICallWithAuthorization;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Nettverk\OmradeKontaktperson;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement(intval((get_option('pl_id'))));
$eierOmrade = $arrangement->getEierOmrade();

$tilgang = $eierOmrade->getType() == 'kommune' ? 'kommune_eller_fylke' : 'fylke';
$tilgangAttribute = $eierOmrade->getForeignId();

$handleCall = new HandleAPICallWithAuthorization([], [], ['GET'], false, false, $tilgang, $tilgangAttribute);

$omradeKontaktperson = OmradeKontaktperson::createEmpty();


UKMnettverket::addViewData('omradekontaktperson', $omradeKontaktperson);
UKMnettverket::addViewData('omrade', $eierOmrade);
