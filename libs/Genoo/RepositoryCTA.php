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


use Genoo\Wordpress\Post;

class RepositoryCTA extends Repository
{
    /** @var \Genoo\Cache */
    private $cache;
    /** 3600 seconds = hour */
    const REPO_TIMER = '3600';
    /** cache namespace */
    const REPO_NAMESPACE = 'cta';


    /**
     * @param Cache $cache
     */

    function __construct(\Genoo\Cache $cache)
    {
        $this->cache = $cache;
        parent::__construct();
    }


    /**
     * Get all
     *
     * @return bool|mixed|null
     */

    public function getAll()
    {
        try {
            $ctas = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE);
        } catch(\Exception $e){}
        if($ctas){
            return $ctas;
        }
        return null;
    }

    /**
     * Get CTA
     *
     * @param $id
     * @return bool|mixed|null
     */

    public function get($id)
    {
        try {
            $cta = $this->cache->get((string)$id, self::REPO_NAMESPACE);
        } catch(\Exception $e){}
        if($cta){
            return $cta;
        }
        return null;
    }


    /**
     * Save
     *
     * @param $id
     * @param $data
     * @return bool
     */

    public function save($id, $data)
    {
        try {
            $this->cache->set((string)$id, $data, self::REPO_TIMER, self::REPO_NAMESPACE);
            return true;
        } catch(\Exception $e){
            return false;
        }
    }


    /**
     * Get array of ctas for JS
     *
     * @return array
     */

    public function getArray()
    {
        $r = array();
        $ctas = get_posts(array('post_type' => 'cta', 'posts_per_page' => -1));
        if($ctas){
            foreach($ctas as $cta){
                $r[$cta->ID] = $cta->post_title;
            }
        }

        return $r;
    }


    /**
     * @return bool
     */

    public function flush(){ return $this->cache->flush(self::REPO_NAMESPACE); }
}