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
    Genoo\Wordpress\Action,
    Genoo\Tracer,
    Genoo\Api;

class Users
{
    /**
     * Add newly registered users to Genoo as a lead
     */

    public static function register(RepositorySettings $repositorySettings, Api $api)
    {
        // User Registration
        Action::add('user_register', function($user_id) use ($repositorySettings, $api){
            // Get user data
            $roles = $repositorySettings->getSavedRolesGuide();
            $user = get_userdata($user_id);
            // Check user role and add
            if($roles){
                foreach($roles as $key => $leadId){
                    if(Users::checkRole($key, $user_id)){
                        try{
                            $api->setLead(
                                $leadId,
                                $user->user_email,
                                $user->first_name,
                                $user->last_name,
                                $user->user_url
                            );
                        } catch (\Exception $e){
                            $repositorySettings->addSavedNotice('error', __('Error adding Genoo lead while registering a new user: ', 'genoo') . $e->getMessage());
                        }
                        break;
                    }
                }
            }
        }, 10, 1);

        // User role change
        Action::add('set_user_role', function($user_id, $role, $old_roles) use ($repositorySettings, $api){
            // Get user data
            $roles = $repositorySettings->getSavedRolesGuide();
            $user = get_userdata($user_id);
            // WP higher then 3.6
            if(isset($old_roles) && !empty($old_roles) && is_array($old_roles)){
                $leadtypes = array();
                // Do we have leadtypes to remove?
                foreach($old_roles as $roling){
                    if(array_key_exists($roling, $roles)){
                        $leadtypes[] = $roles[$roling];
                    }
                }
            }
            // Let's try this
            try {
                // Data
                $userEmail = $user->user_email;
                $userExisting = $api->getLeadByEmail($userEmail);
                $userNewLead = isset($roles[$role]) ? $roles[$role] : null;
                $userGenoo = $api->getLeadByEmail($userEmail);
                $userGenoo = Users::getUserFromLead($userGenoo);
                // Update
                if(!is_null($userGenoo) && !empty($leadtypes)){
                    // Leads, one or more?
                    $leadtypesFinal = count($leadtypes) == 1 ? $leadtypes[0] : $leadtypes;
                    // Existing User, remove from Leadtype
                    $api->removeLeadFromLeadtype($userGenoo->genoo_id, $leadtypesFinal);
                    // Add to leadtype
                    $api->setLeadUpdate($userGenoo->genoo_id, $userNewLead, $userEmail, $user->first_name, $user->last_name);
                } elseif(!is_null($userGenoo)){
                    // Update lead
                    $api->setLeadUpdate($userGenoo->genoo_id, $userNewLead, $userEmail, $user->first_name, $user->last_name);
                } else {
                    // set lead
                    $result = $api->setLead($userNewLead, $userEmail, $user->first_name, $user->last_name);
                }
            } catch (\Exception $e){
                $repositorySettings->addSavedNotice('error', __('Error changing Genoo user lead: ', 'genoo') . $e->getMessage());
            }
        }, 10, 3);
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
     * Get User from Genoo Lead
     *
     * @param $lead
     * @return null
     */

    public static function getUserFromLead($lead)
    {
        if(is_array($lead)){
            return $lead[0];
        }
        return null;
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