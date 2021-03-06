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


class Utils
{
    /**
     * Wordpress human_time_diff, returns "2 hours ago" etc.
     * - takes unix timestamp
     *
     * @param $from
     * @param $to
     * @return mixed
     */

    public static function timeToString($from, $to){ return human_time_diff($from, $to); }


    /**
     * For our porpuses, we just need to add API key to each call
     *
     * @param $url
     * @param $key
     * @param $value
     * @return mixed
     */

    public static function addQueryParam($url, $key, $value = null){ return add_query_arg($key, $value, $url); }


    /**
     * Add query params, in array
     *
     * @param $url
     * @param array $params
     * @return mixed
     */

    public static function addQueryParams($url, array $params = array()){ return add_query_arg($params, $url); }


    /**
     * Remove query parameter
     *
     * @param $url
     * @param $key
     * @return mixed
     */

    public static function removeQueryParam($url, $key){ return remove_query_arg($key, $url); }


    /**
     * Gets real server lastQuery
     *
     * @return string
     */

    public static function getRealUrl()
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
    }


    /**
     * Does what it says, converts camel case to underscore
     *
     * @param $string
     * @return string
     */

    public static function camelCaseToUnderscore($string){ return strtolower(preg_replace('/(?!^)[[:upper:]]/','_\0', $string)); }


    /**
     * Does what it says, converts underscore to camelcase
     *
     * @param $string
     * @param bool $firstCaps
     * @return mixed
     */

    public static function underscoreToCamelCase($string, $firstCaps = true)
    {
        if($firstCaps == true){$string[0] = strtoupper($string[0]); } $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $string);
    }


    /**
     * String to udnerscore
     *
     * @param $string
     * @return string
     */

    public static function toUnderscore($string){ return strtolower(preg_replace('/([a-z])([A-Z])/','$1_$2', $string)); }


    /**
     * Debug to console
     *
     * @param $data
     */

    public static function debugConsole($data){
        if (is_array($data)){
            $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
        } else {
            $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";
        }
        echo $output;
    }

    /**
     * Is iterable?
     *
     * @param $var
     * @return bool
     */
    public static function isIterable($var)
    {
        return $var !== null
                && (is_array($var)
                || $var instanceof \Traversable
                || $var instanceof \Iterator
                || $var instanceof \IteratorAggregate
        );
    }

    /**
     * @param $url
     * @return mixed
     */
    public static function nonProtocolUrl($url)
    {
        $http = self::isSecure() ? 'https://' : 'http://';
        return str_replace(
            array(
                'http://',
                'https://',
            ),
            array(
                $http,
                'https://'
            ),
            $url
        );
    }

    /**
     * @return bool
     */
    public static function isSecure()
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }


    /**
     * Does what it says
     *
     * @param $array
     * @return bool
     */
    public static function definedAndFalse($array)
    {
        $r = $array;
        if(is_array($r)){
            foreach($r as $constant){
                if (defined($constant) && constant($constant) == TRUE){
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     * @return bool
     */
    public static function isSafeFrontend()
    {
        return self::definedAndFalse(
            array(
                'DOING_AJAX',
                'DOING_AUTOSAVE',
                'DOING_CRON',
                'WP_ADMIN',
                'WP_IMPORTING',
                'WP_INSTALLING',
                'WP_UNINSTALL_PLUGIN',
                'IFRAME_REQUEST',
                '#WP_INSTALLING_NETWORK',
                'WP_NETWORK_ADMIN',
                'WP_LOAD_IMPORTERS',
                'WP_REPAIRING',
                'WP_UNINSTALL_PLUGIN',
                'WP_USER_ADMIN',
                'XMLRPC_REQUEST'
            )
        );
    }
}