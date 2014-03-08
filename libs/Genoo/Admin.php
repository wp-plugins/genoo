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
    Genoo\RepositoryUser,
    Genoo\RepositoryForms,
    Genoo\RepositoryLumens,
    Genoo\Api,
    Genoo\TableForms,
    Genoo\TableLumens,
    Genoo\Wordpress\Utils,
    Genoo\Wordpress\Settings,
    Genoo\Wordpress\Page,
    Genoo\Wordpress\Notice,
    Genoo\Wordpress\Nag,
    Genoo\Tools;

class Admin
{
    /** @var bool */
    private static $instance = false;
    /** @var array Admin Messages */
    var $notices = array();
    /** @var \Genoo\RepositorySettings */
    var $repositarySettings;
    /** @var \Genoo\RepositoryForms */
    var $repositaryForms;
    /** @var \Genoo\RepositoryLumens */
    var $repositaryLumens;
    /** @var \Genoo\RepositoryUser */
    var $user;
    /** @var \Genoo\Api */
    var $api;
    /** @var \Genoo\Wordpress\Settings */
    var $settings;
    /** @var \Genoo\Cache */
    var $cache;
    /** @var \Genoo\TableForms */
    var $tableForms;
    /** @var \Genoo\TableLumens */
    var $tableLumens;


    /**
     * Constructor
     */

    public function __construct(\Genoo\Api $api = null, \Genoo\Cache $cache = null)
    {
        // vars
        $this->cache = $cache ? $cache : new Cache(GENOO_CACHE);
        $this->repositarySettings = new RepositorySettings();
        $this->api = $api ? $api : new Api($this->repositarySettings);
        $this->repositaryForms = new RepositoryForms($this->cache, $this->api);
        $this->repositaryLumens = new RepositoryLumens($this->cache, $this->api);
        $this->user = new RepositoryUser();
        $this->settings = new Settings($this->repositarySettings, $this->api);
        // admin constructor
        add_action('current_screen', array($this, 'adminCurrentScreen'));
        add_action('admin_init', array($this, 'adminInit'));
        add_action('admin_menu', array($this, 'adminMenu'));
        add_action('admin_notices', array($this, 'adminNotices'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
    }


    /**
     * Enqueue Scripts
     */

    public function adminEnqueueScripts()
    {
        // scripts
        wp_enqueue_style('core', GENOO_ASSETS . 'GenooAdmin.css', null, '1.0.2');
        wp_enqueue_script('Genoo', GENOO_ASSETS . 'Genoo.js', array(), '1.3', true);
        wp_localize_script('Genoo', 'GenooVars', array(
            'GenooPluginUrl' => GENOO_ASSETS,
            'GenooMessages'  => array(
                'importing'  => __('Importing...', 'genoo')
            ),
            'GenooTinyMCE' => array(
                'themes' => $this->repositarySettings->getSettingsThemes(),
                'forms'  => $this->repositaryForms->getFormsArray()
            )
        ));
    }


    /**
     * Current screen
     */

    public function adminCurrentScreen($currentScreen)
    {
        switch($currentScreen->id){
            case 'genoo_page_GenooForms':
                $this->tableForms = new TableForms($this->repositaryForms, $this->repositarySettings);
                break;
            case 'genoo_page_GenooLumens':
                $this->tableLumens = new TableLumens($this->repositaryLumens, $this->repositarySettings);
                break;
        }
    }


    /**
     * Admin Init
     */

    public function adminInit()
    {

        /**
         * 1. Check and hide user nag, if set + Check tool's requests
         */

        Nag::check(array('hideGenooNag', 'hideGenooApi'));
        Tools::check(array('genooActionImport', 'genooActionFlush', 'genooActionDelete', 'genooActionValidate', 'genooActionCheck'));

        /**
         * 2. Check if set up, display nag if not
         */

        if(!GENOO_SETUP && !Nag::visible('hideGenooNag')){
            $msgPluginLink = ' ' . Nag::adminLink(__('Genoo settings page.', 'genoo'), 'Genoo') . ' | ';
            $msgHideLink = Nag::hideLink(__('Hide this warning.', 'genoo'), 'hideGenooNag');
            $this->addNotice('error', sprintf(__('Genoo plugin requires setting up your API key, tracking code and comment lead type to run correctly.', 'genoo')) . $msgPluginLink . $msgHideLink);
        }

        /**
         * 3. Plugin meta links
         */

        add_filter('plugin_action_links',   array($this, 'adminPluginLinks'), 10, 2);
        add_filter('plugin_row_meta',       array($this, 'adminPluginMeta'),  10, 2);
    }


    /**
     * Admin Menu
     */

    public function adminMenu()
    {
        // Admin Pages
        add_menu_page('Settings', 'Genoo', 'manage_options', 'Genoo', array($this, 'renderGenooSettings'), NULL, '71.22');
        if(GENOO_SETUP){
            add_submenu_page('Genoo', 'Forms', 'Forms', 'manage_options', 'GenooForms', array($this, 'renderGenooForms'));
            if(GENOO_LUMENS){ add_submenu_page('Genoo', 'Lumens', 'Lumens', 'manage_options', 'GenooLumens', array($this, 'renderGenooLumens')); }
            add_submenu_page('Genoo', 'Tools', 'Tools', 'manage_options', 'GenooTools', array($this, 'renderGenooTools'));
        }
    }


    /** ----------------------------------------------------- */
    /**                      Renderers                        */
    /** ----------------------------------------------------- */

    /**
     * Renders Admin Page
     */

    public function renderGenooSettings()
    {
        echo '<div class="wrap"><h2>' . __('Genoo Settings', 'genoo') . '</h2>';
            $this->settings->render();
        echo '</div>';
    }


    /**
     * Renders Admin Page
     */

    public function renderGenooForms()
    {
        echo '<div class="wrap"><h2>' . __('Genoo Lead Capture Forms', 'genoo') . '</h2>';
            $this->tableForms->display();
        echo '</div>';
    }


    /**
     * Renders Lumens page
     */

    public function renderGenooLumens()
    {
        echo '<div class="wrap"><h2>' . __('Class Lists', 'genoo') . '</h2>';
            $this->tableLumens->display();
        echo '</div>';
    }


    /**
     * Renders Tools page
     */

    public function renderGenooTools()
    {
        $page = new Page();
        $page->addTitle(__('Genoo Tools', 'genoo'));
        $page->addWidget('Create Genoo Leads from WordPress Approved Comments.', Tools::getWidgetImport());
        $page->addWidget('Create Genoo Leads from WordPress	blog subscribers.', Tools::getWidgetImportSubscribers($this->api));
        $page->addWidget('Delete all cached files.', Tools::getWidgetDelete());
        $page->addWidget('Clear plugin Settings.', Tools::getWidgetFlush());
        $page->addWidget('Validate API key.', Tools::getWidgetValidate());
        $page->addWidget('Theme check.', Tools::getWidgetCheck());
        $page->addWidget('Bug Report Info.', Tools::getWidgetBug());
        echo $page;
    }


    /** ----------------------------------------------------- */
    /**                 Plugin meta links                     */
    /** ----------------------------------------------------- */

    /**
     * Plugin action links
     *
     * @param $links
     * @param $file
     * @return mixed
     */

    public function adminPluginLinks($links, $file)
    {
        if ($file == GENOO_FILE){
            array_push($links, '<a href="' . admin_url('admin.php?page=Genoo') . '">'. __('Settings', 'genoo') .'</a>');
        }
        return $links;
    }


    /**
     * Plugin meta links
     *
     * @param $links
     * @param $file
     * @return mixed
     */

    public function adminPluginMeta($links, $file)
    {
        if ($file == GENOO_FILE){
            // TODO: add REAL support link when ready
            array_push($links, '<a href="http://wordpress.org/support/plugin/genoo">'. __('Support forum', 'genoo') .'</a>');
        }
        return $links;
    }


    /** ----------------------------------------------------- */
    /**               Notification system                     */
    /** ----------------------------------------------------- */

    /**
     * Adds notice to the array of notices
     *
     * @param string $tag
     * @param string $label
     */

    public function addNotice($tag = 'updated', $label = ''){ $this->notices[] = array($tag, $label); }


    /**
     * Returns all notices
     *
     * @return array
     */

    public function getNotices(){ return $this->notices; }


    /**
     * Sends notices to renderer
     */

    public function adminNotices()
    {
        // notices saved in db
        $savedNotices = $this->repositarySettings->getSavedNotices();
        if($savedNotices){
            foreach($savedNotices as $value){
                if(array_key_exists('error', $value)){
                    $this->displayAdminNotice('error', $value['error']);
                } elseif(array_key_exists('updated', $value)){
                    $this->displayAdminNotice('updated', $value['updated']);
                }
                // flush notices after display
                $this->repositarySettings->flushSavedNotices();
            }
        }
        // notices saved in this object
        foreach($this->notices as $key => $value){
            $this->displayAdminNotice($value[0], $value[1]);
        }
    }


    /**
     * Display admin notices
     *
     * @param null $class
     * @param null $text
     */

    private function displayAdminNotice($class = NULL, $text = NULL){ echo Notice::type($class)->text($text); }


    /** ----------------------------------------------------- */
    /**                    Get instance                       */
    /** ----------------------------------------------------- */

    /**
     * Does what it says, get's instance
     *
     * @return bool|Admin
     */

    public static function getInstance()
    {
        if (!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }
}