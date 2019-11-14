<?php

require_once('UKM/Autoloader.php');

use UKMNorge\Arrangement\Arrangement;

$arrangement = new Arrangement( get_option('pl_id') );
$tekst = $arrangement->getInformasjonstekst();

$TWIGdata = [
    'UKM_HOSTNAME' => UKM_HOSTNAME,
    'flashbag' => UKMmonstring::getFlashbag()
];
echo TWIG(
    'Informasjon/editor_pre.html.twig',
    $TWIGdata,
    UKMmonstring::getPluginPath()
);
wp_editor(
    stripslashes( $tekst ),
    'infotekst'
);
echo TWIG(
    'Informasjon/editor_post.html.twig',
    $TWIGdata,
    UKMmonstring::getPluginPath()
);

do_action('admin_print_footer_scripts');