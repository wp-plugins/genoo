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


class Redirect
{
    /** @var int */
    public static $code = 302;

    /**
     * Set code first before redirect
     *
     * @param $code
     * @return Redirect
     */

    public static function code($code)
    {
        static::$code = $code;
        return new static;
    }


    /**
     * Where do we redirect
     *
     * @param $url
     * @throws \InvalidArgumentException
     */

    public static function to($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)){
            wp_redirect($url, static::$code); exit;
        } else {
            throw new \InvalidArgumentException('Provided URL is not valid.');
        }
    }

}