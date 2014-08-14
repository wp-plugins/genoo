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
            $roles = $settings->getSavedRolesGuide();
            // check user role and add
            foreach($roles as $key => $leadId){
                if(Users::checkRole($key, $user_id)){
                    try{
                        $api = new Api($settings);
                        $api->setLead(
                            $leadId,
                            $user->user_email,
                            $user->first_name,
                            $user->last_name,
                            $user->user_url
                        );
                    } catch (\Exception $e){
                        $settings->addSavedNotice('error', __('Error adding Genoo lead while registering a new user: ', 'genoo') . $e->getMessage());
                    }
                    break;
                }
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


    /**
     * Checks if a particular user has a role.
     * Returns true if a match was found.
     *
     * @param string $role Role name.
     * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
     * @return bool
     */

    public static function checkRole($role, $user_id = null)
    {
        if (is_numeric($user_id))
            $user = get_userdata($user_id);
        else
            $user = wp_get_current_user();
        if (empty($user))
            return false;
        return in_array($role, (array)$user->roles);
    }
}