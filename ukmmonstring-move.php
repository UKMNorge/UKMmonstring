<?php

// STORAGE KEYS for mønstringsinfo i site_meta
function UKMMonstring_sitemeta_storage()
{
    return [
        'avgift_subsidiert',
        'avgift_ordinar',
        'avgift_reise',
        'hotelldogn_pris',
        'kvote_ledere',
        'kvote_deltakere',
        'info1',
        'nominasjon_frister'
    ];
}

/**
 *	NETWORK ADMIN FUNCTIONS
 **/
function UKMmonstring_network_admin_rome_opprett()
{
    $_GET['action'] = 'opprett';
    return UKMmonstring_network_admin('romerriket');
}
function UKMmonstring_network_admin_rome_avlys()
{
    $_GET['action'] = 'avlys';
    return UKMmonstring_network_admin('romerriket');
}
function UKMmonstring_network_admin_rome_legg_til()
{
    $_GET['action'] = 'legg_til';
    return UKMmonstring_network_admin('romerriket');
}
function UKMmonstring_network_admin_rome_trekk_ut()
{
    $_GET['action'] = 'trekk_ut';
    return UKMmonstring_network_admin('romerriket');
}

function UKMmonstring_network_admin_undersider()
{
    $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : 'undersider';
    return UKMmonstring_network_admin('wordpress');
}

function UKMmonstring_network_admin($page = '')
{
    define('PLUGIN_DIR', dirname(__FILE__) . '/');
    $TWIGdata = ['UKM_HOSTNAME' => UKM_HOSTNAME];

    $folder = '/network/' . (!empty($page) ? basename($page) . '/' : '');

    $action = isset($_GET['action']) ? $_GET['action'] : 'home';
    $VIEW = $action;
    require_once('controller/' . $folder . $action . '.controller.php');

    echo TWIG($folder . $VIEW . '.html.twig', $TWIGdata, dirname(__FILE__), true);
}