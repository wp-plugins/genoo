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

use Genoo\RepositorySettings,
    Genoo\Api;

class Users
{
    /**
     * Add newly registered users to Genoo as a lead
     */

    public static function register()
    {
        add_action('user_register', function($user_id){
            $user = get_userdata($user_id);
            $settings = new RepositorySettings();
            try{
                $api = new Api($settings);
                $api->setLead(
                    $settings->getLeadType(),
                    $user->user_email,
                    $user->first_name,
                    '',
                    $user->user_url
                );
            } catch (\Exception $e){
                $settings->addSavedNotice('error', __('Error adding Genoo lead while registering a new user: ', 'genoo') . $e->getMessage());
            }
        }, 10, 1);
    }


    /**
     * Get count
     *
     * @param string $role
     * @return int
     */

    public static function getCount($role = 'subscriber')
    {
        return count(get_users(array('role' => $role)));
    }


    /**
     * Get users
     *
     * @param array $arr
     * @return mixed
     */

    public static function get($arr = array())
    {
        return get_users(array_merge(array('role' => 'subscriber'), $arr));
    }


    /**
     * Get ajax users
     *
     * @param $per
     * @param $offest
     * @return mixed
     */

    public static function getAjaxUsers($per, $offest)
    {
        return self::get(array(
            'offset' => (int)$offest,
            'number' => (int)$per,
        ));
    }
}