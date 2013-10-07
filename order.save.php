<?php
$pl_id = get_option('pl_id');

$i = 0;

foreach($_POST['order'] as $contact) {
	$i++;
	$id = (int) $contact;
	if(empty($id))
		continue;
	# UPDATE ORDER
	$qry = new SQLins('smartukm_rel_pl_ab',
					  array('pl_id'=>$pl_id,
					  		'ab_id'=>$id));
	$qry->add('order', $i);
	$res = $qry->run();
}
