<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

/**
 * This small lib is only for dev puproses.
 */

namespace Genoo\Wordpress;

use Genoo\Utils\Strings,
    Genoo\Wordpress\Post;

class Dummy
{

    /**
     * Dummy generated comments for pst
     *
     * @param $postId
     * @param int $count
     */

    public static function commentsForPost($postId, $count = 200)
    {
        if(Post::exists($postId)){
            for ($i = 1; $i <= $count; $i++){
                wp_insert_comment(array(
                    'comment_post_ID'		=> $postId,
                    'comment_author'		=> Strings::random(3, 'a-z') . $i . ' ' . $i . uniqid(null, true),
                    'comment_author_email'	=> Strings::random(5, 'a-z') . $i . uniqid(null, true) . '@' . $i . Strings::random(4,'a-z') . '.com',
                    'comment_auhor_url'		=> '',
                    'comment_content'		=> Strings::random(25,'a-z'),
                    'comment_type'			=> '',
                    'comment_date'			=> current_time('mysql'),
                    'comment_approved'		=> '1'
                ));
            }
        }
    }
}