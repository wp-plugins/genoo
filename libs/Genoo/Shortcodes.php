<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

namespace Genoo;

use Genoo\RepositorySettings,
    Genoo\RepositoryForms,
    Genoo\RepositoryLumens,
    Genoo\Cache,
    Genoo\Api,
    Genoo\Utils\Strings;


class Shortcodes
{

    /**
     * Register shortcodes
     */

    public static function register()
    {
        add_shortcode('genooForm',  array(__CLASS__, 'form'));
        add_shortcode('genooLumen', array(__CLASS__, 'lumen'));
    }


    /**
     * Forms
     *
     * @param $atts
     * @return null|string
     */

    public static function form($atts)
    {
        try {
            $repositorySettings = new RepositorySettings();
            $repositoryForms = new RepositoryForms(new Cache(GENOO_CACHE), new Api($repositorySettings));
            $formId = !empty($atts['id']) && is_numeric($atts['id']) ? $atts['id'] : null;
            $formIdFinal = is_null($formId) ? $repositorySettings->getActiveForm() : $formId;
            $formTheme = !empty($atts['theme']) ? $atts['theme'] : $repositorySettings->getActiveTheme();
            if(!empty($formIdFinal)){
                return '<div class="genooForm themeResetDefault '. $formTheme .'"><div class="genooGuts">' . $repositoryForms->getForm($formIdFinal) . '</div></div>';
            }
        } catch (\Exception $e){
            return null;
        }
    }


    /**
     * Lumen class list
     *
     * @param $atts
     * @return null|string
     */

    public static function lumen($atts)
    {
        try {
            $repositorySettings = new RepositorySettings();
            $repositoryLumens = new RepositoryLumens(new Cache(GENOO_CACHE), new Api($repositorySettings));
            $formId = !empty($atts['id']) && is_numeric($atts['id']) ? $atts['id'] : null;
            if(!is_null($formId)){
                return '<div class="themeResetDefault"><div class="genooGuts">' . $repositoryLumens->getLumen($formId) . '</div></div>';
            }
        } catch (\Exception $e){
            return null;
        }
    }
}