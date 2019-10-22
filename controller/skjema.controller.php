<?php

if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'skjema' ) {
    require_once('skjema.save.php');
}