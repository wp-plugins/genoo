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

use Genoo\Utils\Strings;


class Tracer
{

    /**
     * Was this file in trace of function leading to it?
     *
     * @param $filename
     * @return bool
     */

    public static function ranFrom($filename)
    {
        $trace = debug_backtrace();
        foreach($trace as $file){
            if(isset($file['file'])){
                if(Strings::endsWith($file['file'], '/' . $filename)){
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Debug
     *
     * @param $stuff
     * @return mixed
     */

    public static function debug($stuff)
    {
        if(class_exists('\Tracy\Debugger')){
            return \Tracy\Debugger::dump($stuff);
        }
        return false;
    }


    /**
     * Debug bar
     *
     * @param $stuff
     * @param null $title
     * @return bool
     */

    public static function debugBar($stuff, $title = null)
    {
        if(class_exists('\Tracy\Debugger')){
            return \Tracy\Debugger::barDump($stuff, $title);
        }
        return false;
    }
}