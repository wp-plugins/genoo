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

    public static function register($postTypes = array())
    {
        global $typenow, $pagenow, $wp_screen;
        if(empty($typenow) && !empty($_GET['post'])){
            $post = get_post( $_GET['post'] );
            $typenow = $post->post_type;
        }

        // Cta?
        $cta = false;
        if(is_array($postTypes) && !empty($typenow)){
            $cta = in_array($typenow, $postTypes);
        }

        // Register external plugins
        Filter::add('mce_external_plugins', function($plugin_array) use($cta){
            // Form
            $plugin_array['genooForm'] = GENOO_ASSETS . 'GenooTinyMCEForm.js?v=' . GENOO_REFRESH;
            // CTA
            if($cta) $plugin_array['genooCTA'] = GENOO_ASSETS . 'GenooTinyMCECTA.js?v=' . GENOO_REFRESH;
            // Lumens
            if(GENOO_LUMENS) $plugin_array['genooLumens'] = GENOO_ASSETS . 'GenooTinyMCELumens.js?v=' . GENOO_REFRESH;

            return $plugin_array;
        }, 10, 1);

        // Register external buttons
        Filter::add('mce_buttons', function($buttons) use($cta){
            // Form
            $buttons[] = 'genooForm';
            // CTA
            if($cta) $buttons[] = 'genooCTA';
            // Lumens
            if(GENOO_LUMENS) $buttons[] = 'genooLumens';

            return $buttons;
        }, 10, 1);

        // Add editor style
        add_editor_style(GENOO_ASSETS . 'GenooEditor.css?v=' . GENOO_REFRESH);
    }
}