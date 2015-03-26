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


class RepositoryUser
{
    /** @var  */
    var $user;

    /**
     * Constructor
     */

    public function __construct(){ $this->user = wp_get_current_user(); }


    /**
     * Hide nag?
     *
     * @return bool
     */

    public function hideNag($option = '')
    {
        $nag = $this->getOption($option);
        if(isset($nag) && ($nag == 1)){
            return true;
        }
        return false;
    }


    /**
     * Get user Name
     *
     * @return mixed
     */

    public function getName(){ return $this->user->display_name; }

    /**
     * Get user Login
     *
     * @return mixed
     */

    public function getLogin(){ return $this->user->user_login; }


    /**
     * Get user Email
     *
     * @return mixed
     */

    public function getEmail(){ return $this->user->user_email; }


    /**
     * Get user ID
     *
     * @return mixed
     */

    public function getId(){ return $this->user->ID; }


    /**
     * Update meta
     *
     * @param $key
     * @param $value
     * @return mixed
     */

    public function updateMeta($key, $value){ return update_user_meta($this->user->ID, $key, $value); }


    /**
     * Get meta value
     *
     * @param $key
     * @return mixed
     */

    public function getMeta($key){ return update_user_meta($this->user->ID, $key, true); }


    /**
     * Insert meta
     *
     * @param $key
     * @param $value
     * @return mixed
     */

    public function insertMeta($key, $value){ return add_user_meta($this->user->ID, $key, $value); }


    /**
     * Delete meta
     *
     * @param $key
     * @return mixed
     */

    public function deleteMeta($key){ return delete_user_meta($this->user->ID, $key); }


    /**
     * Upadate option
     *
     * @param $key
     * @param $value
     * @return mixed
     */

    public function updateOption($key, $value){ return update_user_option($this->user->ID, $key, $value); }


    /**
     * Get option
     *
     * @param $key
     * @return mixed
     */

    public function getOption($key){ return get_user_option($key, $this->user->ID); }


    /**
     * Delete option
     *
     * @param $key
     * @return mixed
     */

    public function deleteOption($key){ return delete_user_option($this->user->ID, $key); }


    /**
     * Current user can
     *
     * @param null $what
     * @return mixed
     */

    public function userCan($what = null){ return current_user_can($what); }
}