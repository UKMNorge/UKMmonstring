<?php

use UKMNorge\Database\SQL\Update;

$pl_id = get_option('pl_id');

$i = 0;
foreach( $_POST['order'] as $contact_id ) {
    $i++;
    if( empty( $contact_id ) ) {
        continue;
    }
    
    # UPDATE ORDER
	$qry = new Update(
        'smartukm_rel_pl_ab',
        [
            'pl_id' => $pl_id,
            'ab_id' => str_replace('kontakt_','', $contact_id)
        ]
    );
    $qry->add('order', $i);
	$res = $qry->run();
}

UKMmonstring::addResponseData('success',true);