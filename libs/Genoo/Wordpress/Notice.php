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


class Notice
{
    /** @var string */
    public static $notice;
    /** @var string */
    public static $noticeType;
    /** @var string */
    public static $noticeText;
    /** @var array */
    public static $types = array('error', 'updated');


    /**
     * Type of message
     *
     * @param $type
     * @return Notice
     */

    public static function type($type)
    {
        if(!in_array($type, self::$types)){ $type = 'updated'; }
        static::$noticeType = $type;
        return new static;
    }


    /**
     * Actual text
     *
     * @param $text
     * @return Notice
     */

    public static function text($text)
    {
        self::$noticeText = $text;
        return new static;
    }


    /**
     * Renderer
     *
     * @return string
     */

    public function __toString()
    {
        return (string)('<div id="message" class="strong ' . static::$noticeType . '"><p>' . static::$noticeText . '</p></div>');
    }
}