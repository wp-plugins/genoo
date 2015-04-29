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
use Genoo\RepositoryUser;
use Genoo\RepositoryForms;
use Genoo\RepositoryLumens;
use Genoo\Api;
use Genoo\TableForms;
use Genoo\TableLumens;
use Genoo\Wordpress\Utils;
use Genoo\Wordpress\Settings;
use Genoo\Wordpress\Page;
use Genoo\Wordpress\Notice;
use Genoo\Wordpress\Nag;
use Genoo\Wordpress\Metabox;
use Genoo\Wordpress\PostType;
use Genoo\Wordpress\Action;
use Genoo\Wordpress\Filter;
use Genoo\Wordpress\TinyMCE;
use Genoo\Tools;
use Genoo\Utils\Strings;
use Genoo\Wordpress\Debug;
use Genoo\Wordpress\MetaboxCTA;


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
    /** @var \Genoo\RepositoryCTA  */
    var $repositaryCTAs;
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
        $this->repositaryCTAs = new RepositoryCTA($this->cache);
        // initialise settings and users
        Action::add('init', array($this, 'init'), 1);
        // admin constructor
        Action::add('current_screen', array($this, 'adminCurrentScreen'));
        Action::add('admin_init', array($this, 'adminInit'));
        Action::add('init', array($this, 'adminUI'));
        Action::add('init', array($this, 'adminPostTypes'));
        Action::add('admin_menu', array($this, 'adminMenu'));
        Action::add('admin_notices', array ($this, 'adminNotices'));
        Action::add('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'), 10, 1);
    }

    /**
     * Init variables
     */
    public function init()
    {
        // We first need to get these, but only after init,
        // to retrieve all custom post types correclty
        $this->user = new RepositoryUser();
        $this->settings = new Settings($this->repositarySettings, $this->api);
    }


    /**
     * Enqueue Scripts
     */

    public function adminEnqueueScripts($hook)
    {
        // scripts
        wp_enqueue_style('core', GENOO_ASSETS . 'GenooAdmin.css', null, GENOO_REFRESH);
        wp_enqueue_script('Genoo', GENOO_ASSETS . 'Genoo.js', null, GENOO_REFRESH, true);
        // if post edit or add screeen
        if($hook == 'post-new.php' || $hook == 'post.php'){
            wp_enqueue_script('GenooEditPost', GENOO_ASSETS . 'GenooEditPost.js', array('jquery'), GENOO_REFRESH);
        }
        // if setup up add vars
        if(GENOO_SETUP){
            wp_localize_script('Genoo', 'GenooVars', array(
                'GenooSettings' => array(
                    'GENOO_PART_SETUP' => GENOO_PART_SETUP,
                    'GENOO_SETUP' => GENOO_SETUP,
                    'GENOO_LUMENS' => GENOO_LUMENS
                ),
                'GenooPluginUrl' => GENOO_ASSETS,
                'GenooMessages'  => array(
                    'importing'  => __('Importing...', 'genoo'),
                ),
                'GenooTinyMCE' => array(
                    'themes' => $this->repositarySettings->getSettingsThemes(),
                    'forms'  => $this->repositaryForms->getFormsArray(),
                    'lumens' => $this->repositaryLumens->getLumensArray(),
                    'ctas'   => $this->repositaryCTAs->getArray(),
                    'cta-pt' => $this->repositarySettings->getCTAPostTypes()
                )
            ));
            // register editor styles
            TinyMCE::register($this->repositarySettings->getCTAPostTypes());
        }
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
            case 'widgets':
                wp_enqueue_media();
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

        Filter::add('plugin_action_links',   array($this, 'adminPluginLinks'), 10, 2);
        Filter::add('plugin_row_meta',       array($this, 'adminPluginMeta'),  10, 2);
    }


    /**
     * Admin Menu
     */

    public function adminMenu()
    {
        // Admin menus
        global $menu;
        global $submenu;
        // Admin Pages
        add_menu_page('Settings', 'Genoo', 'manage_options', 'Genoo', array($this, 'renderGenooSettings'), GENOO_ASSETS . 'bgMenuIconSingle.png', '71');
        if(GENOO_SETUP){
            add_submenu_page('Genoo', 'Forms', 'Forms', 'manage_options', 'GenooForms', array($this, 'renderGenooForms'));
            if(GENOO_LUMENS){ add_submenu_page('Genoo', 'Lumens', 'Lumens', 'manage_options', 'GenooLumens', array($this, 'renderGenooLumens')); }
            add_submenu_page('Genoo', 'Tools', 'Tools', 'manage_options', 'GenooTools', array($this, 'renderGenooTools'));
        }
        // Admin top menu order, find where are we
        if(GENOO_SETUP){
            if($menu){
                foreach($menu as $k => $m){
                    if(Strings::contains($m[2], 'edit.php?post_type=cta')){
                        $del = $k;
                        break;
                    }
                }
            }
            if(isset($del)){ unset($menu[$del]); }
            // Admin submenu, assing to Genoo
            if($submenu){
                // find correct submenu
                foreach($submenu as $k => $m){
                    if(Strings::contains($k, 'edit.php?post_type=cta')){
                        $ctaSubMenu = $m;
                    }
                }
                // remove it
                if(isset($submenu['edit.php?post_type=cta'])){ unset($submenu['edit.php?post_type=cta']); }
                // assign it to genoo
                foreach($submenu as $k => $m){
                    if(Strings::contains($k, 'Genoo')){
                        if($ctaSubMenu){
                            foreach($ctaSubMenu as $sMenuItem){
                                $submenu[$k][] = $sMenuItem;
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Admin post types
     */

    public function adminPostTypes()
    {
        if(GENOO_SETUP){
            // Post Type
            new PostType('cta',
                array(
                    'supports' => array('title'),
                    'label' => __('CTA\'s', 'genoo'),
                    'labels' => array(
                        'add_new' => __('New CTA', 'genoo'),
                        'not_found' => __('No CTA\'s found', 'genoo'),
                        'not_found_in_trash' => __('No CTA\'s found in Trash', 'genoo'),
                        'edit_item' => __('Edit CTA', 'genoo'),
                        'add_new_item' => __('Add Call-to-Action (CTA)', 'genoo'),
                    ),
                    'public' => false,
                    'exclude_from_search' => false,
                    'publicly_queryable' => false,
                    'show_ui' => true,
                    'show_in_nav_menus' => false,
                    'show_in_menu' => true,
                    'show_in_admin_bar' => false,
                )
            );
            // Add Post Type Columns
            PostType::columns('cta', array('cta_type' => 'Type'));
            // Add Post Type Columns Content
            PostType::columnsContent('cta', array('cta_type'));
        }
    }


    /**
     * Metaboxes
     */

    public function adminUI()
    {
        if(GENOO_SETUP){
            // Metaboxes
            new Metabox('Genoo CTA Info', 'cta',
                array(
                    array(
                        'type' => 'select',
                        'label' => __('CTA type', 'genoo'),
                        'options' => $this->repositarySettings->getCTADropdownTypes()
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Display CTA\'s', 'genoo'),
                        'options' => array(
                            '0' => __('No title and description', 'genoo'),
                            'titledesc' => __('Title and Description', 'genoo'),
                            'title' => __('Title only', 'genoo'),
                            'desc' => __('Description only', 'genoo'),
                        )
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Description', 'genoo'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Form', 'genoo'),
                        'options' => (array('' => '-- Select Form') + $this->repositaryForms->getFormsArray())
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Form Theme', 'genoo'),
                        'options' => ($this->repositarySettings->getSettingsThemes())
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Form success message', 'genoo'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Form error message', 'genoo'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Button URL', 'genoo'),
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => __('Open in new window?', 'genoo')
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Button Type', 'genoo'),
                        'options' => array(
                            'html' => __('HTML', 'genoo'),
                            'image' => __('Image', 'genoo'),
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Button Text', 'genoo'),
                    ),
                    array(
                        'type' => 'image-select',
                        'label' => __('Button Image', 'genoo')
                    ),
                    array(
                        'type' => 'image-select',
                        'label' => __('Button Hover Image', 'genoo')
                    ),
                    $this->repositarySettings->getLumensDropdown($this->repositaryLumens)
                )
            );
            // CTA metabox is now only legacy
            if(GENOO_LEGACY === TRUE){
                new Metabox('Genoo CTA', $this->repositarySettings->getCTAPostTypes(),
                    array(
                        array(
                            'type' => 'checkbox',
                            'label' => __('Enable CTA for this post', 'genoo')
                        ),
                        array(
                            'type' => 'select',
                            'label' => 'Select CTA',
                            'options' => $this->repositarySettings->getCTAs(),
                            'atts' => array('onChange' => 'Metabox.changeCTALink(this.options[this.selectedIndex].value)',)
                        ),
                    )
                );
            }
            new MetaboxCTA('Genoo Dynamic CTA', $this->repositarySettings->getCTAPostTypes(), array(), $this->repositarySettings->getCTAs());
        }
        return null;
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
            array_push($links, '<a target="_blank" href="http://wordpress.org/support/plugin/genoo">'. __('Support forum', 'genoo') .'</a>');
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