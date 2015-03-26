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


class Sidebars
{

    /**
     * Get All Sidebars
     *
     * @return mixed
     */

    public static function getAll()
    {
        global $wp_registered_sidebars;
        return $wp_registered_sidebars;
    }


    /**
     * Get all as id => name array
     *
     * @return array
     */
    public static function getSidebars()
    {
        $r = array();
        $sidebars = self::getAll();
        if($sidebars){
            $r[] = __('Select Sidebar', 'genoo');
            foreach($sidebars as $name => $sidebar){
                $r[$name] = $sidebar['name'];
            }
        }
        return $r;
    }


    /**
     * Does sidebar exists?
     *
     * @param $key
     * @return bool
     */

    public static function exists($key){ return array_key_exists($key, self::getAll()); }


    /**
     * Un-register
     *
     * @param $name
     */

    public static function unRegister($name)
    {
        unregister_sidebar($name);
    }


    /**
     * Register Widget
     *
     * @param $id
     * @param $name
     * @param $output_callback
     * @param array $options
     */

    public static function registerWidget($id, $name, $output_callback, $options = array())
    {
        wp_register_sidebar_widget($id, $name, $output_callback, $options);
    }


    /**
     * Unregister Sidebar Widget
     *
     * @param $id
     */

    public static function unRegisterWidget($id)
    {
        wp_unregister_sidebar_widget($id);
    }
}