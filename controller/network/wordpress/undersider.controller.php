<?php

if( !isset( $_GET['offset'] ) ) {
    $_GET['offset'] = 0;
}

$TWIGdata['sites_per_load'] = 4;
$TWIGdata['offset'] = $_GET['offset'];