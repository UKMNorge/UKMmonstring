<?php
require_once( PLUGIN_DIR . 'functions/network/ny_sesong/ukmdb.functions.php');

$seasons = getSeasonsData();
$TWIGdata['seasons'] 		= $seasons;
    