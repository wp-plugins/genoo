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
use Genoo\Wordpress\Sidebars;
use Genoo\RepositorySettings;


class CTADynamic extends CTA
{
    /** @var RepositorySettings */
    public $repositarySettings;
    /** @var array */
    public $ctas = array();
    /** @var array */
    public $ctasRegister = array();


    /**
     * Constructor
     *
     * @param \WP_Post $post
     */

    public function __construct(\WP_Post $post)
    {
        $this->post = Post::set($post);
        $this->repositarySettings = new RepositorySettings();
        $this->postTypes = $this->repositarySettings->getCTAPostTypes();
        $this->postObject = $this->post->getPost();
        if($this->has()){
            $this->resolve();
        }
    }


    /**
     * Has dynamic CTA's?
     *
     * @return bool
     */

    public function has()
    {
        $meta = $this->post->getMeta('enable_cta_for_this_post_repeat');
        if(!empty($this->postTypes) && (is_array($this->postTypes)) && (in_array($this->postObject->post_type, $this->postTypes)) && $meta){
            $ctas = $this->post->getMeta('repeatable_genoo-dynamic-cta');
            if(!empty($ctas)){
                return true;
            }
            return false;
        }
        return false;
    }


    /**
     * Has multiple CTA's?
     *
     * @return bool
     */

    public function hasMultiple()
    {

        return !empty($this->ctas);
    }


    /**
     * Resolve
     */

    public function resolve()
    {
        $ctas = $this->post->getMeta('repeatable_genoo-dynamic-cta');
        foreach($ctas as $ct){
            // Does CTA and sidebar Exists?
            if(Post::exists($ct['cta']) && Sidebars::exists($ct['sidebar'])){
                $objCta = new CTA();
                $obj = (object)$ct;
                $obj->cta = $objCta->setCta($obj->cta);
                unset($obj->sidebar);
                $this->ctas[$ct['sidebar']][] = $obj;
                // Inject position before adding
                $obj->cta->position = (int)$ct['position'];
                $obj->cta->sidebar = $ct['sidebar'];
                $this->ctasRegister[] = $obj->cta;
            }
        }
        $this->has = $this->hasMultiple();
        $this->hasMultiple = $this->hasMultiple();
    }


    /**
     * Get CTAs
     *
     * @return array
     */

    public function getCtas()
    {
        return $this->ctas;
    }


    /**
     * Get ctas for Widgets::injectRegisterWidgets()
     *
     * @return array
     */

    public function getCtasRegister()
    {
        return $this->ctasRegister;
    }
}