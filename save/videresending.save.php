<?php


// Hvis arrangementet tar i mot videresending, lagre hvem fra
if ($arrangement->harVideresending()) {
    if (isset($_POST['avsender']) && is_array($_POST['avsender'])) {
        foreach ($_POST['avsender'] as $pl_id) {
            $avsender = new Avsender((int) $pl_id, $arrangement->getId());
            $arrangement->getVideresending()->leggTilAvsender($avsender);
        }
    }
}

// Skal noen ting nomineres?
if (isset($_POST['benyttNominasjon'])) {
    if ($_POST['benyttNominasjon'] == 'true') {
        $arrangement->setHarNominasjon(true);

        // Lagre alle "nominer_$something"-settings
        $count = 0;
        foreach ($_POST as $post_key => $post_value) {
            if (strpos($post_key, 'nominer_') === 0) {
                $har_nominering = $post_value == 'true';
                if ($har_nominering) {
                    $count++;
                }
                $type = Typer::getByKey(str_replace('nominer_', '', $post_key));
                $arrangement->setHarNominasjonFor($type, $har_nominering);
            }
        }
        if ($count == 0) {
            $arrangement->setHarNominasjon(false);
        }
    } else {
        $arrangement->setHarNominasjon(false);
    }
}

// Finnes det en nominasjonsinformasjon som skal lagres?
if (isset($_POST['nominasjon_informasjon'])) {
    $arrangement->setNominasjonInformasjon($_POST['nominasjon_informasjon']);
}

// Lagre diverse metadata hvis vi har tilgang på de
$metadata = [
    'harLederskjema',
    'ledere_per_deltakere',
    'ledere_per_deltakere_deltakere',
    'kvote_deltakere',
    'kvote_ledere',
    'pris_hotelldogn',
    'avgift_ordinar',
    'avgift_subsidiert',
    'avgift_reise',
    'navn_deltakerovernatting',
    'harDeltakerskjema'
];

foreach ($metadata as $meta_key) {
    if (isset($_POST[$meta_key])) {
        $value = $_POST[$meta_key];
        if ($value == 'false') {
            $value = false;
        } elseif ($value == 'true') {
            $value = true;
        }
        $arrangement->getMeta($meta_key)->set($value);
    }
}

// SKJEMA: for arrangement som videresender
$arrangement->setHarSkjema($_POST['vilHaSkjema'] == 'true');
if (isset($_POST['vilHaSkjema']) && $_POST['vilHaSkjema'] == 'true') {
    try {
        $skjema = $arrangement->getSkjema();
    } catch (Exception $e) {
        // Fant ikke skjema
        if ($e->getCode() == 151002) {
            $skjema = SkjemaWrite::createForArrangement($arrangement);
        } else {
            throw $e;
        }
    }
}
