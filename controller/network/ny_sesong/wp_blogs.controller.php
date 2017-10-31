<?php
	
require_once('UKM/wp_blog.class.php');

$brukere = new stdClass();

foreach( wp_blog::getFylkesbrukere() as $brukernavn ) {
	
}