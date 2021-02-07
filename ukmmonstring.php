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
                ['UKMmonstring', 'meny'],
                1000
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
        try {
            $arrangement = static::getArrangement();
            $page = add_submenu_page(
                'index.php',
                'Videresending',
                'Videresending',
                'editor',
                'UKMarrangement_videresending',
                ['UKMMonstring', 'renderAdminVideresending'],
                40
            );
            add_action(
                'admin_print_styles-'.$page,
                ['UKMmonstring', 'scripts_and_styles']
            );
        } catch( Exception $e ) {
            // Do nothing: skal ikke ha den uansett da
        }
        
        add_action(
            'admin_print_styles-index.php',
            ['UKMmonstring', 'scripts_and_styles']
        );
    }

    public static function renderAdminVideresending() {
        static::setAction('videresending');
        return static::renderAdmin();
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
            'UKMMonstring_script_monstring',
            UKMmonstring::getPluginUrl()  . 'js/monstring.js'
        );
        wp_enqueue_script(
            'UKMMonstring_script_kontaktpersoner',
            UKMmonstring::getPluginUrl()  . 'js/kontaktpersoner.js'
        );
        wp_enqueue_script(
            'UKMMonstring_script_videresending',
            UKMmonstring::getPluginUrl()  . 'js/videresending.js'
        );
        wp_enqueue_script(
            'UKMMonstring_script_skjema',
            UKMmonstring::getPluginUrl()  . 'js/skjema.js'
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