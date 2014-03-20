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


class RepositoryForms extends Repository
{
    /** @var \Genoo\Cache */
    private $cache;
    /** @var \Genoo\Api */
    private $api;
    /** 3600 seconds = hour */
    const REPO_TIMER = '3600';
    /** cache namespace */
    const REPO_NAMESPACE = 'forms';


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
     * @return object|string
     */

    public function getForms()
    {
        $prepForms = '';
        try{
            if (!$prepForms = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE)){
                $prepForms = $this->api->getForms();
                $this->cache->set(self::REPO_NAMESPACE, $prepForms, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        return $prepForms;
    }


    /**
     * Get form, cached / or from API
     *
     * @param $id
     * @return bool|mixed
     */

    public function getForm($id)
    {
        $prepForm = '';
        try{
            if (!$prepForm = $this->cache->get((string)$id, self::REPO_NAMESPACE)){
                $prepForm = $this->api->getForm($id);
                $this->cache->set((string)$id, $prepForm, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        return $prepForm;
    }


    /**
     * Get Forms Array
     *
     * @return array
     */

    public function getFormsArray()
    {
        $formsVars = array();
        try{
            $forms = $this->getFormsTable();
            foreach($forms as $form){ $formsVars[$form['id']] = $form['name']; }
        } catch(\Exception $e){}

        return $formsVars;
    }


    /**
     * Get forms for listing table
     *
     * @return array
     */

    public function getFormsTable()
    {
        $forms = array();
        foreach($this->getForms() as $form){
            $form = (object)$form;
            $forms[] = array(
                'id' => $form->id,
                'name' => $form->name,
            );
        }
        return $forms;
    }


    /**
     * @return bool
     */

    public function flush(){ return $this->cache->flush(self::REPO_NAMESPACE); }
}