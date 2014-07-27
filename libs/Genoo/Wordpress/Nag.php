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

use Genoo\RepositoryUser,
    Genoo\Wordpress\Utils;


class Nag
{
    /** @var should hold user repository */
    public static $repositaryUser;


    /**
     * Instance makes sure user repository is set
     */

    public static function instance()
    {
        if(!self::$repositaryUser instanceof RepositoryUser){
            self::$repositaryUser = new RepositoryUser();
        }
    }


    /**
     * Check GET keys
     *
     * @param $keys
     * @return Nag
     */

    public static function check($keys)
    {
        // global GET
        global $_GET;
        // get user repository
        self::instance();
        // check
        if(is_string($keys)){
            if(array_key_exists($keys, $_GET) && ($_GET[$keys] == '1') && (current_user_can('install_plugins'))){
                self::hide($key);
            }
        } elseif(is_array($keys)){
            foreach($keys as $key){
                if(array_key_exists($key, $_GET) && ($_GET[$key] == '1') && (current_user_can('install_plugins'))){
                    self::hide($key);
                }
            }
        }
        return new static;
    }


    /**
     * Hide nag (set user meta)
     *
     * @param $key
     */

    public static function hide($key)
    {
        // get user repository
        self::instance();
        // hide nag
        self::$repositaryUser->updateOption($key, 1);
        return new static;
    }


    /**
     * Returns back hiding generated hiding nag hideLink.
     *
     * @param $text
     * @param $key
     * @return string
     */

    public static function hideLink($text, $key)
    {
        $linkCurrentUrl = Utils::getRealUrl();
        $linkUrl = admin_url(Utils::addQueryParam(basename($linkCurrentUrl), $key, 1));
        return (string)'<a href="'. $linkUrl .'">' . $text . '</a>';
    }


    /**
     * Admin hideLink for nag
     *
     * @param $text
     * @param $page
     * @return string
     */

    public static function adminLink($text, $page)
    {
        return (string)'<a href="'. admin_url('admin.php?page=' . $page) .'">' . $text . '</a>';
    }


    /**
     * Is this nag visible?
     *
     * @param $key
     * @return bool
     */

    public static function visible($key)
    {
        // get user repository
        self::instance();
        return self::$repositaryUser->hideNag($key);
    }

}