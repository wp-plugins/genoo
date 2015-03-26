<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 */

namespace Genoo\Wordpress;


class Action
{

    /**
     * Add an Action
     *
     * @param $h            Hook
     * @param $f            Function
     * @param int $p        Priority
     * @param null $args    Arguments
     */

    public static function add($h, $f, $p = 10, $args = 1)
    {
        add_action($h, $f, $p, $args);
    }


    /**
     * Remove Action
     *
     * @param $t        Tag
     * @param $f        Function
     * @param null $p   Priority
     */

    public static function remove($t, $f, $p = null)
    {
        remove_action($t, $f, $p);
    }


    /**
     * Remove All
     *
     * @param $t        Tag
     * @param null $p   Priority
     */

    public static function removeAll($t, $p = null)
    {
        remove_all_actions($t, $p);
    }


    /**
     * Has Action?
     *
     * @param $t    Tag
     * @param $f    Function
     */

    public static function has($t, $f)
    {
        return has_action($t, $f);
    }


    /**
     * Run Action (do_action)
     *
     * @param $t    Tag
     * @param $args Args
     */

    public static function run($t, $args)
    {
        do_action($t, $args);
    }
}