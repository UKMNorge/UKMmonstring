<?php

use UKMNorge\Arrangement\Arrangement;

$arrangement = new Arrangement(intval(get_option('pl_id')));

UKMmonstring::addViewData('arrangement', $arrangement);