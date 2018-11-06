<?php

require_once('undersider.controller.php');
require_once('UKM/write_wp_blog.class.php');

$sites = get_sites( [
    'offset' => $_GET['offset'],
    'number' => $TWIGdata['sites_per_load']
]);

foreach( $sites as $site ) {
    $type = get_blog_option( $site->blog_id, 'site_type' );
    
    if( !in_array( $type, ['kommune','fylke']) ) {
        continue;
    }
    
    write_wp_blog::setDefaultContent( $site->blog_id, $type );
    $TWIGdata['sites'][] = $site;
} 
$TWIGdata['offset'] += $TWIGdata['sites_per_load'];