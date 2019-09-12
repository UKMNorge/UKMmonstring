<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Eier;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Arrangement\Skjema\Sporsmal;
use UKMNorge\Arrangement\Skjema\Write as WriteSkjema;

require_once('UKM/Arrangement/Arrangement.php');
require_once('UKM/Arrangement/Skjema/Skjema.php');
require_once('UKM/Arrangement/Skjema/Sporsmal.php');
require_once('UKM/Arrangement/Skjema/Write.php');

if( !isset($arrangement)) {
    $arrangement = new Arrangement( get_option('pl_id') );
}
// Opprett et skjema for mønstringen hvis den ikke har det
if (!$arrangement->getSkjema()) { 
    $skjema = WriteSkjema::create( $arrangement );
} else {
    $skjema = $arrangement->getSkjema();
}

$rekkefolge = 0;
foreach ($_POST['sporsmal_type'] as $post_id => $type) {
    $rekkefolge++;
    $id = $_POST['sporsmal_id'][$post_id];
    $tittel = $_POST['sporsmal_tittel'][$post_id];
    $tekst = $_POST['sporsmal_tekst'][$post_id];

    if (empty($id) || !is_numeric($id)) {
        $sporsmal = WriteSkjema::createSporsmal($skjema, $rekkefolge, $type, $tittel, $tekst);
    } else {
        $sporsmal = new Sporsmal(
            (Int) $id,
            $skjema->getId(),
            $rekkefolge,
            $type,
            $tittel,
            $tekst
        );
        WriteSkjema::saveSporsmal( $sporsmal );
    }
}