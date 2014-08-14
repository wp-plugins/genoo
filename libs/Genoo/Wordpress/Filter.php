<?php
/**
 * This file is part of the latorante theme framework
 *
 * Copyright (c) 2014 Martin Pícha (http://latorante.name)
 *
 * These handful of tools has been created to help to carve
 * and amend wordpress core to achieve a lot better Wordpress experience,
 * cleaner code, more theming flexibility and overall better code.
 *
 * If you're even reading this, it means you're not me, the guy who created it,
 * for that matter I can only say, use with love, may it serve you well my friend.
 */

namespace Genoo\Wordpress;

use Genoo\Utils\Strings;

class Filter
{
    /** @var  */
    static $tag;


    /**
     * Get filters
     */

    public static function filters()
    {
        global $wp_filter;
        $hooks = $wp_filter;
        ksort($hooks);
        return $hooks;
    }


    /**
     * Get all
     *
     * @return array
     */

    public static function getAll()
    {
        return self::filters();
    }


    /**
     * Get tag
     *
     * @param string $tag
     * @return null
     */

    public static function get($tag = '')
    {
        global $wp_filter;

        if (isset($wp_filter[$tag]) && is_array($wp_filter[$tag])){
            return $wp_filter[$tag];
        }
        return null;
    }


    /**
     * Add filter
     *
     * @param $tag
     * @param $f
     * @param int $p
     * @param null $args
     */

    public static function add($tag, $f, $p = 10, $args = null)
    {
        add_filter($tag, $f, $p, $args);
    }


    /**
     * Remove filter
     *
     * @param $tag
     * @param $f
     * @param int $p
     */

    public static function remove($tag, $f, $p = 10)
    {
        remove_filter($tag, $f, $p);
    }


    /** ----------------------------------------------------- */
    /**                   Static bindings                     */
    /** ----------------------------------------------------- */

    /**
     * Select
     *
     * @param string $tag
     * @return Filter
     */

    public static function select($tag = '')
    {
        self::$tag = $tag;
        return new static;
    }


    /**
     * Remove from (hook)
     *
     * @param string $tag
     * @return Filter
     */

    public static function removeFrom($tag = '')
    {
        self::select($tag);
        return new static;
    }


    /**
     * Everything Except %LIKE%
     *
     * @param string $like
     */

    public static function everythingExceptLike($like = null)
    {
        $filters = self::get(self::$tag);
        if($filters){
            // hooks, go thru
            foreach($filters as $priority => $hooks){
                // functions
                if(is_array($hooks)){
                    // go thru hooked functions
                    foreach($hooks as $hook){
                        // do we have a winner here?
                        // hook that is not like excpected one? is it string / array arg?
                        if(is_array($like)){
                            foreach($like as $lik){
                                $remove = false;
                                if(!Strings::contains((string)$hook['function'], (string)$lik)){
                                    $remove = true;
                                }
                            }
                            // none of those functions in array is the hold one, remove hook
                            if($remove){
                                self::remove(self::$tag, $hook['function'], $priority);
                            }
                        } elseif (is_string($like)){
                            if(!Strings::contains((string)$hook['function'], (string)$like)){
                                // remove hook
                                self::remove(self::$tag, $hook['function'], $priority);
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Removed already hooked functions,
     * followes after "removeFrom->" static binding
     *
     * @param $function
     */

    public static function hooked($function)
    {
        $filters = self::get(self::$tag);
        if($filters){
            // hooks, go thru
            foreach($filters as $priority => $hooks){
                if(is_array($hooks)){
                    // go thru hooked functions
                    foreach($hooks as $hook){
                        if(Strings::contains((string)$hook['function'], (string)$function)){
                            // remove hook
                            self::remove(self::$tag, $hook['function'], $priority);
                        }
                    }
                }
            }
        }
    }


    /**
     * Removes everything hooked to "action",
     * binds to "removeFrom->" to remove everything hooked there.
     */

    public static function everything()
    {
        $filters = self::get(self::$tag);
        if($filters){
            // hooks, go thru
            foreach($filters as $priority => $hooks){
                // functions
                if(is_array($hooks)){
                    // go thru hooked functions
                    foreach($hooks as $hook){
                        // do we have a winner here?
                        // hook that is not like excpected one? is it string / array arg?
                        self::remove(self::$tag, $hook['function'], $priority);
                    }
                }
            }
        }
    }
}