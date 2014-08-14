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
    Genoo\Wordpress\Utils,
    Genoo\Wordpress\Filter,
    Genoo\ModalWindow,
    Genoo\HtmlForm,
    Genoo\Wordpress\Widget;


class Frontend
{

    /**
     * Constructor
     */

    public function __construct()
    {
        // init
        add_action('init', array($this, 'init'));
        // enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        // footer
        add_action('wp_footer', array($this, 'footerFirst'), 999);
        add_action('wp_footer', array($this, 'footerLast'), 1);
    }


    /**
     * Init, rewrite rules for mobiles
     */

    public function init()
    {
        add_filter('query_vars', function($query_vars){
            $query_vars[] = 'genooMobileWindow';
            return $query_vars;
        });
        add_action('parse_request', function($wp){
            if(array_key_exists('genooMobileWindow', $wp->query_vars)){
                Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                Frontend::renderMobileWindow();
            }
        });
        //Filter::add('the_content', array($this, 'content'), 0);
    }


    /**
     * Content
     *
     * @return mixed
     */

    public function content(){ if(is_single()){} }


    /**
     * Enqueue
     */

    public function enqueue()
    {
        // frontend css
        wp_enqueue_style('genooFrontend', GENOO_ASSETS . 'GenooFrontend.css', null, '1.6');
        // frontend js, if not a mobile window
        if(!isset($_GET['genooMobileWindow'])){
            wp_register_script('genooFrontendJs', GENOO_ASSETS . "GenooFrontend.js", false, '1.4.5', true);
            wp_enqueue_script('genooFrontendJs');
        }
    }


    /**
     * Footer first
     */

    public function footerFirst()
    {
        // tracking code
        if(GENOO_SETUP){
            $settings = new RepositorySettings();
            echo $settings->getTrackingCode();
        }
    }


    /**
     * Footer last
     */

    public function footerLast()
    {
        // prep
        $footerWidgets = Widget::getFooterModals();
        $footerModals = new ModalWindow();
        // footer widgtes
        if(!empty($footerWidgets)){
            // go thru widgers
            foreach($footerWidgets as $id => $widget){
                if(method_exists($widget->widget, 'getHtml')){
                    // prep
                    $modalGuts = $widget->widget->getHtml(array(), $widget->instance);
                    if(!empty($modalGuts)){
                        // inject hidden inputs first
                        $modalGutsInject = new HtmlForm($modalGuts);
                        $modalGutsInject->appendHiddenInputs(array('popup' => 'true', 'returnModalUrl' => ModalWindow::getReturnUrl($id)));
                        // inject message
                        $modalResult = ModalWindow::modalFormResult($id);
                        $repositorySettings = new RepositorySettings();
                        // do we have a result?
                        if(($modalResult == true || $modalResult == false) && (!is_null($modalResult))){
                            // instance messages
                            $widgetMsgSuccess = !empty($widget->instance['msgSuccess']) ? $widget->instance['msgSuccess'] : $repositorySettings->getSuccessMessage();
                            $widgetMsgFail = !empty($widget->instance['msgFail']) ? $widget->instance['msgFail'] : $repositorySettings->getFailureMessage();
                            if($modalResult == false){
                                $modalGutsInject->appendMsg($widgetMsgFail, $modalResult);
                            } elseif($modalResult == true) {
                                $modalGutsInject->appendMsg($widgetMsgSuccess, $modalResult);
                            }
                        }
                        // add html with injected values
                        $footerModals->addModalWindow($id, $modalGutsInject);
                    }
                }
            }
            echo $footerModals;
        }
    }


    /**
     * Render mobile window
     */

    public static function renderMobileWindow()
    {
        // simple template
        echo '<!DOCTYPE html><html class="genooFullPage"><head><meta charset="utf-8" />'
            .'<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">'
            .'<title>Subscribe</title>';
        wp_head();
        echo '</head><body class="genooMobileWindow">';
        wp_footer();
        echo '</body></html>';
        // kill it before WordPress displays his stuff
        exit();
    }
}