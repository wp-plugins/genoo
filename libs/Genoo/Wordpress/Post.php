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


class Post
{

    /**
     * Check post exists
     *
     * @param $postId
     * @return bool
     */

    public static function exists($postId)
    {
        $post = get_post($postId);
        if(!empty($post)){
            return true;
        }
        return false;
    }
}