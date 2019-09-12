<?php
/* 
Plugin Name: UKM M&oslash;nstring
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://www.ukm-norge.no
*/

use UKMNorge\Wordpress\Modul;

require_once('UKM/Wordpress/Modul.class.php');

class UKMmonstring extends Modul
{
    public static $action = 'monstring';
    public static $path_plugin = null;

    /**
     * Register hooks
     */
    public static function hook()
    {
        // Kun mønstringssider skal ha arrangement-meny
        if (is_numeric(get_option('pl_id'))) {
            add_action(
                'admin_menu',
                ['UKMmonstring', 'meny']
            );
        }

        add_action(
            'network_admin_menu',
            ['UKMmonstring', 'network_menu'],
            2000
        );

        add_action(
            'wp_ajax_UKMmonstring',
            ['UKMmonstring', 'ajax']
        );
    }

    /**
     * Rendre meny
     *
     */
    public static function meny()
    {
        $page = add_menu_page(
            'Arrangement',
            'Arrangement',
            'editor',
            'UKMmonstring',
            ['UKMMonstring', 'renderAdmin'],
            'dashicons-buddicons-groups',
            4
        );
        
        add_action(
            'admin_print_styles-' . $page,
            ['UKMmonstring', 'scripts_and_styles']
        );
    }

    /**
     * Scripts and styles for non-network admin
     *
     */
    public static function scripts_and_styles()
    {
        wp_enqueue_media();
        wp_enqueue_script('TwigJS');

        wp_enqueue_script(
            'UKMMonstring_script',
            plugin_dir_url(__FILE__)  . 'monstring.script.js'
        );
        wp_enqueue_style(
            'UKMMonstring_style',
            plugin_dir_url(__FILE__) . 'monstring.style.css'
        );

        wp_enqueue_script('jQuery_autogrow');
        wp_enqueue_script('WPbootstrap3_js');
        wp_enqueue_style('WPbootstrap3_css');
    }

    /**
     * Nettverksmeny
     *
     */
    public static function network_menu()
    {
        $page = add_menu_page(
            'Mønstringer',
            'Mønstringer',
            'superadmin',
            'UKMmonstring_network_admin',
            'UKMmonstring_network_admin',
            'dashicons-buddicons-groups', #//ico.ukm.no/hus-menu.png',
            23
        );
        $subpages[] = add_submenu_page(
            'UKMmonstring_network_admin', 
            'Opprett', 
            'Opprett', 
            'superadmin', 
            'UKMmonstring_network_admin_rome_opprett', 
            'UKMmonstring_network_admin_rome_opprett'
        );
        $subpages[] = add_submenu_page(
            'UKMmonstring_network_admin',
            'Avlys',
            'Avlys',
            'superadmin',
            'UKMmonstring_network_admin_rome_avlys',
            'UKMmonstring_network_admin_rome_avlys'
        );
        $subpages[] = add_submenu_page(
            'UKMmonstring_network_admin',
            'Legg til kommune',
            'Legg til kommune',
            'superadmin',
            'UKMmonstring_network_admin_rome_legg_til',
            'UKMmonstring_network_admin_rome_legg_til'
        );
        $subpages[] = add_submenu_page(
            'UKMmonstring_network_admin',
            'Trekk ut kommune',
            'Trekk ut kommune',
            'superadmin',
            'UKMmonstring_network_admin_rome_trekk_ut',
            'UKMmonstring_network_admin_rome_trekk_ut'
        );
        $subpages[] = add_submenu_page(
            'UKMmonstring_network_admin',
            'Kontroller undersider',
            'Kontroller undersider',
            'superadmin',
            'UKMmonstring_network_admin_undersider',
            'UKMmonstring_network_admin_undersider'
        );
    
        add_action('admin_print_styles-' . $page,     'UKMmonstring_network_script');
        foreach ($subpages as $page) {
            add_action('admin_print_styles-' . $page, 'UKMmonstring_network_script');
        }
    }
    
    /**
     * Nettverksmeny scripts og styles
     *
     * @return void
     */
    function UKMmonstring_network_script()
    {
        wp_enqueue_script('WPbootstrap3_js');
        wp_enqueue_style('WPbootstrap3_css');
    }
}

UKMmonstring::init(__DIR__);
UKMmonstring::hook();