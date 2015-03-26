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

use Genoo\Wordpress\Action;

class Debug
{
    /** debug key */
    const DEBUG_KEY = 'genooDebugCheck';


    /**
     * Hooks check function
     */

    public function __construct(){ Action::add('shutdown', array(__CLASS__, 'checkFiredHooks')); }


    /**
     * Check fired hoooks
     */

    public static function checkFiredHooks()
    {
        // we only test front-end for these
        if(!is_admin()){
            $hooks['wp_footer'] = did_action('wp_footer') ? true : false;
            $hooks['wp_head'] = did_action('wp_head') ? true : false;
            update_option(self::DEBUG_KEY, $hooks);
        }
    }
}