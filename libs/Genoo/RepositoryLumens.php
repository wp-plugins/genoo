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


class RepositoryLumens extends Repository
{
    /** @var \Genoo\Cache */
    private $cache;
    /** @var \Genoo\Api */
    private $api;
    /** 3600 seconds = hour */
    const REPO_TIMER = '3600';
    /** cache namespace */
    const REPO_NAMESPACE = 'lumens';


    /**
     * @param Cache $cache
     */

    function __construct(\Genoo\Cache $cache, \Genoo\Api $api)
    {
        $this->cache = $cache;
        $this->api = $api;
        parent::__construct();
    }


    /**
     * Get Lumens class lists
     *
     * @return object|string
     */

    public function getLumens()
    {
        $prepLumens = '';
        try {
            if (!$prepLumens = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE)){
                $prepLumens = $this->api->getLumensClassList();
                $this->cache->set(self::REPO_NAMESPACE, $prepLumens, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        return $prepLumens;
    }


    /**
     * Get Lumen class list
     *
     * @param $id
     * @return bool|mixed
     */

    public function getLumen($id)
    {
        $prepLumen = '';
        try {
            if (!$prepLumen = $this->cache->get((string)$id, self::REPO_NAMESPACE)){
                $prepLumen = $this->api->getLumen($id);
                $this->cache->set((string)$id, $prepLumen, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        return $prepLumen;
    }


    /**
     * Get Forms Array
     *
     * @return array
     */

    public function getLumensArray()
    {
        $lumensVars = array();
        try{
            $lumens = $this->getLumens();
            if(!empty($lumens)){
                foreach($lumens as $lumen){
                    if(is_array($lumen) && !empty($lumen)){
                        $lumensVars[$lumen['id']] = $lumen['name'];
                    }
                }
            }
        } catch(\Exception $e){}

        return $lumensVars;
    }


    /**
     * Get forms for listing table
     *
     * @return array
     */

    public function getLumensTable()
    {
        $forms = array();
        $lumens = $this->getLumens();
        if(!empty($lumens)){
            foreach($lumens as $form){
                $form = (object)$form;
                $forms[] = array(
                    'id' => $form->id,
                    'name' => $form->name,
                );
            }
        }
        return $forms;
    }


    /**
     * @return bool
     */

    public function flush(){ return $this->cache->flush(self::REPO_NAMESPACE); }
}