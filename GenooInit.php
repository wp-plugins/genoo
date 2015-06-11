<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

use Genoo\RepositorySettings,
    Genoo\RepositoryUser,
    Genoo\Api,
    Genoo\Cache,
    Genoo\Wordpress\Widgets,
    Genoo\Shortcodes,
    Genoo\Users,
    Genoo\Frontend,
    Genoo\Admin,
    Genoo\Wordpress\Action,
    Genoo\Wordpress\Ajax,
    Genoo\Wordpress\Debug,
    Genoo\Wordpress\Comments,
    Genoo\Wordpress\Cron;

class Genoo
{
    /** @var \Genoo\RepositorySettings */
    private $repositarySettings;
    /** @var \Genoo\Api */
    private $api;
    /** @var \Genoo\Cache */
    private $cache;

    /**
     * Constructor, does all this beautiful magic, loads all libs
     * registers all sorts of funky hooks, checks stuff and so on.
     */

    public function __construct()
    {
        // Cosntants define
        define('GENOO_KEY',     'genoo');
        define('GENOO_FILE',    'genoo/Genoo.php');
        define('GENOO_CRON',    'genoo_cron');
        define('GENOO_LEGACY',  FALSE);
        define('GENOO_HOME_URL',get_option('siteurl'));
        define('GENOO_FOLDER',  plugins_url(NULL, __FILE__));
        define('GENOO_ROOT',    dirname(__FILE__) . DIRECTORY_SEPARATOR);
        define('GENOO_ASSETS',  GENOO_FOLDER . '/assets/');
        define('GENOO_ASSETS_DIR', GENOO_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        define('GENOO_CACHE',   GENOO_ROOT . 'cache' . DIRECTORY_SEPARATOR);
        define('GENOO_DEBUG',   get_option('genooDebug'));
        define('GENOO_REFRESH', sha1('genoo-works-around-security-protocols'));
        // start the engine last file to require, rest is auto
        // custom auto loader, PSR-0 Standard
        require_once('GenooRobotLoader.php');
        $classLoader = new GenooRobotLoader('Genoo', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR);
        $classLoader->register();
        // initialize
        $this->repositarySettings = new RepositorySettings();
        $this->api = new Api($this->repositarySettings);
        $this->cache = new Cache(GENOO_CACHE);
        // helper constants
        define('GENOO_PART_SETUP', $this->api->isSetup());
        define('GENOO_SETUP', $this->api->isSetupFull());
        define('GENOO_LUMENS', $this->api->isLumensSetup());
        // wp init
        Action::add('plugins_loaded', array($this, 'init'));
    }


    /**
     * Initialize
     */

    public function init()
    {
        /**
         * 0. Text-domain
         */

        load_plugin_textdomain('genoo', false, dirname(plugin_basename(__FILE__)) . '/lang/');

        /**
         * 1. Debug call?
         */

        if(GENOO_DEBUG){ new Debug(); }

        /**
         * 2. Register Widgets / Shortcodes / Cron, etc.
         */

        //Cron::register(GENOO_CRON);
        if(GENOO_SETUP){
            Ajax::register();
            Comments::register();
            Users::register($this->repositarySettings, $this->api);
            Widgets::register();
            Shortcodes::register();
        }

        /**
         * 3. Admin | Frontend
         */

        if(is_admin()){
            return new Admin($this->api, $this->cache);
        }
        return new Frontend($this->repositarySettings);
    }

    /** Activation hook */
    public static function activate(){ /*Cron::onActivate(GENOO_CRON);*/ }

    /** Deactivation hook */
    public static function deactivate() { /*Cron::onDeactivate(GENOO_CRON);*/ }
}

$genoo = new Genoo();