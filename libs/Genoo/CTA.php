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
    /** @var   */
    public $post;
    /** @var \WP_Post */
    public $postObject;
    /** @var bool  */
    public $has = false;
    /** @var bool  */
    public $isForm = false;
    /** @var null  */
    public $formId = null;
    /** @var null */
    public $desc = null;
    /** @var bool */
    public $displayTitle = false;
    /** @var bool */
    public $displayDesc = false;
    /** @var null */
    public $title = null;
    /** @var null  */
    public $formTheme = null;
    /** @var bool  */
    public $isLink = false;
    /** @var bool  */
    public $isNewWindow = false;
    /** @var bool  */
    public $isImage = false;
    /** @var bool  */
    public $isHtml = false;
    /** @var null  */
    public $linkText = null;
    /** @var null  */
    public $link = null;
    /** @var null  */
    public $image = null;
    /** @var null  */
    public $imageHover = null;


    /**
     * Constructor
     */

    public function __construct($post = null)
    {
        $this->post = Post::set($post);
        $this->repositarySettings = new RepositorySettings();
        $this->postObject = $this->post->getPost();
        if($this->has()){
            $this->resolve();
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
        $postTypes = $this->repositarySettings->getCTAPostTypes();
        $this->has = false;
        if((!empty($postTypes) && (is_array($postTypes))) && ((in_array($this->postObject->post_type, $postTypes)) && (!empty($meta)))){
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
        $h = $this->post->getMeta('display_cta_s');
        $k = ($h == '0' || empty($h)) ? false : true;
        $this->isForm = $a == 'form' ? true : false;
        $this->formId = $h;
        $this->formTheme = $i;
        $this->isLink = $this->isForm ? false : true;
        $this->isNewWindow = $c == 'true' ? true : false;
        $this->isImage = $d == 'image' ? true : false;
        $this->isHtml = $this->isImage ? false : true;
        $this->linkText = $e;
        $this->link = $b;
        $this->image = $f;
        $this->imageHover = $g;
        $this->desc = $j;
        $this->title = $this->post->getTitle();
        $this->displayTitle = ($k == true && ($h == 'titledesc' || $h == 'title')) ? true : false;
        $this->displayDesc = ($k == true && ($h == 'titledesc' || $h == 'desc')) ? true : false;
    }
}