<?php
require_once( PLUGIN_DIR . 'functions/network/ny_sesong/ukmdb.functions.php');

$seasons = getSeasonsData();

update_site_option('season', $seasons->new->year );
update_blog_option(1, 'season', $seasons->new->year );

$TWIGdata['seasons'] = $seasons;