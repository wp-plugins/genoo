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

use Genoo\Wordpress\Post,
    Genoo\RepositorySettings;


class CTA
{
    /** @var \Genoo\RepositorySettings */
    private $repositarySettings;
    /** @var */
    public $post;
    /** @var \WP_Post */
    public $postObject;
    /** @var array post types */
    public $postTypes;
    /** @var bool */
    public $has = false;
    /** @var bool */
    public $hasMultiple = false;
    /** @var bool */
    public $isForm = false;
    /** @var null */
    public $formId = null;
    /** @var null */
    public $desc = null;
    /** @var bool */
    public $displayTitle = false;
    /** @var bool */
    public $displayDesc = false;
    /** @var null */
    public $title = null;
    /** @var null */
    public $formTheme = null;
    /** @var bool */
    public $isLink = false;
    /** @var bool */
    public $isNewWindow = false;
    /** @var bool */
    public $isImage = false;
    /** @var bool */
    public $isHtml = false;
    /** @var bool */
    public $isClasslist = false;
    /** @var null */
    public $linkText = null;
    /** @var null */
    public $link = null;
    /** @var null */
    public $image = null;
    /** @var null */
    public $imageHover = null;
    /** @var null */
    public $messageSuccess = null;
    /** @var null */
    public $messageError = null;
    /** @var null  */
    public $classList = null;

    /** @var null Position should be only set for dynamic CTAs */
    public $position = null;
    /** @var null Sidebar should be only set for dynamic CTAs */
    public $sidebar = null;


    /**
     * Constructor
     *
     * @param null $post
     */

    public function __construct($post = null)
    {
        // Don't override
        if($post != false){
            $this->post = Post::set($post);
            $this->repositarySettings = new RepositorySettings();
            $this->postTypes = $this->repositarySettings->getCTAPostTypes();
            $this->postObject = $this->post->getPost();
            if($this->has()){
                $this->resolve();
            }
        }
        return $this;
    }


    /**
     * Has CTA?
     *
     * @return bool
     */

    public function has()
    {
        $meta = $this->post->getMeta('enable_cta_for_this_post');
        $this->has = false;
        if((!empty($this->postTypes) && (is_array($this->postTypes))) && ((in_array($this->postObject->post_type, $this->postTypes)) && (!empty($meta)))){
            $p = $this->post->getMeta('select_cta');
            if(Post::exists($p)){
                $this->post = Post::set($p);
                $this->has = true;
                return true;
            }
        }
        return false;
    }


    /**
     * Set CTA post
     *
     * @param $postIs
     * @return $this
     */

    public function setCta($postId)
    {
        $this->post = Post::set($postId);
        $this->has = true;
        $this->resolve();
        return $this;
    }


    /**
     * Resolves current CTA
     */

    private function resolve()
    {
        $a = $this->post->getMeta('cta_type'); // link form
        $b = $this->post->getMeta('button_url');
        $c = $this->post->getMeta('open_in_new_window');
        $d = $this->post->getMeta('button_type'); // html image
        $e = $this->post->getMeta('button_text');
        $f = $this->post->getMeta('button_image');
        $g = $this->post->getMeta('button_hover_image');
        $h = $this->post->getMeta('form'); // form id
        $i = $this->post->getMeta('form_theme'); // form id
        $j = $this->post->getMeta('description'); // desc
        $z = $this->post->getMeta('display_cta_s');
        $a1 = $this->post->getMeta('class_list');
        $k = ($z == '0' || empty($z)) ? false : true;
        $this->messageSuccess = $this->post->getMeta('form_success_message');
        $this->messageError = $this->post->getMeta('form_error_message');
        $this->isForm = $a == 'form' ? true : false;
        $this->formId = $h;
        $this->formTheme = $i;
        $this->isClasslist = $a == 'class' ? true : false;
        $this->isLink = $this->isForm ? false : $this->isClasslist ? false : true;
        $this->isNewWindow = ($c == 'true') ? true : false;
        $this->isImage = $d == 'image' ? true : false;
        $this->isHtml = $this->isImage ? false : true;
        $this->classList = $this->isClasslist ? $a1 : null;
        $this->linkText = $e;
        $this->link = $b;
        $this->image = $f;
        $this->imageHover = $g;
        $this->desc = $j;
        $this->title = $this->post->getTitle();
        $this->displayTitle = ($k == true && ($z == 'titledesc' || $z == 'title')) ? true : false;
        $this->displayDesc = ($k == true && ($z == 'titledesc' || $z == 'desc')) ? true : false;
    }
}