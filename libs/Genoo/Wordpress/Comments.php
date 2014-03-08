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

use Genoo\Import;


class Comments
{
    /** const */
    const UNAPPROVED = false;

    /**
     * Preprocess comment
     *
     * Applied to the comment data prior to any other processing, when saving a new comment in the database.
     * Function arguments: comment data array, with indices:
     *
     * "comment_post_ID",
     * "comment_author",
     * "comment_author_email",
     * "comment_author_url",
     * "comment_content",
     * "comment_type", and "user_ID".
     */

    public static function register()
    {
        add_action('preprocess_comment', array(__CLASS__, 'preProcess'));
        add_action('transition_comment_status', array(__CLASS__, 'postProcess'), 10, 3);
        add_action('pre_comment_approved', array(__CLASS__, 'preApproved'), '99', 2);
    }


    /**
     * Pre-process comment
     *
     * @param $args
     * @return mixed
     */

    public static function preProcess($args){ return $args; }


    /**
     * Pre approved
     *
     * @param $approved
     * @param $commentdata
     * @return mixed
     */

    public static function preApproved($approved , $commentdata)
    {
        if($approved == 1){
            $comment = (object)$commentdata;
            $import = new Import();
            $import->importComment($comment);
        }
        return $approved;
    }


    /**
     * Post-process comment
     *
     * @param $new_status
     * @param $old_status
     * @param $originalComment
     */

    public static function postProcess($new_status, $old_status, $originalComment)
    {
        $comment = get_comment($originalComment->comment_ID);
        if($old_status != $new_status){
            if($new_status == 'approved'){
                $import = new Import();
                $import->importComment($comment);
            }
        }
    }


    /**
     * Get comments count, eihter post's or all
     *
     * @param null $postId
     * @return mixed
     */

    public static function getCount($postId = null){ return wp_count_comments(); }


    /**
     * Get single comment
     *
     * @param null $id
     * @return mixed
     */

    public static function getSingle($id = null){ return get_comment($id, OBJECT); }


    /**
     * Get approved comments
     *
     * @param $postId
     * @return mixed
     */

    public static function getApproved($postId){ return get_approved_comments($postID); }


    /**
     * Get all comments by
     *
     * @param array $args
     * @return mixed
     */

    public static function getAll(array $args = array()){ return get_comments($args); }


    /**
     * Get ajax comments
     *
     * @param $per
     * @param $offest
     * @return mixed
     */

    public static function getAjaxComments($per, $offest)
    {
        return get_comments(array(
            'status' => 'approve',
            'number' => (int)$per,
            'offset' => (int)$offest
        ));
    }
}