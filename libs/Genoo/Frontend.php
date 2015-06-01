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
    Genoo\Wordpress\Action,
    Genoo\ModalWindow,
    Genoo\HtmlForm,
    Genoo\Wordpress\Widgets;
use Genoo\Wordpress\Debug;
use Genoo\Wordpress\Post;


class Frontend
{
    /** @var RepositorySettings */
    var $repositorySettings;
    /** @var array */
    var $footerCTAModals = array();

    /**
     * Constructor
     */

    public function __construct(RepositorySettings $repositorySettings)
    {
        // Settings
        $this->repositorySettings = $repositorySettings;
        // Init
        Action::add('init',  array($this, 'init'));
        // wp
        Action::add('wp',    array($this, 'wp'), 10, 1);
        // Enqueue scripts
        Action::add('wp_enqueue_scripts', array($this, 'enqueue'));
        // Footer
        Action::add('wp_footer', array($this, 'footerFirst'), 999);
        Action::add('wp_footer', array($this, 'footerLast'), 1);
        Action::add('shutdown', array($this, 'shutdown'), 10, 1);
    }


    /**
     * Init, rewrite rules for mobiles windows
     */

    public function init()
    {
        Filter::add('query_vars', function($query_vars){
            $query_vars[] = 'genooMobileWindow';
            return $query_vars;
        }, 10, 1);
        Action::add('parse_request', function($wp){
            // If is mobile window
            if(array_key_exists('genooMobileWindow', $wp->query_vars)){
                // Only when query parsed do this
                Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                Frontend::renderMobileWindow();
            }
        });
        Widgets::refreshDynamic();
    }


    /**
     * On Wp, let's register our CTA widgets,
     * if they are present
     *
     * @param $wp
     */

    public function wp($wp)
    {
        // Global post
        global $post;
        // Do we have a post
        if($post instanceof \WP_Post){
            // We only run this on single posts
            if((Post::isSingle() || Post::isPage()) && Post::isPostType($post, $this->repositorySettings->getCTAPostTypes())){
                // Dynamic cta
                $cta = new CTADynamic($post);
                // If the post does have multiple ctas, continue
                if($cta->hasMultiple()){
                    // Set we have multiple CTAs
                    $this->hasMultipleCTAs = true;
                    // Get CTA's
                    $ctas = $cta->getCtas();
                    $ctasRegister = $cta->getCtasRegister();
                    // Injects widgets, registers them
                    $ctasWidgetsRegistered = Widgets::injectRegisterWidgets($ctasRegister);
                    // Save for footer print
                    $this->footerCTAModals = $ctasWidgetsRegistered;
                    // Repositions them
                    Widgets::injectMultipleIntoSidebar($ctasWidgetsRegistered);
                    // Pre-option values
                    Widgets::injectMultipleValues($ctasWidgetsRegistered);
                }
            }
        }
    }


    /**
     * Enqueue
     */

    public function enqueue()
    {
        // Frontend css
        wp_enqueue_style('genooFrontend', GENOO_ASSETS . 'GenooFrontend.css', null, GENOO_REFRESH);
        // Frontend js, if not a mobile window
        if(!isset($_GET['genooMobileWindow'])){
            wp_register_script('genooFrontendJs', GENOO_ASSETS . "GenooFrontend.js", false, GENOO_REFRESH, true);
            wp_enqueue_script('genooFrontendJs');
        }
    }


    /**
     * Footer first
     */

    public function footerFirst()
    {
        // Tracking code
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
        // Prep
        $footerWidgetForms = Widgets::getFooterModals();
        $footerWidgetsDynamicForms = Widgets::getFooterDynamicModals($this->footerCTAModals);
        $footerShortcodeForms = Shortcodes::getFooterCTAs();
        $footerForms = $footerWidgetForms + $footerWidgetsDynamicForms +  $footerShortcodeForms;
        $footerModals = new ModalWindow();
        // footer widgtes
        if(!empty($footerForms)){
            // go through widgers
            foreach($footerForms as $id => $widget){
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
            // print it out
            echo $footerModals;
        }
    }


    /**
     * Render mobile window
     */

    public static function renderMobileWindow()
    {
        // Simple template
        echo '<!DOCTYPE html>'
            .'<html class="genooFullPage">'
            .'<head>'
            .'<meta charset="utf-8" />'
            .'<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">'
            .'<title>Subscribe</title>';
            wp_head();
        echo '</head>';
        echo '<body class="genooMobileWindow">';
        wp_footer();
        echo '</body></html>';
        // Kill it before WordPress does his shenanigans
        exit();
    }


    /**
     * Shutdown
     */
    public function shutdown(){}
}