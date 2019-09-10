<?php

if (is_admin()) {
    if (in_array(get_option('site_type'), array('kommune', 'fylke', 'land'))) {
        add_action('admin_menu', 'UKMMonstring_menu');
        add_action('wp_ajax_UKMmonstring_save_kontaktpersoner', 'UKMmonstring_save_kontaktpersoner');
    }


    add_action('network_admin_menu', 'UKMmonstring_network_menu');
}


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

// GUI MØNSTRING 
function UKMMonstring()
{
    require_once('UKM/innslag_typer.class.php');
    require_once('UKM/monstring.class.php');
    $monstring = new monstring_v2(get_option('pl_id'));
    $TWIGdata = [];

    if (!is_super_admin() && date('m') > 6 && (int) $monstring->getSesong() <= (int) date('Y')) {
        echo TWIG('vent-til-ny-sesong.html.twig', $TWIGdata, dirname(__FILE__));
        return;
    } elseif (date('m') > 6 && (int) $monstring->getSesong() <= (int) date('Y')) {
        $TWIGdata['melding'] = new stdClass();
        $TWIGdata['melding']->success = false;
        $TWIGdata['melding']->text =
            'Redigering er kun mulig fordi du er logget inn som superadmin. ' .
            'Vanlige brukere får ikke opp skjema, da de venter på at ny sesong skal settes opp.';
    }

    $CONTROLLER = isset($_GET['kontakt']) ? 'kontakt' : 'monstring';
    // Might modify $CONTROLLER
    require_once('controller/monstring.save.php');

    require_once('controller/' . $CONTROLLER . '.controller.php');
    echo TWIG($CONTROLLER . '.html.twig', $TWIGdata, dirname(__FILE__));
}


function UKMmonstring_save_kontaktpersoner()
{
    require_once('ajax/order.save.php');
    die();
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


function informasjonsskjema()
{
    $option_name = 'videresending_info_pl' . get_option('pl_id');
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        $TWIG['saved'] = update_site_option($option_name, $_POST['videresending_editor']);
    }
    $TWIGdata = array('UKM_HOSTNAME' => UKM_HOSTNAME);
    echo TWIG('Informasjon/editor_pre.html.twig', $TWIGdata, dirname(__FILE__));
    wp_editor(stripslashes(get_site_option($option_name)), 'videresending_editor', $settings = array());
    echo TWIG('Informasjon/editor_post.html.twig', $TWIGdata, dirname(__FILE__));
}
