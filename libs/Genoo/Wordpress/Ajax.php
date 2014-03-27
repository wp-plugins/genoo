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

use Genoo\Utils\Strings,
    Genoo\Utils\Json,
    Genoo\RepositorySettings,
    Genoo\Wordpress\Comments,
    Genoo\Users,
    Genoo\Import;


class Ajax
{

    /**
     * Ajax register hook,
     * done automatically thru auto-wiring.
     */

    public static function register()
    {
        $methods = get_class_methods(__CLASS__);
        foreach ($methods as $method){
            // Is it "on" event, and not return?
            if(Strings::startsWith($method, 'on') && ($method != 'onReturn')){
                $methodAction = lcfirst(str_replace('on', '', $method));
                add_action('wp_ajax_' . $methodAction, array(__CLASS__, $method));
            }
        }
    }


    /**
     * Genoo import
     */

    public static function onGenooImportStart()
    {
        /**
         * $comments_count->moderated
         * $comments_count->approved
         * $comments_count->spam
         * $comments_count->trash
         * $comments_count->total_comments
         */

        $commentsCount = Comments::getCount();
        $commentsStatus = $commentsCount->approved > 0 ? true : false;
        if(!$commentsStatus){
            self::onReturn(array(
                'commentsMessage' => __('Unfortunately there are no comments to be imported.', 'genoo' ),
                'commentsStatus' => $commentsStatus
            ));
        } else {
            self::onReturn(array(
                'commentsMessage' => sprintf(__( 'We have found %1$s comment(s) to be imported.', 'genoo' ), $commentsCount->approved),
                'commentsStatus' => $commentsStatus,
                'commentsCount' => $commentsCount->approved
            ));
        }
    }


    /**
     * Genoo import comments - step based
     */

    public static function onGenooImportComments()
    {
        $import = new Import();
        self::onReturn(array(
            'messages' => $import->importComments(Comments::getAjaxComments($_REQUEST['per'], $_REQUEST['offset'])),
        ));
    }


    /**
     * Genoo start subscribers import
     */

    public static function onGenooImportSubscribersStart()
    {
        $subscribersCount = Users::getCount();
        $subscribersStatus = $subscribersCount > 0 ? true : false;
        if(!$subscribersStatus){
            self::onReturn(array(
                'message' => __('Unfortunately there are no subscribers to be imported.', 'genoo' ),
                'status' => $subscribersStatus
            ));
        } else {
            self::onReturn(array(
                'message' => sprintf(__( 'We have found %1$s subscriber(s) to be imported.', 'genoo' ), $subscribersCount),
                'status' => $subscribersStatus,
                'count' => $subscribersCount
            ));
        }
    }


    /**
     * Import subscribers
     */

    public static function onGenooImportSubscribers()
    {
        $import = new Import();
        self::onReturn(array(
            'messages' => $import->importSubscribers(
                Users::getAjaxUsers($_REQUEST['per'], $_REQUEST['offset']),
                $_REQUEST['leadType']
            ),
        ));

    }


    /**
     * Return
     *
     * @param $data
     */

    public static function onReturn($data)
    {
        @error_reporting(0); // don't break json
        header('Content-type: application/json');
        try{
            die(Json::encode($data));
        } catch (\Exception $e){} // as of this moment, we don't do anything with exceptions, it's ajax call
                                  // they would just break the thang
    }
}