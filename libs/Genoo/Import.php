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

use Genoo\Api,
    Genoo\RepositorySettings;


class Import
{
    /** @var \Genoo\Api */
    private $api;
    /** @var \Genoo\RepositorySettings */
    private $settings;
    /** @var string */
    public $leadType;


    /**
     * Import constructor
     */

    public function __construct()
    {
        $this->settings = new RepositorySettings();
        $this->api = new Api($this->settings);
        $this->leadType = $this->settings->getLeadType();
    }


    /**
     * Import commnets
     *
     * @param $comments
     * @return array
     */

    public function importComments($comments)
    {
        // don't break us down lad
        @error_reporting(0);
        @ini_set('display_errors', 0);
        // return array
        $arr = array();
        // emails array, for no double entries
        $emails = array();
        // leads to post
        $leads = array();
        // activities to post
        $activities = array();
        // pre activites
        $leadsActivities = array();

        // if we have comments
        if(!empty($comments)){
            foreach($comments as $comment){
                // leads
                if(!in_array($comment->comment_author_email, $emails)){
                    $leads[] = array(
                        'email' => $comment->comment_author_email,
                        'first_name' => $comment->comment_author,
                        'web_site_url' => $comment->comment_author_url
                    );
                    $emails[] = $comment->comment_author_email;
                }
                // lead activites
                $activityDateTime = new \DateTime($comment->comment_date_gmt);
                $activityDate = $activityDateTime->format('c');
                $leadsActivities[$comment->comment_author_email][] = array(
                    'email' => $comment->comment_author_email, // email
                    'activity_date' => $activityDate, // Dates should be in the format that the field is set to or ISO 8601 format.
                    'activity_stream_type' => 'posted comment',
                    'activity_name' => get_the_title($comment->comment_post_ID), // title of post
                    'activity_description' => $comment->comment_content, // comment itself
                    'url' => get_permalink($comment->comment_post_ID) // url of post
                );
            }
            try {
                // set leads
                $apiResult = $this->api->setLeads($this->leadType, $leads);
                // get processed and set activity
                if(!empty($apiResult->process_results)){
                    foreach($apiResult->process_results as $result){
                        if($result->result == 'success'){
                            if(isset($leadsActivities[$result->email]) && !empty($leadsActivities[$result->email])){
                                $activities = array_merge($activities,$leadsActivities[$result->email]);
                            }
                            $arr[] = __('Comment lead imported', 'genoo') . ' email: '. $result->email;
                        }
                    }
                }
                // set activities
                $apiResultActivities = $this->api->postActivities($activities);
                // return info
                return $arr;
            } catch(\Exception $e){
                return array(__('Error while importing lead: ', 'genoo'). $e->getMessage());
            }
        }
        return array(__('No comments provided.', 'genoo'));
    }


    /**
     * Import subscribers
     *
     * @param $subscribers
     * @return array
     */

    public function importSubscribers($subscribers, $leadType)
    {
        // don't break us down lad
        @error_reporting(0);
        @ini_set('display_errors', 0);
        // return array
        $arr = array();
        // leads to post
        $leads = array();
        // leadtype check / fill
        $importLeadType = (isset($leadType) && is_numeric($leadType)) ? $leadType : $this->leadType;

        // if we have subscribers
        if(!empty($subscribers)){
            foreach($subscribers as $subscriber){
                // leads
                $leads[] = array(
                    'email' => $subscriber->data->user_email,
                    'first_name' => $subscriber->data->user_nicename,
                    'web_site_url' => $subscriber->data->user_url
                );
            }
            try {
                // set leads
                $apiResult = $this->api->setLeads($importLeadType, $leads);
                // get processed
                if(!empty($apiResult->process_results)){
                    foreach($apiResult->process_results as $result){
                        if($result->result == 'success'){
                            $arr[] = __('Subscriber lead imported', 'genoo') . ' email: '. $result->email;
                        }
                    }
                }
                // return info
                return $arr;
            } catch(\Exception $e){
                return array(__('Error while importing lead: ', 'genoo'). $e->getMessage());
            }
        }
        return array(__('No subscribers provided.', 'genoo'));
    }


    /**
     * Import single comment
     *
     * @param $comment
     */

    public function importComment($comment)
    {
        try {
            $apiEmail = $this->api->getLeadByEmail($comment->comment_author_email);
            if(!empty($apiEmail)){
                $this->api->putCommentActivity($apiEmail[0]->genoo_id, $comment);
            } else {
                $apiResult = $this->api->setLead(
                    $this->leadType,
                    $comment->comment_author_email,
                    $comment->comment_author,
                    '',
                    $comment->comment_author_url
                );
                $this->api->putCommentActivity($apiResult, $comment);
            }
            // we good?
        } catch(\Exception $e){
            // oops, just show it in admin i guess
            $settins = new RepositorySettings();
            $settins->addSavedNotice('error', __('Error while importing a lead:', 'genoo') . ' ' . $e->getMessage());
        }
    }
}