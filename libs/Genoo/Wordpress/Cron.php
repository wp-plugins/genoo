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

use Genoo\Api,
    Genoo\RepositorySettings;

class Cron
{
    /**
     * Run Cron
     */

    public static function cron($args)
    {
        if($args && is_array($args) && isset($args['action'])){
            switch($args['action']){
                case '':
                    break;
            }
        } else {
            try{
                // test Valid key
                $genooSettings = new RepositorySettings();
                $genooApi = new Api($genooSettings);
                // validate key, throw error in notices
                $genooApi->validate();
            } catch (ApiException $e){
                $genooSettings->addSavedNotice('error', $e->getMessage());
            }
        }
    }


    /**
     * Register cron
     *
     * @param $cron
     */

    public static function register($cron){ add_action($cron, array(__CLASS__, 'cron')); }


    /**
     * Activate WordPress cron job.
     *
     * @param $cron
     */

    public static function onActivate($cron){ self::schedule('daily', $cron); }


    /**
     * Deactivate next scheduled cron job
     *
     * @param $cron
     */

    public static function onDeactivate($cron){ wp_unschedule_event(wp_next_scheduled($cron), $cron); }


    /**
     * Schedule Cron Event
     *
     * @param $time
     * @param $cron
     * @param array $args
     */

    public static function scheduleSingle($time, $cron, array $args = array()){ wp_schedule_single_event($time, $cron, $args); }


    /**
     * Schedule repeating event
     *
     * @param $timing
     * @param $cron
     * @param array $args
     */

    public static function schedule($timing, $cron, array $args = array())
    {
        $times = array('hourly', 'twicedaily', 'daily');
        wp_schedule_event(time(), $timing, $cron, $args);
    }


    /**
     * Clears out given hook
     *
     * @param string $hookName
     */

    public static function unscheduleCronEvents($hookName)
    {
        $events = self::getEvents();
        error_reporting(0);
        if(!empty($events)){
            foreach($events as $time => $cron){
                if(!empty($cron)){
                    foreach($cron as $hook => $dings){
                        if($hook == $hookName){
                            foreach($dings as $sig => $data){
                                wp_unschedule_event($time, $hook, $data['args']);
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Get wordpress cron events
     *
     * @return mixed
     */

    public static function getEvents(){ return get_option('cron'); }
}