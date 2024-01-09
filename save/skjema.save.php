<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Eier;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Arrangement\Skjema\Sporsmal;
use UKMNorge\Arrangement\Skjema\Write as WriteSkjema;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement( intval(get_option('pl_id')) );
if( $_POST['form_type'] == 'person' ) {
    $skjema = $arrangement->getDeltakerSkjema();
} else {
    $skjema = $arrangement->getSkjema();
}

// For alle slettede spørsmål, fjern de fra databasen også. Gjør dette _før_ vi lagrer ny rekkefølge.
if( isset($_POST['slettede_sporsmal']) ) {
    foreach ($_POST['slettede_sporsmal'] as $sporsmal_id) {
        try {        
            WriteSkjema::fjernSporsmalFraSkjema($sporsmal_id, $skjema->getId());
        } catch( Exception $e ) {
			throw new("Klarte ikke å fjerne spørsmål. Systemet sa: ".$e->getMessage().". Kode: ".$e->getCode());
        }
    }    
}

$rekkefolge = 0;
if( is_array($_POST['sporsmal_type'])) {
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
}