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

    public static function addQueryParam($url, $key, $value){ return add_query_arg($key, $value, $url); }


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
}