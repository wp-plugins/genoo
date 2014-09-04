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

        $cta = false;
        if(is_array($postTypes) && !empty($typenow)){
            $cta = in_array($typenow, $postTypes);
        }

        /** Register external plugins */
        add_filter('mce_external_plugins', function($plugin_array) use($cta){
            $plugin_array['genoo'] = GENOO_ASSETS . 'GenooTinyMCEForm.js?refresh=8';
            if($cta) $plugin_array['genooCTA'] = GENOO_ASSETS . 'GenooTinyMCECTA.js?refres=8';
            return $plugin_array;
        });
        /** Register external buttons */
        add_filter('mce_buttons', function($buttons) use($cta){
            $buttons[] = 'genooForm';
            if($cta) $buttons[] = 'genooCTA';
            return $buttons;
        });
        /** Add editor style */
        add_editor_style(GENOO_ASSETS . 'GenooEditor.css?refresh=8');
    }
}