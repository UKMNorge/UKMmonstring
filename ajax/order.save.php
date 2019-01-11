<?php

$pl_id = get_option('pl_id');

$i = 0;
foreach( $_POST['order'] as $contact_id ) {
    $i++;
    if( empty( $contact_id ) ) {
        continue;
    }
    
    # UPDATE ORDER
	$qry = new SQLins(
        'smartukm_rel_pl_ab',
        [
            'pl_id' => $pl_id,
            'ab_id' => $contact_id
        ]
    );
    $qry->add('order', $i);
    echo $qry->debug();
	$res = $qry->run();
}