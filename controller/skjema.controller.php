<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Skjema\Write as WriteSkjema;

$arrangement = new Arrangement( intval(get_option('pl_id')));

// Opprett et skjema for mønstringen hvis den ikke har det
if (!$arrangement->getSkjema()) { 
    $skjema = WriteSkjema::create( $arrangement );
} else {
    $skjema = $arrangement->getSkjema();
}

if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'skjema' ) {
    require_once('skjema.save.php');
}