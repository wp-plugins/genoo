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

use Genoo\CTA;


class Post
{
    /** @var */
    public static $id;
    /** @var \WP_Post object */
    public static $post;


    /**
     * Is Post Id?
     *
     * @param $postId
     * @return mixed
     */

    public static function is($postId){ return is_post($postId); }


    /**
     * Is single
     *
     * @return mixed
     */

    public static function isSingle(){ return is_single(); }


    /**
     * Is Page
     *
     * @return mixed
     */

    public static function isPage(){ return is_page(); }


    /**
     * Is post type "this" type?
     *
     * @param \WP_Post $post
     * @param $type
     * @return bool
     */

    public static function isPostType(\WP_Post $post, $type)
    {
        if(is_string($type) && !empty($type)){
            return $post->post_type == $type;
        } elseif (is_array($type)){
            return in_array($post->post_type, $type);
        }
        return false;
    }


    /**
     * Set post
     *
     * @param $id
     * @return static
     * @throws \InvalidArgumentException
     */

    public static function set($id)
    {
        if(is_numeric($id) || is_string($id)){
            $i = $id;
            $post = get_post($id);
        } elseif (is_object($id) && ($id instanceof \WP_Post)){
            $i = $id->ID;
            $post = $id;
        } else {
            throw new \InvalidArgumentException('ID or Post object needs to be provided.');
        }
        self::$id = $i;
        self::$post = $post;
        return new static;
    }


    /**
     * Returns post
     *
     * @return \WP_Post
     */

    public static function getPost(){ return self::$post; }


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


    /**
     * Get post types
     *
     * @param array $args
     * @return mixe
     */

    public static function getTypes($args = array()){ return get_post_types(array_merge(array('public' => true, 'show_ui' => true), $args), 'objects'); }


    /**
     * Get meta
     *
     * @param $name
     * @return \InvalidArgumentException
     */

    public static function getMeta($name)
    {
        if(empty(self::$id)){
            return new \InvalidArgumentException('No post ID specified. Used method set first.');
        }
        return get_post_meta(self::$id, $name, true);
    }


    /**
     * Gettitle
     *
     * @return \InvalidArgumentException
     */

    public static function getTitle()
    {
        if(empty(self::$id)){
            return new \InvalidArgumentException('No post ID specified. Used method set first.');
        }
        return get_the_title(self::$id);
    }


    /**
     * Set meta
     *
     * @param $name
     * @param $value
     * @return \InvalidArgumentException
     */

    public static function setMeta($name, $value)
    {
        if(empty(self::$id)){
            return new \InvalidArgumentException('No post ID specified. Used method set first.');
        }
        return pdate_post_meta(self::$id, $name, $value);
    }
}