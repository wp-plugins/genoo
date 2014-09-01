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
    Genoo\CTA,
    Genoo\Cache,
    Genoo\Api,
    Genoo\ModalWindow,
    Genoo\HtmlForm,
    Genoo\Wordpress\Utils,
    Genoo\Utils\Strings,
    Genoo\Wordpress\Post;


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
        add_shortcode('genooCTA',   array(__CLASS__, 'cta'));
        add_shortcode('genooCta',   array(__CLASS__, 'cta'));
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
        // get post
        global $post;
        // set static counter
        static $count = 1;
        try {
            if($post instanceof \Wp_Post){
                if($atts['id'] && is_numeric($atts['id']) && Post::exists($atts['id'])){
                    // get CTA
                    $cta = new WidgetCTA();
                    $cta->setThroughShortcode($count, $atts['id'], $atts);
                    // increase id
                    $count++;
                    return $cta->getHtmlInner(array(), $cta->getInnerInstance());
                }
            }
        } catch (\Exception $e){
            return null; // nice catching
        }
    }


    /**
     * Get Shortcodes From Content
     *
     * @param $content
     * @param null $shortcode
     * @return array|bool
     */

    public static function getFromContent($content, $shortcode = null)
    {
        // Dont give back null
        $matches = array();
        $r = array();
        // Check if in post
        if (false === strpos($content, '[')){ return false; }
        // Parse Shortcodes from content
        $matches = self::findShortcodes($content);
        // Purify the result
        $count = 1;
        if($matches){
            foreach($matches as $match){
                // Arguments
                array_filter($match);
                $match = array_map('trim', $match);
                $matchLast = end($match);
                $actualShortcode = $match[0];
                $args = shortcode_parse_atts(str_replace(array('[',']'),'', $actualShortcode));
                // is shortcode set?
                if($shortcode){
                    // Is it here?
                    if(Strings::contains(Strings::lower($args[0]), Strings::lower($shortcode))){
                        $r[$count] = $args;
                        ++$count;
                    } else if (Strings::contains($matchLast, $shortcode)){
                        // Might be wrapped in another Shortcode
                        $tryFinding = self::findRecrusively($matchLast, $shortcode);
                        if(is_array($tryFinding)){
                            $r[$count] = $tryFinding;
                            ++$count;
                        }
                    }
                } else {
                    $r[$count] = $args;
                    ++$count;
                }
            }
        }
        return $r;
    }


    /**
     * Find inside shortcodes
     *
     * @param $shortCodeData
     * @param $shortcodeSearch
     * @return null
     */

    public static function findRecrusively($shortCodeData, $shortcodeSearch)
    {
        $matches = self::findShortcodes($shortCodeData);
        // Prep data
        $shortcodeActual = $matches[0][0];
        $shortcodeActualParsed = shortcode_parse_atts(str_replace(array('[',']'),'', $shortcodeActual));
        reset($matches[0]);
        $shortcodeLast = end($matches[0]);
        if(is_array($shortcodeActualParsed)){
            // Presuming this has the shortcode.
            $shortcode = $shortcodeActualParsed[0];
            if(Strings::contains(Strings::lower($shortcode), Strings::lower($shortcodeSearch))){
                return $shortcodeActualParsed;
            } elseif (Strings::contains($shortcodeLast, $shortcodeSearch)){
                return self::findRecrusively($shortcodeLast, $shortcodeSearch);
            }
            return null;
        }
        return null;
    }


    /**
     * Remove empty arrays
     *
     * @param $sr
     * @return mixed
     */

    public static function findShortcodes($sr)
    {
        preg_match_all('/' . get_shortcode_regex() . '/s', $sr, $arr, PREG_SET_ORDER);
        if(is_array($arr)){
            foreach($arr as $key => $value){
                if(is_array($value)){
                    foreach($value as $keyIn => $valueIn){
                        if(strlen($valueIn) == 0 || empty($valueIn) || $valueIn == '0'){
                            unset($arr[$key][$keyIn]);
                        }
                    }
                } else {
                    if(strlen($value) == 0 || empty($value) || $value == '0'){
                        unset($arr[$key]);
                    }
                }
            }
        }
        return $arr;
    }


    /**
     * Get footer CTA modals for current post
     *
     * @return array
     */

    public static function getFooterCTAs()
    {
        global $post;
        $r = array();
        // Do we have a post?
        if($post){
            // Get shortcodes from content
            $shorcodes = self::getFromContent($post->post_content, 'genooCTA');
            if($shorcodes){
                // Go through shortcodes
                foreach($shorcodes as $id => $atts){
                    // Do we have CTA ID and does it exist?
                    if($atts['id'] && is_numeric($atts['id']) && Post::exists($atts['id'])){
                        // get CTA
                        $cta = new WidgetCTA();
                        $cta->setThroughShortcode($id, $atts['id']);
                        // PUt in array if it is form CTA
                        if($cta->cta->isForm){
                            $r[$cta->id] = new \stdClass();
                            $r[$cta->id]->widget = $cta;
                            $r[$cta->id]->instance = $cta->getInnerInstance();
                        }
                    }
                }
            }
        }
        return $r;
    }
};