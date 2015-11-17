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

use Genoo\RepositorySettings;
use Genoo\Wordpress\Utils;
use Genoo\Wordpress\Filter;
use Genoo\Wordpress\Action;
use Genoo\ModalWindow;
use Genoo\HtmlForm;
use Genoo\Wordpress\Widgets;
use Genoo\Utils\Strings;
use Genoo\Wordpress\Debug;
use Genoo\Wordpress\Post;


class Frontend
{
    /** @var RepositorySettings */
    var $repositorySettings;
    /** @var array */
    var $footerCTAModals = array();
    /** @var Api */
    var $api;
    /** @var Cache */
    var $cache;

    /**
     * Construct Frontend
     *
     * @param RepositorySettings $repositorySettings
     * @param Api $api
     * @param Cache $cache
     */
    public function __construct(RepositorySettings $repositorySettings, Api $api, Cache $cache)
    {
        // Settings
        $this->repositorySettings = $repositorySettings;
        $this->api = $api;
        $this->cache = $cache;
        // Init
        Action::add('init',  array($this, 'init'));
        // wp
        Action::add('wp',    array($this, 'wp'), 999, 1);
        // Enqueue scripts
        Action::add('wp_enqueue_scripts', array($this, 'enqueue'), 1, 1);
        Action::add('wp_head', array($this, 'enqueueFirst'), 1, 1);
        // Footer
        Action::add('wp_footer', array($this, 'footerFirst'), 1);
        Action::add('wp_footer', array($this, 'footerLast'), 999);
        Action::add('shutdown', array($this, 'shutdown'), 10, 1);
    }


    /**
     * Init, rewrite rules for mobiles windows
     */
    public function init()
    {
        Filter::add('query_vars', function($query_vars){
            $query_vars[] = 'genooMobileWindow';
            $query_vars[] = 'genooIframe';
            $query_vars[] = 'genooIframeLumen';
            $query_vars[] = 'genooIframeCTA';
            return $query_vars;
        }, 10, 1);
        Action::add('parse_request', function($wp){
            // If is mobile window
            if(array_key_exists('genooMobileWindow', $wp->query_vars)){
                // Only when query parsed do this
                Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                Frontend::renderMobileWindow();
            }
            // If iframe load for backend, its safe to assume
            // that only logged in users will hava access to tinyMCE editor
            if(array_key_exists('genooIframe', $wp->query_vars) && is_user_logged_in()){
                // Only continue if file actually exists and iframe not empty
                if(!empty($wp->query_vars['genooIframe']) && file_exists(GENOO_ASSETS_DIR . $wp->query_vars['genooIframe'])){
                    // Since this could be potentionally hazardous, to display just any PHP file that is in the folder
                    // we will check if its GenooTinyMCE file first, just to be safe, and of course just those PHP iframe files, not any others.
                    if(Strings::startsWith($wp->query_vars['genooIframe'], 'GenooTinyMCE') && Strings::endsWith($wp->query_vars['genooIframe'], '.php')){
                        // No we have a winner.
                        Frontend::renderTinyMCEIframe($wp->query_vars['genooIframe']);
                    }
                }
            }
            // Genoo preview iframe
            if(array_key_exists('genooIframeLumen', $wp->query_vars) && is_user_logged_in()){
                // This workaround needs id and script source to dispaly the script
                if((isset($_GET['genooIframeLumenSrc']) && !empty($_GET['genooIframeLumenSrc'])) && (!empty($wp->query_vars['genooIframeLumen']))){
                    // Seems like a winner, display content
                    Frontend::renderPreviewLumenIframe($wp->query_vars['genooIframeLumen'], $_GET['genooIframeLumenSrc']);
                }
            }
            // Genoo preview iframe for CTA
            if(array_key_exists('genooIframeCTA', $wp->query_vars) && is_user_logged_in()){
                // This workaround needs id and script source to dispaly the script
                // Only when query parsed do this
                try {
                    error_reporting(0);
                    ini_set('error_reporting', 0);
                    // Set through widget
                    $widget = new \Genoo\WidgetCTA(false);
                    $widget->setThroughShortcode(1, $wp->query_vars['genooIframeCTA'], array());
                    $class = '';
                    if($widget->cta->popup['image-on']){
                        $image = wp_get_attachment_image($widget->cta->popup['image'], 'medium', FALSE);
                        if($image){
                            $class = 'genooModalPopBig';
                        }
                    }
                    // Set HTML
                    $r = '<div aria-hidden="false" id="genooOverlay" class="visible">';
                    $r .= '<div id="modalWindowGenoodynamiccta1" tabindex="-5" role="dialog" class="genooModal '. $class .' visible renderedVisible "><div class="relative">';
                    $r .= $widget->getHtml();
                    $r .= '</div></div>';
                    $r .= '</div>';
                    // Display!
                    //Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                    // TODO: Discover what's causing issue with the above line
                    Frontend::renderMobileWindow('Preview', $r, 'genooPreviewModal');
                } catch (\Exception $e){
                    echo $e->getMessage();
                }
                exit;
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
        // Firstly we do not run this anytime other then on real frontend
        if(Utils::isSafeFrontend()){
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
    }


    /**
     * Enqueue
     */
    public function enqueue()
    {
        // Frontend css
        wp_enqueue_style('genooFrontend', GENOO_ASSETS . 'GenooFrontend.css', NULL, GENOO_REFRESH);
        // Frontend js, if not a mobile window
        if(!isset($_GET['genooMobileWindow'])){
            wp_register_script('genooFrontendJs', GENOO_ASSETS . "GenooFrontend.js", FALSE, GENOO_REFRESH, FALSE);
            wp_enqueue_script('genooFrontendJs');
        }
    }


    /**
     * First
     */
    public function enqueueFirst()
    {
        // Tracking code
        if(GENOO_SETUP){
            $inHeader = apply_filters('genoo_tracking_in_header', FALSE);
            if($inHeader == TRUE){
                // Get repo
                echo $this->repositorySettings->getTrackingCode();
            }
        }
    }


    /**
     * Footer first
     */
    public function footerFirst()
    {
        // Tracking code
        if(GENOO_SETUP){
            $inHeader = apply_filters('genoo_tracking_in_header', FALSE);
            // Get repo
            if($inHeader == FALSE){
                echo $this->repositorySettings->getTrackingCode();
            }
            global $GENOO_STYLES;
            echo $GENOO_STYLES;
        }
    }


    /**
     * Footer last
     */
    public function footerLast()
    {
        // Get post / page
        global $post;
        // Prep
        $footerWidgetForms = Widgets::getFooterModals();
        $footerWidgetsDynamicForms = Widgets::getFooterDynamicModals($this->footerCTAModals);
        $footerShortcodeForms = Shortcodes::getFooterCTAs();
        $footerPopOverData = CTA::getFooterPopOvers();
        $footerForms = $footerWidgetForms + $footerWidgetsDynamicForms + $footerShortcodeForms + $footerPopOverData;
        // Prepare modals
        $footerModals = new ModalWindow();
        // footer widgtes
        if(!empty($footerForms)){
            // go through widgers
            foreach($footerForms as $id => $widget){
                if(method_exists($widget->widget, 'getHtml')){
                    // prep
                    $modalGuts = $widget->widget->getHtml(array(), $widget->instance);
                    $modalClass = '';
                    if(method_exists($widget->widget, 'getCTAModalClass')){
                        $modalClass = $widget->widget->getCTAModalClass($widget->instance);
                    }
                    if(!empty($modalGuts)){
                        // inject hidden inputs first
                        $modalGutsInject = new HtmlForm($modalGuts);
                        if(isset($widget->cta) && isset($widget->cta->followOriginalUrl) && ($widget->cta->followOriginalUrl == TRUE)){
                            // do not inject anything
                        } else {
                            $modalGutsInject->appendHiddenInputs(array('popup' => 'true', 'returnModalUrl' => ModalWindow::getReturnUrl($id)));
                        }
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
                        $footerModals->addModalWindow($id, $modalGutsInject, FALSE, $modalClass);
                    }
                }
            }
            // Add open modal javascript fro PopOver (if set)
            if(isset($footerPopOverData) && !empty($footerPopOverData)){
                // There can be olny one popOver on post page, so it's always the same id, here:
                $footerModals = $footerModals . WidgetForm::getModalOpenJavascript('modalWindowGenooctaShortcodepopover');
            }
            // print it out
            echo $footerModals;
        }
    }


    /**
     * Render mobile window
     *
     * @param string $subscribe
     * @param null $html
     */
    public static function renderMobileWindow($subscribe = 'Subscribe', $html = NULL, $bodyClass = '')
    {
        header('Content-Type: text/html; charset=utf-8');
        // Simple template
        echo '<!DOCTYPE html>'
            .'<html class="genooFullPage">'
            .'<head>'
            .'<meta charset="utf-8" />'
            .'<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">'
            .'<title>'. $subscribe .'</title>';
            wp_head();
        echo '</head>';
        echo '<body class="genooMobileWindow '. $bodyClass .'">';
        if(!is_null($html)){
            echo $html;
        }
        wp_footer();
        echo '</body></html>';
        // Kill it before WordPress does his shenanigans
        exit();
    }

    /**
     * @param $file
     */
    public static function renderTinyMCEIframe($file)
    {
        header('Content-Type: text/html; charset=utf-8');
        include_once GENOO_ASSETS_DIR . $file;
        exit();
    }

    /**
     * @param $id
     * @param $src
     */
    public static function renderPreviewLumenIframe($id, $src)
    {
        $src = Utils::nonProtocolUrl(base64_decode($src));
        echo '<script src="'. $src .'" type="text/javascript"></script>';
        echo '<div id="'. $id .'"></div>';
        exit();
    }

    /**
     * Shutdown
     */
    public function shutdown(){}
}