<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

namespace Genoo\Wordpress;


class TinyMCE
{
    /**
     * Register, extends TinyMCE
     */

    public static function register()
    {
        add_action('init', array(__CLASS__, 'extend'));
    }


    /**
     * Extend
     */

    public static function extend()
    {
        /** Register external plugins */
        add_filter('mce_external_plugins', function($plugin_array){
            $plugin_array['genoo'] = GENOO_ASSETS . 'GenooTinyMCE.js?ref';
            return $plugin_array;
        });
        /** Register external buttons */
        add_filter('mce_buttons', function($buttons){
            $buttons[] = 'genooForm';
            return $buttons;
        });
        /** Add editor style */
        add_editor_style(GENOO_ASSETS . 'GenooEditor.css');
    }
}