<?php

use UKMNorge\Google\StaticMap;

require_once('UKM/kontakt.class.php');
require_once('UKM/write_kontakt.class.php');
require_once('UKM/write_monstring.class.php');
require_once('UKM/Google/StaticMap.php');

$start = DateTime::createFromFormat(
    'd.m.Y-H:i',
    $_POST['start'] . '-' . $_POST['start_time']
);
$stop = DateTime::createFromFormat(
    'd.m.Y-H:i',
    $_POST['stop'] . '-' . $_POST['stop_time']
);
$frist1 = DateTime::createFromFormat(
    'd.m.Y-H:i:s',
    $_POST['frist_1'] . '-23:59:59'
);

// For kommuner er frist 2 påmeldingsfrist for "bidra med noe-innslag"
$frist2_time = $arrangement->getType() == 'kommune' ? '23:59:59' : '08:00:00';
$frist2 = DateTime::createFromFormat(
    'd.m.Y-H:i:s',
    $_POST['frist_2'] . '-' . $frist2_time
);

if (isset($_POST['navn'])) {
    $arrangement->setNavn($_POST['navn']);
    global $blog_id;
    update_option(
        'blogname',
        $_POST['navn']
    );
    update_option(
        'blogdescription',
        ($arrangement->getType() == 'fylke' ? '' : 'UKM ') .
            $_POST['navn']
    );
}

// Google-map
$map = StaticMap::fromPOST('location');
$arrangement->setGoogleMapData($map->toJSON());

$arrangement->setSted($_POST['sted']);
$arrangement->setStart($start->getTimestamp());
$arrangement->setStop($stop->getTimestamp());
$arrangement->setFrist1($frist1->getTimestamp());
$arrangement->setFrist2($frist2->getTimestamp());

$arrangement->getInnslagtyper()->getAll(); // laster de inn
foreach (['konferansier', 'nettredaksjon', 'arrangor', 'matkultur', 'ressurs'] as $tilbud) {
    if (!isset($_POST['tilbud_' . $tilbud])) {
        try {
            $arrangement->getInnslagtyper()->fjern(innslag_typer::getByName($tilbud));
        } catch (Exception $e) {
            if ($e->getCode() != 110001) {
                throw $e;
            }
        }
    } else {
        $arrangement->getInnslagtyper()->leggTil(innslag_typer::getByName($tilbud));
    }
}

try {
    write_monstring::save($arrangement);
    UKMmonstring::getFlashbag()->add(
        'success',
        'Arrangement-info er lagret!'
    );
} catch (Exception $e) {
    UKMmonstring::getFlashbag()->add(
        'danger',
        'Kunne ikke lagre arrangement-info. Systemet sa: ' . $e->getMessage()
    );
}

if ($arrangement->getType() == 'land') {
    throw new Exception('Flytt meta-data!');
    foreach (UKMMonstring_sitemeta_storage() as $key) {
        if (isset($_POST[$key])) {
            update_site_option(
                'UKMFvideresending_' . $key . '_' . $arrangement->getSesong(),
                $_POST[$key]
            );
        }
    }
}
