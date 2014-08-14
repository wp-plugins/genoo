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
    Genoo\WidgetCTA,
    Genoo\Cache,
    Genoo\Api,
    Genoo\ModalWindow,
    Genoo\HtmlForm,
    Genoo\Wordpress\Utils,
    Genoo\Utils\Strings;


class Shortcodes
{
    /* Shortcode get parameter */
    const SHORTCODE_ID = 'gs';


    /**
     * Register shortcodes
     */

    public static function register()
    {
        add_shortcode('genooForm',  array(__CLASS__, 'form'));
        add_shortcode('genooLumen', array(__CLASS__, 'lumen'));
        //add_shortcode('genooCTA',   array(__CLASS__, 'cta'));
        //add_shortcode('genooCta',   array(__CLASS__, 'cta'));
    }


    /**
     * Return url for shortcode
     *
     * @param $id
     * @return mixed
     */

    public static function getReturnUrlShortcode($id)
    {
        return Utils::addQueryParams(ModalWindow::closeUrl(),array(self::SHORTCODE_ID => self::getShortcodeId($id)));
    }


    /**
     * Get shortcode id
     *
     * @param $id
     * @return mixed
     */

    public static function getShortcodeId($id)
    {
        return str_replace('-', '', self::SHORTCODE_ID . Strings::firstUpper($id));
    }


    /**
     * Is modal visible, static
     *
     * @param $id
     * @return bool
     */

    public static function isShortcodeVisible($id)
    {
        $modalId = self::getShortcodeId($id);
        if((isset($_GET[self::SHORTCODE_ID]) && $_GET[self::SHORTCODE_ID] == $modalId)){
            return true;
        }
        return false;
    }


    /**
     * Shortcode form result
     *
     * @param $id
     * @return bool|null
     */

    public static function shortcoeFormResult($id)
    {
        if(self::isShortcodeVisible($id)){
            if(isset($_GET['formResult'])){
                if($_GET['formResult'] == 'true'){
                    return true;
                } elseif($_GET['formResult'] == 'false'){
                    return false;
                }
            }
        }
        return null;
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
            // prep
            $repositorySettings = new RepositorySettings();
            $repositoryForms = new RepositoryForms(new Cache(GENOO_CACHE), new Api($repositorySettings));
            $formId = !empty($atts['id']) && is_numeric($atts['id']) ? $atts['id'] : null;
            $formIdFinal = is_null($formId) ? $repositorySettings->getActiveForm() : $formId;
            $formTheme = !empty($atts['theme']) ? $atts['theme'] : $repositorySettings->getActiveTheme();
            $formSuccess = !empty($atts['msgsuccess']) ? $atts['msgsuccess'] : null;
            $formFail = !empty($atts['msgfail']) ? $atts['msgfail'] : null;
            // do we have a form ID?
            if(!empty($formIdFinal)){
                // prep html
                $h = '<div class="genooForm genooShortcode themeResetDefault '. $formTheme .'"><div class="genooGuts"><div id="genooMsg"></div>';
                $h .= $repositoryForms->getForm($formIdFinal);
                $h .= '</div></div>';
                // id
                $id = $formIdFinal;
                // inject inputs and message
                $inject = new HtmlForm($h);
                if(!empty($formSuccess) && !empty($formFail)){
                    $inject->appendHiddenInputs(array('popup' => 'true','returnModalUrl' => self::getReturnUrlShortcode($id)));
                }
                $result = self::shortcoeFormResult($id);
                // do we have a result?
                if(($result == true || $result == false) && (!is_null($result))){
                    if($result == false){
                        $inject->appendMsg($formFail, $result);
                    } elseif($result == true) {
                        $inject->appendMsg($formSuccess, $result);
                    }
                }
                // return html
                return $inject;
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


    /**
     * Genoo CTA
     *
     * @param $atts
     */

    public static function cta($atts)
    {
        global $post;
        if($post instanceof \Wp_Post){
            $widget = new WidgetCTA(false);
            $widget->widget(array(), array());
        }
    }
};