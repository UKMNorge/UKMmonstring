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

require_once('UKM/Autoloader.php');

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
        
        /*
        $page = add_submenu_page(
            'index.php',
            'Arrangement',
            'Arrangement',
            'editor',
            'UKMmonstring',
            ['UKMMonstring', 'renderAdmin'],
            'dashicons-buddicons-groups',
            4
        );
        */
        
        add_action(
            'admin_print_styles-index.php',
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
            UKMmonstring::getPluginUrl()  . 'monstring.script.js'
        );
        wp_enqueue_style(
            'UKMMonstring_style',
            UKMmonstring::getPluginUrl() . 'monstring.style.css'
        );
        wp_enqueue_script('jQuery_autogrow');
        wp_enqueue_script('WPbootstrap3_js');
        wp_enqueue_style('WPbootstrap3_css');
    }
}

UKMmonstring::init(__DIR__);
UKMmonstring::hook();